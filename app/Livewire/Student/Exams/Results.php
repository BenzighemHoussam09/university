<?php

namespace App\Livewire\Student\Exams;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\GradeEntry;
use App\Models\StudentAnswer;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.student')]
class Results extends Component
{
    public Exam $exam;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
    }

    public function render(): View
    {
        $student = Auth::guard('student')->user();

        $session = ExamSession::where('exam_id', $this->exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'completed')
            ->firstOrFail();

        $total = ExamSessionQuestion::where('exam_session_id', $session->id)->count();

        // Build per-question review
        $review = ExamSessionQuestion::where('exam_session_id', $session->id)
            ->with(['question.choices'])
            ->orderBy('display_order')
            ->get()
            ->map(function (ExamSessionQuestion $esq) use ($session) {
                $answer = StudentAnswer::where('exam_session_id', $session->id)
                    ->where('question_id', $esq->question_id)
                    ->first();

                $selectedChoice = $answer?->selected_choice_id
                    ? $esq->question->choices->firstWhere('id', $answer->selected_choice_id)
                    : null;

                $correctChoice = $esq->question->choices->firstWhere('is_correct', true);

                return [
                    'question_text' => $esq->question->text,
                    'selected_choice_text' => $selectedChoice?->text ?? '(no answer)',
                    'correct_choice_text' => $correctChoice?->text ?? '—',
                    'is_correct' => $selectedChoice?->is_correct ?? false,
                ];
            });

        $moduleId = $session->exam?->group?->module_id;
        $gradeEntry = $moduleId
            ? GradeEntry::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('module_id', $moduleId)
                ->first()
            : null;

        $finalGrade = $gradeEntry?->final_grade ?? $session->exam_score_component ?? 0.0;

        return view('livewire.student.exams.results', [
            'session' => $session,
            'review' => $review,
            'examComponent' => $session->exam_score_component ?? 0.0,
            'finalGrade' => $finalGrade,
            'rawCorrect' => $session->exam_score_raw ?? 0,
            'total' => $total,
        ]);
    }
}
