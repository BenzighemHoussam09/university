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

        // All exams the student has a session for, grouped by status
        $sessionExamIds = ExamSession::where('student_id', $student->id)
            ->pluck('exam_id');

        $upcoming = Exam::whereIn('id', $sessionExamIds)
            ->whereIn('status', [ExamStatus::Scheduled, ExamStatus::Active])
            ->with('group.module')
            ->orderBy('scheduled_at')
            ->get();

        $past = Exam::whereIn('id', $sessionExamIds)
            ->where('status', ExamStatus::Ended)
            ->with('group.module')
            ->orderByDesc('ended_at')
            ->get();

        return view('livewire.student.exams.index', compact('upcoming', 'past'));
    }
}
