<?php

namespace App\Support;

/**
 * Normalización de códigos antes de validar y guardar (decisión 18.19).
 * MySQL (*_ci) compara sin distinguir mayúsculas y PostgreSQL sí; guardando
 * siempre en mayúsculas ambos motores tratan "comp-a0g11" y "COMP-A0G11"
 * como el mismo código.
 */
final class Normalize
{
    public static function code(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_strtoupper(trim((string) $value));

        return $value === '' ? null : $value;
    }

    public static function email(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = mb_strtolower(trim((string) $value));

        return $value === '' ? null : $value;
    }

    public static function text(mixed $value): ?string
    {
        if ($value === null) {
            return null;
        }

        $value = trim(preg_replace('/\s+/u', ' ', (string) $value));

        return $value === '' ? null : $value;
    }
}
