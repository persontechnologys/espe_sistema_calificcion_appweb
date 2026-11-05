@extends('layouts.app')
@section('title', 'Corregir calificación')
@section('content')
@php($offering = $enrollment->courseOffering)
@php($grade = $enrollment->grade)
@php($student = $enrollment->student)
<x-ui.page-header title="Corregir calificación" :subtitle="$student->institutional_id.' · '.$student->full_name.' · NRC '.$offering->nrc" />

<div class="card mb-3"><div class="card-body">@include('partials.offering-summary')</div></div>

<div class="row g-3">
    <div class="col-lg-5">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Valores actuales</h2></div>
            <div class="table-responsive">
                <table class="table table-sm mb-0">
                    <thead><tr><th class="num">1P</th><th class="num">2P</th><th class="num">3P</th><th class="num">Prom.</th><th class="num">Total</th><th>Estado</th></tr></thead>
                    <tbody><tr>@include('partials.grade-cells', ['grade' => $grade])</tr></tbody>
                </table>
            </div>
            <div class="card-body border-top">
                <h2 class="card-title mb-3">Nuevos valores</h2>
                <form method="POST" action="{{ route('admin.enrollments.correction.update', $enrollment) }}" novalidate data-validate data-correction-form>
                    @csrf @method('PUT')
                    <input type="hidden" name="version" value="{{ $grade?->version ?? 0 }}">
                    <div class="row">
                        @foreach (['partial_1' => '1P', 'partial_2' => '2P', 'partial_3' => '3P'] as $field => $label)
                            @php($stored = (string) $grade?->{$field})
                            <div class="col-4 mb-3">
                                <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                                <input id="{{ $field }}" name="{{ $field }}" type="text" inputmode="decimal" maxlength="5" autocomplete="off"
                                       pattern="^\d{1,2}([.,]\d{1,2})?$" data-pattern-message="De 0 a 20, máx. 2 decimales."
                                       data-label="{{ $label }}" data-old="{{ $stored }}"
                                       value="{{ old($field, $stored) }}" class="form-control text-end tabular @error($field) is-invalid @enderror">
                                <x-form.error :name="$field" />
                            </div>
                        @endforeach
                    </div>
                    <div class="form-text mt-n2 mb-3">0 a 20, máx. 2 decimales · vacío = pendiente.</div>
                    <div class="mb-3">
                        <label class="form-label" for="reason">Motivo de la corrección<span class="req" aria-hidden="true">*</span></label>
                        <textarea id="reason" name="reason" rows="3" maxlength="500" required aria-required="true" aria-describedby="reason-help"
                                  class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
                        <div id="reason-help" class="form-text">Obligatorio. Queda registrado en la auditoría junto con los valores anteriores y nuevos.</div>
                        <x-form.error name="reason" />
                    </div>
                    <x-form.actions :cancel="route('admin.course-offerings.show', $offering)" label="Registrar corrección" />
                </form>
            </div>
        </div>
    </div>
    <div class="col-lg-7">
        <div class="card">
            <div class="card-header"><h2 class="card-title">Historial de cambios</h2></div>
            @include('partials.audit-table', ['logs' => $enrollment->auditLogs, 'showEnrollment' => false])
        </div>
    </div>
</div>
@endsection

@push('modals')
{{-- Vista previa antes de enviar. app.js la rellena con textContent; el servidor vuelve a validar todo. --}}
<div class="modal fade" id="correction-modal" tabindex="-1" aria-labelledby="correction-modal-title" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
        <div class="modal-content">
            <div class="modal-header">
                <h2 class="modal-title" id="correction-modal-title"><i class="bi bi-pencil-square text-warning" aria-hidden="true"></i>Confirmar corrección</h2>
                <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Cancelar"></button>
            </div>
            <div class="modal-body">
                <p class="mb-2">{{ $student->institutional_id }} · {{ $student->full_name }} · NRC {{ $offering->nrc }}</p>
                <table class="table table-sm compare-table">
                    <thead><tr><th>Parcial</th><th>Anterior</th><th>Nuevo</th></tr></thead>
                    <tbody data-compare-body></tbody>
                </table>
                <div class="small text-muted mb-1">Motivo</div>
                <div class="confirm-item" data-compare-reason></div>
                <p class="small text-muted mt-3 mb-0">El promedio, el total y el estado se recalculan en el servidor. La corrección queda auditada.</p>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-outline-secondary" data-bs-dismiss="modal">Cancelar</button>
                <button type="button" class="btn btn-primary" data-correction-accept>Registrar corrección</button>
            </div>
        </div>
    </div>
</div>
@endpush
