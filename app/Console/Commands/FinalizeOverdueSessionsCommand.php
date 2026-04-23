<?php

namespace App\Console\Commands;

use App\Domain\Exam\Actions\FinalizeSessionAction;
use App\Models\ExamSession;
use Illuminate\Console\Command;

/**
 * Finalizes exam sessions that have passed their deadline.
 *
 * Runs every 15 seconds (registered in routes/console.php).
 * In console context the BelongsToTeacher scope is bypassed — this command
 * must NOT call withoutGlobalScopes() since TeacherScope already no-ops
 * when no guard is authenticated.
 */
class FinalizeOverdueSessionsCommand extends Command
{
    protected $signature = 'app:finalize-overdue-sessions';

    protected $description = 'Finalize exam sessions that have passed their deadline';

    public function handle(FinalizeSessionAction $finalizeSession): int
    {
        $overdue = ExamSession::where('status', 'active')
            ->where('deadline', '<', now())
            ->get();

        foreach ($overdue as $session) {
            try {
                $finalizeSession->handle($session, 'deadline');
                $this->line("Finalized session {$session->id}");
            } catch (\Throwable $e) {
                $this->error("Failed to finalize session {$session->id}: {$e->getMessage()}");
            }
        }

        return self::SUCCESS;
    }
}
