<?php

namespace App\Livewire\Teacher\Exams;

use App\Enums\ExamStatus;
use App\Models\Exam;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Index extends Component
{
    public function render(): View
    {
        $scheduled = Exam::where('status', ExamStatus::Scheduled)
            ->with('group.module')
            ->orderBy('scheduled_at')
            ->get();

        $active = Exam::where('status', ExamStatus::Active)
            ->with('group.module')
            ->orderBy('started_at', 'desc')
            ->get();

        $ended = Exam::where('status', ExamStatus::Ended)
            ->with('group.module')
            ->orderByDesc('ended_at')
            ->get();

        return view('livewire.teacher.exams.index', compact('scheduled', 'active', 'ended'));
    }
}
