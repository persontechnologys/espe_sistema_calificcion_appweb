<?php

namespace Database\Factories;

use App\Enums\PeriodStatus;
use App\Models\AcademicPeriod;
use Illuminate\Database\Eloquent\Factories\Factory;

class AcademicPeriodFactory extends Factory
{
    public function definition(): array
    {
        return [
            'code' => fake()->unique()->numerify('2025##'),
            'description' => 'PREGRADO S-I ABR 25 - AGO 25',
            'start_date' => '2025-04-01',
            'end_date' => '2025-08-31',
        ];
    }

    public function status(PeriodStatus $status): static
    {
        // status no es fillable: se fija después de crear.
        return $this->afterMaking(fn (AcademicPeriod $p) => $p->status = $status);
    }

    public function open(): static
    {
        return $this->status(PeriodStatus::Open);
    }

    public function closed(): static
    {
        return $this->status(PeriodStatus::Closed);
    }
}
