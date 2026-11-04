<?php

namespace Database\Factories;

use Illuminate\Database\Eloquent\Factories\Factory;

class StudentFactory extends Factory
{
    public function definition(): array
    {
        return [
            'institutional_id' => 'L'.fake()->unique()->numerify('########'),
            'first_names' => fake()->firstName().' '.fake()->firstName(),
            'last_names' => fake()->lastName().' '.fake()->lastName(),
            'email' => fake()->boolean() ? fake()->unique()->safeEmail() : null,
            'is_active' => true,
        ];
    }
}
