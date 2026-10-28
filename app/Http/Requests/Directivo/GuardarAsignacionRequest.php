<?php

namespace App\Http\Requests\Directivo;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\ContextoDocente;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class GuardarAsignacionRequest extends FormRequest
{
    public function authorize(): bool
    {
        $institucion = app(ContextoDocente::class)->institucion($this);
        $asignacion = $this->route('asignacion');

        return ! $asignacion || (int) $asignacion->institucion_id === (int) $institucion->id;
    }

    protected function prepareForValidation(): void
    {
        $datos = ['titulo' => trim((string) $this->input('titulo'))];
        if ($this->input('tipo') === 'carga') {
            $datos['curso_id'] = Paralelo::whereHas('curso', fn ($query) => $query->where('institucion_id', app(ContextoDocente::class)->institucion($this)->id))
                ->find($this->integer('paralelo_id'))?->curso_id;
        }
        $this->merge($datos);
    }

    public function rules(): array
    {
        $institucionId = app(ContextoDocente::class)->institucion($this)->id;

        return [
            'periodo_id' => ['required', 'integer', Rule::exists('periodos_lectivos', 'id')->where('institucion_id', $institucionId)],
            'docente_id' => ['required', 'integer', Rule::exists('usuarios', 'id')->where('institucion_id', $institucionId)],
            'tipo' => ['required', Rule::in(array_keys(AsignacionDocente::TIPOS))],
            'titulo' => ['required', 'string', 'max:255', Rule::unique('asignaciones_docentes', 'titulo')
                ->where('periodo_id', $this->input('periodo_id'))->where('docente_id', $this->input('docente_id'))
                ->where('tipo', $this->input('tipo'))->ignore($this->route('asignacion'))],
            'horas_semanales' => ['required', 'numeric', $this->input('tipo') === 'comision' ? 'min:0' : 'min:0.25', 'max:168', 'decimal:0,2'],
            'curso_id' => ['exclude_unless:tipo,carga', 'required', 'integer', Rule::exists('cursos', 'id')->where('institucion_id', $institucionId)],
            'paralelo_id' => ['exclude_unless:tipo,carga', 'required', 'integer', Rule::exists('paralelos', 'id')->where('curso_id', $this->input('curso_id'))],
            'asignatura_id' => ['exclude_unless:tipo,carga', 'required', 'integer', Rule::exists('asignaturas', 'id')->where('institucion_id', $institucionId)],
            'observaciones' => ['nullable', 'string', 'max:2000'],
            'fecha_limite' => ['nullable', 'date_format:Y-m-d'],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $contexto = app(ContextoDocente::class);
            $institucion = $contexto->institucion($this);
            $otras = AsignacionDocente::where('institucion_id', $institucion->id)
                ->where('periodo_id', $this->integer('periodo_id'))->where('docente_id', $this->integer('docente_id'))
                ->when($this->route('asignacion'), fn ($query, $asignacion) => $query->whereKeyNot($asignacion->id));
            if ($this->input('tipo') === 'carga' && (float) (clone $otras)->where('tipo', 'carga')->sum('horas_semanales') + (float) $this->input('horas_semanales') > 25) {
                $validator->errors()->add('horas_semanales', 'La carga pedagógica semanal del docente no puede superar 25 períodos.');
            }
            if ($this->input('tipo') === 'comision' && (clone $otras)->where('tipo', 'comision')->count() >= 2) {
                $validator->errors()->add('tipo', 'Un docente puede integrar como máximo dos comisiones por período.');
            }
            if (! $contexto->docentes($institucion->id)->whereKey($this->integer('docente_id'))->exists()) {
                $validator->errors()->add('docente_id', 'Seleccione una cuenta con rol docente de su institución.');
            }
            if ($this->input('tipo') === 'carga' && ! Curso::find($this->integer('curso_id'))->asignaturas()->whereKey($this->integer('asignatura_id'))->exists()) {
                $validator->errors()->add('asignatura_id', 'La asignatura debe pertenecer al currículo del curso seleccionado.');
            }
            $periodo = PeriodoAcademico::find($this->integer('periodo_id'));
            if ($this->filled('fecha_limite') && ($this->input('fecha_limite') < $periodo->fecha_inicio->format('Y-m-d') || $this->input('fecha_limite') > $periodo->fecha_fin->format('Y-m-d'))) {
                $validator->errors()->add('fecha_limite', 'La fecha debe estar dentro del período académico.');
            }
        }];
    }
}
