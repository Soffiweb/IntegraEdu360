<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directivo\GuardarAsignacionRequest;
use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\ContextoDocente;
use Illuminate\Http\Request;

class AsignacionDocenteController extends Controller
{
    public function index(Request $request, ContextoDocente $contexto)
    {
        $institucion = $contexto->institucion($request);
        $filtros = $request->validate(['periodo' => ['nullable', 'integer'], 'docente' => ['nullable', 'integer'], 'edit' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:120']]);
        $periodos = PeriodoAcademico::where('institucion_id', $institucion->id)->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = isset($filtros['periodo']) ? $periodos->firstWhere('id', $filtros['periodo']) : $periodos->first();
        abort_if(isset($filtros['periodo']) && ! $periodo, 404);
        $editando = isset($filtros['edit']) ? AsignacionDocente::where('institucion_id', $institucion->id)->findOrFail($filtros['edit']) : null;
        if ($editando) {
            $periodo = $periodos->firstWhere('id', $editando->periodo_id);
        }
        $base = AsignacionDocente::where('institucion_id', $institucion->id)->where('periodo_id', $periodo?->id);
        $horas = (clone $base)->where('tipo', 'carga')->select('docente_id')->selectRaw('SUM(horas_semanales) as horas')->groupBy('docente_id')->pluck('horas', 'docente_id');
        $docentes = $contexto->docentes($institucion->id)->with('persona')->orderBy('username')->get()->sortBy(fn ($docente) => $docente->nombre_completo)->values();
        $docenteId = $editando?->docente_id ?? ($filtros['docente'] ?? $docentes->first()?->id);
        $docente = $docenteId ? $docentes->firstWhere('id', $docenteId) : null;
        abort_if($docenteId && ! $docente, 404);
        $busqueda = mb_strtolower(trim($filtros['search'] ?? ''));
        $docentesVisibles = $docentes->filter(fn ($docente) => $busqueda === '' || str_contains(mb_strtolower($docente->nombre_completo.' '.$docente->username), $busqueda));
        $asignaciones = (clone $base)->where('docente_id', $docente?->id)->with(['curso', 'paralelo', 'asignatura'])->orderBy('tipo')->orderBy('titulo')->get();
        $cursos = Curso::where('institucion_id', $institucion->id)->with(['paralelos', 'especialidad', 'asignaturas' => fn ($query) => $query->where('asignaturas.institucion_id', $institucion->id)])->orderBy('nombre')->get();

        return view('directivo.asignaciones.index', compact('institucion', 'periodos', 'periodo', 'docente', 'docentesVisibles', 'asignaciones', 'horas', 'editando', 'cursos') + ['agendaRole' => 'directivo']);
    }

    public function store(GuardarAsignacionRequest $request, ContextoDocente $contexto)
    {
        $datos = $request->validated();
        $asignacion = AsignacionDocente::create($datos + ['institucion_id' => $contexto->institucion($request)->id]);

        return $this->volver($asignacion, 'Asignación guardada correctamente.');
    }

    public function update(GuardarAsignacionRequest $request, AsignacionDocente $asignacion)
    {
        $asignacion->update($request->validated() + ['curso_id' => null, 'paralelo_id' => null, 'asignatura_id' => null]);

        return $this->volver($asignacion, 'Asignación actualizada correctamente.');
    }

    public function destroy(Request $request, AsignacionDocente $asignacion, ContextoDocente $contexto)
    {
        abort_unless((int) $asignacion->institucion_id === (int) $contexto->institucion($request)->id, 404);
        $asignacion->delete();

        return $this->volver($asignacion, 'Asignación retirada correctamente.');
    }

    private function volver(AsignacionDocente $asignacion, string $mensaje)
    {
        return redirect()->route('directivo.asignaciones.index', ['periodo' => $asignacion->periodo_id, 'docente' => $asignacion->docente_id])->with('status', $mensaje);
    }
}
