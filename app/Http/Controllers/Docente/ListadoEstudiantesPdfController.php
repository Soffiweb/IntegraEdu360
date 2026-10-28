<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ListadoEstudiantesPdfController extends Controller
{
    public function __invoke(Request $request, int $asignacion)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403);
        $carga = AsignacionDocente::where('docente_id', $docente->id)
            ->where('institucion_id', $docente->institucion_id)->where('tipo', 'carga')
            ->with(['curso.especialidad', 'paralelo', 'asignatura'])->findOrFail($asignacion);
        $institucion = Institucion::findOrFail($docente->institucion_id);
        $periodo = PeriodoAcademico::where('institucion_id', $institucion->id)->findOrFail($carga->periodo_id);
        abort_unless($carga->curso && (int) $carga->curso->institucion_id === (int) $institucion->id, 404);
        abort_unless($carga->paralelo && (int) $carga->paralelo->curso_id === (int) $carga->curso_id, 404);
        $matriculas = Matricula::where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)
            ->where('paralelo_id', $carga->paralelo_id)->where('estado', 'ACTIVA')
            ->whereHas('estudiante', fn ($q) => $q->alumnos()->where('institucion_id', $institucion->id))
            ->with('estudiante.persona')->get()
            ->sortBy(fn ($m) => implode(' ', [
                $m->estudiante->persona?->primer_apellido, $m->estudiante->persona?->segundo_apellido,
                $m->estudiante->nombre_completo,
            ]), SORT_NATURAL | SORT_FLAG_CASE)->values();
        $sexos = $matriculas->countBy(fn ($m) => $m->estudiante->persona?->sexo ?? '');
        $resumen = ['total' => $matriculas->count(), 'masculino' => $sexos->get('M', 0),
            'femenino' => $sexos->get('F', 0), 'otros' => $matriculas->count() - $sexos->get('M', 0) - $sexos->get('F', 0)];
        $pdf = Pdf::loadView('docente.estudiantes-pdf', compact('institucion', 'periodo', 'docente', 'carga', 'matriculas', 'resumen'))
            ->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('estudiantes-paralelo-'.$carga->paralelo_id.'-periodo-'.$periodo->id.'.pdf');
    }
}
