@extends('layouts.app')
@section('title', 'Reporte NRC '.$offering->nrc)
@section('content')
<div class="d-flex flex-wrap justify-content-between align-items-center gap-2 mb-3 no-print">
    <a href="{{ route('reports.index') }}" class="btn btn-link px-0 text-decoration-none"><i class="bi bi-arrow-left me-1" aria-hidden="true"></i>Volver a reportes</a>
    <button type="button" class="btn btn-primary" data-print><i class="bi bi-printer me-1" aria-hidden="true"></i>Imprimir / Guardar como PDF</button>
</div>

<article class="report">
    <header class="text-center mb-3">
        <div class="fw-semibold">UNIVERSIDAD DE LAS FUERZAS ARMADAS - ESPE</div>
        <h1 class="mb-1">REPORTE DE CALIFICACIONES DE ESTUDIANTES</h1>
    </header>

    <table class="table table-sm meta mb-3">
        <tbody>
        <tr><th>Período:</th><td colspan="3">{{ $offering->academicPeriod->label }}</td></tr>
        <tr><th>Materia:</th><td>{{ $offering->subject->name }}</td><th>Código:</th><td>{{ $offering->subject->code }}</td></tr>
        <tr><th>NRC:</th><td>{{ $offering->nrc }}</td><th>Aula:</th><td>{{ $offering->classroom ?? '—' }}</td></tr>
        <tr><th>Campus:</th><td>{{ $offering->campus ?? '—' }}</td><th>Estado período:</th><td>{{ $offering->academicPeriod->status->label() }}</td></tr>
        <tr><th>Docente:</th><td>{{ mb_strtoupper($offering->teacher->name) }}</td><th>ID docente:</th><td>{{ $offering->teacher->institutional_id }}</td></tr>
        </tbody>
    </table>

    <table class="table table-bordered grades mb-3">
        <thead>
        <tr>
            <th rowspan="2">N.º</th><th rowspan="2">ID</th><th rowspan="2">Apellidos y nombres</th>
            <th colspan="3">Parciales</th><th rowspan="2">Promedio<br>/20</th><th rowspan="2">Total<br>/60</th><th rowspan="2">Estado</th>
        </tr>
        <tr><th>1P</th><th>2P</th><th>3P</th></tr>
        </thead>
        <tbody>
        @forelse ($rows as $i => $row)
            @php($r = $row['result'])
            <tr>
                <td class="num">{{ $i + 1 }}</td>
                <td>{{ $row['enrollment']->student->institutional_id }}</td>
                <td>{{ $row['enrollment']->student->full_name }}</td>
                <td class="num">{{ $r->partialFormatted(0) }}</td>
                <td class="num">{{ $r->partialFormatted(1) }}</td>
                <td class="num">{{ $r->partialFormatted(2) }}</td>
                <td class="num fw-semibold">{{ $r->averageFormatted() }}</td>
                <td class="num">{{ $r->totalFormatted() }}</td>
                <td class="st-{{ $r->status->value }}">{{ $r->status->value }}</td>
            </tr>
        @empty
            <tr><td colspan="9" class="text-center text-muted py-3">Sin estudiantes matriculados.</td></tr>
        @endforelse
        </tbody>
    </table>

    <footer class="small">
        <p class="mb-1">
            <strong>Resumen:</strong>
            Aprobados: <span data-summary="APROBADO">{{ $summary['APROBADO'] }}</span> ·
            Reprobados: <span data-summary="REPROBADO">{{ $summary['REPROBADO'] }}</span> ·
            Incompletos: <span data-summary="INCOMPLETO">{{ $summary['INCOMPLETO'] }}</span> ·
            Total: {{ $rows->count() }}
        </p>
        <p class="mb-1 text-muted">
            Regla: APROBADO si el total acumulado es mayor o igual a 42,00 / 60 (sin nota mínima por parcial).
            El promedio (total / 3) se redondea a dos decimales solo para mostrarlo; el estado se decide sobre el total exacto.
            «—» indica parcial pendiente; un registro con parciales pendientes es INCOMPLETO.
        </p>
        <p class="mb-0 text-muted">Reporte generado por el Sistema de Gestión Académica el {{ $generatedAt->format('d/m/Y H:i') }} por {{ auth()->user()->name }}.</p>
    </footer>
</article>
@endsection
