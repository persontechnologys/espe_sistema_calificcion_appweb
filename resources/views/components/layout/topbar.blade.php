@props(['title' => ''])
<header class="topbar no-print">
    <button type="button" class="btn btn-icon btn-light d-lg-none" data-bs-toggle="offcanvas" data-bs-target="#sidebar" aria-controls="sidebar" aria-label="Abrir menú">
        <i class="bi bi-list fs-4" aria-hidden="true"></i>
    </button>
    <button type="button" class="btn btn-icon btn-light d-none d-lg-inline-flex" data-sidebar-toggle aria-controls="sidebar" aria-expanded="true" aria-label="Contraer menú">
        <i class="bi bi-layout-sidebar-inset" aria-hidden="true"></i>
    </button>
    <div class="topbar-title">
        <x-layout.breadcrumbs :title="$title" />
    </div>
    <div class="dropdown">
        <button class="btn btn-light dropdown-toggle" type="button" data-bs-toggle="dropdown" aria-expanded="false">
            <i class="bi bi-person-circle" aria-hidden="true"></i>
            <span class="d-none d-md-inline">{{ auth()->user()->name }}</span>
            <span class="visually-hidden d-md-none">Menú de usuario</span>
        </button>
        <ul class="dropdown-menu dropdown-menu-end shadow-sm">
            <li class="px-3 py-2">
                <div class="fw-semibold">{{ auth()->user()->name }}</div>
                <div class="small text-muted">{{ auth()->user()->role->label() }}</div>
            </li>
            <li><hr class="dropdown-divider"></li>
            <li>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" class="dropdown-item"><i class="bi bi-box-arrow-left me-2" aria-hidden="true"></i>Cerrar sesión</button>
                </form>
            </li>
        </ul>
    </div>
</header>
