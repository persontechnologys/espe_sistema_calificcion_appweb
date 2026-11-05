<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Artisan;
use Throwable;

/**
 * Inicialización del despliegue desde el navegador, para hosting sin terminal.
 *
 * Seguridad:
 *  - Desactivada si INIT_TOKEN está vacío o tiene menos de 32 caracteres.
 *  - Token comparado en tiempo constante; si falta o no coincide responde 404 (no revela la ruta).
 *  - Lista FIJA de comandos: no acepta comandos ni parámetros del cliente.
 *  - Nunca ejecuta comandos destructivos (migrate:fresh, db:wipe, rollback).
 *  - La configuración NO se cachea, para que vaciar INIT_TOKEN en .env la desactive al instante.
 */
class InitController extends Controller
{
    public const MIN_TOKEN_LENGTH = 32;

    public function __invoke(Request $request): Response
    {
        $expected = (string) config('app.init_token');
        $given = (string) $request->query('token', '');

        abort_if(strlen($expected) < self::MIN_TOKEN_LENGTH || ! hash_equals($expected, $given), 404);

        $steps = [
            ['optimize:clear', []],
            ['migrate', ['--force' => true]],
        ];

        // Datos de demostración solo si se piden; DemoSeeder no hace nada si ya hay usuarios.
        if ($request->boolean('seed')) {
            $steps[] = ['db:seed', ['--force' => true]];
        }

        $steps[] = ['route:cache', []];
        $steps[] = ['view:cache', []];

        $log = [];
        $ok = true;

        foreach ($steps as [$command, $params]) {
            try {
                $code = Artisan::call($command, $params);
                $log[] = "## {$command} → código {$code}\n".trim(Artisan::output());
                if ($code !== 0) {
                    $ok = false;
                    break;
                }
            } catch (Throwable $e) {
                // Solo el mensaje, sin traza.
                $log[] = "## {$command} → ERROR\n".$e->getMessage();
                $ok = false;
                break;
            }
        }

        $log[] = $ok
            ? "\nInicialización completa. Vacíe INIT_TOKEN en .env para desactivar esta ruta."
            : "\nInicialización detenida por un error. Corrija y vuelva a ejecutar.";

        return response(implode("\n\n", $log), $ok ? 200 : 500)
            ->header('Content-Type', 'text/plain; charset=utf-8')
            ->header('Cache-Control', 'no-store');
    }
}
