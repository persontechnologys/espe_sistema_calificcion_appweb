<?php

namespace Database\Factories;

use App\Models\AcademicPeriod;
use App\Models\Subject;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

class CourseOfferingFactory extends Factory
{
    public function definition(): array
    {
        return [
            'academic_period_id' => AcademicPeriod::factory()->open(),
            'subject_id' => Subject::factory(),
            'teacher_id' => User::factory()->teacher(),
            'nrc' => fake()->unique()->numerify('#####'),
            'classroom' => 'B-'.fake()->numerify('###'),
            'campus' => 'MATRIZ SANGOLQUÍ',
        ];
    }
}
