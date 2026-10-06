<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class SaveRolRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        $rol = $this->route('rol');

        return [
            'codigo' => $rol
                ? ['required', Rule::in([$rol->codigo])]
                : ['required', 'string', 'max:255', 'regex:/^[A-Z0-9_]+$/', Rule::unique('roles', 'codigo')],
            'nombre' => ['required', 'string', 'max:255'],
        ];
    }

    public function messages(): array
    {
        return ['codigo.in' => 'El código de un rol existente no se puede cambiar porque identifica sus accesos.'];
    }
}
