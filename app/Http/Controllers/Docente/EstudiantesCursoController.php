<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use Illuminate\Http\Request;

class EstudiantesCursoController extends Controller
{
    public function index(Request $request)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403, 'La cuenta debe tener una institución asignada.');
        $request->validate(['periodo' => ['nullable', 'integer'], 'curso' => ['nullable', 'integer'], 'paralelo' => ['nullable', 'integer']]);
        $periodos = PeriodoAcademico::where('institucion_id', $docente->institucion_id)
            ->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = $request->filled('periodo') ? $periodos->firstWhere('id', $request->integer('periodo')) : $periodos->first();
        abort_if($request->filled('periodo') && ! $periodo, 404);
        $asignaciones = AsignacionDocente::where('institucion_id', $docente->institucion_id)
            ->where('periodo_id', $periodo?->id)->where('docente_id', $docente->id)
            ->where('tipo', 'carga')->get(['curso_id', 'paralelo_id']);
        $tutorias = TutoriaParalelo::where('institucion_id', $docente->institucion_id)
            ->where('periodo_id', $periodo?->id)->where('docente_id', $docente->id)->pluck('paralelo_id');
        $paralelosPermitidos = Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $docente->institucion_id))
            ->where(function ($q) use ($asignaciones, $tutorias) {
                $q->whereIn('id', $tutorias)->orWhereIn('id', $asignaciones->pluck('paralelo_id')->filter())
                    ->orWhereIn('curso_id', $asignaciones->whereNull('paralelo_id')->pluck('curso_id')->filter());
            })->orderBy('letra')->get();
        $cursos = Curso::where('institucion_id', $docente->institucion_id)->with('especialidad')
            ->whereIn('id', $asignaciones->pluck('curso_id')->merge($paralelosPermitidos->pluck('curso_id'))->filter())
            ->orderBy('grado')->orderBy('nombre')->get();
        $curso = $request->filled('curso') ? $cursos->firstWhere('id', $request->integer('curso')) : $cursos->first();
        abort_if($request->filled('curso') && ! $curso, 404);
        $paralelos = $paralelosPermitidos->where('curso_id', $curso?->id)->values();
        $paralelo = $request->filled('paralelo') ? $paralelos->firstWhere('id', $request->integer('paralelo')) : null;
        abort_if($request->filled('paralelo') && ! $paralelo, 404);
        $matriculas = Matricula::where('institucion_id', $docente->institucion_id)->where('periodo_id', $periodo?->id)
            ->where('estado', 'ACTIVA')->whereIn('paralelo_id', $paralelo ? [$paralelo->id] : $paralelos->pluck('id'))
            ->whereHas('estudiante', fn ($q) => $q->alumnos()->where('institucion_id', $docente->institucion_id))
            ->with(['estudiante.persona', 'paralelo'])->get()
            ->sortBy(fn ($m) => ($m->estudiante->persona?->primer_apellido ?? '').' '.$m->estudiante->nombre_completo, SORT_NATURAL | SORT_FLAG_CASE)->values();

        return view('dashboard-role', compact('periodos', 'periodo', 'cursos', 'curso', 'paralelos', 'paralelo', 'matriculas') + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'estudiantes-curso',
        ]);
    }
}
