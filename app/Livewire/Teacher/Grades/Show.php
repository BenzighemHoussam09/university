<?php

namespace App\Livewire\Teacher\Grades;

use App\Domain\Exam\Exceptions\InvalidGradingTemplateException;
use App\Domain\Exam\Services\GradingService;
use App\Models\GradeEntry;
use App\Models\GradingTemplate;
use App\Models\Group;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Show extends Component
{
    public Group $group;

    /** @var array<int, array<string, mixed>> */
    public array $rows = [];

    public string $successMessage = '';

    public string $errorMessage = '';

    public function mount(Group $group): void
    {
        $this->group = $group;
        $this->loadRows();
    }

    private function loadRows(): void
    {
        $teacher = Auth::guard('teacher')->user();
        $template = GradingTemplate::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->first();

        $students = $this->group->students()->get();
        $moduleId = $this->group->module_id;

        $rows = [];
        foreach ($students as $student) {
            $entry = GradeEntry::withoutGlobalScopes()
                ->where('student_id', $student->id)
                ->where('module_id', $moduleId)
                ->first();

            $rows[$student->id] = [
                'student_id' => $student->id,
                'student_name' => $student->name,
                'exam_component' => $entry?->exam_component ?? 0.0,
                'personal_work' => $entry?->personal_work ?? 0.0,
                'attendance' => $entry?->attendance ?? 0.0,
                'participation' => $entry?->participation ?? 0.0,
                'final_grade' => $entry?->final_grade ?? 0.0,
            ];
        }

        $this->rows = $rows;
    }

    public function saveRow(int $studentId): void
    {
        $teacher = Auth::guard('teacher')->user();
        $template = GradingTemplate::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->first();

        if (! $template) {
            $this->errorMessage = 'Grading template not found. Please save settings first.';

            return;
        }

        $row = $this->rows[$studentId] ?? null;
        if (! $row) {
            return;
        }

        $gradingService = app(GradingService::class);

        try {
            $gradingService->validateComponentValue('personal_work', (float) $row['personal_work'], $template);
            $gradingService->validateComponentValue('attendance', (float) $row['attendance'], $template);
            $gradingService->validateComponentValue('participation', (float) $row['participation'], $template);
        } catch (InvalidGradingTemplateException $e) {
            $this->errorMessage = $e->getMessage();

            return;
        }

        $moduleId = $this->group->module_id;

        $entry = GradeEntry::withoutGlobalScopes()->updateOrCreate(
            ['student_id' => $studentId, 'module_id' => $moduleId],
            [
                'teacher_id' => $teacher->id,
                'personal_work' => (float) $row['personal_work'],
                'attendance' => (float) $row['attendance'],
                'participation' => (float) $row['participation'],
            ]
        );

        // Recompute final_grade
        $entry->final_grade = $gradingService->computeFinalGrade($entry);
        $entry->save();

        // Update live row
        $this->rows[$studentId]['final_grade'] = $entry->final_grade;

        $this->successMessage = 'Grades saved.';
        $this->errorMessage = '';
    }

    public function saveAll(): void
    {
        $this->successMessage = '';
        $this->errorMessage = '';

        foreach (array_keys($this->rows) as $studentId) {
            $this->saveRow((int) $studentId);
            if ($this->errorMessage) {
                return;
            }
        }
    }

    public function cancelChanges(): void
    {
        $this->loadRows();
        $this->successMessage = '';
        $this->errorMessage = '';
    }

    public function render(): View
    {
        $teacher = Auth::guard('teacher')->user();
        $template = GradingTemplate::withoutGlobalScopes()
            ->where('teacher_id', $teacher->id)
            ->first();

        return view('livewire.teacher.grades.show', [
            'template' => $template,
        ])->layout('layouts.teacher');
    }
}
