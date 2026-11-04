<?php

namespace App\Http\Controllers\Concerns;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\QueryException;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\DB;

/**
 * Borrado con ON DELETE RESTRICT: si hay registros dependientes la BD lo rechaza
 * y se muestra un mensaje comprensible en lugar de un error 500.
 *
 * El borrado va en su propia transacción (savepoint si ya hay una abierta): en
 * PostgreSQL una sentencia fallida aborta toda la transacción en curso.
 */
trait DeletesRestricted
{
    protected function deleteRestricted(Model $model, string $indexRoute, string $label): RedirectResponse
    {
        try {
            DB::transaction(fn () => $model->delete());
        } catch (QueryException) {
            return back()->withErrors([
                'general' => "No se puede eliminar {$label}: tiene registros dependientes. Márquelo como inactivo en su lugar.",
            ]);
        }

        return redirect()->route($indexRoute)->with('status', 'Registro eliminado.');
    }
}
