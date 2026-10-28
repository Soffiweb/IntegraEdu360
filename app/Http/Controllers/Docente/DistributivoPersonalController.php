<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Models\AsignacionDocente;
use App\Models\PeriodoAcademico;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use Illuminate\Http\Request;

class DistributivoPersonalController extends Controller
{
    public function index(Request $request)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403, 'La cuenta debe tener una institución asignada.');
        $request->validate(['periodo' => ['nullable', 'integer']]);
        $periodos = PeriodoAcademico::where('institucion_id', $docente->institucion_id)
            ->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = $request->filled('periodo') ? $periodos->firstWhere('id', $request->integer('periodo')) : $periodos->first();
        abort_if($request->filled('periodo') && ! $periodo, 404);
        $asignaciones = AsignacionDocente::where('institucion_id', $docente->institucion_id)
            ->where('docente_id', $docente->id)->where('periodo_id', $periodo?->id)
            ->where('tipo', 'carga')->with(['asignatura', 'curso.especialidad', 'paralelo'])
            ->orderBy('asignatura_id')->orderBy('curso_id')->orderBy('paralelo_id')->get();
        $comisiones = AsignacionDocente::where('institucion_id', $docente->institucion_id)
            ->where('docente_id', $docente->id)->where('periodo_id', $periodo?->id)
            ->where('tipo', 'comision')->orderBy('titulo')->get();
        $tutorias = TutoriaParalelo::where('institucion_id', $docente->institucion_id)
            ->where('docente_id', $docente->id)->where('periodo_id', $periodo?->id)
            ->with('paralelo.curso.especialidad')->orderBy('paralelo_id')->get();

        return view('dashboard-role', compact('periodos', 'periodo', 'asignaciones', 'comisiones', 'tutorias') + [
            'roleKey' => 'docente',
            'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'],
            'dashboardSection' => 'distributivo-personal',
            'totalHoras' => $asignaciones->sum('horas_semanales'),
        ]);
    }
}
