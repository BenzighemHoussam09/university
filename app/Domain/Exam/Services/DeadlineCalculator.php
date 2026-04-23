<?php

namespace App\Domain\Exam\Services;

use App\Models\ExamSession;
use Illuminate\Support\Carbon;

/**
 * Pure service for computing a session's finalization deadline.
 *
 * deadline = started_at + duration_minutes + global_extra_minutes + student_extra_minutes
 */
class DeadlineCalculator
{
    public function for(ExamSession $session): Carbon
    {
        return $session->started_at->copy()->addMinutes(
            $session->exam->duration_minutes
            + $session->exam->global_extra_minutes
            + $session->student_extra_minutes
        );
    }
}
