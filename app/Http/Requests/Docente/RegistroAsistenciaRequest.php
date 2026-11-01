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
            'fecha' => [$guardando ? 'required' : 'nullable', 'date_format:Y-m-d'],
            'horas' => [$guardando ? 'required' : 'exclude', 'integer', 'min:1'],
            'tema' => [$guardando ? 'nullable' : 'exclude', 'string', 'max:255'],
            'estudiantes' => [$guardando ? 'required' : 'exclude', 'array'],
            'estudiantes.*' => ['nullable', Rule::in(array_keys(AsistenciaEstudiante::ESTADOS))],
            'observaciones' => [$guardando ? 'nullable' : 'exclude', 'array'],
            'observaciones.*' => ['nullable', 'string', 'max:500'],
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
            $permitidos = $contexto['matriculas']->pluck('id')->map(fn ($id) => (string) $id)->all();
            $estados = $this->input('estudiantes', []);
            $ids = array_map('strval', array_keys($estados));
            if (! $contexto['carga'] || ! count($permitidos)) {
                $validator->errors()->add('estudiantes', 'El registro debe corresponder a una carga con estudiantes matriculados.');
            } elseif (array_diff($ids, $permitidos) || array_diff($permitidos, $ids)
                || array_diff(array_map('strval', array_keys($this->input('observaciones', []))), $permitidos)) {
                $validator->errors()->add('estudiantes', 'La lista de estudiantes cambió o contiene matrículas ajenas. Recargue la vista.');
            }
            if ((int) $this->input('horas') > $contexto['maxHoras']) {
                $validator->errors()->add('horas', "La clase no puede durar más de {$contexto['maxHoras']} períodos según la carga semanal.");
            }
            foreach ($this->input('observaciones', []) as $matricula => $observacion) {
                if (filled($observacion) && blank($estados[$matricula] ?? null)) {
                    $validator->errors()->add("observaciones.$matricula", 'Marque la asistencia del estudiante antes de registrar una observación.');
                }
            }
        }];
    }
}
