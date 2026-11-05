@extends('layouts.app')
@section('title', 'Oferta NRC '.$offering->nrc)
@section('content')
@php($status = $offering->academicPeriod->status)
<x-ui.page-header :title="'Oferta NRC '.$offering->nrc" :subtitle="$offering->subject->code.' · '.$offering->subject->name">
    <x-slot:actions>
        <a class="btn btn-outline-secondary" href="{{ route('reports.show', $offering) }}"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Reporte</a>
        <a class="btn btn-outline-primary" href="{{ route('admin.course-offerings.edit', $offering) }}"><i class="bi bi-pencil me-1" aria-hidden="true"></i>Editar / reasignar docente</a>
    </x-slot:actions>
</x-ui.page-header>

<div class="card mb-3"><div class="card-body">@include('partials.offering-summary')</div></div>

<div class="card mb-3">
    <div class="card-header"><h2 class="card-title">Matricular estudiante</h2></div>
    <div class="card-body">
        @if ($status->allowsEnrollment())
            <form method="POST" action="{{ route('admin.course-offerings.enrollments.store', $offering) }}" class="row g-2 align-items-start" novalidate data-validate>
                @csrf
                <div class="col-md-8 col-lg-6">
                    <label for="student_id" class="visually-hidden">Estudiante</label>
                    <select id="student_id" name="student_id" class="form-select @error('student_id') is-invalid @enderror" required aria-required="true">
                        <option value="">Seleccione un estudiante activo…</option>
                        @foreach ($availableStudents as $s)
                            <option value="{{ $s->id }}" @selected((string) old('student_id') === (string) $s->id)>{{ $s->institutional_id }} · {{ $s->full_name }}</option>
                        @endforeach
                    </select>
                    <x-form.error name="student_id" />
                </div>
                <div class="col-auto">
                    <button class="btn btn-primary" type="submit"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Matricular</button>
                </div>
            </form>
        @else
            <p class="text-muted mb-0"><i class="bi bi-lock me-1" aria-hidden="true"></i>El período está cerrado: no admite matrículas.</p>
        @endif
    </div>
</div>

<div class="card">
    <div class="card-header d-flex justify-content-between align-items-center">
        <h2 class="card-title">Estudiantes matriculados</h2>
        <span class="small text-muted tabular">{{ $enrollments->count() }}</span>
    </div>
    <div class="table-responsive">
        <table class="table table-hover align-middle">
            <thead><tr>
                <th>ID</th><th>Apellidos y nombres</th><th class="num">1P</th><th class="num">2P</th><th class="num">3P</th>
                <th class="num">Promedio /20</th><th class="num">Total /60</th><th>Estado</th><th class="text-end"><span class="visually-hidden">Acciones</span></th>
            </tr></thead>
            <tbody>
            @forelse ($enrollments as $enrollment)
                <tr>
                    <td class="cell-strong text-nowrap">{{ $enrollment->student->institutional_id }}</td>
                    <td>{{ $enrollment->student->full_name }}</td>
                    @include('partials.grade-cells', ['grade' => $enrollment->grade])
                    <td class="text-end">
                        <div class="table-actions">
                            @if ($status->allowsAdminCorrection())
                                <x-ui.icon-link :href="route('admin.enrollments.correction.edit', $enrollment)" icon="bi-pencil-square" variant="outline-warning"
                                                :label="'Corregir notas de '.$enrollment->student->institutional_id" />
                            @endif
                            @if ($enrollment->grade === null && $status->allowsEnrollment())
                                <form method="POST" action="{{ route('admin.course-offerings.enrollments.destroy', [$offering, $enrollment]) }}" class="d-inline"
                                      data-confirm-title="¿Quitar la matrícula?" data-confirm-variant="danger" data-confirm-button="Quitar"
                                      data-confirm-message="Solo es posible si no tiene calificaciones. Use esta opción para corregir errores de digitación."
                                      data-confirm-item="{{ $enrollment->student->institutional_id }} · {{ $enrollment->student->full_name }} (NRC {{ $offering->nrc }})">
                                    @csrf @method('DELETE')
                                    <button class="btn btn-sm btn-icon btn-outline-danger" aria-label="Quitar matrícula de {{ $enrollment->student->institutional_id }}"
                                            data-bs-toggle="tooltip" data-bs-title="Quitar matrícula"><i class="bi bi-person-dash" aria-hidden="true"></i></button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <x-ui.empty-row colspan="9" icon="bi-people" title="Sin estudiantes matriculados" message="Use el formulario de arriba para matricular estudiantes activos." />
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
