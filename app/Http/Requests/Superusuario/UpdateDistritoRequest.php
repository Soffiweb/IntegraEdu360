<?php

namespace App\Http\Requests\Superusuario;

use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateDistritoRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'superusuario';
    }

    public function rules(): array
    {
        return [
            'codigo' => ['required', 'string', 'regex:/^[0-9]{2}D[0-9]{2}$/', Rule::unique('distritos', 'codigo')->ignore($this->route('distrito'))],
            'nombre' => ['required', 'string', 'max:255'],
            'provincia' => ['required', 'string', 'max:100'],
        ];
    }

    public function messages(): array
    {
        return [
            'codigo.regex' => 'El código debe tener el formato 08D01.',
            'codigo.unique' => 'Este código ya pertenece a otro distrito.',
            'codigo.required' => 'Ingrese el código del distrito.',
            'nombre.required' => 'Ingrese el nombre del distrito.',
            'provincia.required' => 'Ingrese la provincia del distrito.',
        ];
    }
}
