<?php

namespace App\Http\Controllers;

use App\Http\Controllers\Controller;
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
     */
    public function store(Request $request)
    {
        // 1. Validate Input
        $request->validate([
            'date' => 'required|date|after_or_equal:today',
            'start_time' => 'required',
            'end_time' => 'required|after:start_time',
            'duration' => 'required|integer|in:15,20,30,45,60',
        ]);

        $advisorId = Auth::id();
        $date = $request->date;

        // --- THE FIX IS HERE: Force (int) casting ---
        $duration = (int) $request->duration;

        // 2. Parse Times
        $start = Carbon::parse("$date {$request->start_time}");
        $end = Carbon::parse("$date {$request->end_time}");

        $count = 0;

        // 3. Loop: Create slots until we hit the end time
        while ($start->copy()->addMinutes($duration)->lte($end)) {

            $slotEnd = $start->copy()->addMinutes($duration);

            AppointmentSlot::create([
                'advisor_id' => $advisorId,
                'start_time' => $start,
                'end_time' => $slotEnd,
                'status' => 'active',
                'is_recurring' => false,
            ]);

            // Move the start time forward
            $start->addMinutes($duration);
            $count++;
        }

        return redirect()->back()->with('success', "Successfully generated {$count} slots for {$date}.");
    }

    /**
     * Delete a slot.
     */
    public function destroy($id)
    {
        $slot = AppointmentSlot::where('advisor_id', Auth::id())->findOrFail($id);

        // Only allow deleting if not booked (optional safety check)
        if ($slot->status !== 'active') {
            return redirect()->back()->with('error', 'Cannot delete a booked slot.');
        }

        $slot->delete();

        return redirect()->back()->with('success', 'Slot removed successfully.');
    }
}
