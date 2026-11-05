{{--
    Menú lateral. Ocultar enlaces por rol es solo presentación:
    la autorización la aplican el middleware role:admin y las Policies en el servidor.
--}}
@php
    $user = auth()->user();
    $sections = $user->isAdmin()
        ? [
            null => [
                ['dashboard', 'dashboard', 'bi-house-door', 'Inicio'],
            ],
            'Académico' => [
                ['admin.academic-periods.index', 'admin.academic-periods.*', 'bi-calendar3', 'Períodos'],
                ['admin.subjects.index', 'admin.subjects.*', 'bi-journal-bookmark', 'Asignaturas'],
                ['admin.course-offerings.index', ['admin.course-offerings.*', 'admin.enrollments.*'], 'bi-collection', 'Ofertas (NRC)'],
            ],
            'Personas' => [
                ['admin.students.index', 'admin.students.*', 'bi-mortarboard', 'Estudiantes'],
                ['admin.users.index', 'admin.users.*', 'bi-person-badge', 'Usuarios y docentes'],
            ],
            'Calificaciones' => [
                ['reports.index', 'reports.*', 'bi-file-earmark-text', 'Reportes'],
                ['admin.audit-logs.index', 'admin.audit-logs.*', 'bi-shield-check', 'Auditoría'],
            ],
        ]
        : [
            null => [
                ['dashboard', ['dashboard', 'offerings.grades.*'], 'bi-house-door', 'Inicio'],
                ['reports.index', 'reports.*', 'bi-file-earmark-text', 'Reportes'],
            ],
        ];
    $initials = collect(preg_split('/\s+/', trim($user->name)))->filter()->take(2)->map(fn ($p) => mb_substr($p, 0, 1))->implode('');
@endphp
<aside id="sidebar" class="sidebar offcanvas-lg offcanvas-start" tabindex="-1" aria-label="Menú principal">
    <div class="sidebar-inner">
        <div class="d-flex align-items-center">
            <a href="{{ route('dashboard') }}" class="sidebar-brand flex-grow-1" data-sidebar-tooltip="Sistema de Gestión Académica">
                <x-ui.brand-mark />
                <span class="sidebar-brand-text">Sistema de Gestión<small>Académica</small></span>
            </a>
            <button type="button" class="btn-close btn-close-white d-lg-none me-3" data-bs-dismiss="offcanvas" data-bs-target="#sidebar" aria-label="Cerrar menú"></button>
        </div>

        <nav class="sidebar-nav">
            @foreach ($sections as $section => $items)
                @if ($section)
                    <div class="sidebar-section">{{ $section }}</div>
                @endif
                @foreach ($items as [$route, $pattern, $icon, $label])
                    @if (Route::has($route))
                        @php($active = request()->routeIs(...(array) $pattern))
                        <a href="{{ route($route) }}" @class(['sidebar-link', 'active' => $active])
                           @if ($active) aria-current="page" @endif data-sidebar-tooltip="{{ $label }}">
                            <i class="bi {{ $icon }}" aria-hidden="true"></i><span class="sidebar-label">{{ $label }}</span>
                        </a>
                    @endif
                @endforeach
            @endforeach
        </nav>

        <div class="sidebar-footer">
            <div class="sidebar-user">
                <span class="avatar" aria-hidden="true">{{ mb_strtoupper($initials) }}</span>
                <span class="sidebar-user-text min-w-0">
                    <span class="sidebar-user-name d-block">{{ $user->name }}</span>
                    <span class="sidebar-user-role">{{ $user->role->label() }}</span>
                </span>
            </div>
            <form method="POST" action="{{ route('logout') }}">
                @csrf
                <button type="submit" class="sidebar-link sidebar-logout" data-sidebar-tooltip="Cerrar sesión">
                    <i class="bi bi-box-arrow-left" aria-hidden="true"></i><span class="sidebar-label">Cerrar sesión</span>
                </button>
            </form>
        </div>
    </div>
</aside>
