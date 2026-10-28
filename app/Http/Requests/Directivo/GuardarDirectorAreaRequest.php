<?php

namespace App\Http\Requests\Directivo;

use App\Models\DirectorArea;
use App\Services\Directivo\ContextoDocente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarDirectorAreaRequest extends FormRequest
{
    public function authorize(): bool
    {
        app(ContextoDocente::class)->institucion($this);

        return true;
    }

    public function rules(): array
    {
        $institucionId = app(ContextoDocente::class)->institucion($this)->id;

        return ['area' => ['required', Rule::in(array_keys(DirectorArea::AREAS))],
            'periodo_id' => ['required', 'integer', Rule::exists('periodos_lectivos', 'id')->where('institucion_id', $institucionId)],
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
