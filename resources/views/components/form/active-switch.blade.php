@props(['value' => true, 'help' => 'Un registro inactivo se conserva, pero no se ofrece para nuevas operaciones.'])
<div class="form-check form-switch mb-3">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" role="switch" name="is_active" id="is_active" value="1"
           aria-describedby="is_active-help" @checked(old('is_active', $value ?? true))>
    <label class="form-check-label" for="is_active">Activo</label>
    <div id="is_active-help" class="form-text">{{ $help }}</div>
    <x-form.error name="is_active" />
</div>
