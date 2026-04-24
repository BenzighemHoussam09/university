<?php

namespace App\Notifications;

use App\Models\Exam;
use App\Notifications\Channels\InPlatformChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ExamStartedNotification extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly Exam $exam) {}

    public function via(object $notifiable): array
    {
        return ['mail', InPlatformChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('الامتحان بدأ الآن: '.$this->exam->title)
            ->line('بدأ الامتحان الآن. انضم فوراً.')
            ->line('الامتحان: '.$this->exam->title)
            ->action('دخول الامتحان', url(route('student.exams.session', $this->exam)));
    }

    public function toInPlatform(object $notifiable): array
    {
        return [
            'kind' => 'exam_started',
            'payload' => [
                'exam_id'     => $this->exam->id,
                'exam_title'  => $this->exam->title,
                'session_url' => route('student.exams.session', $this->exam),
            ],
        ];
    }
}
