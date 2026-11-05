@extends('layouts.app')
@section('title', 'Calificaciones NRC '.$offering->nrc)
@section('content')
@php($period = $offering->academicPeriod)
<x-ui.page-header :title="'Calificaciones · NRC '.$offering->nrc" :subtitle="$offering->subject->code.' · '.$offering->subject->name">
    <x-slot:meta>
        <div class="grade-legend mt-2" id="grade-legend">
            <span><i class="bi bi-123" aria-hidden="true"></i>0 a 20, máx. 2 decimales</span>
            <span><i class="bi bi-dash-lg" aria-hidden="true"></i>vacío = pendiente</span>
            <span><i class="bi bi-check2-circle" aria-hidden="true"></i>aprobado con total ≥ 42,00</span>
        </div>
    </x-slot:meta>
    <x-slot:actions>
        <a class="btn btn-outline-secondary" href="{{ route('reports.show', $offering) }}"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Ver reporte</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card mb-3"><div class="card-body">@include('partials.offering-summary')</div></div>

@if (! $canEdit)
    <div class="alert alert-warning d-flex gap-2 align-items-start" role="status">
        <i class="bi bi-lock-fill mt-1" aria-hidden="true"></i>
        <div>
            <strong>Solo lectura.</strong> El período está <strong>{{ $period->status->label() }}</strong>: el registro de notas está bloqueado.
            @if ($period->status === \App\Enums\PeriodStatus::Closed)
                Las rectificaciones las realiza el administrador con un motivo, que queda auditado.
            @endif
        </div>
    </div>
@else
    <p class="text-muted small mb-3">
        <i class="bi bi-info-circle me-1" aria-hidden="true"></i>
        Se acepta punto o coma decimal. <strong>0</strong> es una nota registrada. Guarde cada fila por separado
        (botón o tecla Enter); el promedio, el total y el estado los calcula el servidor al guardar.
    </p>
@endif

<div class="card">
    <div class="table-responsive">
        <table class="table align-middle grade-sheet" data-grade-sheet aria-describedby="grade-legend">
            <thead>
            <tr>
                <th>ID</th><th>Apellidos y nombres</th>
                <th>1P</th><th>2P</th><th>3P</th>
                <th class="num">Promedio /20</th><th class="num">Total /60</th><th>Estado</th>
                <th class="text-end"><span class="visually-hidden">Guardar</span></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($enrollments as $enrollment)
                @php($formId = 'f'.$enrollment->id)
                @php($bag = $errors->getBag('grade_'.$enrollment->id))
                @php($isOld = (string) old('row') === (string) $enrollment->id)
                @php($student = $enrollment->student)
                <tr id="row-{{ $enrollment->id }}" data-grade-row="{{ $formId }}" @class(['has-error' => $bag->any(), 'table-danger' => $bag->any()])>
                    <td class="cell-strong text-nowrap">{{ $student->institutional_id }}</td>
                    <td>
                        {{ $student->full_name }}
                        <span class="dirty-dot d-block"><i class="bi bi-circle-fill me-1" aria-hidden="true"></i>Sin guardar</span>
                    </td>
                    @foreach (['partial_1' => '1P', 'partial_2' => '2P', 'partial_3' => '3P'] as $field => $label)
                        @php($stored = (string) $enrollment->grade?->{$field})
                        <td>
                            <input form="{{ $formId }}" name="{{ $field }}" type="text" inputmode="decimal" maxlength="5" autocomplete="off"
                                   pattern="^\d{1,2}([.,]\d{1,2})?$" data-label="{{ $label }}" data-original="{{ $stored }}"
                                   aria-label="{{ $label }} de {{ $student->institutional_id }} {{ $student->full_name }}"
                                   @if ($bag->any()) aria-describedby="msg-{{ $formId }}" @endif
                                   value="{{ $isOld ? old($field) : $stored }}"
                                   @class(['form-control', 'form-control-sm', 'grade-input', 'is-invalid' => $bag->has($field)])
                                   @disabled(! $canEdit)>
                        </td>
                    @endforeach
                    @include('partials.grade-cells', ['grade' => $enrollment->grade, 'withoutPartials' => true])
                    <td class="text-end">
                        @if ($canEdit)
                            <form id="{{ $formId }}" method="POST" action="{{ route('offerings.grades.update', [$offering, $enrollment]) }}">
                                @csrf @method('PUT')
                                <input type="hidden" name="row" value="{{ $enrollment->id }}">
                                <input type="hidden" name="version" value="{{ $enrollment->grade?->version ?? 0 }}">
                                <button class="btn btn-sm btn-outline-primary btn-save" type="submit" form="{{ $formId }}"
                                        aria-label="Guardar notas de {{ $student->institutional_id }}">
                                    <i class="bi bi-check2 me-1" aria-hidden="true"></i>Guardar
                                </button>
                            </form>
                        @endif
                    </td>
                </tr>
                <tr id="msg-{{ $formId }}" @class(['row-messages', 'table-danger', 'd-none' => ! $bag->any()]) aria-live="polite">
                    <td colspan="9">
                        @if ($bag->any())
                            <div data-server-messages>
                                @foreach ($bag->all() as $message)
                                    <div><i class="bi bi-exclamation-circle me-1" aria-hidden="true"></i>{{ $message }}</div>
                                @endforeach
                            </div>
                        @endif
                        <div data-client-messages></div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="9" icon="bi-people" title="No hay estudiantes matriculados" message="El administrador matricula a los estudiantes en la oferta." />
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
