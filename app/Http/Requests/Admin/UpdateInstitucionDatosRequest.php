<?php

namespace App\Http\Requests\Admin;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class UpdateInstitucionDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        $sesion = $this->session()->get('auth_user', []);
        $institucionId = ! empty($sesion['id'])
            ? Usuario::find($sesion['id'])?->institucion_id : ($sesion['institucion_id'] ?? null);

        return ($sesion['role'] ?? null) === 'admin'
            && $institucionId && (int) $institucionId === (int) $this->route('institucion')->id;
    }

    public function rules(): array
    {
        $institucion = $this->route('institucion');

        return [
            'codigo_amie' => ['required', 'string', 'max:20', Rule::unique('instituciones', 'codigo_amie')->ignore($institucion)],
            'nombre' => ['required', 'string', 'max:255'],
            'slogan' => ['nullable', 'string', 'max:255'],
            'representante_nombre' => ['nullable', 'string', 'max:255'],
            'representante_cargo' => ['nullable', 'string', 'max:120'],
            'logo1' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'logo2' => ['nullable', 'image', 'mimes:jpg,jpeg,png,webp', 'extensions:jpg,jpeg,png,webp', 'max:2048'],
            'sostenimiento_id' => ['required', 'integer', Rule::exists('cat_sostenimiento', 'id')],
            'regimen_id' => ['required', 'integer', Rule::exists('cat_regimen_escolar', 'id')],
            'provincia' => ['required', 'string', 'max:120'],
            'canton' => ['required', 'string', 'max:120'],
            'parroquia' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('instituciones', 'email')->ignore($institucion)],
            'estado' => ['required', Rule::in(['ACTIVO', 'INACTIVO'])],
        ];
    }

    protected function prepareForValidation(): void
    {
        if (is_string($this->input('codigo_amie'))) {
            $this->merge(['codigo_amie' => strtoupper(trim($this->input('codigo_amie')))]);
        }
    }
}
