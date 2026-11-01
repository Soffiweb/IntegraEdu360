<?php

namespace App\Services\Docente;

use App\Models\CalificacionTrimestral;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Registro trimestral según el Acuerdo MINEDEC-MINEDEC-2026-00082-A. Las notas se calculan en centésimas
 * enteras para que los dos decimales truncados del art. 8 no dependan de la precisión de los flotantes.
 */
class RegistroCalificaciones
{
    public const TRIMESTRES = [1 => 'Primer trimestre', 2 => 'Segundo trimestre', 3 => 'Tercer trimestre'];

    // El orden reproduce el art. 35: A+, B+ y C+ promedian B+.
    public const ESCALA = ['A+' => 10, 'A-' => 9, 'B+' => 8, 'B-' => 7, 'C+' => 6, 'C-' => 5, 'D+' => 4, 'D-' => 3, 'E+' => 2, 'E-' => 1];

    public const REGLAS_FILTROS = ['periodo' => ['nullable', 'integer'], 'carga' => ['nullable', 'integer'], 'trimestre' => ['nullable', 'in:1,2,3,anual']];

    private const PESO_FORMATIVA = 70;

    private const PESO_SUMATIVA = 30;

    private const MINIMA_APROBACION = 700;

    private const MINIMA_SUPLETORIO = 401;

    private const TOPE_MEJORA_DIRECTA = 900;

    private const LIMITES_MEJORA = ['directa' => ['asignatura' => 1, 'total' => 3], 'refuerzo' => ['asignatura' => 2, 'total' => 6]];

    public function __construct(private RegistroAsistencia $registro) {}

    public function contexto(Request $request): array
    {
        [$docente, $periodos, $periodo, $cargas, $carga] = $this->registro->clasesFiltradas($request);
        $carga ??= $cargas->first();
        $trimestre = $request->input('trimestre') ?: 1;
        $trimestre = $trimestre === 'anual' ? 'anual' : (int) $trimestre;
        $institucion = Institucion::findOrFail($docente->institucion_id);
        $tipo = $carga ? $this->tipoEvaluacion((int) $carga->curso->grado) : null;
        $matriculas = $carga ? $this->registro->matriculasActivas($docente->institucion_id, $periodo->id, $carga->paralelo_id) : collect();
        $registros = CalificacionTrimestral::where('asignacion_docente_id', $carga?->id)->whereIn('matricula_id', $matriculas->modelKeys())->get()->groupBy('matricula_id');
        $minimo = $carga ? $this->minimoAportes((float) $carga->horas_semanales) : 0;
        $filas = $matriculas->map(fn ($matricula) => $this->fila($matricula, $registros->get($matricula->id, collect()), $tipo, $trimestre, $minimo, (int) ($carga?->curso->grado)));
        $columnas = max($minimo, (int) $filas->max(fn ($fila) => count($fila['aportes'])));
        $mejoraDisponible = ! $this->aplicaAcuerdoAnterior($institucion, $periodo);

        return compact('docente', 'institucion', 'periodos', 'periodo', 'cargas', 'carga', 'trimestre', 'tipo', 'filas', 'minimo', 'columnas', 'mejoraDisponible') + [
            'trimestres' => self::TRIMESTRES, 'escala' => array_keys(self::ESCALA), 'resumen' => $this->resumen($filas, $tipo, $trimestre),
        ];
    }

    public function guardar(array $contexto, array $calificaciones): void
    {
        $carga = $contexto['carga'];
        $trimestre = $contexto['trimestre'];
        DB::transaction(function () use ($contexto, $calificaciones, $carga, $trimestre) {
            $filas = $contexto['filas'];
            $otrasMejoras = CalificacionTrimestral::whereIn('matricula_id', $filas->pluck('matricula.id'))->whereNotNull('sumativa_mejora')
                ->where(fn ($q) => $q->where('asignacion_docente_id', '!=', $carga->id)->orWhere('trimestre', '!=', $trimestre))->lockForUpdate()->get()->groupBy('matricula_id');
            foreach ($filas as $fila) {
                $matricula = $fila['matricula'];
                $datos = $calificaciones[$matricula->id] ?? [];
                $valores = $contexto['tipo'] === 'cualitativa'
                    ? ['aportes' => null, 'sumativa' => null, 'sumativa_mejora' => null, 'valoracion' => $datos['valoracion'] ?? null]
                    : ['aportes' => $this->aportes($datos['aportes'] ?? []), 'sumativa' => $datos['sumativa'] ?? null, 'sumativa_mejora' => $contexto['mejoraDisponible'] ? ($datos['mejora'] ?? null) : null, 'valoracion' => null];
                if ($valores['sumativa_mejora'] !== null) {
                    $this->validarMejora($fila['nombre'], $valores, $otrasMejoras->get($matricula->id, collect()), $carga->id);
                }
                $clave = ['asignacion_docente_id' => $carga->id, 'matricula_id' => $matricula->id, 'trimestre' => $trimestre];
                if (collect($valores)->filter(fn ($valor) => $valor !== null)->isEmpty()) {
                    CalificacionTrimestral::where($clave)->delete();
                } else {
                    CalificacionTrimestral::updateOrCreate($clave, $valores);
                }
            }
        });
    }

