@extends('layouts.app')

@section('title', 'Inicio')

@section('content')
@if (auth()->user()->isAdmin())
    <x-ui.page-header title="Panel de administración" subtitle="Resumen de la información académica registrada.">
        <x-slot:actions>
            <a href="{{ route('admin.students.create') }}" class="btn btn-outline-primary"><i class="bi bi-person-plus me-1" aria-hidden="true"></i>Nuevo estudiante</a>
            <a href="{{ route('admin.course-offerings.create') }}" class="btn btn-outline-primary"><i class="bi bi-plus-lg me-1" aria-hidden="true"></i>Nueva oferta</a>
            <a href="{{ route('reports.index') }}" class="btn btn-primary"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Reportes</a>
        </x-slot:actions>
    </x-ui.page-header>

    @php($cards = [
        ['students', 'Estudiantes activos', 'bi-mortarboard', route('admin.students.index')],
        ['teachers', 'Docentes activos', 'bi-person-badge', route('admin.users.index', ['role' => 'teacher'])],
        ['subjects', 'Asignaturas activas', 'bi-journal-bookmark', route('admin.subjects.index')],
        ['openOfferings', 'Ofertas del período abierto', 'bi-collection', route('admin.course-offerings.index')],
        ['enrollments', 'Matrículas', 'bi-people', null],
        ['incomplete', 'Registros incompletos', 'bi-hourglass-split', null],
    ])
    <div class="row g-3 mb-4">
        @foreach ($cards as [$key, $label, $icon, $href])
            <div class="col-6 col-md-4 col-xxl-2">
                <div class="card h-100 position-relative">
                    <div class="stat-card">
                        <span @class(['stat-icon', 'is-warning' => $key === 'incomplete' && $stats[$key] > 0])><i class="bi {{ $icon }}" aria-hidden="true"></i></span>
                        <div class="min-w-0">
                            <div class="stat-value" data-stat="{{ $key }}">{{ $stats[$key] }}</div>
                            <div class="stat-label">
                                @if ($href)<a href="{{ $href }}" class="stretched-link text-reset text-decoration-none">{{ $label }}</a>@else{{ $label }}@endif
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="row g-3">
        <div class="col-xl-8">
            <div class="card mb-3">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title">Período abierto</h2>
                    <a href="{{ route('admin.academic-periods.index') }}" class="small">Ver períodos</a>
                </div>
                @if ($openPeriod)
                    <div class="card-body d-flex flex-wrap align-items-center gap-3">
                        <div class="stat-icon"><i class="bi bi-calendar-check" aria-hidden="true"></i></div>
                        <div class="flex-grow-1">
                            <div class="fw-semibold">{{ $openPeriod->code }} - {{ $openPeriod->description }}</div>
                            <div class="text-muted small">{{ $openPeriod->start_date->format('d/m/Y') }} – {{ $openPeriod->end_date->format('d/m/Y') }}</div>
                        </div>
                        <x-ui.status-badge :status="$openPeriod->status" />
                    </div>
                @else
                    <x-ui.empty-state icon="bi-calendar-x" title="No hay un período abierto"
                                      message="Abra un período para que los docentes registren calificaciones.">
                        <a href="{{ route('admin.academic-periods.index') }}" class="btn btn-sm btn-primary">Gestionar períodos</a>
                    </x-ui.empty-state>
                @endif
            </div>

            <div class="card">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title">Ofertas recientes</h2>
                    <a href="{{ route('admin.course-offerings.index') }}" class="small">Ver todas</a>
                </div>
                <div class="table-responsive">
                    <table class="table table-hover align-middle">
                        <thead><tr>
                            <th>NRC</th><th>Asignatura</th><th class="d-none d-md-table-cell">Docente</th>
                            <th class="d-none d-sm-table-cell">Período</th><th class="num">Matriculados</th><th><span class="visually-hidden">Acciones</span></th>
                        </tr></thead>
                        <tbody>
                        @forelse ($offerings as $offering)
                            <tr>
                                <td class="cell-strong tabular">{{ $offering->nrc }}</td>
                                <td>{{ $offering->subject->name }}<span class="cell-sub">{{ $offering->subject->code }}</span></td>
                                <td class="d-none d-md-table-cell">{{ $offering->teacher->name }}</td>
                                <td class="d-none d-sm-table-cell text-nowrap">{{ $offering->academicPeriod->code }} <x-ui.status-badge :status="$offering->academicPeriod->status" /></td>
                                <td class="num">{{ $offering->enrollments_count }}</td>
                                <td class="text-end">
                                    <div class="table-actions">
                                        <x-ui.icon-link :href="route('admin.course-offerings.show', $offering)" icon="bi-eye" :label="'Ver oferta NRC '.$offering->nrc" />
                                        <x-ui.icon-link :href="route('reports.show', $offering)" icon="bi-file-earmark-text" :label="'Reporte NRC '.$offering->nrc" />
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <x-ui.empty-row colspan="6" icon="bi-collection" title="No hay ofertas académicas" message="Cree la primera oferta (NRC) para matricular estudiantes.">
                                <a href="{{ route('admin.course-offerings.create') }}" class="btn btn-sm btn-primary">Nueva oferta</a>
                            </x-ui.empty-row>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

        <div class="col-xl-4">
            <div class="card h-100">
                <div class="card-header d-flex justify-content-between align-items-center">
                    <h2 class="card-title">Actividad reciente</h2>
                    <a href="{{ route('admin.audit-logs.index') }}" class="small">Auditoría</a>
                </div>
                @if ($activity->isEmpty())
                    <x-ui.empty-state icon="bi-clock-history" title="Sin actividad" message="Aquí aparecerán los últimos cambios de calificaciones." />
                @else
                    <ul class="list-activity">
                        @foreach ($activity as $log)
                            <li>
                                <i class="bi {{ $log->operation === \App\Enums\AuditOperation::Correccion ? 'bi-pencil-square' : 'bi-pencil' }}" aria-hidden="true"></i>
                                <div class="min-w-0">
                                    <div class="small">
                                        <x-ui.status-badge :status="$log->operation" />
                                        <span class="fw-semibold">{{ $log->user->name }}</span>
                                    </div>
                                    <div class="small text-truncate">{{ $log->enrollment->student->full_name }} · NRC {{ $log->enrollment->courseOffering->nrc }}</div>
                                    <div class="small text-muted">{{ $log->created_at->format('d/m/Y H:i') }}</div>
                                </div>
                            </li>
                        @endforeach
                    </ul>
                @endif
            </div>
        </div>
    </div>
