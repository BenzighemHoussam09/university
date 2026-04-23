<?php

namespace App\Livewire\Student\Grades;

use App\Models\GradeEntry;
use Illuminate\Support\Facades\Auth;
use Illuminate\View\View;
use Livewire\Component;

class Index extends Component
{
    public function render(): View
    {
        $student = Auth::guard('student')->user();

        $entries = GradeEntry::withoutGlobalScopes()
            ->where('student_id', $student->id)
            ->with('module')
            ->get();

        return view('livewire.student.grades.index', [
            'entries' => $entries,
        ])->layout('layouts.student');
    }
}
