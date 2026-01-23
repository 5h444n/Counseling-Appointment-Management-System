<?php

namespace App\Notifications;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class SystemNotice extends Notification
{
    use Queueable;

    public $notice;

    /**
     * Create a new notification instance.
     */
    public function __construct($notice)
    {
        $this->notice = $notice;
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
            'type' => 'system_notice',
            'title' => 'System Notice: ' . $this->notice->title,
            'message' => \Illuminate\Support\Str::limit($this->notice->content, 100),
            'notice_id' => $this->notice->id,
            'url' => '#', 
        ];
    }
}
