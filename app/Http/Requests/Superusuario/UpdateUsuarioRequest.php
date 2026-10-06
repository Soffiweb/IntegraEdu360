<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateUsuarioRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        return [
            'username' => ['required', 'string', 'max:255', Rule::unique('usuarios', 'username')->ignore($this->route('usuario'))],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('usuarios', 'email')->ignore($this->route('usuario'))],
            'password' => ['nullable', 'string', 'min:8', 'max:255', 'confirmed'],
        ];
    }
}
