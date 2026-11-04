<form method="GET" class="d-flex gap-2 mb-3 no-print" role="search">
    <input type="search" name="q" value="{{ request('q') }}" class="form-control" placeholder="{{ $placeholder ?? 'Buscar…' }}" style="max-width: 22rem">
    {{ $slot ?? '' }}
    <button class="btn btn-outline-secondary" type="submit">Buscar</button>
    @if (request()->query())
        <a class="btn btn-link" href="{{ url()->current() }}">Limpiar</a>
    @endif
</form>
