<?php

namespace App\Notifications;

use App\Models\Exam;
use App\Notifications\Channels\InPlatformChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamReminder extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['mail', InPlatformChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $scheduledAt = $this->exam->scheduled_at->format('d M Y \a\t H:i');

        return (new MailMessage)
            ->subject('Upcoming exam: '.$this->exam->title)
            ->line('You have an exam scheduled soon.')
            ->line('Exam: '.$this->exam->title)
            ->line('Scheduled at: '.$scheduledAt)
            ->action('View exam', url(route('student.exams.waiting', $this->exam)));
    }

    public function toInPlatform(object $notifiable): array
    {
        return [
            'kind' => 'exam_reminder',
            'payload' => [
                'exam_id' => $this->exam->id,
                'exam_title' => $this->exam->title,
                'scheduled_at' => $this->exam->scheduled_at->toIso8601String(),
                'waiting_url' => route('student.exams.waiting', $this->exam),
            ],
        ];
    }
}
