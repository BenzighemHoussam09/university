<?php

namespace App\Livewire\Teacher\Exams;

use App\Domain\Exam\Actions\CreateExamAction;
use App\Domain\Exam\Exceptions\BankTooSmallException;
use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Group;
use App\Models\Module;
use App\Models\Question;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Create extends Component
{
    public ?int $moduleId = null;

    public ?int $groupId = null;

    public string $title = '';

    public int $easyCount = 0;

    public int $mediumCount = 0;

    public int $hardCount = 0;

    public int $durationMinutes = 60;

    public string $scheduledAt = '';

    public function updatingModuleId(): void
    {
        $this->groupId = null;
    }

    public function save(): void
    {
        $levelValues = implode(',', array_column(Level::cases(), 'value'));

        $this->validate([
            'groupId' => 'required|integer|exists:groups,id',
            'title' => 'required|string|max:160',
            'easyCount' => 'required|integer|min:0',
            'mediumCount' => 'required|integer|min:0',
            'hardCount' => 'required|integer|min:0',
            'durationMinutes' => 'required|integer|min:1|max:600',
            'scheduledAt' => 'required|date|after:now',
        ]);

        if (($this->easyCount + $this->mediumCount + $this->hardCount) < 1) {
            $this->addError('easyCount', 'At least one question is required.');

            return;
        }

        $teacher = Auth::guard('teacher')->user();

        try {
            $exam = app(CreateExamAction::class)->handle($teacher, [
                'group_id' => $this->groupId,
                'title' => $this->title,
                'easy_count' => $this->easyCount,
                'medium_count' => $this->mediumCount,
                'hard_count' => $this->hardCount,
                'duration_minutes' => $this->durationMinutes,
                'scheduled_at' => $this->scheduledAt,
            ]);

            $this->redirect(route('teacher.exams.show', $exam), navigate: true);
        } catch (BankTooSmallException $e) {
            foreach ($e->deficits as $difficulty => $deficit) {
                $this->addError("bank.{$difficulty}", "لا توجد أسئلة {$difficulty} في البنك لهذا الفوج.");
            }
        }
    }

    public function render(): View
    {
        $modules = Module::orderBy('name')->get();

        $groups = Group::with('module')
            ->when($this->moduleId, fn ($q) => $q->where('module_id', $this->moduleId))
            ->orderBy('name')
            ->get();

        $bankCounts = ['easy' => 0, 'medium' => 0, 'hard' => 0];

        if ($this->groupId) {
            $group = $groups->firstWhere('id', $this->groupId)
                ?? Group::find($this->groupId);

            if ($group) {
                $teacher = Auth::guard('teacher')->user();
                foreach (Difficulty::cases() as $d) {
                    $bankCounts[$d->value] = Question::withoutGlobalScopes()
                        ->where('teacher_id', $teacher->id)
                        ->where('module_id', $group->module_id)
                        ->where('level', $group->level->value)
                        ->where('difficulty', $d->value)
                        ->count();
                }
            }
        }

        return view('livewire.teacher.exams.create', [
            'modules' => $modules,
            'groups' => $groups,
            'bankCounts' => $bankCounts,
        ]);
    }
}