@else
    <x-ui.page-header title="Mis ofertas académicas" subtitle="Seleccione una oferta para registrar calificaciones o ver su reporte." />

    <div class="row g-3">
        @forelse ($offerings as $offering)
            @php($period = $offering->academicPeriod)
            @php($total = $offering->enrollments_count)
            @php($complete = $offering->complete_count)
            <div class="col-md-6 col-xxl-4">
                <article class="card offering-card h-100">
                    <div class="card-body d-flex flex-column">
                        <div class="d-flex justify-content-between align-items-start gap-2 mb-2">
                            <div>
                                <div class="small text-muted">NRC</div>
                                <div class="offering-nrc">{{ $offering->nrc }}</div>
                            </div>
                            <x-ui.status-badge :status="$period->status" />
                        </div>
                        <h2 class="h6 mb-1">{{ $offering->subject->name }}</h2>
                        <div class="small text-muted mb-3">{{ $offering->subject->code }} · Período {{ $period->code }}</div>

                        <div class="d-flex justify-content-between small mb-1">
                            <span><i class="bi bi-people me-1" aria-hidden="true"></i>{{ $total }} {{ $total == 1 ? 'matriculado' : 'matriculados' }}</span>
                            <span class="tabular" data-progress="{{ $offering->id }}">{{ $complete }} de {{ $total }} registros completos</span>
                        </div>
                        <div class="progress mb-3" role="progressbar" aria-label="Registros completos NRC {{ $offering->nrc }}"
                             aria-valuenow="{{ $complete }}" aria-valuemin="0" aria-valuemax="{{ $total }}">
                            <div class="progress-bar" style="width: {{ $total > 0 ? round($complete * 100 / $total) : 0 }}%"></div>
                        </div>

                        <div class="d-flex gap-2 mt-auto">
                            @can('recordGrades', $offering)
                                @if ($period->status->allowsTeacherGrading())
                                    <a class="btn btn-primary btn-sm" href="{{ route('offerings.grades.edit', $offering) }}"><i class="bi bi-pencil-square me-1" aria-hidden="true"></i>Calificaciones</a>
                                @else
                                    <a class="btn btn-outline-primary btn-sm" href="{{ route('offerings.grades.edit', $offering) }}"><i class="bi bi-eye me-1" aria-hidden="true"></i>Ver</a>
                                @endif
                            @endcan
                            <a class="btn btn-outline-secondary btn-sm" href="{{ route('reports.show', $offering) }}"><i class="bi bi-file-earmark-text me-1" aria-hidden="true"></i>Reporte</a>
                        </div>
                    </div>
                </article>
            </div>
        @empty
            <div class="col-12">
                <div class="card">
                    <x-ui.empty-state icon="bi-collection" title="No tiene ofertas asignadas"
                                      message="Cuando el administrador le asigne una oferta (NRC), aparecerá aquí." />
                </div>
            </div>
        @endforelse
    </div>
@endif
@endsection
