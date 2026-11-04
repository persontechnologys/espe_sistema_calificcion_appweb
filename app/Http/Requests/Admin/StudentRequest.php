<?php

namespace App\Http\Requests\Admin;

use App\Support\Normalize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class StudentRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'institutional_id' => Normalize::code($this->input('institutional_id')),
            'first_names' => Normalize::text($this->input('first_names')),
            'last_names' => Normalize::text($this->input('last_names')),
            'email' => Normalize::email($this->input('email')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        return [
            'institutional_id' => ['required', 'string', 'max:20', Rule::unique('students', 'institutional_id')->ignore($this->route('student'))],
            'first_names' => ['required', 'string', 'max:100'],
            'last_names' => ['required', 'string', 'max:100'],
            'email' => ['nullable', 'email', 'max:255'],
            'is_active' => ['boolean'],
        ];
    }
}
