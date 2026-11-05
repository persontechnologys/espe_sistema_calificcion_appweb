@props(['title', 'subtitle' => null])
<div {{ $attributes->class(['page-header']) }}>
    <div class="min-w-0">
        <h1 class="page-title">{{ $title }}</h1>
        @if ($subtitle)
            <p class="page-subtitle">{{ $subtitle }}</p>
        @endif
        {{ $meta ?? '' }}
    </div>
    @isset($actions)
        <div class="page-actions no-print">{{ $actions }}</div>
    @endisset
</div>
