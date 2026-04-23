<?php

namespace Database\Factories;

use App\Models\Exam;
use App\Models\ExamSession;
use App\Models\Student;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<ExamSession>
 */
class ExamSessionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'exam_id' => Exam::factory(),
            'student_id' => Student::factory(),
            'status' => 'waiting',
            'started_at' => null,
            'deadline' => null,
            'student_extra_minutes' => 0,
            'last_heartbeat_at' => null,
            'completed_at' => null,
            'exam_score_raw' => null,
            'exam_score_component' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'active',
            'started_at' => now(),
            'deadline' => now()->addHour(),
            'last_heartbeat_at' => now(),
        ]);
    }

    public function completed(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => 'completed',
            'started_at' => now()->subHour(),
            'deadline' => now()->subMinutes(5),
            'completed_at' => now(),
        ]);
    }
}
