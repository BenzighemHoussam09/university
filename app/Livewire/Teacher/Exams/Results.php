<?php

namespace App\Livewire\Teacher\Exams;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\StudentAnswer;
use Illuminate\Support\Collection;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Results extends Component
{
    public Exam $exam;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->authorize('view', $exam);
    }

    public function render(): View
    {
        $sessions = ExamSession::where('exam_id', $this->exam->id)
            ->where('status', 'completed')
            ->with('student')
            ->get();

        // Per-student score rows
        $rows = $sessions->map(fn (ExamSession $s) => [
            'student_id' => $s->student_id,
            'student_name' => $s->student->name,
            'raw' => $s->exam_score_raw ?? 0,
            'component' => $s->exam_score_component ?? 0.0,
        ]);

        $groupAverage = $rows->avg('component') ?? 0.0;

        // Top-10 most-missed questions
        $mostMissed = $this->computeMostMissed($this->exam->id);

        return view('livewire.teacher.exams.results', [
            'exam'         => $this->exam,
            'rows'         => $rows,
            'groupAverage' => round($groupAverage, 2),
            'highestScore' => round($rows->max('component') ?? 0.0, 2),
            'lowestScore'  => round($rows->min('component') ?? 0.0, 2),
            'passCount'    => $rows->where('component', '>=', 10)->count(),
            'mostMissed'   => $mostMissed,
        ]);
    }

    /**
     * Return the top 10 questions by wrong-answer rate across all completed sessions.
     */
    private function computeMostMissed(int $examId): Collection
    {
        $sessionIds = ExamSession::where('exam_id', $examId)
            ->where('status', 'completed')
            ->pluck('id');

        if ($sessionIds->isEmpty()) {
            return collect();
        }

        // Count incorrect (or unanswered) answers per question
        $assigned = ExamSessionQuestion::whereIn('exam_session_id', $sessionIds)
            ->with('question')
            ->get()
            ->groupBy('question_id');

        $results = collect();

        foreach ($assigned as $questionId => $assignments) {
            $total = $assignments->count();

            $correct = StudentAnswer::whereIn('exam_session_id', $sessionIds)
                ->where('question_id', $questionId)
                ->where('status', 'final')
                ->whereHas('selectedChoice', fn ($q) => $q->where('is_correct', true))
                ->count();

            $wrongRate = $total > 0 ? ($total - $correct) / $total : 0;

            $results->push([
                'question_id' => $questionId,
                'question_text' => $assignments->first()->question->text ?? '',
                'wrong_rate' => round($wrongRate, 3),
                'total' => $total,
                'correct' => $correct,
            ]);
        }

        return $results->sortByDesc('wrong_rate')->take(10)->values();
    }
}
