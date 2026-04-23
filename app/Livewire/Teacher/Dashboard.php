<?php

namespace App\Livewire\Teacher;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\Group;
use App\Models\InPlatformNotification;
use App\Models\Question;
use App\Models\Student;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Dashboard extends Component
{
    public function render(): View
    {
        $studentCount = Student::count();
        $groupCount = Group::count();
        $questionCount = Question::count();
        $examCount = 0;

        $upcomingExams = collect();
        $recentEndedExams = collect();

        if (Schema::hasTable('exams')) {
            $examCount = Exam::count();

            $upcomingExams = Exam::with('group.module')
                ->whereIn('status', [ExamStatus::Scheduled, ExamStatus::Active])
                ->orderBy('scheduled_at')
                ->limit(5)
                ->get();

            $recentEndedExams = Exam::with('group.module')
                ->where('status', ExamStatus::Ended)
                ->orderByDesc('ended_at')
                ->limit(4)
                ->get();
        }

        $recentNotifications = InPlatformNotification::withoutGlobalScopes()
            ->where('recipient_type', 'teacher')
            ->where('recipient_id', Auth::guard('teacher')->id())
            ->orderByDesc('created_at')
            ->limit(4)
            ->get();

        return view('livewire.teacher.dashboard', compact(
            'studentCount',
            'groupCount',
            'questionCount',
            'examCount',
            'upcomingExams',
            'recentEndedExams',
            'recentNotifications',
        ));
    }
}
