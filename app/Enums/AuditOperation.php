<?php

namespace App\Enums;

enum AuditOperation: string
{
    /** Registro ordinario de notas por el docente de la oferta. */
    case Registro = 'registro';
    /** Corrección del administrador, con motivo obligatorio. */
    case Correccion = 'correccion';

    public function label(): string
    {
        return match ($this) {
            self::Registro => 'Registro',
            self::Correccion => 'Corrección',
        };
    }
}
