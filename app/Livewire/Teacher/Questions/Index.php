<?php

namespace App\Livewire\Teacher\Questions;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Module;
use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;
use Livewire\WithPagination;

#[Layout('layouts.teacher')]
class Index extends Component
{
    use WithPagination;

    public ?int $moduleId = null;

    public ?string $level = null;

    public ?string $difficulty = null;

    public function updatingModuleId(): void
    {
        $this->resetPage();
    }

    public function updatingLevel(): void
    {
        $this->resetPage();
    }

    public function updatingDifficulty(): void
    {
        $this->resetPage();
    }

    public function delete(int $questionId): void
    {
        $question = Question::findOrFail($questionId);
        $this->authorize('delete', $question);
        $question->delete();
    }

    public function render(): View
    {
        $questions = Question::with(['module', 'choices'])
            ->when($this->moduleId, fn ($q) => $q->where('module_id', $this->moduleId))
            ->when($this->level, fn ($q) => $q->where('level', $this->level))
            ->when($this->difficulty, fn ($q) => $q->where('difficulty', $this->difficulty))
            ->orderByDesc('created_at')
            ->paginate(20);

        $modules = Module::orderBy('name')->get();

        return view('livewire.teacher.questions.index', [
            'questions' => $questions,
            'modules' => $modules,
            'levels' => Level::cases(),
            'difficulties' => Difficulty::cases(),
        ]);
    }
}
