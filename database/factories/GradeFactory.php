<?php

namespace Database\Factories;

use App\Models\Enrollment;
use Illuminate\Database\Eloquent\Factories\Factory;

class GradeFactory extends Factory
{
    public function definition(): array
    {
        return [
            'enrollment_id' => Enrollment::factory(),
            'partial_1' => '14.00',
            'partial_2' => '14.00',
            'partial_3' => '14.00',
            'version' => 1,
        ];
    }
}
