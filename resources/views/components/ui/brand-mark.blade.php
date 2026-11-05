{{-- Monograma propio del sistema (no es un logotipo institucional). --}}
@props(['size' => null])
<span {{ $attributes->class(['monogram', 'monogram-lg' => $size === 'lg']) }} aria-hidden="true">
    <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.8" stroke-linecap="round" stroke-linejoin="round">
        <path d="M2 9.5 12 5l10 4.5-10 4.5L2 9.5Z"/>
        <path d="M6 11.3v4.2c0 1.4 2.7 2.8 6 2.8s6-1.4 6-2.8v-4.2"/>
        <path d="M22 9.5v5"/>
    </svg>
</span>
