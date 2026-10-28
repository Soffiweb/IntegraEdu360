<?php

namespace App\Services\Directivo;

use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;

class ReporteDocentesAgrupado
{
    public const TIPOS = ['comisiones' => 'Docentes por comisiones', 'tutores' => 'Docentes tutores por curso y paralelo',
        'asignaturas' => 'Docentes por asignaturas', 'areas' => 'Docentes por áreas'];

    private const AREAS = ['MATEMATICA' => 'Matemática', 'LENGUAJE' => 'Lenguaje y Literatura', 'CIENCIAS_NATURALES' => 'Ciencias Naturales',
        'CIENCIAS_SOCIALES' => 'Ciencias Sociales', 'IDIOMAS' => 'Idiomas', 'EDUCACION_ARTISTICA' => 'Educación Artística',
        'EDUCACION_FISICA' => 'Educación Física', 'TECNICA_TECNOLOGICA' => 'Técnica y Tecnológica', 'OTRAS' => 'Otras'];

    public function __construct(private ReporteDistribucion $distribucion) {}

    public function datos(Institucion $institucion, PeriodoAcademico $periodo, string $tipo, string $busqueda = ''): array
    {
        abort_unless(isset(self::TIPOS[$tipo]), 404);
        $base = $this->distribucion->datos($institucion, $periodo, null, $busqueda);
        $docentes = $base['grupos']->pluck('docente')->keyBy('id');
        $filas = collect();
        if ($tipo === 'tutores') {
            $paralelos = Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $institucion->id)->where('estado', 'ACTIVO'))
                ->where('estado', 'ACTIVO')->with(['curso.especialidad', 'tutorias' => fn ($q) => $q->where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)])->get()
                ->sortBy(fn ($p) => sprintf('%03d', $p->curso->grado).'|'.($p->curso->especialidad?->nombre ?? '').'|'.$p->curso->nombre.'|'.$p->letra, SORT_NATURAL)->values();
            foreach ($paralelos as $paralelo) {
                $docente = $docentes->get($paralelo->tutorias->first()?->docente_id);
                if (trim($busqueda) !== '' && ! $docente) {
                    continue;
                }
                $filas->push(['grupo' => $paralelo->curso_id, 'titulo' => $paralelo->curso->nombre.' · '.($paralelo->curso->especialidad?->nombre ?? 'Educación General Básica'),
                    'docente_id' => $docente?->id, 'nombre' => $docente ? ($docente->persona ? $docente->nombre_completo : $docente->username) : 'Sin tutor asignado válido',
                    'identificacion' => $docente?->persona?->numero_identificacion ?: 'Sin registro', 'detalle' => $paralelo->letra,
                    'paralelo_id' => $paralelo->id, 'horas' => 0]);
            }
        } else {
            foreach ($base['grupos'] as $grupo) {
                $docente = $grupo['docente'];
                foreach ($tipo === 'comisiones' ? $grupo['comisiones'] : $grupo['cargas'] as $asignacion) {
                    $nombreGrupo = match ($tipo) {
                        'comisiones' => trim($asignacion->titulo),
                        'asignaturas' => $asignacion->asignatura?->nombre ?? 'Sin asignatura',
                        'areas' => self::AREAS[$asignacion->asignatura?->area] ?? ($asignacion->asignatura?->area ?: 'Sin área'),
                    };
                    $detalle = $tipo === 'comisiones' ? null : ($asignacion->curso?->nombre ?? 'Sin curso').' '.($asignacion->paralelo?->letra ?? 'Sin paralelo')
                        .' · '.($asignacion->curso?->especialidad?->nombre ?? 'EGB');
                    if ($tipo === 'areas') {
                        $detalle = ($asignacion->asignatura?->nombre ?? 'Sin asignatura').' · '.$detalle;
                    }
                    $filas->push(['grupo' => mb_strtolower($nombreGrupo), 'titulo' => $nombreGrupo, 'docente_id' => $docente->id,
                        'nombre' => $docente->persona ? $docente->nombre_completo : $docente->username,
                        'identificacion' => $docente->persona?->numero_identificacion ?: 'Sin registro', 'detalle' => $detalle,
                        'paralelo_id' => $asignacion->paralelo_id, 'horas' => $tipo === 'comisiones' ? 0 : (float) $asignacion->horas_semanales]);
                }
            }
        }
        $grupos = $filas->groupBy('grupo')->map(function ($items) use ($tipo) {
            $filasGrupo = $tipo === 'tutores' ? $items->values() : $items->groupBy('docente_id')->map(fn ($asignaciones) => [
                'docente_id' => $asignaciones->first()['docente_id'], 'nombre' => $asignaciones->first()['nombre'],
                'identificacion' => $asignaciones->first()['identificacion'], 'detalle' => $asignaciones->pluck('detalle')->filter()->unique()->implode("\n"),
                'horas' => $asignaciones->sum('horas'),
            ])->sortBy('nombre')->values();

            return ['titulo' => $items->first()['titulo'], 'filas' => $filasGrupo, 'docentes' => $items->pluck('docente_id')->filter()->unique()->count(),
                'horas' => $items->sum('horas'), 'paralelos' => $items->pluck('paralelo_id')->filter()->unique()->count()];
        });
        $grupos = ($tipo === 'tutores' ? $grupos : $grupos->sortBy('titulo'))->values();
        $resumen = ['grupos' => $grupos->count(), 'docentes' => $filas->pluck('docente_id')->filter()->unique()->count(),
            'participaciones' => $grupos->sum(fn ($grupo) => $grupo['filas']->count()), 'horas' => $filas->sum('horas'),
            'paralelos' => $filas->pluck('paralelo_id')->filter()->unique()->count(),
            'sin_tutor' => $tipo === 'tutores' ? $filas->whereNull('docente_id')->count() : 0];

        return compact('institucion', 'periodo', 'tipo', 'grupos', 'resumen', 'busqueda') + ['titulo' => self::TIPOS[$tipo]];
    }
}
