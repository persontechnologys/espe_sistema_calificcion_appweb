@extends('layouts.app')
@section('title', 'Corregir calificación')
@section('content')
@php($offering = $enrollment->courseOffering)
@php($grade = $enrollment->grade)
<h1 class="h3 mb-3">Corregir calificación</h1>
<div class="card shadow-sm mb-3"><div class="card-body">
    @include('partials.offering-summary')
    <hr>
    <p class="mb-0"><strong>Estudiante:</strong> {{ $enrollment->student->institutional_id }} · {{ $enrollment->student->full_name }}</p>
</div></div>

<div class="row g-3">
<div class="col-lg-5">
<div class="card shadow-sm"><div class="card-body">
    <h2 class="h6">Valores actuales</h2>
    <table class="table table-sm mb-3"><thead><tr><th class="text-end">1P</th><th class="text-end">2P</th><th class="text-end">3P</th><th class="text-end">Prom.</th><th class="text-end">Total</th><th>Estado</th></tr></thead>
        <tbody><tr>@include('partials.grade-cells', ['grade' => $grade])</tr></tbody></table>

    <form method="POST" action="{{ route('admin.enrollments.correction.update', $enrollment) }}">
        @csrf @method('PUT')
        <input type="hidden" name="version" value="{{ $grade?->version ?? 0 }}">
        <div class="row">
            @foreach (['partial_1' => '1P', 'partial_2' => '2P', 'partial_3' => '3P'] as $field => $label)
                <div class="col-4 mb-3">
                    <label class="form-label" for="{{ $field }}">{{ $label }}</label>
                    <input id="{{ $field }}" name="{{ $field }}" type="text" inputmode="decimal" maxlength="5"
                           value="{{ old($field, $grade?->{$field}) }}" class="form-control text-end @error($field) is-invalid @enderror">
                    @include('partials.field-error', ['name' => $field])
                </div>
            @endforeach
        </div>
        <div class="mb-3">
            <label class="form-label" for="reason">Motivo de la corrección <span class="text-danger">*</span></label>
            <textarea id="reason" name="reason" rows="3" maxlength="500" required
                      class="form-control @error('reason') is-invalid @enderror">{{ old('reason') }}</textarea>
            @include('partials.field-error', ['name' => 'reason'])
            <div class="form-text">Queda registrado en la auditoría junto con los valores anteriores y nuevos.</div>
        </div>
        @include('partials.form-actions', ['cancel' => route('admin.course-offerings.show', $offering)])
    </form>
</div></div>
</div>
<div class="col-lg-7">
<div class="card shadow-sm"><div class="card-body">
    <h2 class="h6">Historial de cambios</h2>
    @include('partials.audit-table', ['logs' => $enrollment->auditLogs, 'showEnrollment' => false])
</div></div>
</div>
</div>
@endsection
