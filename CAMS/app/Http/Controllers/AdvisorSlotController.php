<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class AdvisorSlotController extends Controller
{
    /**
     * Display the availability manager.
     */
    public function index()
    {
        // Fetch slots for the logged-in advisor, sorted by date/time
        $slots = AppointmentSlot::where('advisor_id', Auth::id())
            ->where('start_time', '>=', now()) // Only show future slots
            ->orderBy('start_time', 'asc')
            ->get();

        return view('advisor.slots', compact('slots'));
    }

    /**
     * Store new slots (The "Splitter" Logic).
     * Note: Date validation uses the server's configured timezone (UTC by default).
     * The view displays a message informing users about the timezone.
     */
    public function store(Request $request)
    {
        // 1. Validate Input
        // Note: 'after_or_equal:today' uses the server's configured timezone (UTC by default)
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_time && $value <= $request->start_time) {
                    $fail('The end time must be after the start time.');
                }
            }],
            'duration' => 'required|integer|in:20,30,45,60',
        ]);

        $advisorId = Auth::id();
        $date = $request->date;

        $duration = (int) $request->duration;

        // 2. Parse Times
        try {
            $start = Carbon::parse("$date {$request->start_time}");
            $end = Carbon::parse("$date {$request->end_time}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date or time format provided.');
        }

        // Validate that the start time is in the future
        if ($start->isPast()) {
            return redirect()->back()->with('error', 'Cannot create slots in the past.');
        }

        // Validate that the time range is sufficient for at least one slot
        $totalMinutes = $start->diffInMinutes($end);
        if ($totalMinutes < $duration) {
            return redirect()->back()->with('error', "The time range must be at least {$duration} minutes for the selected duration.");
        }

        $count = 0;

        // 3. Fetch all existing overlapping slots once before the loop
        $existingSlots = AppointmentSlot::where('advisor_id', $advisorId)
            ->where('status', 'active')
            ->where('start_time', '<', $end)
            ->where('end_time', '>', $start)
            ->get();

        // 4. Loop: Create slots until we hit the end time
        while ($start->copy()->addMinutes($duration)->lte($end)) {

            $slotEnd = $start->copy()->addMinutes($duration);

            // Check for overlapping or duplicate slots in memory
            $overlap = $existingSlots->first(function($slot) use ($start, $slotEnd) {
                return $slot->start_time < $slotEnd && $slot->end_time > $start;
            });

            if (!$overlap) {
                AppointmentSlot::create([
                    'advisor_id' => $advisorId,
                    'start_time' => $start->copy(),
                    'end_time' => $slotEnd,
                    'status' => 'active',
                    'is_recurring' => false,
                ]);
                $count++;
            }

            // Move the start time forward
            $start->addMinutes($duration);
        }

        if ($count === 0) {
            return redirect()->back()->with('error', "No slots could be generated. All slots in this time range already exist.");
        }

        return redirect()->back()->with('success', "Successfully generated {$count} slot(s) for {$date}.");
    }

    /**
     * Delete a slot.
     */
    public function destroy($id)
    {
        $slot = AppointmentSlot::where('advisor_id', Auth::id())->findOrFail($id);

        // Only allow deleting if not booked
        if ($slot->status !== 'active') {
            return redirect()->back()->with('error', 'Cannot delete a booked slot. Please decline the appointment first.');
        }

        // Additional check: ensure no appointments are linked to this slot
        if ($slot->appointment()->exists()) {
            return redirect()->back()->with('error', 'Cannot delete a slot with an existing appointment.');
        }

        $slot->delete();

        return redirect()->back()->with('success', 'Slot removed successfully.');
    }
}
