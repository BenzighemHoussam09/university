<?php

namespace Database\Factories;

use App\Models\Module;
use App\Models\Teacher;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Module>
 */
class ModuleFactory extends Factory
{
    public function definition(): array
    {
        return [
            'teacher_id' => Teacher::factory(),
            'name' => fake()->words(3, true),
            'created_from_catalog_id' => null,
        ];
    }
}
