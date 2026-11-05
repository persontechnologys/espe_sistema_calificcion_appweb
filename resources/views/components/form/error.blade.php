@props(['name', 'bag' => 'default'])
@error($name, $bag)
    <div class="invalid-feedback d-block">{{ $message }}</div>
@enderror
