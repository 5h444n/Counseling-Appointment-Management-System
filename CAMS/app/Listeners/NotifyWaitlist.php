<?php

namespace App\Listeners;

use App\Events\SlotFreedUp;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use App\Models\Waitlist;
use App\Mail\SlotAvailableNotification;
use Illuminate\Support\Facades\Mail;
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
     */
    public function handle($event)
{
    $slot = $event->slot;

    // Find first student in line
    $entry = Waitlist::where('slot_id', $slot->id)->orderBy('created_at', 'asc')->first();

    if ($entry) {
        // Send Email
        Mail::to($entry->student->email)->send(new SlotAvailableNotification($slot, $entry->student));

        // Remove from list (or mark notified)
        $entry->delete();

        Log::info("Waitlist: Notified student {$entry->student_id} for slot {$slot->id}");
    }
}
}
