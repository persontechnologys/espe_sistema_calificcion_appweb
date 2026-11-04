<?php

namespace App\Enums;

enum GradeStatus: string
{
    case Aprobado = 'APROBADO';
    case Reprobado = 'REPROBADO';
    case Incompleto = 'INCOMPLETO';

    public function badgeClass(): string
    {
        return match ($this) {
            self::Aprobado => 'text-bg-success',
            self::Reprobado => 'text-bg-danger',
            self::Incompleto => 'text-bg-warning',
        };
    }
}
