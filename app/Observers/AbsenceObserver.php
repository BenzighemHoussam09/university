<?php

namespace App\Observers;

use App\Models\Absence;

class AbsenceObserver
{
    public function created(Absence $absence): void
    {
        $student = $absence->student()->withoutGlobalScopes()->firstOrFail();
        $student->increment('absence_count');
        $student->refresh();

        if (
            $student->absence_count >= config('exam.absence_threshold', 5)
            && is_null($student->blocked_at)
        ) {
            $student->blocked_at = now();
            $student->saveQuietly();
        }
    }

    public function deleted(Absence $absence): void
    {
        $student = $absence->student()->withoutGlobalScopes()->firstOrFail();
        $student->decrement('absence_count');
        $student->refresh();

        if (
            $student->absence_count < config('exam.absence_threshold', 5)
            && $student->blocked_at !== null
        ) {
            $student->blocked_at = null;
            $student->saveQuietly();
        }
    }
}
