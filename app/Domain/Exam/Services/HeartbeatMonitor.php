<?php

namespace App\Domain\Exam\Services;

use App\Models\ExamSession;

/**
 * Pure service that evaluates whether a student's session is still connected
 * based on the last received heartbeat timestamp.
 *
 * A session is considered disconnected when:
 *   last_heartbeat_at < now() - heartbeat_window_seconds
 *
 * Per research.md Decision 1 and config/exam.php.
 */
class HeartbeatMonitor
{
    public function isConnected(ExamSession $session): bool
    {
        if ($session->last_heartbeat_at === null) {
            return false;
        }

        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        return $session->last_heartbeat_at->gt(now()->subSeconds($windowSeconds));
    }
}
