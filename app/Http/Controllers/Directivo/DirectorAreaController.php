<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directivo\GuardarDirectorAreaRequest;
use App\Models\DirectorArea;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\ContextoDocente;
use App\Services\Directivo\DesignarDirectorArea;
use Illuminate\Http\Request;

class DirectorAreaController extends Controller
{
    public function index(Request $request, ContextoDocente $contexto)
    {
        $institucion = $contexto->institucion($request);
        $filtros = $request->validate(['periodo' => ['nullable', 'integer']]);
        $periodos = PeriodoAcademico::where('institucion_id', $institucion->id)->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = isset($filtros['periodo']) ? $periodos->firstWhere('id', $filtros['periodo']) : $periodos->firstWhere('es_activo', true) ?? $periodos->first();
        abort_if(isset($filtros['periodo']) && ! $periodo, 404);
        $docentes = $contexto->docentes($institucion->id)->where('estado', 'ACTIVO')->with('persona')->get()->sortBy(fn ($docente) => $docente->persona ? $docente->nombre_completo : $docente->username)->values();
        $areas = DirectorArea::AREAS;
        $designaciones = $periodo ? DirectorArea::where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)
            ->with('docente.persona')->get()->keyBy('area') : collect();

        return view('directivo.directores-area.index', compact('institucion', 'periodos', 'periodo', 'docentes', 'areas', 'designaciones') + ['agendaRole' => 'directivo']);
    }

    public function update(GuardarDirectorAreaRequest $request, ContextoDocente $contexto, DesignarDirectorArea $designar)
    {
        $datos = $request->validated();
        $designar->guardar($contexto->institucion($request)->id, (int) $datos['periodo_id'], $datos['area'], isset($datos['docente_id']) ? (int) $datos['docente_id'] : null);

        return redirect()->route('directivo.directores-area.index', ['periodo' => $datos['periodo_id']])->with('status', empty($datos['docente_id']) ? 'Director retirado del área en el período seleccionado.' : 'Director de área designado correctamente.');
    }
}
