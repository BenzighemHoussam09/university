<?php

namespace App\Livewire\Teacher\Questions;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionChoice;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Create extends Component
{
    public string $text = '';

    /** @var array<int, string> */
    public array $choices = ['', '', '', ''];

    public int $correctIndex = 0;

    public ?int $moduleId = null;

    public string $level = '';

    public string $difficulty = '';

    private function doSave(): void
    {
        $levelValues = implode(',', array_column(Level::cases(), 'value'));
        $difficultyValues = implode(',', array_column(Difficulty::cases(), 'value'));

        $this->validate([
            'text' => 'required|string|max:1000',
            'moduleId' => 'required|integer|exists:modules,id',
            'level' => "required|in:{$levelValues}",
            'difficulty' => "required|in:{$difficultyValues}",
            'choices' => 'required|array|size:4',
            'choices.*' => 'required|string|max:500',
            'correctIndex' => 'required|integer|min:0|max:3',
        ]);

        $question = Question::create([
            'teacher_id' => Auth::guard('teacher')->id(),
            'module_id' => $this->moduleId,
            'level' => $this->level,
            'difficulty' => $this->difficulty,
            'text' => trim($this->text),
        ]);

        foreach ($this->choices as $i => $choiceText) {
            QuestionChoice::create([
                'question_id' => $question->id,
                'text' => trim($choiceText),
                'is_correct' => $i === $this->correctIndex,
                'position' => $i + 1,
            ]);
        }
    }

    public function save(): void
    {
        $this->doSave();
        $this->redirect(route('teacher.questions.index'), navigate: true);
    }

    public function saveAndAnother(): void
    {
        $this->doSave();
        $this->text = '';
        $this->choices = ['', '', '', ''];
        $this->correctIndex = 0;
        $this->dispatch('question-saved');
    }

    public function render(): View
    {
        return view('livewire.teacher.questions.create', [
            'modules' => Module::orderBy('name')->get(),
            'levels' => Level::cases(),
            'difficulties' => Difficulty::cases(),
        ]);
    }
}
