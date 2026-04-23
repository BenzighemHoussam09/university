<?php

namespace App\Domain\Exam\Actions;

use App\Domain\Exam\Services\DeadlineCalculator;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;

/**
 * Extends time for an entire exam (global) or a single student session.
 * Recomputes the deadline via DeadlineCalculator after incrementing the extra minutes.
 *
 * Per contracts/domain-actions.md §ExtendTimeAction.
 */
class ExtendTimeAction
{
    public function __construct(private readonly DeadlineCalculator $deadlineCalculator) {}

    /**
     * Add $minutes to exams.global_extra_minutes and recompute deadline for every active session.
     */
    public function global(Exam $exam, int $minutes): void
    {
        DB::transaction(function () use ($exam, $minutes) {
            $exam->increment('global_extra_minutes', $minutes);
            $exam->refresh(); // pick up the new global_extra_minutes in-memory

            ExamSession::where('exam_id', $exam->id)
                ->where('status', 'active')
                ->get()
                ->each(function (ExamSession $session) use ($exam) {
                    $session->setRelation('exam', $exam);
                    $session->update([
                        'deadline' => $this->deadlineCalculator->for($session),
                    ]);
                });
        });
    }

    /**
     * Add $minutes to exam_sessions.student_extra_minutes and recompute that session's deadline.
     */
    public function student(ExamSession $session, int $minutes): void
    {
        DB::transaction(function () use ($session, $minutes) {
            $session->increment('student_extra_minutes', $minutes);
            $session->refresh(); // pick up the new student_extra_minutes in-memory
            $session->loadMissing('exam');

            $session->update([
                'deadline' => $this->deadlineCalculator->for($session),
            ]);
        });
    }
}
