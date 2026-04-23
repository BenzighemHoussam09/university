<?php

namespace App\Domain\Exam\Actions;

use App\Models\ExamSession;
use App\Models\StudentAnswerIncident;

/**
 * Records a lockdown-violation incident during an active exam session.
 *
 * Does NOT change session status — the Monitor page reads aggregated counts.
 *
 * Per contracts/domain-actions.md §RecordLockdownIncidentAction.
 */
class RecordLockdownIncidentAction
{
    /**
     * @param  string  $kind  One of: visibility_hidden|window_blur|navigation_attempt
     */
    public function handle(ExamSession $session, string $kind): void
    {
        StudentAnswerIncident::create([
            'exam_session_id' => $session->id,
            'kind' => $kind,
            'occurred_at' => now(),
            'created_at' => now(),
        ]);
    }
}
