@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
<h1 class="h3 mb-4">{{ auth()->user()->isAdmin() ? 'Panel de administración' : 'Mis ofertas académicas' }}</h1>

@if ($stats)
    <div class="row g-3 mb-4">
        @foreach ($stats as $label => $value)
            <div class="col-6 col-md-4 col-xl-2">
                <div class="card text-center shadow-sm">
                    <div class="card-body">
                        <div class="display-6">{{ $value }}</div>
                        <div class="text-muted small">{{ $label }}</div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>
    <h2 class="h5">Ofertas recientes</h2>
@endif

<div class="card shadow-sm">
    <div class="table-responsive">
        <table class="table table-hover align-middle mb-0">
            <thead class="table-light">
            <tr>
                <th>Período</th><th>NRC</th><th>Código</th><th>Asignatura</th><th>Docente</th><th class="text-end">Matriculados</th><th></th>
            </tr>
            </thead>
            <tbody>
            @forelse ($offerings as $offering)
                <tr>
                    <td>
                        {{ $offering->academicPeriod->code }}
                        <span class="badge {{ $offering->academicPeriod->status->badgeClass() }}">{{ $offering->academicPeriod->status->label() }}</span>
                    </td>
                    <td><strong>{{ $offering->nrc }}</strong></td>
                    <td>{{ $offering->subject->code }}</td>
                    <td>{{ $offering->subject->name }}</td>
                    <td>{{ $offering->teacher->name }}</td>
                    <td class="text-end">{{ $offering->enrollments_count }}</td>
                    <td class="text-end text-nowrap">
                        @if (Route::has('offerings.grades.edit'))
                            @can('viewGrades', $offering)
                                <a class="btn btn-sm btn-outline-success" href="{{ route('offerings.grades.edit', $offering) }}">Calificaciones</a>
                            @endcan
                        @endif
                        @if (Route::has('reports.show'))
                            <a class="btn btn-sm btn-outline-secondary" href="{{ route('reports.show', $offering) }}">Reporte</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr><td colspan="7" class="text-center text-muted py-4">No hay ofertas académicas{{ auth()->user()->isTeacher() ? ' asignadas' : '' }}.</td></tr>
            @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
