<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\CourseOffering;
use App\Support\Normalize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class CourseOfferingRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'nrc' => Normalize::code($this->input('nrc')),
            'classroom' => Normalize::text($this->input('classroom')),
            'campus' => Normalize::text($this->input('campus')),
        ]);
    }

    public function rules(): array
    {
        /** @var CourseOffering|null $offering */
        $offering = $this->route('course_offering');

        return [
            'academic_period_id' => ['required', 'integer', Rule::exists('academic_periods', 'id')],
            // Una asignatura inactiva no se puede ofertar de nuevo, pero la oferta existente puede conservarla.
            'subject_id' => [
                'required', 'integer',
                $offering && (int) $this->input('subject_id') === $offering->subject_id
                    ? Rule::exists('subjects', 'id')
                    : Rule::exists('subjects', 'id')->where('is_active', true),
            ],
            // La BD solo garantiza que teacher_id sea un usuario; que sea docente activo se valida aquí.
            'teacher_id' => [
                'required', 'integer',
                Rule::exists('users', 'id')->where('role', UserRole::Teacher->value)->where('is_active', true),
            ],
            // Decisión 18.1: NRC único dentro del período.
            'nrc' => [
                'required', 'string', 'max:20',
                Rule::unique('course_offerings', 'nrc')
                    ->where('academic_period_id', (int) $this->input('academic_period_id'))
                    ->ignore($offering),
            ],
            'classroom' => ['nullable', 'string', 'max:50'],
            'campus' => ['nullable', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'teacher_id.exists' => 'El usuario seleccionado no es un docente activo.',
            'subject_id.exists' => 'La asignatura seleccionada no existe o está inactiva.',
            'nrc.unique' => 'Ya existe una oferta con ese NRC en el período seleccionado.',
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            /** @var CourseOffering|null $offering */
            $offering = $this->route('course_offering');

            // Mover una oferta con matrículas a otro período llevaría sus notas a otro período.
            if ($offering
                && (int) $this->input('academic_period_id') !== $offering->academic_period_id
                && $offering->enrollments()->exists()) {
                $validator->errors()->add('academic_period_id', 'La oferta tiene matrículas: no se puede cambiar de período.');
            }
        }];
    }
}
