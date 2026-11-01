<?php

namespace App\Services\Docente;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaEstudiante;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Support\Collection;
use Symfony\Component\HttpFoundation\Response;

class BoletaAlertaAsistencia
{
    public function __construct(private AlertasAsistencia $alertas) {}

    /** Solo existe boleta mientras la matrícula siga activa y bajo el umbral, con las mismas cifras que muestra la pestaña de alertas. */
    public function datos(Matricula $matricula): array
    {
        abort_unless($matricula->estado === 'ACTIVA', 404);
        $asistencia = $this->alertas->enRiesgo([$matricula->id])->get($matricula->id);
        abort_unless($asistencia, 404);
        $matricula->load(['estudiante.persona', 'estudiante.representante.persona', 'paralelo.curso.especialidad']);

        return compact('matricula', 'asistencia') + [
            'institucion' => Institucion::findOrFail($matricula->institucion_id),
            'periodo' => PeriodoAcademico::findOrFail($matricula->periodo_id),
            'asignaturas' => $this->porAsignatura($matricula),
            'estados' => AsistenciaEstudiante::ESTADOS,
            'umbral' => AlertasAsistencia::UMBRAL,
        ];
    }

    public function pdf(Matricula $matricula): Response
    {
        $pdf = Pdf::loadView('docente.boleta-alerta-asistencia-pdf', $this->datos($matricula))->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('boleta-asistencia-matricula-'.$matricula->id.'.pdf');
    }

    private function porAsignatura(Matricula $matricula): Collection
    {
        $filas = AsistenciaEstudiante::where('matricula_id', $matricula->id)
            ->join('sesiones_asistencia as s', 's.id', '=', 'asistencias_estudiantes.sesion_asistencia_id')
            ->conteosPorMatricula()->addSelect('s.asignacion_docente_id')->groupBy('s.asignacion_docente_id')->get();
        $cargas = AsignacionDocente::whereKey($filas->pluck('asignacion_docente_id')->all())->with('asignatura')->get()->keyBy('id');

        return $filas->map(function ($fila) use ($cargas) {
            $carga = $cargas[$fila->asignacion_docente_id];
            $estados = collect(AsistenciaEstudiante::ESTADOS)->keys()->mapWithKeys(fn ($e) => [$e => (int) $fila->{'total_'.strtolower($e)}])->all();

            return ['nombre' => $carga->asignatura?->nombre ?? $carga->titulo, 'estados' => $estados,
                'porcentaje' => ($estados['P'] + $estados['A']) * 100 / array_sum($estados)];
        })->sortBy('nombre', SORT_NATURAL | SORT_FLAG_CASE)->values();
    }
}
