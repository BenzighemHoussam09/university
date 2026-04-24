<?php

namespace App\Livewire\Student;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\GradeEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
class Dashboard extends Component
{
    public function render(): View
    {
        $student = Auth::guard('student')->user();

        $groupIds = $student->groups()->pluck('groups.id');

        $upcomingExams = Exam::whereIn('group_id', $groupIds)
            ->whereIn('status', [ExamStatus::Scheduled, ExamStatus::Active])
            ->with('group.module')
            ->orderByRaw("(status = 'active') DESC")
            ->orderBy('scheduled_at')
            ->limit(5)
            ->get();

        $completedCount = ExamSession::where('student_id', $student->id)
            ->where('status', 'completed')
            ->count();

        $recentResults = ExamSession::where('student_id', $student->id)
            ->where('status', 'completed')
            ->with('exam.group.module')
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        $avgGrade = GradeEntry::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->whereNotNull('final_grade')
            ->avg('final_grade');

        $avgGrade = $avgGrade !== null ? round((float) $avgGrade, 1) : null;

        $absenceThreshold = config('exam.absence_threshold', 5);
        $remaining = max(0, $absenceThreshold - $student->absence_count);

        return view('livewire.student.dashboard', compact(
            'student', 'upcomingExams', 'completedCount',
            'recentResults', 'avgGrade', 'remaining'
        ));
    }
}
