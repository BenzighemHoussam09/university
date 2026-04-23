<?php

namespace App\Notifications;

use App\Models\ExamSession;
use App\Notifications\Channels\InPlatformChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class ResultsAvailable extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public readonly ExamSession $session) {}

    public function via(object $notifiable): array
    {
        return ['mail', InPlatformChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your exam results are available')
            ->line('Your results for "'.$this->session->exam->title.'" are now available.')
            ->line('Score: '.$this->session->exam_score_component.' / '.$this->session->exam->group->module->name)
            ->action('View results', url(route('student.exams.results', $this->session->exam)));
    }

    public function toInPlatform(object $notifiable): array
    {
        return [
            'kind' => 'results_available',
            'payload' => [
                'exam_id' => $this->session->exam_id,
                'exam_title' => $this->session->exam->title,
                'session_id' => $this->session->id,
                'results_url' => route('student.exams.results', $this->session->exam),
            ],
        ];
    }
}
