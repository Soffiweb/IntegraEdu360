<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateZonaRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'integer', 'min:1', 'max:255', Rule::unique('zonas', 'codigo')->ignore($this->route('zona'))],
            'nombre' => ['required', 'string', 'max:255'],
            'cobertura' => ['required', 'string', 'max:10000'],
        ];
    }
}
