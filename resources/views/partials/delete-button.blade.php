<form method="POST" action="{{ $action }}" class="d-inline" data-confirm="¿Eliminar este registro? Esta acción no se puede deshacer.">
    @csrf
    @method('DELETE')
    <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
</form>
