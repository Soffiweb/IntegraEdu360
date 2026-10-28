<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class VincularMatriculas extends Command
{
    protected $signature = 'matriculas:vincular {institucion} {periodo} {--aplicar : Guardar las matrículas; por defecto solo simula}';

    protected $description = 'Distribuye estudiantes sin matrícula por edad y cupos, conservando las matrículas existentes';

    public function handle(): int
    {
        $institucion = Institucion::findOrFail($this->argument('institucion'));
        $periodo = PeriodoAcademico::where('institucion_id', $institucion->id)->findOrFail($this->argument('periodo'));
        $resultado = DB::transaction(function () use ($institucion, $periodo) {
            $paralelos = Paralelo::where('estado', 'ACTIVO')
                ->whereHas('curso', fn ($q) => $q->where('institucion_id', $institucion->id)->where('estado', 'ACTIVO'))
                ->with('curso.especialidad')->lockForUpdate()->get()
                ->sortBy(fn ($p) => sprintf('%03d|%010d|%s', $p->curso->grado, $p->curso_id, $p->letra))->values();
            $existentes = Matricula::where('periodo_id', $periodo->id)->lockForUpdate()->get();
            $ocupacion = $existentes->where('estado', 'ACTIVA')->countBy('paralelo_id')->all();
            $alumnos = Usuario::alumnos()->where('institucion_id', $institucion->id)->where('estado', 'ACTIVO')
                ->whereNotIn('id', $existentes->pluck('estudiante_id'))->with('persona')->lockForUpdate()->get()
                ->sortBy(fn ($u) => ($u->persona?->fecha_nacimiento ?? '9999-12-31').'|'.sprintf('%010d', $u->id), SORT_STRING, true)->values();
            $cupos = $paralelos->mapWithKeys(fn ($p) => [$p->id => max(0, $p->capacidad - ($ocupacion[$p->id] ?? 0))])->all();
            if (array_sum($cupos) < $alumnos->count()) {
                throw new \RuntimeException('La capacidad disponible no permite matricular a todos los estudiantes. No se guardaron cambios.');
            }
            $nuevas = array_fill_keys($paralelos->pluck('id')->all(), 0);
            for ($i = 0; $i < $alumnos->count(); $i++) {
                $candidatos = $paralelos->filter(fn ($p) => $nuevas[$p->id] < $cupos[$p->id]);
                $destino = $candidatos->sortBy(fn ($p) => ($ocupacion[$p->id] ?? 0) + $nuevas[$p->id])->first();
                $nuevas[$destino->id]++;
            }
            $indice = 0;
            foreach ($paralelos as $paralelo) {
                for ($i = 0; $i < $nuevas[$paralelo->id]; $i++) {
                    $alumno = $alumnos[$indice++];
                    if ($this->option('aplicar')) {
                        Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id,
                            'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id, 'estado' => 'ACTIVA']);
                    }
                }
            }

            return ['aplicado' => (bool) $this->option('aplicar'), 'institucion' => $institucion->nombre,
                'periodo' => $periodo->nombre, 'matriculas_nuevas' => $indice,
                'cursos' => $paralelos->pluck('curso_id')->unique()->count(), 'paralelos' => $paralelos->count(),
                'distribucion' => $paralelos->map(fn ($p) => ['paralelo_id' => $p->id, 'curso' => $p->curso->nombre,
                    'especialidad' => $p->curso->especialidad?->nombre, 'paralelo' => $p->letra,
                    'matriculados' => ($ocupacion[$p->id] ?? 0) + $nuevas[$p->id], 'capacidad' => $p->capacidad])->all()];
        });
        $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
