<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlotAvailable extends Notification
{
    use Queueable;

    public $slot;

    /**
     * Create a new notification instance.
     */
    public function __construct($slot)
    {
        $this->slot = $slot;
    }

    /**
     * Get the notification's delivery channels.
     *
     * @return array<int, string>
     */
    public function via(object $notifiable): array
    {
        return ['database'];
    }

    /**
     * Get the array representation of the notification.
     *
     * @return array<string, mixed>
     */
    public function toArray(object $notifiable): array
    {
        return [
            'type' => 'slot_available',
            'title' => 'Waitlist Opportunity!',
            'message' => "Good news! A slot with " . $this->slot->advisor->name . " on " . $this->slot->start_time->format('M d, h:i A') . " has freed up.",
            'url' => route('student.advisors.show', $this->slot->advisor_id),
        ];
    }
}
