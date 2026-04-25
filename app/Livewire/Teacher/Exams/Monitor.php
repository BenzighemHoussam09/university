<?php

namespace App\Livewire\Teacher\Exams;

use App\Domain\Exam\Actions\EndExamAction;
use App\Domain\Exam\Actions\ExtendTimeAction;
use App\Domain\Exam\Services\HeartbeatMonitor;
use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\DB;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

/**
 * Teacher live-monitor page for an active exam.
 *
 * Polls every 5 seconds to refresh student statuses.
 * Dispatches the `student-disconnected` browser event when a student
 * transitions from connected → disconnected, which triggers an audio alert
 * in the companion Alpine monitor.js component.
 *
 * Per contracts/livewire-components.md §Teacher\Exams\Monitor.
 */
#[Layout('layouts.teacher')]
class Monitor extends Component
{
    public Exam $exam;

    /** Tracks which students were connected in the previous poll cycle. */
    public array $previouslyConnected = [];

    /** Tracks incident counts per student_id from the previous poll cycle. */
    public array $previousIncidentCounts = [];

    /** Tracks session status per student_id from the previous poll cycle. */
    public array $previousStatuses = [];

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->authorize('view', $exam);

        // Seed initial states so the first refresh() can detect transitions.
        $heartbeatMonitor = app(HeartbeatMonitor::class);
        ExamSession::where('exam_id', $this->exam->id)->get()
            ->each(function (ExamSession $session) use ($heartbeatMonitor) {
                $this->previouslyConnected[$session->student_id] = $heartbeatMonitor->isConnected($session);
                $this->previousIncidentCounts[$session->student_id] = $session->incidents()->count();
                $this->previousStatuses[$session->student_id] = $session->status instanceof \BackedEnum
                    ? $session->status->value
                    : (string) $session->status;
            });
    }

    /**
     * Called by wire:poll.5s — refreshes liveStatuses and fires audio alerts
     * when a student's connectivity transitions from connected to disconnected.
     */
    public function refresh(): void
    {
        $this->exam->refresh();

        $heartbeatMonitor = app(HeartbeatMonitor::class);
        $windowSeconds = config('exam.heartbeat_window_seconds', 25);

        $sessions = ExamSession::where('exam_id', $this->exam->id)
            ->with('student')
            ->get();

        $newConnectionStates = [];
        $newIncidentCounts = [];
        $newStatuses = [];

        foreach ($sessions as $session) {
            $currentStatus = $session->status instanceof \BackedEnum
                ? $session->status->value
                : (string) $session->status;

            $newStatuses[$session->student_id] = $currentStatus;
            $prevStatus = $this->previousStatuses[$session->student_id] ?? null;

            // Detect fresh submission: active → completed transition.
            if ($prevStatus === 'active' && $currentStatus === 'completed') {
                $this->dispatch('student-submitted', studentId: $session->student_id, studentName: $session->student?->name ?? '');
            }

            $connected = $heartbeatMonitor->isConnected($session);
            $newConnectionStates[$session->student_id] = $connected;

            // Skip connection and violation alerts for completed sessions.
            if ($currentStatus === 'completed') {
                $newIncidentCounts[$session->student_id] = $this->previousIncidentCounts[$session->student_id] ?? 0;
                continue;
            }

            $wasConnected = $this->previouslyConnected[$session->student_id] ?? null;

            // Dispatch event only on the connected → disconnected transition.
            if ($wasConnected === true && $connected === false) {
                $this->dispatch('student-disconnected', studentId: $session->student_id, studentName: $session->student?->name ?? '');
            }

            // Track new violations since last poll.
            $currentCount = $session->incidents()->count();
            $newIncidentCounts[$session->student_id] = $currentCount;
            $prevCount = $this->previousIncidentCounts[$session->student_id] ?? null;

            if ($prevCount !== null && $currentCount > $prevCount) {
                $latestIncident = $session->incidents()->latest('occurred_at')->first();
                $this->dispatch(
                    'student-violation',
                    studentId: $session->student_id,
                    studentName: $session->student?->name ?? '',
                    kind: $latestIncident?->kind instanceof \BackedEnum
                        ? $latestIncident->kind->value
                        : (string) ($latestIncident?->kind ?? ''),
                    total: $currentCount,
                );
            }
        }

        $this->previouslyConnected = $newConnectionStates;
        $this->previousIncidentCounts = $newIncidentCounts;
        $this->previousStatuses = $newStatuses;
    }

    /**
     * Add minutes to the exam's global extra time; recomputes all active session deadlines.
     */
    public function extendGlobal(int $minutes): void
    {
        $this->authorize('update', $this->exam);

        if ($minutes <= 0) {
            return;
        }

        app(ExtendTimeAction::class)->global($this->exam, $minutes);
        $this->exam->refresh();
    }

    /**
     * Add minutes to a single student's extra time.
     */
    public function extendStudent(int $studentId, int $minutes): void
    {
        $this->authorize('update', $this->exam);

        if ($minutes <= 0) {
            return;
        }

        $session = ExamSession::where('exam_id', $this->exam->id)
            ->where('student_id', $studentId)
            ->where('status', 'active')
            ->firstOrFail();

        app(ExtendTimeAction::class)->student($session, $minutes);
    }

    /**
     * End the exam: finalize all remaining active sessions and mark exam as ended.
     */
    public function endExam(): void
    {
        $this->authorize('update', $this->exam);

        app(EndExamAction::class)->handle($this->exam);

        $this->redirect(route('teacher.exams.results', $this->exam), navigate: true);
    }

    public function render(): View
    {
        $heartbeatMonitor = app(HeartbeatMonitor::class);

        $sessions = ExamSession::where('exam_id', $this->exam->id)
            ->with('student')
            ->get();

        // Last answered question index per session (max display_order with an answer)
        $lastAnsweredMap = $this->buildLastAnsweredMap($sessions->pluck('id')->all());

        $liveStatuses = $sessions->map(function (ExamSession $s) use ($heartbeatMonitor, $lastAnsweredMap) {
            $connected = $heartbeatMonitor->isConnected($s);

            $remainingSeconds = $s->deadline
                ? max(0, (int) now()->diffInSeconds($s->deadline, false))
                : null;

            return [
                'student_id' => $s->student_id,
                'name' => $s->student?->name ?? '—',
                'status' => $s->status instanceof \BackedEnum ? $s->status->value : (string) $s->status,
                'last_answered_question_index' => $lastAnsweredMap[$s->id] ?? null,
                'remaining_seconds' => $remainingSeconds,
                'connected' => $connected,
                'incident_count' => $s->incidents()->count(),
                'last_heartbeat_at' => $s->last_heartbeat_at?->toIso8601String(),
            ];
        });

        return view('livewire.teacher.exams.monitor', [
            'exam' => $this->exam,
            'liveStatuses' => $liveStatuses,
        ]);
    }

    /**
     * Build a map of [session_id => max display_order that has an answer].
     */
    private function buildLastAnsweredMap(array $sessionIds): array
    {
        if (empty($sessionIds)) {
            return [];
        }

        $rows = DB::table('exam_session_questions as esq')
            ->join('student_answers as sa', function ($join) {
                $join->on('sa.question_id', '=', 'esq.question_id')
                    ->whereColumn('sa.exam_session_id', 'esq.exam_session_id');
            })
            ->whereIn('esq.exam_session_id', $sessionIds)
            ->groupBy('esq.exam_session_id')
            ->select('esq.exam_session_id', DB::raw('MAX(esq.display_order) as last_order'))
            ->get();

        return $rows->pluck('last_order', 'exam_session_id')->all();
    }
}
