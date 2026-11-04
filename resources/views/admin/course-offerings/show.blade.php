@extends('layouts.app')
@section('title', 'Oferta NRC '.$offering->nrc)
@section('content')
<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Oferta NRC {{ $offering->nrc }}</h1>
    <div class="d-flex gap-2">
        @if (Route::has('reports.show'))
            <a class="btn btn-outline-secondary" href="{{ route('reports.show', $offering) }}">Reporte</a>
        @endif
        <a class="btn btn-outline-primary" href="{{ route('admin.course-offerings.edit', $offering) }}">Editar / reasignar docente</a>
    </div>
</div>
<div class="card shadow-sm mb-3"><div class="card-body">@include('partials.offering-summary')</div></div>

@php($status = $offering->academicPeriod->status)
<div class="card shadow-sm mb-3"><div class="card-body">
    <h2 class="h6">Matricular estudiante</h2>
    @if ($status->allowsEnrollment())
        <form method="POST" action="{{ route('admin.course-offerings.enrollments.store', $offering) }}" class="d-flex gap-2 flex-wrap">
            @csrf
            <select name="student_id" class="form-select @error('student_id') is-invalid @enderror" style="max-width: 32rem" required>
                <option value="">Seleccione un estudiante activo…</option>
                @foreach ($availableStudents as $s)
                    <option value="{{ $s->id }}" @selected((string) old('student_id') === (string) $s->id)>{{ $s->institutional_id }} · {{ $s->full_name }}</option>
                @endforeach
            </select>
            <button class="btn btn-success" type="submit">Matricular</button>
            @include('partials.field-error', ['name' => 'student_id'])
        </form>
    @else
        <p class="text-muted mb-0">El período está cerrado: no admite matrículas.</p>
    @endif
</div></div>

<div class="card shadow-sm"><div class="table-responsive">
<table class="table table-hover align-middle mb-0">
    <thead class="table-light">
    <tr><th>ID</th><th>Apellidos y nombres</th><th class="text-end">1P</th><th class="text-end">2P</th><th class="text-end">3P</th>
        <th class="text-end">Promedio /20</th><th class="text-end">Total /60</th><th>Estado</th><th></th></tr>
    </thead>
    <tbody>
    @forelse ($enrollments as $enrollment)
        <tr>
            <td>{{ $enrollment->student->institutional_id }}</td>
            <td>{{ $enrollment->student->full_name }}</td>
            @include('partials.grade-cells', ['grade' => $enrollment->grade])
            <td class="text-end text-nowrap">
                @if ($status->allowsAdminCorrection())
                    <a class="btn btn-sm btn-outline-warning" href="{{ route('admin.enrollments.correction.edit', $enrollment) }}">Corregir</a>
                @endif
                @if ($enrollment->grade === null && $status->allowsEnrollment())
                    <form method="POST" action="{{ route('admin.course-offerings.enrollments.destroy', [$offering, $enrollment]) }}" class="d-inline"
                          data-confirm="¿Eliminar esta matrícula? Solo es posible si no tiene calificaciones.">
                        @csrf @method('DELETE')
                        <button class="btn btn-sm btn-outline-danger">Quitar</button>
                    </form>
                @endif
            </td>
        </tr>
    @empty
        <tr><td colspan="9" class="text-center text-muted py-4">Sin estudiantes matriculados.</td></tr>
    @endforelse
    </tbody>
</table>
</div></div>
@endsection
