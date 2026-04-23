<?php

namespace App\Policies;

use App\Models\Student;
use App\Models\Teacher;

class StudentPolicy
{
    public function view(Teacher $teacher, Student $student): bool
    {
        return $student->teacher_id === $teacher->id;
    }

    public function update(Teacher $teacher, Student $student): bool
    {
        return $student->teacher_id === $teacher->id;
    }

    public function delete(Teacher $teacher, Student $student): bool
    {
        return $student->teacher_id === $teacher->id;
    }
}
