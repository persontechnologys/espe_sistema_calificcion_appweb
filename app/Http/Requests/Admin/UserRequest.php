<?php

namespace App\Http\Requests\Admin;

use App\Enums\UserRole;
use App\Models\User;
use App\Support\Normalize;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\Validator;

/**
 * Administradores y docentes. Un docente debe tener ID institucional (también CHECK en BD).
 * La contraseña es obligatoria al crear; al editar, si se envía, la restablece el administrador.
 */
class UserRequest extends FormRequest
{
    public function authorize(): bool
    {
        return true;
    }

    protected function prepareForValidation(): void
    {
        $this->merge([
            'name' => Normalize::text($this->input('name')),
            'email' => Normalize::email($this->input('email')),
            'institutional_id' => Normalize::code($this->input('institutional_id')),
            'is_active' => $this->boolean('is_active'),
        ]);
    }

    public function rules(): array
    {
        /** @var User|null $user */
        $user = $this->route('user');

        return [
            'name' => ['required', 'string', 'max:150'],
            'email' => ['required', 'email', 'max:255', Rule::unique('users', 'email')->ignore($user)],
            'role' => ['required', Rule::enum(UserRole::class)],
            'institutional_id' => [
                'nullable', 'string', 'max:20',
                Rule::requiredIf($this->input('role') === UserRole::Teacher->value),
                Rule::unique('users', 'institutional_id')->ignore($user),
            ],
            'password' => [$user ? 'nullable' : 'required', 'confirmed', Password::defaults()],
            'is_active' => ['boolean'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            /** @var User|null $user */
            $user = $this->route('user');

            if ($user === null) {
                return;
            }

            // Evita que el administrador se bloquee a sí mismo.
            if ($user->is($this->user())) {
                if (! $this->boolean('is_active')) {
                    $validator->errors()->add('is_active', 'No puede desactivar su propia cuenta.');
                }
                if ($this->input('role') !== UserRole::Admin->value) {
                    $validator->errors()->add('role', 'No puede quitarse el rol de administrador.');
                }
            }

            // Un docente con ofertas asignadas no puede dejar de serlo: course_offerings.teacher_id
            // quedaría apuntando a un usuario que no es docente.
            if ($user->isTeacher() && $this->input('role') !== UserRole::Teacher->value && $user->courseOfferings()->exists()) {
                $validator->errors()->add('role', 'El docente tiene ofertas asignadas. Reasígnelas antes de cambiar el rol.');
            }
        }];
    }

    /** @return array<string, mixed> */
    public function userData(): array
    {
        $data = $this->safe()->only(['name', 'email', 'role', 'institutional_id', 'is_active']);

        if ($this->filled('password')) {
            $data['password'] = $this->input('password'); // Se hashea por el cast 'hashed'.
        }

        return $data;
    }
}
