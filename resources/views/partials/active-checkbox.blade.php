<div class="form-check mb-3">
    <input type="hidden" name="is_active" value="0">
    <input class="form-check-input" type="checkbox" name="is_active" id="is_active" value="1"
           @checked(old('is_active', $value ?? true))>
    <label class="form-check-label" for="is_active">Activo</label>
    @include('partials.field-error', ['name' => 'is_active'])
</div>
