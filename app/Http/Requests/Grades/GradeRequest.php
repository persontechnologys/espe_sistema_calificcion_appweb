<?php

namespace App\Http\Requests\Grades;

use Illuminate\Foundation\Http\FormRequest;

/**
 * Tres parciales opcionales (vacío = pendiente/NULL), 0–20 con máximo dos decimales,
 * y la versión que tenía la nota al abrir el formulario (bloqueo optimista).
 * Cualquier otro campo enviado (promedio, total, estado) se ignora: solo se usan los validados.
 */
class GradeRequest extends FormRequest
{
    public const PARTIAL_REGEX = '/^\d{1,2}(\.\d{1,2})?$/';

    public function authorize(): bool
    {
        return true; // La autorización se hace con Policies en el controlador.
    }

    protected function prepareForValidation(): void
    {
        // Errores separados por fila de la planilla.
        $this->errorBag = 'grade_'.$this->route('enrollment')?->id;

        $normalized = [];
        foreach (['partial_1', 'partial_2', 'partial_3'] as $field) {
            $value = $this->input($field);
            // Se acepta coma decimal (10,50) además del punto.
            $normalized[$field] = is_string($value) ? str_replace(',', '.', trim($value)) : $value;
            if ($normalized[$field] === '') {
                $normalized[$field] = null;
            }
        }
        $this->merge($normalized);
    }

    public function rules(): array
    {
        $partial = ['nullable', 'string', 'regex:'.self::PARTIAL_REGEX, 'numeric', 'between:0,20'];

        return [
            'partial_1' => $partial,
            'partial_2' => $partial,
            'partial_3' => $partial,
            'version' => ['required', 'integer', 'min:0'],
        ];
    }

    public function messages(): array
    {
        $msg = 'El :attribute debe ser un número entre 0 y 20 con máximo dos decimales.';

        return [
            'partial_1.regex' => $msg, 'partial_2.regex' => $msg, 'partial_3.regex' => $msg,
            'partial_1.between' => $msg, 'partial_2.between' => $msg, 'partial_3.between' => $msg,
        ];
    }

    /** @return array{0: ?string, 1: ?string, 2: ?string} */
    public function partials(): array
    {
        $v = $this->validated();

        return [$v['partial_1'] ?? null, $v['partial_2'] ?? null, $v['partial_3'] ?? null];
    }

    public function expectedVersion(): int
    {
        return (int) $this->validated('version');
    }
}
