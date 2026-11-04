<?php

namespace App\Enums;

/**
 * Estados del período académico (CLAUDE.md §7, decisión 18.12).
 * Transiciones solo hacia adelante: planned -> open -> closed. Sin reapertura.
 */
enum PeriodStatus: string
{
    case Planned = 'planned';
    case Open = 'open';
    case Closed = 'closed';

    public function label(): string
    {
        return match ($this) {
            self::Planned => 'Planificado',
            self::Open => 'Abierto',
            self::Closed => 'Cerrado',
        };
    }

    public function badgeClass(): string
    {
        return match ($this) {
            self::Planned => 'text-bg-secondary',
            self::Open => 'text-bg-success',
            self::Closed => 'text-bg-dark',
        };
    }

    public function allowsEnrollment(): bool
    {
        return $this !== self::Closed;
    }

    public function allowsTeacherGrading(): bool
    {
        return $this === self::Open;
    }

    public function allowsAdminCorrection(): bool
    {
        return $this !== self::Planned;
    }

    public function next(): ?self
    {
        return match ($this) {
            self::Planned => self::Open,
            self::Open => self::Closed,
            self::Closed => null,
        };
    }
}
