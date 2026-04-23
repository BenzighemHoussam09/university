<?php

namespace App\Notifications\Channels;

use App\Models\InPlatformNotification;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Notifications\Notification;

class InPlatformChannel
{
    /**
     * Send the given notification to the in-platform inbox.
     *
     * The notification class must implement toInPlatform($notifiable)
     * returning ['kind' => string, 'payload' => array].
     */
    public function send(object $notifiable, Notification $notification): void
    {
        $data = $notification->toInPlatform($notifiable);

        if ($notifiable instanceof Teacher) {
            $teacherId = $notifiable->id;
            $recipientType = 'teacher';
        } elseif ($notifiable instanceof Student) {
            $teacherId = $notifiable->teacher_id;
            $recipientType = 'student';
        } else {
            return;
        }

        InPlatformNotification::withoutGlobalScopes()->create([
            'teacher_id' => $teacherId,
            'recipient_type' => $recipientType,
            'recipient_id' => $notifiable->id,
            'kind' => $data['kind'],
            'payload' => $data['payload'] ?? [],
        ]);
    }
}
