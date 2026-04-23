<?php

namespace App\Policies;

use App\Models\Module;
use App\Models\Teacher;

class ModulePolicy
{
    /**
     * All methods return true for the owning teacher.
     * Cross-teacher access is pre-filtered by the BelongsToTeacher global scope,
     * so if the model instance is reachable, it already belongs to this teacher.
     */
    public function view(Teacher $teacher, Module $module): bool
    {
        return $module->teacher_id === $teacher->id;
    }

    public function update(Teacher $teacher, Module $module): bool
    {
        return $module->teacher_id === $teacher->id;
    }

    public function delete(Teacher $teacher, Module $module): bool
    {
        return $module->teacher_id === $teacher->id;
    }
}
