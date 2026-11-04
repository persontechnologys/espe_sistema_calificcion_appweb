<?php

namespace App\Http\Requests\Admin;

use App\Support\Normalize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SubjectRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true; // Acceso restringido por middleware role:admin.
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Normalize::code($this->input('code')),
            'name' => Normalize::text($this->input('name')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('subjects', 'code')->ignore($this->route('subject'))],
            'name' => ['required', 'string', 'max:150'],
            'is_active' => ['boolean'],
        ];
    }
}
