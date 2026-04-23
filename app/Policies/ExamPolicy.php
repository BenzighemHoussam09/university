<?php

namespace App\Policies;

use App\Models\Exam;
use App\Models\Teacher;

/**
 * Exam policy.
 *
 * Cross-teacher access is pre-filtered by the BelongsToTeacher global scope,
 * so all methods return true for the owning teacher.
 */
class ExamPolicy
{
    public function viewAny(Teacher $teacher): bool
    {
        return true;
    }

    public function view(Teacher $teacher, Exam $exam): bool
    {
        return $exam->teacher_id === $teacher->id;
    }

    public function create(Teacher $teacher): bool
    {
        return true;
    }

    public function update(Teacher $teacher, Exam $exam): bool
    {
        return $exam->teacher_id === $teacher->id;
    }

    public function delete(Teacher $teacher, Exam $exam): bool
    {
        return $exam->teacher_id === $teacher->id;
    }
}
