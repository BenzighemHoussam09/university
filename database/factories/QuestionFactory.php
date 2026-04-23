<?php

namespace Database\Factories;

use App\Enums\Difficulty;
use App\Enums\Level;
use App\Models\Module;
use App\Models\Question;
use App\Models\QuestionChoice;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Question>
 */
class QuestionFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'module_id' => Module::factory(),
            'level' => fake()->randomElement(Level::cases()),
            'difficulty' => fake()->randomElement(Difficulty::cases()),
            'text' => rtrim(fake()->sentence(8), '.').'?',
        ];
    }

    /**
     * Create the question with exactly 4 choices (1 correct).
     */
    public function withChoices(): static
    {
        return $this->afterCreating(function (Question $question) {
            $correctPosition = fake()->numberBetween(1, 4);

            foreach (range(1, 4) as $position) {
                QuestionChoice::create([
                    'question_id' => $question->id,
                    'text' => fake()->sentence(4),
                    'is_correct' => $position === $correctPosition,
                    'position' => $position,
                ]);
            }
        });
    }
}
