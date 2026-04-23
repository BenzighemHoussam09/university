<?php

namespace App\Livewire\Teacher\Questions;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Module;
use App\Models\Question;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Edit extends Component
{
    public Question $question;

    public string $text = '';

    /** @var array<int, string> */
    public array $choices = ['', '', '', ''];

    public int $correctIndex = 0;

    public ?int $moduleId = null;

    public string $level = '';

    public string $difficulty = '';

    public function mount(Question $question): void
    {
        $this->authorize('update', $question);

        $this->question = $question;
        $this->text = $question->text;
        $this->moduleId = $question->module_id;
        $this->level = $question->level->value;
        $this->difficulty = $question->difficulty->value;

        $questionChoices = $question->choices()->orderBy('position')->get();

        foreach ($questionChoices as $i => $choice) {
            $this->choices[$i] = $choice->text;
            if ($choice->is_correct) {
                $this->correctIndex = $i;
            }
        }
    }

    public function delete(): void
    {
        $this->authorize('delete', $this->question);
        $this->question->delete();
        $this->redirect(route('teacher.questions.index'), navigate: true);
    }

    public function save(): void
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

        $this->question->update([
            'module_id' => $this->moduleId,
            'level' => $this->level,
            'difficulty' => $this->difficulty,
            'text' => trim($this->text),
        ]);

        $existingChoices = $this->question->choices()->orderBy('position')->get();

        foreach ($existingChoices as $i => $choice) {
            $choice->update([
                'text' => trim($this->choices[$i]),
                'is_correct' => $i === $this->correctIndex,
            ]);
        }

        $this->redirect(route('teacher.questions.index'), navigate: true);
    }

    public function render(): View
    {
        return view('livewire.teacher.questions.edit', [
            'modules' => Module::orderBy('name')->get(),
            'levels' => Level::cases(),
            'difficulties' => Difficulty::cases(),
        ]);
    }
}
