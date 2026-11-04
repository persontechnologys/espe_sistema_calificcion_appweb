<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class SubjectFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => 'COMP-'.fake()->unique()->bothify('?#?##'),
            'name' => fake()->words(3, true),
            'is_active' => true,
        ];
    }
}
