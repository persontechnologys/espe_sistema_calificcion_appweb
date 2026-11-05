@props(['icon' => 'bi-inbox', 'title' => 'Sin registros', 'message' => null])
<div {{ $attributes->class(['empty-state']) }}>
    <div class="empty-state-icon"><i class="bi {{ $icon }}" aria-hidden="true"></i></div>
    <div class="empty-state-title">{{ $title }}</div>
    @if ($message)
        <p>{{ $message }}</p>
    @endif
    {{ $slot }}
</div>
