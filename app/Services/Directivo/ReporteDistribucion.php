<?php

namespace App\Services\Directivo;

use App\Models\AsignacionDocente;
use App\Models\Institucion;
use App\Models\PeriodoAcademico;

class ReporteDistribucion
{
    public function datos(Institucion $institucion, PeriodoAcademico $periodo, ?int $docenteId = null, string $busqueda = ''): array
    {
        abort_unless((int) $periodo->institucion_id === (int) $institucion->id, 404);
        $docentes = app(ContextoDocente::class)->docentes($institucion->id)->with('persona')
            ->when($docenteId !== null, fn ($query) => $query->whereKey($docenteId))->get();
        abort_if($docenteId !== null && $docentes->isEmpty(), 404);
        $busqueda = mb_strtolower(trim($busqueda));
        $docentes = $docentes->filter(fn ($docente) => $busqueda === '' || str_contains(mb_strtolower($docente->nombre_completo.' '.$docente->username), $busqueda))
            ->sortBy(fn ($docente) => $docente->persona ? $docente->nombre_completo : $docente->username)->values();
        $asignaciones = AsignacionDocente::where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)
            ->whereIn('docente_id', $docentes->modelKeys())->with(['curso.especialidad', 'paralelo', 'asignatura'])
            ->orderBy('curso_id')->orderBy('paralelo_id')->orderBy('titulo')->get();
        $porDocente = $asignaciones->groupBy('docente_id');
        $grupos = $docentes->map(function ($docente) use ($porDocente) {
            $filas = $porDocente->get($docente->id, collect());
            $cargas = $filas->where('tipo', 'carga')->values();

            return ['docente' => $docente, 'cargas' => $cargas, 'comisiones' => $filas->where('tipo', 'comision')->values(),
                'actividades' => $filas->where('tipo', 'actividad')->values(), 'horas' => (float) $cargas->sum('horas_semanales'),
                'paralelos' => $cargas->pluck('paralelo_id')->filter()->unique()->count(),
                'asignaturas' => $cargas->pluck('asignatura_id')->filter()->unique()->count()];
        });
        $cargas = $asignaciones->where('tipo', 'carga');
        $resumen = ['docentes' => $grupos->count(), 'con_carga' => $grupos->where('horas', '>', 0)->count(),
            'horas' => (float) $cargas->sum('horas_semanales'), 'paralelos' => $cargas->pluck('paralelo_id')->filter()->unique()->count(),
            'cursos' => $cargas->pluck('curso_id')->filter()->unique()->count(), 'asignaciones' => $cargas->count(),
            'comisiones' => $asignaciones->where('tipo', 'comision')->count(), 'actividades' => $asignaciones->where('tipo', 'actividad')->count(),
            'promedio' => $grupos->isEmpty() ? 0 : $grupos->avg('horas'), 'maximo' => $grupos->max('horas') ?? 0];

        return compact('institucion', 'periodo', 'grupos', 'resumen', 'busqueda') + ['individual' => $docenteId !== null,
            'titulo' => $docenteId !== null ? 'Distribución individual de horas pedagógicas' : 'Distribución general de horas pedagógicas'];
    }
}
