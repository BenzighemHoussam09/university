<?php

namespace App\Livewire\Student\Exams;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
class Index extends Component
{
    public function render(): View
    {
        $student = Auth::guard('student')->user();

        $groupIds = $student->groups()->pluck('groups.id');

        $sessionExamIds = ExamSession::where('student_id', $student->id)
            ->pluck('exam_id');

        // Upcoming: all exams in student's groups that are scheduled/active
        $upcoming = Exam::whereIn('group_id', $groupIds)
            ->whereIn('status', [ExamStatus::Scheduled, ExamStatus::Active])
            ->with('group.module')
            ->orderByRaw("(status = 'active') DESC")
            ->orderBy('scheduled_at')
            ->get();

        // Past: exams the student actually sat (has a session)
        $past = Exam::whereIn('id', $sessionExamIds)
            ->where('status', ExamStatus::Ended)
            ->with('group.module')
            ->orderByDesc('ended_at')
            ->get();

        return view('livewire.student.exams.index', compact('upcoming', 'past'));
    }
}
