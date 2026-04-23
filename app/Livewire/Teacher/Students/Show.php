<?php

namespace App\Livewire\Teacher\Students;

use App\Domain\Students\Actions\RecordAbsenceAction;
use App\Models\Absence;
use App\Models\Student;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Show extends Component
{
    public Student $student;

    public string $absenceDate = '';

    public function mount(Student $student): void
    {
        $this->authorize('view', $student);
        $this->student = $student;
        $this->absenceDate = now()->toDateString();
    }

    public function addAbsence(): void
    {
        $this->authorize('update', $this->student);

        $this->validate([
            'absenceDate' => ['required', 'date', 'before_or_equal:today'],
        ]);

        (new RecordAbsenceAction)->execute($this->student, $this->absenceDate);

        $this->student->refresh();
        $this->absenceDate = now()->toDateString();
    }

    public function deleteAbsence(int $absenceId): void
    {
        $this->authorize('update', $this->student);

        $absence = Absence::findOrFail($absenceId);
        $absence->delete();

        $this->student->refresh();
    }

    public function render(): View
    {
        $groups = $this->student->groups()->with('module')->orderBy('name')->get();

        $absences = collect();
        if (Schema::hasTable('absences')) {
            $absences = Absence::where('student_id', $this->student->id)
                ->orderByDesc('occurred_on')
                ->get();
        }

        $grades = collect();
        if (Schema::hasTable('grade_entries')) {
            $grades = DB::table('grade_entries')
                ->join('modules', 'modules.id', '=', 'grade_entries.module_id')
                ->where('grade_entries.student_id', $this->student->id)
                ->select('modules.name as module_name', 'grade_entries.*')
                ->get();
        }

        return view('livewire.teacher.students.show', compact('groups', 'absences', 'grades'));
    }
}
