<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="utf-8"><meta name="viewport" content="width=device-width, initial-scale=1">
    <title>{{ $code }} · {{ config('app.name') }}</title>
    <link rel="stylesheet" href="{{ asset('vendor/bootstrap/bootstrap.min.css') }}">
</head>
<body class="bg-light">
<main class="container py-5 text-center" style="max-width: 36rem">
    <div class="display-4 text-muted">{{ $code }}</div>
    <h1 class="h4 my-3">{{ $title }}</h1>
    <p class="text-muted">{{ $message }}</p>
    <a href="{{ url('/dashboard') }}" class="btn btn-success">Volver al inicio</a>
</main>
</body>
</html>
