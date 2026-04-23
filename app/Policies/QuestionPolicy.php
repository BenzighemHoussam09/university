<?php

namespace App\Policies;

use App\Models\Question;
use App\Models\Teacher;

class QuestionPolicy
{
    /**
     * Cross-teacher access is pre-filtered by the BelongsToTeacher global scope.
     * If the Question instance is reachable, it already belongs to this teacher.
     */
    public function view(Teacher $teacher, Question $question): bool
    {
        return $question->teacher_id === $teacher->id;
    }

    public function update(Teacher $teacher, Question $question): bool
    {
        return $question->teacher_id === $teacher->id;
    }

    public function delete(Teacher $teacher, Question $question): bool
    {
        return $question->teacher_id === $teacher->id;
    }
}
