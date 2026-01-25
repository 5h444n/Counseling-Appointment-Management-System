<?php

namespace App\Listeners;

use App\Events\SlotFreedUp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Waitlist;
use Illuminate\Support\Facades\Log;

class NotifyWaitlist
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     *
     * Processes the SlotFreedUp event by notifying the first student
     * in the waitlist queue via email and removing them from the list.
     *
     * @param SlotFreedUp $event The event containing the freed slot
     * @return void
     */
    public function handle(SlotFreedUp $event): void
    {
        $slot = $event->slot;

        // Get the first student in line (oldest entry)
        $firstEntry = Waitlist::where('slot_id', $slot->id)
            ->with('student')
            ->oldest()
            ->first();

        if ($firstEntry && $firstEntry->student) {
            // Send email notification to the first student
            \Illuminate\Support\Facades\Mail::to($firstEntry->student->email)
                ->queue(new \App\Mail\SlotAvailableNotification($slot, $firstEntry->student));
            
            // Remove the first student from waitlist
            $firstEntry->delete();
            
            Log::info("Waitlist: Notified student {$firstEntry->student->id} for freed slot {$slot->id} and removed from waitlist");
        }
    }
}
