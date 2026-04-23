<?php

namespace App\Livewire\Student\Exams;

use App\Models\Exam;
use App\Models\ExamSession;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Poll;
use Livewire\Component;

#[Layout('layouts.student')]
class Waiting extends Component
{
    public Exam $exam;

    public bool $started = false;

    public int $secondsUntilScheduled = 0;

    public function mount(Exam $exam): void
    {
        $this->exam = $exam;
        $this->computeState();
    }

    #[Poll('5s')]
    public function poll(): void
    {
        $this->exam->refresh();
        $this->computeState();

        if ($this->started) {
            $student = Auth::guard('student')->user();

            $session = ExamSession::where('exam_id', $this->exam->id)
                ->where('student_id', $student->id)
                ->where('status', 'active')
                ->first();

            if ($session) {
                $this->redirect(route('student.exams.session', ['exam' => $this->exam->id]), navigate: true);
            }
        }
    }

    private function computeState(): void
    {
        $this->started = $this->exam->status->value === 'active';
        $this->secondsUntilScheduled = max(0, (int) now()->diffInSeconds($this->exam->scheduled_at, false));
    }

    public function render(): View
    {
        return view('livewire.student.exams.waiting', [
            'exam' => $this->exam,
            'started' => $this->started,
            'secondsUntilScheduled' => $this->secondsUntilScheduled,
        ]);
    }
}
