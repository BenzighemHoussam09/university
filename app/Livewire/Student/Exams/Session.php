<?php

namespace App\Livewire\Student\Exams;

use App\Domain\Exam\Actions\FinalizeSessionAction;
use App\Domain\Exam\Actions\RecordLockdownIncidentAction;
use App\Domain\Exam\Actions\SaveDraftAnswerAction;
use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\ExamSessionQuestion;
use App\Models\StudentAnswer;
use App\Models\StudentAnswerIncident;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Renderless;
use Livewire\Component;

/**
 * THE LOCKED EXAM PAGE.
 *
 * This page must be fully locked: no tab switching, no app switching.
 * All lockdown events are captured via Alpine.js (examSession.js) and
 * recorded server-side via recordIncident().
 *
 * Per contracts/livewire-components.md §Student\Exams\Session.
 */
#[Layout('layouts.exam-session')]
class Session extends Component
{
    public int $sessionId;

    public string $deadlineIso = '';

    public int $incidentCount = 0;

    /** @var array<int, int> Map of questionId → choiceId */
    public array $draftSelections = [];

    public function mount(Exam $exam): void
    {
        $student = Auth::guard('student')->user();

        $session = ExamSession::where('exam_id', $exam->id)
            ->where('student_id', $student->id)
            ->where('status', 'active')
            ->firstOrFail();

        $this->sessionId = $session->id;
        $this->deadlineIso = $session->deadline->toIso8601String();

        $session->update(['last_heartbeat_at' => now()]);

        // Load existing draft selections
        $this->draftSelections = StudentAnswer::where('exam_session_id', $session->id)
            ->pluck('selected_choice_id', 'question_id')
            ->map(fn ($v) => (int) $v)
            ->toArray();

        $this->incidentCount = StudentAnswerIncident::where('exam_session_id', $session->id)->count();
    }

    /**
     * Heartbeat — called every 10s by wire:poll.
     * Updates last_heartbeat_at and refreshes deadlineIso (picks up extensions).
     */
    #[Renderless]
    public function heartbeat(): void
    {
        $session = $this->getSession();

        if (! $session || $session->status !== 'active') {
            return;
        }

        $session->update(['last_heartbeat_at' => now()]);

        // Refresh deadline in case teacher extended time
        $session->refresh();
        if ($session->deadline) {
            $this->deadlineIso = $session->deadline->toIso8601String();
        }
    }

    /**
     * Save a draft answer (idempotent).
     */
    #[Renderless]
    public function saveDraft(int $questionId, int $choiceId): void
    {
        $session = $this->getSession();

        if (! $session || $session->status !== 'active') {
            return;
        }

        app(SaveDraftAnswerAction::class)->handle($session, $questionId, $choiceId);

        $this->draftSelections[$questionId] = $choiceId;
    }

    /**
     * Record a lockdown violation incident.
     *
     * @param  string  $kind  One of: visibility_hidden|window_blur|navigation_attempt
     */
    #[Renderless]
    public function recordIncident(string $kind): void
    {
        $validKinds = ['visibility_hidden', 'window_blur', 'navigation_attempt'];

        if (! in_array($kind, $validKinds, true)) {
            return;
        }

        $session = $this->getSession();

        if (! $session || $session->status !== 'active') {
            return;
        }

        app(RecordLockdownIncidentAction::class)->handle($session, $kind);

        $this->incidentCount++;
    }

    /**
     * Final submission — confirm all answers, finalize, redirect to results.
     */
    public function submitFinal(): void
    {
        $session = $this->getSession();

        abort_if(! $session || $session->status !== 'active', 422, 'Session is not active.');

        app(FinalizeSessionAction::class)->handle($session, 'manual');

        $this->redirect(
            route('student.exams.results', ['exam' => $session->exam_id]),
            navigate: true
        );
    }

    public function render(): View
    {
        $session = $this->getSession();

        // Load assigned questions with their choices
        $assignedQuestions = ExamSessionQuestion::where('exam_session_id', $this->sessionId)
            ->with('question.choices')
            ->orderBy('display_order')
            ->get()
            ->map(function (ExamSessionQuestion $esq) {
                // Shuffle choices with a session-stable hash ordering
                $choices = $esq->question->choices->sortBy(
                    fn ($c) => md5($this->sessionId.'-'.$esq->question_id.'-'.$c->id)
                )->values();

                return [
                    'display_order' => $esq->display_order,
                    'question' => $esq->question,
                    'choices' => $choices,
                ];
            });

        return view('livewire.student.exams.session', [
            'session' => $session,
            'assignedQuestions' => $assignedQuestions,
            'deadlineIso' => $this->deadlineIso,
            'incidentCount' => $this->incidentCount,
            'draftSelections' => $this->draftSelections,
        ]);
    }

    private function getSession(): ?ExamSession
    {
        return ExamSession::with('exam.group.module')->find($this->sessionId);
    }
}
