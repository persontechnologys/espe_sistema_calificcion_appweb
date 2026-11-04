<?php

namespace App\Http\Requests\Admin;

use App\Support\Normalize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

/**
 * Datos del período. El estado NO se edita aquí: cambia con AcademicPeriodStatusController.
 */
class AcademicPeriodRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'code' => Normalize::code($this->input('code')),
            'description' => Normalize::code($this->input('description')),
        ]);
    }

    public function rules(): array
    {
        return [
            'code' => ['required', 'string', 'max:20', Rule::unique('academic_periods', 'code')->ignore($this->route('academic_period'))],
            'description' => ['required', 'string', 'max:150'],
            'start_date' => ['required', 'date'],
            'end_date' => ['required', 'date', 'after_or_equal:start_date'],
        ];
    }
}
