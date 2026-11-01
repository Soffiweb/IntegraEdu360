<?php

namespace App\Services\Docente;

use App\Models\AlertaAsistencia;
use App\Models\AsistenciaEstudiante;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Notifications\AlertaAsistenciaRepresentante;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

class AlertasAsistencia
{
    public const UMBRAL = 90.0;

    // Evita alertar a la familia por una sola ausencia en los primeros días de clase.
    public const MIN_REGISTROS = 10;

    /** Asistencia de cada matrícula en todas las clases del período; presentes y atrasados cuentan como asistencia. */
    public function porcentajes(iterable $matriculas): Collection
    {
        return AsistenciaEstudiante::whereIn('matricula_id', collect($matriculas)->all())->conteosPorMatricula()->get()
            ->mapWithKeys(function ($fila) {
                $estados = collect(AsistenciaEstudiante::ESTADOS)->keys()->mapWithKeys(fn ($e) => [$e => (int) $fila->{'total_'.strtolower($e)}])->all();
                $registros = array_sum($estados);

                return [$fila->matricula_id => ['estados' => $estados, 'registros' => $registros, 'porcentaje' => ($estados['P'] + $estados['A']) * 100 / $registros]];
            });
    }

    public function enRiesgo(iterable $matriculas): Collection
    {
        return $this->porcentajes($matriculas)->filter(fn ($datos) => $this->bajoUmbral($datos));
    }

    /** Abre una alerta y notifica una sola vez por episodio; si el estudiante se recupera, la siguiente caída notifica de nuevo. */
    public function evaluar(iterable $matriculas, bool $aplicar = true): array
    {
        return DB::transaction(function () use ($matriculas, $aplicar) {
            $filas = Matricula::whereKey(collect($matriculas)->all())->where('estado', 'ACTIVA')->with('estudiante.representante')->lockForUpdate()->get();
            $porcentajes = $this->porcentajes($filas->modelKeys());
            $activas = AlertaAsistencia::whereIn('matricula_id', $filas->modelKeys())->whereNull('resuelta_at')->get()->keyBy('matricula_id');
            $resultado = ['evaluadas' => $filas->count(), 'en_riesgo' => 0, 'nuevas' => 0, 'notificadas' => 0, 'sin_correo_representante' => 0, 'resueltas' => 0];
            foreach ($filas as $matricula) {
                $datos = $porcentajes[$matricula->id] ?? null;
                $alerta = $activas[$matricula->id] ?? null;
                if (! $datos || ! $this->bajoUmbral($datos)) {
                    if ($alerta) {
                        $resultado['resueltas']++;
                        $aplicar && $alerta->update(['resuelta_at' => now()]);
                    }

                    continue;
                }
                $resultado['en_riesgo']++;
                $resultado['nuevas'] += $alerta ? 0 : 1;
                $representante = $matricula->estudiante->representante;
                $pendiente = ! $alerta?->notificada_at;
                if ($pendiente && blank($representante?->email)) {
                    $resultado['sin_correo_representante']++;
                }
                $notificar = $pendiente && filled($representante?->email);
                $resultado['notificadas'] += $notificar ? 1 : 0;
                if (! $aplicar) {
                    continue;
                }
                $alerta ??= new AlertaAsistencia(['matricula_id' => $matricula->id]);
                $alerta->fill(['representante_id' => $representante?->id, 'porcentaje' => round($datos['porcentaje'], 2), 'registros' => $datos['registros']]);
                $alerta->notificada_at = $notificar ? now() : $alerta->notificada_at;
                $alerta->save();
                if ($notificar) {
                    $representante->notify(new AlertaAsistenciaRepresentante($alerta));
                }
            }

            return $resultado;
        });
    }

    public function evaluarPeriodo(int $institucionId, int $periodoId, bool $aplicar = false): array
    {
        PeriodoAcademico::where('institucion_id', $institucionId)->findOrFail($periodoId);
        $ids = Matricula::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)->where('estado', 'ACTIVA')->pluck('id');

        return ['aplicado' => $aplicar] + $this->evaluar($ids, $aplicar);
    }

    private function bajoUmbral(array $datos): bool
    {
        return $datos['registros'] >= self::MIN_REGISTROS && $datos['porcentaje'] < self::UMBRAL;
    }
}
