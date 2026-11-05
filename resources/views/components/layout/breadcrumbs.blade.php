{{-- Migas de pan derivadas del nombre de la ruta; el último elemento es el título de la página. --}}
@props(['title' => ''])
@php
    $modules = [
        'admin.academic-periods.' => ['Académico', 'Períodos', 'admin.academic-periods.index'],
        'admin.subjects.' => ['Académico', 'Asignaturas', 'admin.subjects.index'],
        'admin.course-offerings.' => ['Académico', 'Ofertas (NRC)', 'admin.course-offerings.index'],
        'admin.enrollments.' => ['Académico', 'Ofertas (NRC)', 'admin.course-offerings.index'],
        'admin.students.' => ['Personas', 'Estudiantes', 'admin.students.index'],
        'admin.users.' => ['Personas', 'Usuarios y docentes', 'admin.users.index'],
        'admin.audit-logs.' => ['Calificaciones', 'Auditoría', 'admin.audit-logs.index'],
        'reports.' => ['Calificaciones', 'Reportes', 'reports.index'],
        'offerings.grades.' => ['Calificaciones', null, null],
    ];
    $name = (string) request()->route()?->getName();
    $module = collect($modules)->first(fn ($m, $prefix) => str_starts_with($name, $prefix));
    $isIndex = $module && $module[2] === $name;
@endphp
<nav aria-label="Ruta de navegación">
    <ol class="breadcrumb">
        @if ($name === 'dashboard')
            <li class="breadcrumb-item active" aria-current="page">Inicio</li>
        @else
            <li class="breadcrumb-item"><a href="{{ route('dashboard') }}">Inicio</a></li>
            @if ($module)
                <li class="breadcrumb-item d-none d-md-list-item">{{ $module[0] }}</li>
                @if ($module[1] && ! $isIndex)
                    <li class="breadcrumb-item d-none d-sm-list-item"><a href="{{ route($module[2]) }}">{{ $module[1] }}</a></li>
                @endif
            @endif
            <li class="breadcrumb-item active" aria-current="page">{{ $title }}</li>
        @endif
    </ol>
</nav>
