<?php

namespace App\Notifications;

use App\Notifications\Channels\InPlatformChannel;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class StudentAccountCreated extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(
        public readonly string $plainPassword,
    ) {}

    public function via(object $notifiable): array
    {
        return ['mail', InPlatformChannel::class];
    }

    public function toMail(object $notifiable): MailMessage
    {
        return (new MailMessage)
            ->subject('Your exam platform account')
            ->line('An account has been created for you on the exam platform.')
            ->line('Email: '.$notifiable->email)
            ->line('Password: '.$this->plainPassword)
            ->line('Please log in and change your password as soon as possible.')
            ->action('Log in', url(route('student.login')));
    }

    public function toInPlatform(object $notifiable): array
    {
        return [
            'kind' => 'student_account_created',
            'payload' => [
                'login_url' => route('student.login'),
            ],
        ];
    }
}
