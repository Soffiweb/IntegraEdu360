<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ListDistritosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        return [
            'zona_id' => ['nullable', 'required_with:edit', 'integer', Rule::exists('zonas', 'id')],
            'edit' => ['nullable', 'integer', 'min:1'],
            'page' => ['nullable', 'integer', 'min:1'],
        ];
    }
}
