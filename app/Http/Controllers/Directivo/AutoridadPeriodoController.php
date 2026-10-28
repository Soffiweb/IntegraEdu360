<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use App\Http\Requests\Directivo\GuardarAutoridadPeriodoRequest;
use App\Models\AutoridadPeriodo;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\ContextoDocente;
use App\Services\Directivo\DesignarAutoridadPeriodo;
use Illuminate\Http\Request;

class AutoridadPeriodoController extends Controller
{
    public function index(Request $request, ContextoDocente $contexto)
    {
        $institucion = $contexto->institucion($request);
        $filtros = $request->validate(['periodo' => ['nullable', 'integer']]);
        $periodos = PeriodoAcademico::where('institucion_id', $institucion->id)->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = isset($filtros['periodo']) ? $periodos->firstWhere('id', $filtros['periodo']) : $periodos->firstWhere('es_activo', true) ?? $periodos->first();
        abort_if(isset($filtros['periodo']) && ! $periodo, 404);
        $docentes = app(DesignarAutoridadPeriodo::class)->candidatos($institucion->id)->with('persona')->get()->sortBy(fn ($docente) => $docente->persona ? $docente->nombre_completo : $docente->username)->values();
        $cargos = AutoridadPeriodo::CARGOS;
        $designaciones = $periodo ? AutoridadPeriodo::where('institucion_id', $institucion->id)->where('periodo_id', $periodo->id)
            ->with('docente.persona')->get()->keyBy('cargo') : collect();

        return view('directivo.autoridades.index', compact('institucion', 'periodos', 'periodo', 'docentes', 'cargos', 'designaciones') + ['agendaRole' => 'directivo']);
    }

    public function update(GuardarAutoridadPeriodoRequest $request, ContextoDocente $contexto, DesignarAutoridadPeriodo $designar)
    {
        $datos = $request->validated();
        $designar->guardar($contexto->institucion($request)->id, (int) $datos['periodo_id'], $datos['cargo'], isset($datos['docente_id']) ? (int) $datos['docente_id'] : null);

        return redirect()->route('directivo.autoridades.index', ['periodo' => $datos['periodo_id']])->with('status', empty($datos['docente_id']) ? 'Designación retirada en el período seleccionado.' : 'Autoridad o integrante del consejo designado correctamente.');
    }
}
