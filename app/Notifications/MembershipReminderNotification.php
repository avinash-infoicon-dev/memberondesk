<?php

namespace App\Notifications;

use App\Models\Member;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Notification;

class MembershipReminderNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public string $body) {}

    public function via(object $notifiable): array
    {
        return ['database'];
    }

    public function toArray(object $notifiable): array
    {
        return [
            'message' => $this->body,
            'member_id' => $notifiable instanceof Member ? $notifiable->id : null,
        ];
    }
}
