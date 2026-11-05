{{-- Barra de búsqueda y filtros dentro de la tarjeta del listado. "Limpiar" solo aparece con filtros activos. --}}
@props(['placeholder' => 'Buscar…', 'search' => 'q', 'searchLabel' => 'Buscar'])
@php($hasFilters = collect(request()->except('page'))->filter(fn ($v) => $v !== null && $v !== '')->isNotEmpty())
<form method="GET" class="card-toolbar no-print" role="search">
    @if ($search)
        <div class="input-group toolbar-search">
            <span class="input-group-text bg-white"><i class="bi bi-search" aria-hidden="true"></i></span>
            <input type="search" name="{{ $search }}" value="{{ request($search) }}" class="form-control" placeholder="{{ $placeholder }}" aria-label="{{ $searchLabel }}">
        </div>
    @endif
    {{ $slot }}
    <button class="btn btn-outline-primary" type="submit"><i class="bi bi-funnel me-1" aria-hidden="true"></i>Filtrar</button>
    @if ($hasFilters)
        <a class="btn btn-link text-decoration-none" href="{{ url()->current() }}"><i class="bi bi-x-circle me-1" aria-hidden="true"></i>Limpiar</a>
    @endif
</form>
