<?php

namespace App\Domain\Students\Actions;

use App\Models\Absence;
use App\Models\Student;

class RecordAbsenceAction
{
    public function execute(Student $student, string $occurredOn): Absence
    {
        return Absence::create([
            'teacher_id' => $student->teacher_id,
            'student_id' => $student->id,
            'occurred_on' => $occurredOn,
        ]);
    }
}
