<?php

namespace App\Console\Commands;

use App\Models\Exam;
use App\Notifications\ExamReminder;
use Illuminate\Console\Command;

/**
 * Dispatches exam reminder notifications to students whose exam starts within
 * the configured reminder window. Per research.md Decision 2.
 *
 * Query window: scheduled_at BETWEEN now()+R-1min AND now()+R
 * where R = reminder_lead_minutes (default 30).
 * Idempotency guard: reminders_sent_at IS NULL (set after dispatch).
 */
class DispatchExamRemindersCommand extends Command
{
    protected $signature = 'app:dispatch-exam-reminders';

    protected $description = 'Dispatch exam reminder notifications to students whose exam starts soon.';

    public function handle(): int
    {
        $lead = config('exam.reminder_lead_minutes', 30);

        $windowStart = now()->addMinutes($lead - 1);
        $windowEnd = now()->addMinutes($lead);

        $exams = Exam::withoutGlobalScopes()
            ->whereBetween('scheduled_at', [$windowStart, $windowEnd])
            ->whereNull('reminders_sent_at')
            ->with('group.students')
            ->get();

        foreach ($exams as $exam) {
            $students = $exam->group->students ?? collect();

            foreach ($students as $student) {
                $student->notify(new ExamReminder($exam));
            }

            $exam->update(['reminders_sent_at' => now()]);
        }

        $this->info("Dispatched reminders for {$exams->count()} exam(s).");

        return self::SUCCESS;
    }
}