    /** Inicial y Preparatoria se registran por ámbito y destreza (arts. 6 y 35); Elemental es cualitativa y desde Media cuantitativa (arts. 7 y 8). */
    public function tipoEvaluacion(int $grado): string
    {
        return match (true) {
            $grado <= 1 => 'destrezas',
            $grado <= 4 => 'cualitativa',
            default => 'cuantitativa',
        };
    }

    /** Art. 37. */
    public function minimoAportes(float $horasSemanales): int
    {
        return match (true) {
            $horasSemanales >= 6 => 9,
            $horasSemanales > 3 => 5,
            default => 3,
        };
    }

    /** Art. 36: promedio de aportes truncado y luego 70 % formativa + 30 % sumativa, en centésimas. */
    public function notaTrimestre(int $formativa, int $sumativa): int
    {
        return intdiv($formativa * self::PESO_FORMATIVA + $sumativa * self::PESO_SUMATIVA, 100);
    }

    /** Arts. 12 y 13: promedio de la nota inicial y la adicional, con tope de 9,00 o 7,00 según el rango inicial. */
    public function sumativaConMejora(int $inicial, int $adicional): int
    {
        return min(intdiv($inicial + $adicional, 2), $inicial < self::MINIMA_APROBACION ? self::MINIMA_APROBACION : self::TOPE_MEJORA_DIRECTA);
    }

    public static function formato(?int $centesimas): string
    {
        return $centesimas === null ? '—' : number_format($centesimas / 100, 2, ',', '.');
    }

    /** Arts. 21, 23, 25 y 27; en otros sostenimientos los estatutos pueden fijar otros requisitos de promoción. */
    public function situacionFinal(int $promedio, int $grado): string
    {
        return match (true) {
            $promedio >= self::MINIMA_APROBACION => 'Promoción directa',
            $promedio >= self::MINIMA_SUPLETORIO => 'Evaluación supletoria',
            $grado <= 7 => 'Refuerzo y compromisos',
            default => 'Repitencia directa',
        };
    }

    /** Disposición transitoria octava: Costa-Galápagos conserva el Acuerdo 2024-00031-A hasta el fin del año lectivo 2026-2027. */
    private function aplicaAcuerdoAnterior(Institucion $institucion, ?PeriodoAcademico $periodo): bool
    {
        if (! $periodo || ! $institucion->regimen_id || $periodo->fecha_fin->toDateString() > '2027-03-01') {
            return false;
        }

        return DB::table('cat_regimen_escolar')->where('id', $institucion->regimen_id)->value('nombre') === 'Costa';
    }

    private function fila(Matricula $matricula, Collection $registros, ?string $tipo, int|string $trimestre, int $minimo, int $grado): array
    {
        $persona = $matricula->estudiante->persona;
        $base = ['matricula' => $matricula, 'identificacion' => $persona?->numero_identificacion ?: 'Sin registro',
            'nombre' => $persona ? trim(implode(' ', array_filter([$persona->primer_apellido, $persona->segundo_apellido, $persona->primer_nombre, $persona->segundo_nombre]))) : $matricula->estudiante->username];
        $porTrimestre = $registros->keyBy('trimestre');
        $resultados = collect(self::TRIMESTRES)->keys()->mapWithKeys(fn ($t) => [$t => $tipo === 'cualitativa' ? $this->resultadoCualitativo($porTrimestre->get($t)) : $this->resultado($porTrimestre->get($t), $minimo)]);

        if ($trimestre === 'anual') {
            $promedio = $tipo === 'cualitativa' ? $this->promedioCualitativo($resultados) : $this->promedioFinal($resultados);

            return $base + ['aportes' => [], 'notas' => $resultados->map(fn ($r) => $r['nota_texto'])->all(), 'promedio' => $promedio['valor'], 'promedio_texto' => $promedio['texto'],
                'situacion' => $promedio['valor'] === null ? 'Pendiente' : ($tipo === 'cualitativa' ? 'Promoción automática' : $this->situacionFinal($promedio['valor'], $grado))];
        }

        return $base + ['aportes' => $porTrimestre->get($trimestre)?->aportes ?? [], 'registro' => $porTrimestre->get($trimestre)] + $resultados[$trimestre];
    }

