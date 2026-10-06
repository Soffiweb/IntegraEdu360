<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListUsuariosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        return [
            'q' => ['nullable', 'string', 'max:255'],
            'estado' => ['nullable', Rule::in(['ACTIVO', 'INACTIVO', 'OBSERVACION'])],
            'page' => ['nullable', 'integer', 'min:1'],
            'edit' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
