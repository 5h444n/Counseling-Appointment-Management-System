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
            ->paginate(20); // Pagination added for performance

        return view('advisor.slots', compact('slots'));
    }

    /**
     * Store new slots (The "Splitter" Logic).
     * Note: Date validation uses the server's configured timezone (UTC by default).
     * The view displays a message informing users about the timezone.
     */
    public function store(Request $request)
    {
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
            'recurrence_weeks' => 'nullable|integer|min:1|max:12',
            'days' => 'nullable|array', // Array of day indices (0=Sun, 1=Mon, etc.)
            'days.*' => 'integer|min:0|max:6',
        ]);

        $advisorId = Auth::id();
        $baseDate = Carbon::parse($request->date);
        $duration = (int) $request->duration;
        $isRecurring = $request->boolean('is_recurring');
        $weeks = $isRecurring ? (int) $request->recurrence_weeks : 0; // 0 weeks means just the single day
        $selectedDays = $request->input('days', []); // If empty, defaults to just the day of 'date' if recurring is off, or we need to handle "Recurring but no days selected" (default to base day)

        // If recurring but no days selected, default to the day of the start date
        if ($isRecurring && empty($selectedDays)) {
            $selectedDays = [$baseDate->dayOfWeek];
        }

        // 2. Parse Times to get the time component
        try {
            // We use these just to extract the time part
            $timeStart = Carbon::parse($request->start_time);
            $timeEnd = Carbon::parse($request->end_time);
        } catch (\Exception $e) {
            return redirect()->back()->with('error', 'Invalid time format.');
        }

        // Calculate the range of dates to process
        $startDate = $baseDate->copy();
        $endDate = $isRecurring ? $baseDate->copy()->addWeeks($weeks) : $baseDate->copy(); // If not recurring, end date is same as start

        $totalCreated = 0;
        $currentDate = $startDate->copy();

        // Loop through every day from start to end
        while ($currentDate->lte($endDate)) {
            
            // Should we generate slots for this day?
            // If not recurring, only process the specific start date.
            // If recurring, check if current day is in selectedDays.
            $processDay = false;
            
            if (!$isRecurring) {
                if ($currentDate->isSameDay($baseDate)) {
                    $processDay = true;
                }
            } else {
                if (in_array($currentDate->dayOfWeek, $selectedDays)) {
                    $processDay = true;
                }
            }

            if ($processDay) {
                // Construct start and end times for this specific date
                $slotStart = $currentDate->copy()->setTime($timeStart->hour, $timeStart->minute);
                $dayEndTime = $currentDate->copy()->setTime($timeEnd->hour, $timeEnd->minute);

                // Validation: Don't create slots in the past
                if ($slotStart->isFuture()) {
                    
                    // Generate slots for the day
                    while ($slotStart->copy()->addMinutes($duration)->lte($dayEndTime)) {
                        $slotEnd = $slotStart->copy()->addMinutes($duration);

                        // Check intersection
                        $exists = AppointmentSlot::where('advisor_id', $advisorId)
                            ->where('status', 'active')
                            ->where(function ($query) use ($slotStart, $slotEnd) {
                                $query->where('start_time', '<', $slotEnd)
                                      ->where('end_time', '>', $slotStart);
                            })
                            ->exists();

                        if (!$exists) {
                            AppointmentSlot::create([
                                'advisor_id' => $advisorId,
                                'start_time' => $slotStart->copy(),
                                'end_time' => $slotEnd,
                                'status' => 'active',
                                'is_recurring' => false,
                            ]);
                            $totalCreated++;
                        }

                        $slotStart->addMinutes($duration);
                    }
                }
            }

            $currentDate->addDay();
        }

        if ($totalCreated === 0) {
            return redirect()->back()->with('warning', "No new slots were created. Slots may already exist or dates are in the past.");
        }

        return redirect()->back()->with('success', "Successfully generated {$totalCreated} slot(s).");
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
