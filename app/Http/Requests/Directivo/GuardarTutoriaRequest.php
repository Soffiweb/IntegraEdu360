<?php

namespace App\Http\Requests\Directivo;

use App\Services\Directivo\ContextoDocente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarTutoriaRequest extends FormRequest
{
    public function authorize(): bool
    {
        $institucion = app(ContextoDocente::class)->institucion($this);

        return (int) $this->route('paralelo')->curso->institucion_id === (int) $institucion->id;
    }

    public function rules(): array
    {
        $institucionId = app(ContextoDocente::class)->institucion($this)->id;

        return ['periodo_id' => ['required', 'integer', Rule::exists('periodos_lectivos', 'id')->where('institucion_id', $institucionId)],
            'docente_id' => ['present', 'nullable', 'integer', Rule::exists('usuarios', 'id')->where('institucion_id', $institucionId)->where('estado', 'ACTIVO')]];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty() || ! $this->filled('docente_id')) {
                return;
            }
            $contexto = app(ContextoDocente::class);
            if (! $contexto->docentes($contexto->institucion($this)->id)->whereKey($this->integer('docente_id'))->exists()) {
                $validator->errors()->add('docente_id', 'Seleccione un docente activo de su institución.');
            }
        }];
    }
}
