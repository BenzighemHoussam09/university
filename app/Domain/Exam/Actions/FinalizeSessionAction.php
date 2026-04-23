<?php

namespace App\Domain\Exam\Actions;

use App\Domain\Exam\Events\SessionFinalized;
use App\Domain\Exam\Services\GradingService;
use App\Models\ExamSession;
use App\Models\GradeEntry;
use App\Models\GradingTemplate;
use App\Models\StudentAnswer;
use App\Models\Teacher;
use App\Notifications\ResultsAvailable;
use Illuminate\Support\Facades\DB;

/**
 * Finalizes an exam session: flips drafts → final, computes scores, upserts grade_entries.
 *
 * Per contracts/domain-actions.md §FinalizeSessionAction.
 */
class FinalizeSessionAction
{
    public function __construct(private readonly GradingService $gradingService) {}

    /**
     * @param  string  $reason  One of: manual|deadline|teacher_ended
     */
    public function handle(ExamSession $session, string $reason = 'manual'): void
    {
        DB::transaction(function () use ($session) {
            // 1. Flip all draft answers to final
            StudentAnswer::where('exam_session_id', $session->id)
                ->where('status', 'draft')
                ->update(['status' => 'final']);

            // 2. Count correct answers
            $answers = StudentAnswer::where('exam_session_id', $session->id)
                ->where('status', 'final')
                ->with('selectedChoice')
                ->get();

            $rawCorrect = $answers->filter(
                fn (StudentAnswer $a) => $a->selected_choice_id !== null
                    && $a->selectedChoice?->is_correct === true
            )->count();

            $total = $session->assignedQuestions()->count();

            // 3. Resolve exam_max from teacher's GradingTemplate (fall back to system default id=1 → 12)
            $session->loadMissing('exam.teacher');
            $teacher = $session->exam->teacher;
            $template = $this->resolveTemplate($teacher);
            $examMax = $template ? $template->exam_max : 12;

            // 4. Compute normalized exam component
            $examScoreComponent = $this->gradingService->computeExamComponent(
                $rawCorrect,
                $total,
                $examMax
            );

            // 5. Update session
            $session->update([
                'status' => 'completed',
                'completed_at' => now(),
                'exam_score_raw' => $rawCorrect,
                'exam_score_component' => $examScoreComponent,
            ]);

            // 6. Upsert grade_entries (student_id, module_id) with new exam_component; recompute final_grade
            $session->loadMissing('exam.group');
            $moduleId = $session->exam->group->module_id;

            $entry = GradeEntry::withoutGlobalScopes()->updateOrCreate(
                [
                    'student_id' => $session->student_id,
                    'module_id' => $moduleId,
                ],
                [
                    'teacher_id' => $session->exam->teacher_id,
                    'exam_component' => $examScoreComponent,
                ]
            );

            // Recompute final_grade as sum of all four components
            $entry->final_grade = $this->gradingService->computeFinalGrade($entry);
            $entry->save();
        });

        // Dispatch notification outside transaction (queued)
        $session->load('student');
        if ($session->student) {
            $session->student->notify(new ResultsAvailable($session));
        }

        SessionFinalized::dispatch($session->fresh(), $reason);
    }

    private function resolveTemplate(?Teacher $teacher): ?GradingTemplate
    {
        if ($teacher && $teacher->grading_template_id) {
            return GradingTemplate::withoutGlobalScopes()
                ->find($teacher->grading_template_id);
        }

        return GradingTemplate::withoutGlobalScopes()->find(1);
    }
}
