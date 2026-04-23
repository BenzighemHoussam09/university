<?php

namespace Database\Factories;

use App\Models\GradeEntry;
use App\Models\Module;
use App\Models\Student;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradeEntry>
 */
class GradeEntryFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'student_id' => Student::factory(),
            'module_id' => Module::factory(),
            'exam_component' => 0.0,
            'personal_work' => 0.0,
            'attendance' => 0.0,
            'participation' => 0.0,
            'final_grade' => 0.0,
        ];
    }
}
