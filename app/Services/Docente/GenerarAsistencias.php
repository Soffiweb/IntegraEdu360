<?php

namespace App\Services\Docente;

use App\Models\AsignacionDocente;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\SesionAsistencia;
use Carbon\CarbonImmutable;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Random\Engine\Mt19937;
use Random\Randomizer;

class GenerarAsistencias
{
    public const DESDE = '2026-09-01';

    public const HASTA = '2026-12-31';

    // Feriados aplicables a Loja; el 20/11 corresponde al traslado de la independencia de Loja.
    public const FERIADOS = ['2026-10-09', '2026-11-02', '2026-11-03', '2026-11-20'];

    public const INICIO_VACACIONES = '2026-12-25';

    public const TIPOS = [
        'presente' => ['P', []],
        'participacion' => ['P', ['Participa activamente en clase.', 'Apoya a sus compañeros en las actividades.', 'Entregó el trabajo de clase con excelente calidad.']],
        'conducta' => ['P', ['Se distrae con frecuencia durante la clase.', 'Usa el celular sin autorización.', 'No presentó la tarea asignada.', 'Interrumpe el desarrollo de la clase.']],
        'atraso_ingreso' => ['A', ['Ingresó después del inicio de la jornada.']],
        'atraso_cambio_hora' => ['A', ['Llegó tarde después del cambio de hora o receso.']],
        'falta_dia' => ['F', []],
        'falta_clase' => ['F', ['Estuvo en la institución, pero no ingresó a esta clase.']],
        'justificada_salud' => ['J', ['Presentó certificado médico.']],
        'justificada_calamidad' => ['J', ['Calamidad doméstica justificada por el representante.']],
        'justificada_institucional' => ['J', ['Representó a la institución en una actividad autorizada.']],
    ];

    // Probabilidades por día (falta_dia, salud) o por clase (el resto).
    private const PERFILES = [
        'puntual' => ['peso' => 50, 'falta_dia' => .004, 'salud' => .002, 'atraso_ingreso' => .005, 'atraso_cambio_hora' => .002, 'falta_clase' => .0005, 'participacion' => .03, 'conducta' => .005],
        'participativo' => ['peso' => 15, 'falta_dia' => .003, 'salud' => .002, 'atraso_ingreso' => .005, 'atraso_cambio_hora' => .002, 'falta_clase' => 0, 'participacion' => .15, 'conducta' => .002],
        'distraido' => ['peso' => 10, 'falta_dia' => .01, 'salud' => .003, 'atraso_ingreso' => .02, 'atraso_cambio_hora' => .01, 'falta_clase' => .005, 'participacion' => .01, 'conducta' => .12],
        'atrasos_frecuentes' => ['peso' => 10, 'falta_dia' => .01, 'salud' => .003, 'atraso_ingreso' => .2, 'atraso_cambio_hora' => .06, 'falta_clase' => .005, 'participacion' => .02, 'conducta' => .03],
        'salud' => ['peso' => 7, 'falta_dia' => .005, 'salud' => .035, 'atraso_ingreso' => .01, 'atraso_cambio_hora' => .003, 'falta_clase' => .0005, 'participacion' => .03, 'conducta' => .005],
        'ausentismo' => ['peso' => 5, 'falta_dia' => .1, 'salud' => .005, 'atraso_ingreso' => .04, 'atraso_cambio_hora' => .02, 'falta_clase' => .02, 'participacion' => .01, 'conducta' => .05],
        'desercion_progresiva' => ['peso' => 3, 'falta_dia' => .02, 'falta_dia_final' => .45, 'salud' => .002, 'atraso_ingreso' => .03, 'atraso_cambio_hora' => .02, 'falta_clase' => .03, 'participacion' => .01, 'conducta' => .03],
    ];

    private const TOPE_DOCENTE_DIA = 6;

    private const PROB_CALAMIDAD = .002;

    private const PROB_INSTITUCIONAL = .003;

