<?php

namespace App\Http\Controllers\Directivo;

use App\Http\Controllers\Controller;
use App\Models\PeriodoAcademico;
use App\Services\Directivo\ContextoDocente;
use App\Services\Directivo\ReporteDocentesAgrupado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class ReporteDocentesAgrupadoController extends Controller
{
    public function __invoke(Request $request, ContextoDocente $contexto, ReporteDocentesAgrupado $reporte, string $agrupacion)
    {
        $institucion = $contexto->institucion($request);
        $filtros = $request->validate(['periodo' => ['nullable', 'integer'], 'search' => ['nullable', 'string', 'max:120']]);
        $periodos = PeriodoAcademico::where('institucion_id', $institucion->id);
        $periodo = isset($filtros['periodo']) ? $periodos->findOrFail($filtros['periodo']) : $periodos->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->firstOrFail();
        $datos = $reporte->datos($institucion, $periodo, $agrupacion, $filtros['search'] ?? '');
        $pdf = Pdf::loadView('directivo.asignaciones.agrupado-pdf', $datos)->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('docentes-por-'.$agrupacion.'-periodo-'.$periodo->id.'.pdf');
    }
}
