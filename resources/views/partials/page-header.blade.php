<div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">{{ $title }}</h1>
    @isset($createRoute)
        <a href="{{ $createRoute }}" class="btn btn-success no-print">{{ $createLabel ?? 'Nuevo' }}</a>
    @endisset
</div>
