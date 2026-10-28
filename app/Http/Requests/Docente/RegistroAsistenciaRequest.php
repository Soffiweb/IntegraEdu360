<?php

namespace App\Http\Requests\Docente;

use App\Models\AsistenciaEstudiante;
use App\Services\Docente\RegistroAsistencia;
use Illuminate\Foundation\Http\FormRequest;
use Illuminate\Validation\Rule;
use Illuminate\Validation\Validator;

class RegistroAsistenciaRequest extends FormRequest
{
    private ?array $contextoRegistro = null;

    public function authorize(): bool
    {
        return $this->session()->get('auth_user.role') === 'docente';
    }

    public function contexto(): array
    {
        return $this->contextoRegistro ??= app(RegistroAsistencia::class)->contexto($this);
    }

    public function rules(): array
    {
        $guardando = $this->isMethod('put');

        return [
            'periodo' => [$guardando ? 'required' : 'nullable', 'integer'],
            'carga' => [$guardando ? 'required' : 'nullable', 'integer'],
            'semana' => [$guardando ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'sesiones' => [$guardando ? 'required' : 'exclude', 'array', 'max:168'],
            'sesiones.*' => ['array:fecha,estudiantes'],
            'sesiones.*.fecha' => ['nullable', 'date_format:Y-m-d'],
            'sesiones.*.estudiantes' => ['required', 'array'],
            'sesiones.*.estudiantes.*' => ['nullable', Rule::in(array_keys(AsistenciaEstudiante::ESTADOS))],
        ];
    }

    public function after(): array
    {
        return [function (Validator $validator) {
            if ($validator->errors()->isNotEmpty()) {
                return;
            }
            $contexto = $this->contexto();
            if (! $this->isMethod('put')) {
                return;
            }
            $entradas = $this->input('sesiones', []);
            $permitidos = $contexto['matriculas']->pluck('id')->map(fn ($id) => (string) $id)->all();
            if (! $contexto['carga'] || count($entradas) !== $contexto['cantidad'] || ! count($permitidos)) {
                $validator->errors()->add('sesiones', 'El registro debe corresponder a la carga semanal y tener estudiantes matriculados.');
            }
            foreach ($entradas as $numero => $entrada) {
                if (! ctype_digit((string) $numero) || (string) (int) $numero !== (string) $numero || (int) $numero < 1 || (int) $numero > $contexto['cantidad']) {
                    $validator->errors()->add('sesiones', 'El número de períodos supera la carga semanal asignada.');
                }
                $ids = array_map('strval', array_keys($entrada['estudiantes'] ?? []));
                if (array_diff($ids, $permitidos) || array_diff($permitidos, $ids)) {
                    $validator->errors()->add('sesiones', 'La lista de estudiantes cambió o contiene matrículas ajenas. Recargue la vista.');
                }
                $fecha = $entrada['fecha'] ?? null;
                $marcados = array_filter($entrada['estudiantes'] ?? []);
                if (! $fecha && ($marcados || $contexto['sesiones']->get($numero)?->asistencias->isNotEmpty())) {
                    $validator->errors()->add("sesiones.$numero.fecha", 'Seleccione la fecha de la clase antes de registrar asistencia.');
                }
                if ($fecha && ($fecha < $contexto['semana']->toDateString() || $fecha > $contexto['semana']->endOfWeek()->toDateString()
                    || $fecha < $contexto['periodo']->fecha_inicio->toDateString() || $fecha > $contexto['periodo']->fecha_fin->toDateString())) {
                    $validator->errors()->add("sesiones.$numero.fecha", 'La fecha debe pertenecer a esta semana y al período académico.');
                }
            }
        }];
    }
}
