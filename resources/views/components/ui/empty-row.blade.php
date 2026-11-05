{{-- Fila de tabla con estado vacío. --}}
@props(['colspan', 'icon' => 'bi-inbox', 'title' => 'Sin registros', 'message' => null])
<tr>
    <td colspan="{{ $colspan }}" class="p-0">
        <x-ui.empty-state :icon="$icon" :title="$title" :message="$message">{{ $slot }}</x-ui.empty-state>
    </td>
</tr>