    private function resultado(?CalificacionTrimestral $registro, int $minimo): array
    {
        $aportes = collect($registro?->aportes ?? [])->filter(fn ($aporte) => $aporte !== null)->map(fn ($aporte) => $this->centesimas($aporte));
        $promedio = $aportes->isEmpty() ? null : intdiv($aportes->sum(), $aportes->count());
        $formativa = $aportes->count() >= $minimo ? $promedio : null;
        $inicial = $registro?->sumativa === null ? null : $this->centesimas($registro->sumativa);
        $sumativa = $inicial !== null && $registro->sumativa_mejora !== null ? $this->sumativaConMejora($inicial, $this->centesimas($registro->sumativa_mejora)) : $inicial;
        $nota = $formativa !== null && $sumativa !== null ? $this->notaTrimestre($formativa, $sumativa) : null;

        return ['registrados' => $aportes->count(), 'formativa' => $formativa, 'sumativa_final' => $sumativa, 'nota' => $nota, 'nota_texto' => self::formato($nota),
            'alerta' => $promedio !== null && $promedio < self::MINIMA_APROBACION];
    }

    private function resultadoCualitativo(?CalificacionTrimestral $registro): array
    {
        $valor = self::ESCALA[$registro?->valoracion] ?? null;

        return ['nota' => $valor, 'nota_texto' => $registro?->valoracion ?? '—', 'alerta' => $valor !== null && $valor <= self::ESCALA['C+']];
    }

    private function promedioFinal(Collection $resultados): array
    {
        $notas = $resultados->pluck('nota');
        $valor = $notas->contains(null) ? null : intdiv($notas->sum(), $notas->count());

        return ['valor' => $valor, 'texto' => self::formato($valor)];
    }

    /** El art. 35 define promedio simple sin regla para valores intermedios: se aplica el truncamiento del art. 8 y se conserva la letra inferior. */
    private function promedioCualitativo(Collection $resultados): array
    {
        $notas = $resultados->pluck('nota');
        $valor = $notas->contains(null) ? null : intdiv($notas->sum(), $notas->count());

        return ['valor' => $valor, 'texto' => $valor === null ? '—' : array_search($valor, self::ESCALA, true)];
    }

    private function resumen(Collection $filas, ?string $tipo, int|string $trimestre): array
    {
        $clave = $trimestre === 'anual' ? 'promedio' : 'nota';
        $notas = $filas->pluck($clave)->filter(fn ($nota) => $nota !== null);
        $base = ['estudiantes' => $filas->count(), 'con_nota' => $notas->count(), 'alertas' => $filas->where('alerta', true)->count()];
        if ($tipo === 'cualitativa') {
            return $base + ['alcanzado' => $notas->filter(fn ($n) => $n >= self::ESCALA['B-'])->count(),
                'en_proceso' => $notas->filter(fn ($n) => $n >= self::ESCALA['C-'] && $n <= self::ESCALA['C+'])->count(),
                'iniciado' => $notas->filter(fn ($n) => $n <= self::ESCALA['D+'])->count()];
        }

        return $base + ['promedio' => self::formato($notas->isEmpty() ? null : intdiv($notas->sum(), $notas->count())),
            'sobre_minimo' => $notas->filter(fn ($n) => $n >= self::MINIMA_APROBACION)->count(), 'bajo_minimo' => $notas->filter(fn ($n) => $n < self::MINIMA_APROBACION)->count()];
    }

    /** Conserva la posición de cada aporte y descarta solo las columnas vacías del final. */
    private function aportes(array $aportes): ?array
    {
        if (! $aportes) {
            return null;
        }
        $lista = [];
        foreach (range(0, max(array_keys($aportes))) as $indice) {
            $lista[] = $aportes[$indice] ?? null;
        }
        while ($lista && end($lista) === null) {
            array_pop($lista);
        }

        return $lista ?: null;
    }

    private function validarMejora(string $nombre, array $valores, Collection $otras, int $cargaId): void
    {
        $tipo = $valores['sumativa'] === null ? null : $this->tipoMejora($this->centesimas($valores['sumativa']));
        if (! $tipo) {
            throw ValidationException::withMessages(['calificaciones' => "{$nombre}: la mejora solo aplica a sumativas de 0,01 a 6,99 o superiores a 7,00 e inferiores a 9,00."]);
        }
        $delMismoTipo = $otras->filter(fn ($otra) => $this->tipoMejora($this->centesimas($otra->sumativa)) === $tipo);
        $limites = self::LIMITES_MEJORA[$tipo];
        if ($delMismoTipo->where('asignacion_docente_id', $cargaId)->count() >= $limites['asignatura']) {
            throw ValidationException::withMessages(['calificaciones' => "{$nombre}: ya usó las {$limites['asignatura']} mejoras permitidas de este tipo en la asignatura durante el año lectivo."]);
        }
        if ($delMismoTipo->count() >= $limites['total']) {
            throw ValidationException::withMessages(['calificaciones' => "{$nombre}: ya usó las {$limites['total']} mejoras permitidas de este tipo en todas las asignaturas durante el año lectivo."]);
        }
    }

    private function tipoMejora(int $sumativa): ?string
    {
        return match (true) {
            $sumativa < self::MINIMA_APROBACION => 'refuerzo',
            $sumativa > self::MINIMA_APROBACION && $sumativa < self::TOPE_MEJORA_DIRECTA => 'directa',
            default => null,
        };
    }

    private function centesimas(mixed $valor): int
    {
        return (int) round((float) $valor * 100);
    }
}
