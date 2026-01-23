<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use App\Models\AppointmentSlot;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;

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
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required|date_format:H:i',
            'end_time' => ['required', 'date_format:H:i', function ($attribute, $value, $fail) use ($request) {
                if ($request->start_time && $value <= $request->start_time) {
                    $fail('The end time must be after the start time.');
                }
            }],
            'duration' => 'required|integer|in:20,30,45,60',
            'is_recurring' => 'nullable|boolean',
            'recurrence_weeks' => 'nullable|integer|min:1|max:12', // Max 12 weeks
        ]);

        $advisorId = Auth::id();
        $date = $request->date;
        $duration = (int) $request->duration;
        $isRecurring = $request->boolean('is_recurring');
        $weeks = $isRecurring ? (int) $request->recurrence_weeks : 1;

        // 2. Parse Times
        try {
            $baseStart = Carbon::parse("$date {$request->start_time}");
            $baseEnd = Carbon::parse("$date {$request->end_time}");
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid date or time format provided.');
        }

        if ($baseStart->isPast()) {
            return redirect()->back()->with('error', 'Cannot create slots in the past.');
        }

        $totalMinutes = $baseStart->diffInMinutes($baseEnd);
        if ($totalMinutes < $duration) {
            return redirect()->back()->with('error', "The time range must be at least {$duration} minutes.");
        }

        $totalCreated = 0;

        // 3. Loop through weeks
        for ($w = 0; $w < $weeks; $w++) {
            // Calculate start/end for this week
            $currentStart = $baseStart->copy()->addWeeks($w);
            $currentEnd = $baseEnd->copy()->addWeeks($w);
            
            // Loop through time range for this day
            $slotStart = $currentStart->copy();
            
            while ($slotStart->copy()->addMinutes($duration)->lte($currentEnd)) {
                $slotEnd = $slotStart->copy()->addMinutes($duration);

                // Check for overlapping slots
                $exists = AppointmentSlot::where('advisor_id', $advisorId)
                    ->where('status', 'active')
                    ->where(function ($query) use ($slotStart, $slotEnd) {
                        $query->where(function ($q) use ($slotStart, $slotEnd) {
                            $q->where('start_time', '<', $slotEnd)
                              ->where('end_time', '>', $slotStart);
                        });
                    })
                    ->exists();

                if (!$exists) {
                    AppointmentSlot::create([
                        'advisor_id' => $advisorId,
                        'start_time' => $slotStart->copy(),
                        'end_time' => $slotEnd,
                        'status' => 'active',
                        'is_recurring' => false, // We store as individual slots
                    ]);
                    $totalCreated++;
                }

                $slotStart->addMinutes($duration);
            }
        }

        if ($totalCreated === 0) {
            return redirect()->back()->with('warning', "No new slots were created. Slots may already exist for these times.");
        }

        return redirect()->back()->with('success', "Successfully generated {$totalCreated} slot(s) over {$weeks} week(s).");
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

    /**
     * Delete multiple slots.
     */
    public function bulkDestroy(Request $request)
    {
        $request->validate([
            'slots' => 'required|array',
            'slots.*' => 'exists:appointment_slots,id',
        ]);

        $count = 0;
        foreach ($request->slots as $id) {
            $slot = AppointmentSlot::where('advisor_id', Auth::id())->find($id);
            
            if ($slot && $slot->status === 'active' && !$slot->appointment()->exists()) {
                $slot->delete();
                $count++;
            }
        }

        if ($count === 0) {
            return redirect()->back()->with('error', 'No valid slots could be deleted. They might be booked or already removed.');
        }

        return redirect()->back()->with('success', "{$count} slots removed successfully.");
    }
}
