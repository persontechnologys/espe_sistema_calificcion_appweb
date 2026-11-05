<?php

namespace App\Services\Grades;

use App\Enums\GradeStatus;
use InvalidArgumentException;

/**
 * Único punto de cálculo académico (especificación §4).
 *
 * - total = p1 + p2 + p3 (exacto, en centésimas enteras; sin float).
 * - promedio = total / 3, redondeado a 2 decimales solo para mostrar.
 * - APROBADO si total >= 42,00; el estado se decide sobre el total exacto,
 *   nunca sobre el promedio redondeado (41,99 -> promedio 14,00 -> REPROBADO).
 * - Si algún parcial es NULL el registro es INCOMPLETO: sin total, promedio ni estado definitivo.
 */
final class GradeCalculator
{
    public const MIN_CENTS = 0;

    public const MAX_CENTS = 2000;

    public const PASSING_TOTAL_CENTS = 4200;

    /**
     * @param  array<int, string|int|null>  $partials  Tres valores: cadenas decimales ("10.10"), enteros o NULL.
     */
    public function calculate(array $partials): GradeResult
    {
        $partials = array_values($partials);

        if (count($partials) !== 3) {
            throw new InvalidArgumentException('Se requieren exactamente tres parciales.');
        }

        $cents = array_map(fn ($value) => $value === null ? null : self::toCents($value), $partials);

        if (in_array(null, $cents, true)) {
            return new GradeResult($cents, null, null, GradeStatus::Incompleto);
        }

        $total = array_sum($cents);

        return new GradeResult(
            partialCents: $cents,
            totalCents: $total,
            averageCents: self::roundedThird($total),
            status: $total >= self::PASSING_TOTAL_CENTS ? GradeStatus::Aprobado : GradeStatus::Reprobado,
        );
    }

    /**
     * Convierte "10.1", "10.10", "20" o 20 a centésimas enteras (1010, 2000).
     * Rechaza formatos no numéricos, más de dos decimales y valores fuera de 0–20.
     */
    public static function toCents(string|int $value): int
    {
        $value = trim((string) $value);

        if (! preg_match('/^(\d{1,3})(?:\.(\d{1,2}))?$/', $value, $m)) {
            throw new InvalidArgumentException("Valor de parcial no válido: {$value}");
        }

        $cents = ((int) $m[1]) * 100 + (int) str_pad($m[2] ?? '0', 2, '0');

        if ($cents < self::MIN_CENTS || $cents > self::MAX_CENTS) {
            throw new InvalidArgumentException("Parcial fuera del rango 0–20: {$value}");
        }

        return $cents;
    }

    /** Centésimas a texto con coma decimal: 1400 -> "14,00"; NULL -> "—". */
    public static function formatCents(?int $cents): string
    {
        if ($cents === null) {
            return '—';
        }

        return intdiv($cents, 100).','.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    /** Centésimas a cadena decimal con punto, para guardar en DECIMAL(5,2): 1010 -> "10.10". */
    public static function centsToDecimalString(?int $cents): ?string
    {
        if ($cents === null) {
            return null;
        }

        return intdiv($cents, 100).'.'.str_pad((string) ($cents % 100), 2, '0', STR_PAD_LEFT);
    }

    /**
     * round(n / 3) en aritmética entera (n >= 0). n/3 solo puede terminar en
     * ,000 / ,333 / ,667, así que no hay empates y el redondeo es inequívoco.
     */
    private static function roundedThird(int $totalCents): int
    {
        return intdiv(2 * $totalCents + 3, 6);
    }
}
