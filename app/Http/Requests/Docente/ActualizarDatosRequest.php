<?php

namespace App\Http\Requests\Docente;

use App\Models\Usuario;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;

class ActualizarDatosRequest extends FormRequest
{
    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'docente';
    }

    public function rules(): array
    {
        $docente = Usuario::findOrFail($this->session()->get('auth_user.id'));

        return [
            'numero_identificacion' => ['required', 'string', 'max:30', Rule::unique('personas', 'numero_identificacion')->ignore($docente->persona_id)],
            'primer_nombre' => ['required', 'string', 'max:120'],
            'segundo_nombre' => ['nullable', 'string', 'max:120'],
            'primer_apellido' => ['required', 'string', 'max:120'],
            'segundo_apellido' => ['nullable', 'string', 'max:120'],
            'fecha_nacimiento' => ['nullable', 'date_format:Y-m-d', 'before_or_equal:today'],
            'sexo' => ['nullable', Rule::in(['M', 'F'])],
            'telefono' => ['nullable', 'string', 'max:50'],
            'celular' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'canton' => ['nullable', 'string', 'max:120'],
            'parroquia' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ];
    }
}
