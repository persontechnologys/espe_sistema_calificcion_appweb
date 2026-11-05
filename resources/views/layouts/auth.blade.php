<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <x-layout.head :title="html_entity_decode(trim($__env->yieldContent('title', 'Iniciar sesión')), ENT_QUOTES | ENT_HTML5, 'UTF-8')" />
</head>
<body class="auth-page">
@yield('content')
</body>
</html>
