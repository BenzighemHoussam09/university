<?php

namespace App\Domain\Exam\Actions;

use App\Domain\Exam\Events\ExamEnded;
use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;

/**
 * Ends an exam: finalizes every still-active student session, then marks the exam as ended.
 *
 * All DB writes are wrapped in a single transaction (all-or-nothing semantics per FR-041).
 * ExamEnded event is emitted after the transaction commits.
 *
 * Per contracts/domain-actions.md §EndExamAction.
 */
class EndExamAction
{
    public function __construct(private readonly FinalizeSessionAction $finalizeSession) {}

    public function handle(Exam $exam): void
    {
        $activeSessions = ExamSession::where('exam_id', $exam->id)
            ->where('status', 'active')
            ->get();

        DB::transaction(function () use ($exam, $activeSessions) {
            foreach ($activeSessions as $session) {
                $this->finalizeSession->handle($session, 'teacher_ended');
            }

            $exam->update([
                'status' => ExamStatus::Ended,
                'ended_at' => now(),
            ]);
        });

        ExamEnded::dispatch($exam->fresh());
    }
}