    private const ACTIVIDADES = ['Introducción de contenidos', 'Práctica guiada', 'Trabajo colaborativo', 'Aplicación de destrezas', 'Evaluación formativa'];

    public function ejecutar(int $institucionId, int $periodoId, bool $aplicar = false): array
    {
        return DB::transaction(function () use ($institucionId, $periodoId, $aplicar) {
            $periodo = PeriodoAcademico::where('institucion_id', $institucionId)->findOrFail($periodoId);
            $cargas = AsignacionDocente::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)
                ->where('tipo', 'carga')->where('horas_semanales', '>', 0)->whereNotNull('paralelo_id')
                ->whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionId))
                ->whereHas('paralelo')->with('paralelo')->orderBy('id')->get()
                ->filter(fn ($c) => (int) $c->paralelo->curso_id === (int) $c->curso_id)->values();
            $matriculas = Matricula::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)->where('estado', 'ACTIVA')
                ->whereHas('estudiante', fn ($q) => $q->alumnos()->where('institucion_id', $institucionId))
                ->orderBy('id')->get(['id', 'paralelo_id'])->groupBy('paralelo_id');
            $previas = SesionAsistencia::whereIn('asignacion_docente_id', $cargas->modelKeys())->toBase()
                ->get(['asignacion_docente_id', 'fecha', 'horas'])->groupBy('asignacion_docente_id');
            $dias = $this->diasLectivos($periodo);
            [$horario, $maximoParalelo, $maximoDocente] = $this->horario($cargas);

            $conteo = array_fill_keys(array_keys(self::TIPOS), 0);
            $perfiles = array_fill_keys(array_keys(self::PERFILES), 0);
            $clases = 0;
            $horas = 0;
            foreach ($cargas->groupBy('paralelo_id') as $paraleloId => $delParalelo) {
                $rng = new Randomizer(new Mt19937(crc32($periodoId.'|'.$paraleloId)));
                $alumnos = ($matriculas[$paraleloId] ?? collect())->pluck('id')->mapWithKeys(fn ($id) => [$id => $this->elegirPerfil($rng)])->all();
                foreach (array_count_values($alumnos) as $perfil => $total) {
                    $perfiles[$perfil] += $total;
                }
                $calendario = $this->clasesDelParalelo($delParalelo, $horario, $previas, $dias);
                $episodios = [];
                foreach ($calendario as $fecha => $delDia) {
                    $ausencias = [];
                    $avance = (array_search($fecha, $dias, true) ?: 0) / max(1, count($dias) - 1);
                    foreach ($alumnos as $matricula => $perfil) {
                        $ausencias[$matricula] = $this->ausenciaDelDia($rng, $perfil, $avance, $episodios, $matricula);
                    }
                    foreach ($delDia as $orden => $clase) {
                        $clases++;
                        $horas += $clase['horas'];
                        $sesionId = $aplicar ? DB::table('sesiones_asistencia')->insertGetId($clase['fila'] + ['fecha' => $fecha, 'created_at' => now(), 'updated_at' => now()]) : null;
                        $filas = [];
                        foreach ($alumnos as $matricula => $perfil) {
                            $tipo = $ausencias[$matricula] ?? $this->tipoDeClase($rng, $perfil, $orden);
                            $conteo[$tipo]++;
                            [$estado, $observaciones] = self::TIPOS[$tipo];
                            if ($aplicar) {
                                $filas[] = ['sesion_asistencia_id' => $sesionId, 'matricula_id' => $matricula, 'estado' => $estado,
                                    'observacion' => $observaciones ? $observaciones[$rng->getInt(0, count($observaciones) - 1)] : null, 'created_at' => now(), 'updated_at' => now()];
                            }
                        }
                        foreach (array_chunk($filas, 500) as $lote) {
                            DB::table('asistencias_estudiantes')->insert($lote);
                        }
                    }
                }
            }

            return ['aplicado' => $aplicar, 'dias_lectivos' => count($dias), 'cargas' => $cargas->count(), 'clases_conservadas' => $previas->flatten(1)->count(),
                'clases_nuevas' => $clases, 'horas_nuevas' => $horas, 'registros_nuevos' => array_sum($conteo), 'tipos' => $conteo, 'perfiles' => $perfiles,
                'maximo_horas_dia_paralelo' => $maximoParalelo, 'maximo_horas_dia_docente' => $maximoDocente];
        });
    }

    /** @return list<string> */
    private function diasLectivos(PeriodoAcademico $periodo): array
    {
        $dias = [];
        $hasta = CarbonImmutable::parse(self::HASTA)->min(CarbonImmutable::parse($periodo->fecha_fin));
        $fin = $hasta->min(CarbonImmutable::parse(self::INICIO_VACACIONES)->subDay());
        for ($dia = CarbonImmutable::parse(self::DESDE)->max(CarbonImmutable::parse($periodo->fecha_inicio)); $dia->lessThanOrEqualTo($fin); $dia = $dia->addDay()) {
            if ($dia->isWeekday() && ! in_array($dia->toDateString(), self::FERIADOS, true)) {
                $dias[] = $dia->toDateString();
            }
        }

        return $dias;
    }

    /** Asigna a cada carga días fijos de la semana, equilibrando las horas diarias del paralelo y del docente. */
    private function horario(Collection $cargas): array
    {
        $porParalelo = [];
        $porDocente = [];
        $horario = [];
        $topeParalelo = $cargas->groupBy('paralelo_id')->map(fn ($g) => (int) ceil($g->sum(fn ($c) => ceil((float) $c->horas_semanales)) / 5));
        foreach ($cargas->sortByDesc(fn ($c) => (float) $c->horas_semanales) as $carga) {
            foreach ($this->bloques((float) $carga->horas_semanales) as $horas) {
                $dia = collect(range(1, 5))->reject(fn ($d) => isset($horario[$carga->id][$d]))
                    ->sortBy(function ($d) use ($porParalelo, $porDocente, $carga, $horas, $topeParalelo) {
                        $paralelo = $porParalelo[$carga->paralelo_id][$d] ?? 0;
                        $docente = $porDocente[$carga->docente_id][$d] ?? 0;

                        return max(0, $docente + $horas - self::TOPE_DOCENTE_DIA) * 10000 + max(0, $paralelo + $horas - $topeParalelo[$carga->paralelo_id] - 1) * 1000 + $paralelo * 10 + $docente;
                    })->first();
                $horario[$carga->id][$dia] = $horas;
                $porParalelo[$carga->paralelo_id][$dia] = ($porParalelo[$carga->paralelo_id][$dia] ?? 0) + $horas;
                $porDocente[$carga->docente_id][$dia] = ($porDocente[$carga->docente_id][$dia] ?? 0) + $horas;
            }
        }
        $maximo = fn (array $totales) => empty($totales) ? 0 : max(array_map('max', $totales));

        return [$horario, $maximo($porParalelo), $maximo($porDocente)];
    }

    /** @return list<int> horas de cada clase semanal, en bloques de 2 o más cuando la carga no cabe en cinco días */
    private function bloques(float $horasSemanales): array
    {
        $restantes = (int) ceil($horasSemanales);
        $tamano = (int) max(2, ceil($restantes / 5));
        $bloques = [];
        while ($restantes > 0) {
            $bloques[] = min($tamano, $restantes);
            $restantes -= $tamano;
        }

        return $bloques;
    }

    /** Clases por fecha sin superar las horas semanales, descontando las clases ya registradas. */
    private function clasesDelParalelo(Collection $cargas, array $horario, Collection $previas, array $dias): array
    {
        $calendario = [];
        foreach ($cargas as $carga) {
            $registradas = collect($previas[$carga->id] ?? []);
            $ocupadas = $registradas->map(fn ($s) => substr((string) $s->fecha, 0, 10))->all();
            $usadas = [];
            foreach ($registradas as $sesion) {
                $semana = CarbonImmutable::parse($sesion->fecha)->startOfWeek()->toDateString();
                $usadas[$semana] = ($usadas[$semana] ?? 0) + (int) $sesion->horas;
            }
            $numero = 0;
            foreach ($dias as $fecha) {
                $dia = CarbonImmutable::parse($fecha);
                $bloque = $horario[$carga->id][$dia->dayOfWeekIso] ?? 0;
                $semana = $dia->startOfWeek()->toDateString();
                $horas = min($bloque, (int) ceil((float) $carga->horas_semanales) - ($usadas[$semana] ?? 0));
                if ($horas <= 0 || in_array($fecha, $ocupadas, true)) {
                    continue;
                }
                $usadas[$semana] = ($usadas[$semana] ?? 0) + $horas;
                $calendario[$fecha][] = ['horas' => $horas, 'fila' => ['asignacion_docente_id' => $carga->id, 'horas' => $horas, 'tema' => $this->tema($fecha, $numero++)]];
            }
        }
        ksort($calendario);

        return $calendario;
    }

    private function tema(string $fecha, int $numero): string
    {
        $etapa = match (true) {
            $fecha < '2026-09-15' => 'Diagnóstico y nivelación',
            $fecha < '2026-10-19' => 'Unidad 1',
            $fecha < '2026-11-23' => 'Unidad 2',
            $fecha <= '2026-12-01' => 'Cierre del primer período',
            $fecha < '2026-12-14' => 'Retroalimentación de resultados',
            default => 'Unidad 3',
        };

        return $etapa.' · '.self::ACTIVIDADES[$numero % count(self::ACTIVIDADES)];
    }

    private function elegirPerfil(Randomizer $rng): string
    {
        $valor = $rng->getInt(1, array_sum(array_column(self::PERFILES, 'peso')));
        foreach (self::PERFILES as $perfil => $datos) {
            $valor -= $datos['peso'];
            if ($valor <= 0) {
                return $perfil;
            }
        }

        return 'puntual';
    }

    /** La falta y la justificación cubren todas las clases del día; las de salud duran de 2 a 4 días lectivos. */
    private function ausenciaDelDia(Randomizer $rng, string $perfil, float $avance, array &$episodios, int $matricula): ?string
    {
        if (($episodios[$matricula] ?? 0) > 0) {
            $episodios[$matricula]--;

            return 'justificada_salud';
        }
        $datos = self::PERFILES[$perfil];
        if ($this->ocurre($rng, $datos['salud'])) {
            $episodios[$matricula] = $rng->getInt(1, 3);

            return 'justificada_salud';
        }
        if ($this->ocurre($rng, self::PROB_CALAMIDAD)) {
            return 'justificada_calamidad';
        }
        if ($this->ocurre($rng, self::PROB_INSTITUCIONAL)) {
            return 'justificada_institucional';
        }
        $faltaDia = $datos['falta_dia'] + (($datos['falta_dia_final'] ?? $datos['falta_dia']) - $datos['falta_dia']) * $avance;

        return $this->ocurre($rng, $faltaDia) ? 'falta_dia' : null;
    }

    private function tipoDeClase(Randomizer $rng, string $perfil, int $orden): string
    {
        $datos = self::PERFILES[$perfil];
        if ($orden === 0 && $this->ocurre($rng, $datos['atraso_ingreso'])) {
            return 'atraso_ingreso';
        }
        if ($orden > 0 && $this->ocurre($rng, $datos['atraso_cambio_hora'])) {
            return 'atraso_cambio_hora';
        }
        foreach (['falta_clase', 'conducta', 'participacion'] as $tipo) {
            if ($this->ocurre($rng, $datos[$tipo])) {
                return $tipo;
            }
        }

        return 'presente';
    }

    private function ocurre(Randomizer $rng, float $probabilidad): bool
    {
        return $rng->getFloat(0, 1) < $probabilidad;
    }
}
