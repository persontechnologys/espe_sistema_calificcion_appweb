@extends('layouts.app')
@section('title', 'Calificaciones NRC '.$offering->nrc)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Calificaciones · NRC {{ $offering->nrc }}</h1>
    @if (Route::has('reports.show'))
        <a class="btn btn-outline-secondary" href="{{ route('reports.show', $offering) }}">Ver reporte</a>
    @endif
</div>
<div class="card shadow-sm mb-3"><div class="card-body">@include('partials.offering-summary')</div></div>

@if (! $canEdit)
    <div class="alert alert-warning">
        El período está <strong>{{ $offering->academicPeriod->status->label() }}</strong>: el registro de notas está bloqueado.
        @if ($offering->academicPeriod->status === \App\Enums\PeriodStatus::Closed)
            Las rectificaciones las realiza el administrador con un motivo, que queda auditado.
        @endif
    </div>
@else
    <p class="text-muted small">
        Parciales de 0 a 20 con máximo dos decimales (se acepta punto o coma). Deje vacío un parcial pendiente;
        <strong>0</strong> es una nota registrada. Aprobado con total ≥ 42,00 / 60. Guarde cada fila por separado.
    </p>
@endif

<div class="card shadow-sm"><div class="table-responsive">
<table class="table align-middle mb-0">
    <thead class="table-light">
    <tr>
        <th>ID</th><th>Apellidos y nombres</th>
        <th style="width:7rem">1P</th><th style="width:7rem">2P</th><th style="width:7rem">3P</th>
        <th class="text-end">Promedio /20</th><th class="text-end">Total /60</th><th>Estado</th><th></th>
    </tr>
    </thead>
    <tbody>
    @forelse ($enrollments as $enrollment)
        @php($bag = $errors->getBag('grade_'.$enrollment->id))
        @php($isOld = (string) old('row') === (string) $enrollment->id)
        <tr id="row-{{ $enrollment->id }}" @class(['table-danger' => $bag->any()])>
            <td>{{ $enrollment->student->institutional_id }}</td>
            <td>{{ $enrollment->student->full_name }}</td>
            @foreach (['partial_1', 'partial_2', 'partial_3'] as $field)
                <td>
                    <input form="f{{ $enrollment->id }}" name="{{ $field }}" type="text" inputmode="decimal" maxlength="5"
                           aria-label="{{ $field }} {{ $enrollment->student->institutional_id }}"
                           value="{{ $isOld ? old($field) : $enrollment->grade?->{$field} }}"
                           class="form-control form-control-sm text-end @if($bag->has($field)) is-invalid @endif"
                           @disabled(! $canEdit)>
                </td>
            @endforeach
            @include('partials.grade-cells', ['grade' => $enrollment->grade, 'withoutPartials' => true])
            <td>
                @if ($canEdit)
                    <form id="f{{ $enrollment->id }}" method="POST" action="{{ route('offerings.grades.update', [$offering, $enrollment]) }}">
                        @csrf @method('PUT')
                        <input type="hidden" name="row" value="{{ $enrollment->id }}">
                        <input type="hidden" name="version" value="{{ $enrollment->grade?->version ?? 0 }}">
                        <button class="btn btn-sm btn-success" type="submit">Guardar</button>
                    </form>
                @endif
            </td>
        </tr>
        @if ($bag->any())
            <tr class="table-danger"><td colspan="9" class="small">
                @foreach ($bag->all() as $message)<div>{{ $message }}</div>@endforeach
            </td></tr>
        @endif
    @empty
        <tr><td colspan="9" class="text-center text-muted py-4">No hay estudiantes matriculados.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
@endsection
