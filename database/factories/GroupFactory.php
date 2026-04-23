<?php

namespace Database\Factories;

use App\Enums\Level;
use App\Models\Group;
use App\Models\Module;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Group>
 */
class GroupFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'module_id' => Module::factory(),
            'level' => fake()->randomElement(Level::cases()),
            'name' => strtoupper(fake()->bothify('##?')),
        ];
    }
}
