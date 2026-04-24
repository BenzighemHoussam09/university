<?php

namespace App\Listeners;

use App\Domain\Exam\Events\ExamStarted;
use App\Notifications\ExamStartedNotification;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Notification;

class NotifyStudentsOnExamStart implements ShouldQueue
{
    public function handle(ExamStarted $event): void
    {
        $students = $event->exam->group->students()->get();

        Notification::send($students, new ExamStartedNotification($event->exam));
    }
}
