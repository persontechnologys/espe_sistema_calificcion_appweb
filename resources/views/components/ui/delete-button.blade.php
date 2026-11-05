{{-- Eliminar con el modal de confirmación. $item identifica el registro (se escapa con {{ }}). --}}
@props(['action', 'item' => null, 'label' => 'Eliminar', 'title' => '¿Eliminar este registro?',
        'message' => 'Esta acción no se puede deshacer. Si el registro tiene datos dependientes, el sistema la rechazará.'])
<form method="POST" action="{{ $action }}" class="d-inline"
      data-confirm-title="{{ $title }}" data-confirm-message="{{ $message }}"
      data-confirm-item="{{ $item }}" data-confirm-variant="danger" data-confirm-button="{{ $label }}">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-icon btn-outline-danger" aria-label="{{ $label }}{{ $item ? ' '.$item : '' }}"
            data-bs-toggle="tooltip" data-bs-title="{{ $label }}"><i class="bi bi-trash" aria-hidden="true"></i></button>
</form>
