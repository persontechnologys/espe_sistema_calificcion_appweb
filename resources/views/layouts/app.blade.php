<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>@yield('title', 'Inicio') · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
    <style>
        body { background: #f5f6f8; }
        .navbar-brand small { font-size: .7rem; opacity: .75; }
        @media print {
            .no-print { display: none !important; }
            body { background: #fff; }
            main { padding: 0 !important; }
        }
    </style>
    @stack('styles')
</head>
<body>
@auth
    <nav class="navbar navbar-expand-lg navbar-dark bg-success no-print">
        <div class="container-fluid">
            <a class="navbar-brand" href="{{ route('dashboard') }}">
                Gestión Académica <small>demo</small>
            </a>
            <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#nav" aria-label="Menú">
                <span class="navbar-toggler-icon"></span>
            </button>
            <div class="collapse navbar-collapse" id="nav">
                <ul class="navbar-nav me-auto">
                    <li class="nav-item"><a class="nav-link @if(request()->routeIs('dashboard')) active @endif" href="{{ route('dashboard') }}">Inicio</a></li>
                    @if (auth()->user()->isAdmin())
                        @foreach ([
                            'admin.academic-periods.index' => 'Períodos',
                            'admin.subjects.index' => 'Asignaturas',
                            'admin.students.index' => 'Estudiantes',
                            'admin.users.index' => 'Usuarios y docentes',
                            'admin.course-offerings.index' => 'Ofertas (NRC)',
                        ] as $routeName => $label)
                            @if (Route::has($routeName))
                                <li class="nav-item"><a class="nav-link @if(request()->routeIs(Str::beforeLast($routeName, '.').'.*')) active @endif" href="{{ route($routeName) }}">{{ $label }}</a></li>
                            @endif
                        @endforeach
                    @endif
                    @if (Route::has('reports.index'))
                        <li class="nav-item"><a class="nav-link @if(request()->routeIs('reports.*')) active @endif" href="{{ route('reports.index') }}">Reportes</a></li>
                    @endif
                    @if (auth()->user()->isAdmin() && Route::has('admin.audit-logs.index'))
                        <li class="nav-item"><a class="nav-link @if(request()->routeIs('admin.audit-logs.*')) active @endif" href="{{ route('admin.audit-logs.index') }}">Auditoría</a></li>
                    @endif
                </ul>
                <span class="navbar-text text-white me-3 small">
                    {{ auth()->user()->name }} · {{ auth()->user()->role->label() }}
                </span>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button class="btn btn-outline-light btn-sm" type="submit">Cerrar sesión</button>
                </form>
            </div>
        </div>
    </nav>
@endauth

<main class="container-fluid py-4 px-lg-5">
    @include('partials.flash')
    @yield('content')
</main>

<footer class="text-center text-muted small pb-3 no-print">
    Sistema de demostración. No es un documento ni sistema oficial de la Universidad de las Fuerzas Armadas - ESPE.
</footer>
<script src="{{ asset('vendor/bootstrap/bootstrap.bundle.min.js') }}"></script>
</body>
</html>
