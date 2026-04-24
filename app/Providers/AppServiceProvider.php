<?php

namespace App\Providers;

use App\Domain\Exam\Events\ExamStarted;
use App\Listeners\NotifyStudentsOnExamStart;
use App\Models\Absence;
use App\Models\Teacher;
use App\Observers\AbsenceObserver;
use App\Observers\TeacherObserver;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        Teacher::observe(TeacherObserver::class);
        Absence::observe(AbsenceObserver::class);

        Event::listen(ExamStarted::class, NotifyStudentsOnExamStart::class);
    }
}
