<?php

namespace Database\Factories;

use App\Enums\UserRole;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

/**
 * @extends Factory<User>
 */
class UserFactory extends Factory
{
    protected static ?string $password;

    public function definition(): array
    {
        return [
            'name' => fake()->name(),
            'email' => fake()->unique()->safeEmail(),
            'password' => static::$password ??= Hash::make('password'),
            'role' => UserRole::Teacher,
            'is_active' => true,
            'institutional_id' => 'T'.fake()->unique()->numerify('########'),
            'remember_token' => Str::random(10),
        ];
    }

    public function admin(): static
    {
        return $this->state(['role' => UserRole::Admin, 'institutional_id' => null]);
    }

    public function teacher(): static
    {
        return $this->state(['role' => UserRole::Teacher]);
    }

    public function inactive(): static
    {
        return $this->state(['is_active' => false]);
    }
}
