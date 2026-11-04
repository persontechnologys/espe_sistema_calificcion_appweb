<?php

namespace App\Services\Grades;

use App\Enums\GradeStatus;

/**
 * Resultado derivado de los tres parciales. Inmutable.
 * Los valores se guardan en centésimas (enteros) para no usar punto flotante.
 */
final readonly class GradeResult
{
    /**
     * @param  array{0: ?int, 1: ?int, 2: ?int}  $partialCents
     */
    public function __construct(
        public array $partialCents,
        public ?int $totalCents,
        public ?int $averageCents,
        public GradeStatus $status,
    ) {}

    public function isComplete(): bool
    {
        return $this->status !== GradeStatus::Incompleto;
    }

    /** Total con dos decimales y coma decimal, o "—" si está incompleto. */
    public function totalFormatted(): string
    {
        return GradeCalculator::formatCents($this->totalCents);
    }

    /** Promedio redondeado a dos decimales SOLO para presentación. */
    public function averageFormatted(): string
    {
        return GradeCalculator::formatCents($this->averageCents);
    }

    public function partialFormatted(int $index): string
    {
        return GradeCalculator::formatCents($this->partialCents[$index] ?? null);
    }
}
