<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directivo\GuardarTutoriaRequest;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\AsignarTutorParalelo;
use App\Services\Directivo\ContextoDocente;
use Illuminate\Http\Request;

class TutoriaController extends Controller
{
    public function index(Request $request, ContextoDocente $contexto)
    {
        $institucion = $contexto->institucion($request);
        $filtros = $request->validate(['periodo' => ['nullable', 'integer']]);
        $periodos = PeriodoAcademico::where('institucion_id', $institucion->id)->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = isset($filtros['periodo']) ? $periodos->firstWhere('id', $filtros['periodo']) : $periodos->firstWhere('es_activo', true) ?? $periodos->first();
        abort_if(isset($filtros['periodo']) && ! $periodo, 404);
        $docentes = $contexto->docentes($institucion->id)->where('estado', 'ACTIVO')->with('persona')->get()->sortBy(fn ($docente) => $docente->persona ? $docente->nombre_completo : $docente->username)->values();
        $paralelos = $periodo ? Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $institucion->id)->where('estado', 'ACTIVO'))
            ->where('estado', 'ACTIVO')->with(['curso.especialidad', 'tutorias' => fn ($q) => $q->where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)->with('docente.persona')])->get()
            ->sortBy(fn ($p) => sprintf('%03d', $p->curso->grado).'|'.($p->curso->especialidad?->nombre ?? '').'|'.$p->curso->nombre.'|'.$p->letra, SORT_NATURAL)->values() : collect();

        return view('directivo.tutorias.index', compact('institucion', 'periodos', 'periodo', 'docentes', 'paralelos') + ['agendaRole' => 'directivo']);
    }

    public function update(GuardarTutoriaRequest $request, Paralelo $paralelo, ContextoDocente $contexto, AsignarTutorParalelo $asignar)
    {
        $datos = $request->validated();
        $asignar->guardar($contexto->institucion($request)->id, (int) $datos['periodo_id'], $paralelo->id, isset($datos['docente_id']) ? (int) $datos['docente_id'] : null);

        return redirect()->route('directivo.tutorias.index', ['periodo' => $datos['periodo_id']])->with('status', empty($datos['docente_id']) ? 'Tutor retirado del paralelo en el período seleccionado.' : 'Docente tutor asignado al paralelo en el período seleccionado.');
    }
}
