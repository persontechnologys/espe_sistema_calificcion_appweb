{{-- Páginas de error propias: sin trazas ni detalles técnicos (especificación §11, decisión 32). --}}
<!DOCTYPE html>
<html lang="es">
<head>
    <x-layout.head :title="$code.' · '.$title" />
</head>
<body>
<main class="error-page">
    <div class="card error-card">
        <div class="d-flex justify-content-center mb-3"><x-ui.brand-mark size="lg" /></div>
        <div class="error-code">{{ $code }}</div>
        <h1 class="h4 my-3">{{ $title }}</h1>
        <p class="text-muted">{{ $message }}</p>
        <div class="d-flex justify-content-center gap-2 flex-wrap">
            <a href="{{ url('/dashboard') }}" class="btn btn-primary"><i class="bi bi-house-door me-1" aria-hidden="true"></i>Volver al inicio</a>
        </div>
    </div>
    <footer class="auth-footer">Sistema de Gestión Académica · <x-ui.attribution /></footer>
</main>
</body>
</html>
