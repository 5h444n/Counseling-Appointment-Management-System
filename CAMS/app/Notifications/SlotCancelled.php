<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SlotCancelled extends Notification
{
    use Queueable;

    /**
     * Create a new notification instance.
     */
    public $slotInfo; // Store basic info string

    /**
     * Create a new notification instance.
     */
    public function __construct($slotInfo)
    {
        $this->slotInfo = $slotInfo;
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
            'type' => 'slot_cancelled',
            'title' => 'Slot Removed',
            'message' => "A slot you were waitlisted for has been removed by the advisor: {$this->slotInfo}.",
            'url' => route('student.advisors.index'),
        ];
    }
}
