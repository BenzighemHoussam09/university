<?php

namespace App\Livewire\Teacher\Students;

use App\Models\Student;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Index extends Component
{
    public function delete(int $id): void
    {
        $student = Student::findOrFail($id);
        $this->authorize('delete', $student);
        $student->delete();
    }

    public function render(): View
    {
        $students = Student::withCount('groups')
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.students.index', compact('students'));
    }
}
