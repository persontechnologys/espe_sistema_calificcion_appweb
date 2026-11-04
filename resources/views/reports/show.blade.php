@extends('layouts.app')
@section('title', 'Reporte NRC '.$offering->nrc)
@push('styles')
<style>
    .report { background: #fff; max-width: 1100px; margin: 0 auto; padding: 1.5rem 2rem; border: 1px solid #dee2e6; }
    .report h1 { font-size: 1.15rem; letter-spacing: .02em; }
    .report h2 { font-size: 1rem; font-weight: 600; }
    .report .meta th { width: 9rem; font-weight: 600; white-space: nowrap; }
    .report .meta td, .report .meta th { padding: .15rem .5rem; border: 0; }
    .report table.grades th, .report table.grades td { padding: .3rem .45rem; font-size: .9rem; }
    .report table.grades thead th { background: #e9ecef; text-align: center; vertical-align: middle; }
    .num { text-align: right; font-variant-numeric: tabular-nums; }
    .st-APROBADO { color: #146c43; font-weight: 600; }
    .st-REPROBADO { color: #b02a37; font-weight: 600; }
    .st-INCOMPLETO { color: #997404; font-weight: 600; }
    @media print {
        @page { size: A4 portrait; margin: 12mm; }
        .report { border: 0; padding: 0; max-width: none; }
        .report table.grades thead th { background: #e9ecef !important; -webkit-print-color-adjust: exact; print-color-adjust: exact; }
        .report tr { page-break-inside: avoid; }
    }
</style>
@endpush
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3 no-print">
    <a href="{{ route('reports.index') }}" class="btn btn-link px-0">&laquo; Volver a reportes</a>
    <button type="button" class="btn btn-success" onclick="window.print()">Imprimir / Guardar como PDF</button>
</div>

<article class="report">
    <header class="text-center mb-3">
        <div class="fw-semibold">UNIVERSIDAD DE LAS FUERZAS ARMADAS - ESPE</div>
        <h1 class="mb-1">REPORTE DE CALIFICACIONES DE ESTUDIANTES</h1>
        <div class="small text-muted">Generado por el sistema de demostración · no es un documento oficial</div>
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
            <tr><td colspan="9" class="text-center text-muted">Sin estudiantes matriculados.</td></tr>
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
        <p class="mb-0 text-muted">Generado el {{ $generatedAt->format('d/m/Y H:i') }} por {{ auth()->user()->name }}.</p>
    </footer>
</article>
@endsection
