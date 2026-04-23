<?php

namespace App\Policies;

use App\Models\Group;
use App\Models\Teacher;

class GroupPolicy
{
    public function view(Teacher $teacher, Group $group): bool
    {
        return $group->teacher_id === $teacher->id;
    }

    public function update(Teacher $teacher, Group $group): bool
    {
        return $group->teacher_id === $teacher->id;
    }

    public function delete(Teacher $teacher, Group $group): bool
    {
        return $group->teacher_id === $teacher->id;
    }
}
