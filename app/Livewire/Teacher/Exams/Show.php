<?php

namespace App\Livewire\Teacher\Exams;

use App\Domain\Exam\Actions\StartExamAction;
use App\Enums\ExamStatus;
use App\Models\Exam;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Show extends Component
{
    public Exam $exam;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->authorize('view', $exam);
    }

    public function start(): void
    {
        $this->authorize('update', $this->exam);

        abort_if($this->exam->status !== ExamStatus::Scheduled, 422, 'Exam is not in scheduled state.');

        app(StartExamAction::class)->handle($this->exam);

        $this->redirect(route('teacher.exams.monitor', $this->exam), navigate: true);
    }

    public function render(): View
    {
        $this->exam->load(['group.module', 'group.students', 'sessions.student']);

        return view('livewire.teacher.exams.show', [
            'exam' => $this->exam,
            'sessions' => $this->exam->sessions,
        ]);
    }
}
