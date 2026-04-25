<?php

namespace App\Livewire\Teacher\Groups;

use App\Models\Group;
use App\Models\Student;
use App\Notifications\StudentAccountCreated;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use Illuminate\View\View;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.teacher')]
class Show extends Component
{
    public Group $group;

    public string $newStudentName = '';

    public string $newStudentEmail = '';

    public ?int $assignExistingId = null;

    public function mount(Group $group): void
    {
        $this->authorize('view', $group);
        $this->group = $group;
    }

    public function addStudent(): void
    {
        $teacherId = Auth::guard('teacher')->id();

        $this->validate([
            'newStudentName' => 'required|string|max:120',
            'newStudentEmail' => [
                'required',
                'email',
                'max:190',
                // Unique per teacher
                function ($attribute, $value, $fail) use ($teacherId) {
                    $exists = Student::withoutGlobalScopes()
                        ->where('teacher_id', $teacherId)
                        ->where('email', $value)
                        ->exists();
                    if ($exists) {
                        $fail('A student with this email already exists in your account.');
                    }
                },
            ],
        ]);

        $plainPassword = app()->environment('local') ? 'password' : Str::random(10);

        $student = Student::create([
            'teacher_id' => $teacherId,
            'name' => trim($this->newStudentName),
            'email' => trim($this->newStudentEmail),
            'password' => Hash::make($plainPassword),
        ]);

        $this->group->students()->syncWithoutDetaching([$student->id]);

        // TODO (US6): Replace with full StudentAccountCreated dispatch once Phase 8 lands.
        $student->notify(new StudentAccountCreated($plainPassword));

        $this->reset(['newStudentName', 'newStudentEmail']);
    }

    public function assignExisting(): void
    {
        $studentId = (int) $this->assignExistingId;
        if (! $studentId) {
            return;
        }

        $student = Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $this->group->students()->syncWithoutDetaching([$student->id]);
        $this->assignExistingId = null;
    }

    public function removeFromGroup(int $studentId): void
    {
        $student = Student::findOrFail($studentId);
        $this->authorize('view', $student);

        $this->group->students()->detach($studentId);
    }

    public function render(): View
    {
        $students = $this->group->students()->orderBy('name')->get();

        // Students that belong to this teacher but are NOT already in this group
        $unassigned = Student::whereDoesntHave('groups', fn ($q) => $q->where('groups.id', $this->group->id))
            ->orderBy('name')
            ->get();

        return view('livewire.teacher.groups.show', compact('students', 'unassigned'));
    }
}
