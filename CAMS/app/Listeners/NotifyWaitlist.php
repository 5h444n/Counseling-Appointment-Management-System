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

        // Get all students on waitlist
        $entries = Waitlist::where('slot_id', $slot->id)
            ->with('student')
            ->get();

        foreach ($entries as $entry) {
            if ($entry->student) {
                // Notify via Database (and Mail if configured in Notification class)
                $entry->student->notify(new \App\Notifications\SlotAvailable($slot));
            }
        }

        if ($entries->isNotEmpty()) {
            Log::info("Waitlist: Notified {$entries->count()} students for freed slot {$slot->id}");
        }
    }
}
