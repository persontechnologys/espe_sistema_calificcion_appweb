@if (session('status'))
    <div class="alert alert-success no-print" role="status">{{ session('status') }}</div>
@endif
@if ($errors->has('general'))
    <div class="alert alert-danger no-print" role="alert">{{ $errors->first('general') }}</div>
@endif
