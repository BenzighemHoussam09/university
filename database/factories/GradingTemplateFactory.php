<?php

namespace Database\Factories;

use App\Models\GradingTemplate;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<GradingTemplate>
 */
class GradingTemplateFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => null,
            'exam_max' => 12,
            'personal_work_max' => 4,
            'attendance_max' => 2,
            'participation_max' => 2,
        ];
    }

    public function forTeacher(int $teacherId): static
    {
        return $this->state(fn () => ['teacher_id' => $teacherId]);
    }
}
