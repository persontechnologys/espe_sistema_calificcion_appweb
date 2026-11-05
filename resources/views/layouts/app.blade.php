@php($pageTitle = html_entity_decode(trim($__env->yieldContent('title', 'Inicio')), ENT_QUOTES | ENT_HTML5, 'UTF-8'))
<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layout.head :title="$pageTitle" />
    @stack('styles')
</head>
<body>
<a class="visually-hidden-focusable" href="#main">Saltar al contenido</a>
<div class="app-shell">
    <x-layout.sidebar />
    <div class="app-main">
        <x-layout.topbar :title="$pageTitle" />
        <main id="main" class="app-content" tabindex="-1">
            @yield('content')
        </main>
        <footer class="app-footer no-print">
            Sistema de Gestión Académica · <x-ui.attribution />
        </footer>
    </div>
</div>
<x-ui.toast-stack />
<x-ui.confirm-modal />
@stack('modals')
</body>
</html>
