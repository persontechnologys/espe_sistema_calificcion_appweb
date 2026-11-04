<?php

namespace App\Http\Controllers\Admin;

use App\Enums\PeriodStatus;
use App\Http\Controllers\Controller;
use App\Models\AcademicPeriod;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

/**
 * Apertura y cierre del período. Solo hacia adelante: planned -> open -> closed.
 */
class AcademicPeriodStatusController extends Controller
{
    public function __invoke(Request $request, AcademicPeriod $academicPeriod): RedirectResponse
    {
        $validated = $request->validate(['status' => ['required', Rule::enum(PeriodStatus::class)]]);
        $target = PeriodStatus::from($validated['status']);

        DB::transaction(function () use ($academicPeriod, $target) {
            // Bloquea la fila: un guardado de notas concurrente lee el estado con el mismo
            // bloqueo, así que ve el período abierto o cerrado, nunca a medias.
            $period = AcademicPeriod::whereKey($academicPeriod->id)->lockForUpdate()->firstOrFail();

            if ($period->status->next() !== $target) {
                throw ValidationException::withMessages([
                    'general' => "Transición no permitida: {$period->status->label()} → {$target->label()}.",
                ]);
            }

            $period->status = $target;
            $period->save();
        });

        return back()->with('status', "Período {$academicPeriod->code}: {$target->label()}.");
    }
}
