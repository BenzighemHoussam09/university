<?php

namespace Database\Factories;

use App\Enums\ExamStatus;
use App\Models\Exam;
use App\Models\Group;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Exam>
 */
class ExamFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'group_id' => Group::factory(),
            'title' => rtrim(fake()->sentence(4), '.'),
            'easy_count' => 3,
            'medium_count' => 2,
            'hard_count' => 1,
            'duration_minutes' => 60,
            'scheduled_at' => now()->addHour(),
            'status' => ExamStatus::Scheduled,
            'started_at' => null,
            'ended_at' => null,
            'global_extra_minutes' => 0,
            'reminders_sent_at' => null,
        ];
    }

    public function active(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamStatus::Active,
            'started_at' => now(),
        ]);
    }

    public function ended(): static
    {
        return $this->state(fn (array $attributes) => [
            'status' => ExamStatus::Ended,
            'started_at' => now()->subHour(),
            'ended_at' => now(),
        ]);
    }
}
