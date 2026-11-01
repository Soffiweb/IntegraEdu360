<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\RegistroAsistenciaRequest;
use App\Services\Docente\RegistroAsistencia;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;

class AsistenciaController extends Controller
{
    public function index(Request $request, RegistroAsistencia $registro)
    {
        $request->validate(['periodo' => ['nullable', 'integer'], 'carga' => ['nullable', 'integer']]);

        return view('dashboard-role', $registro->listado($request) + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'asistencia', 'asistenciaVista' => 'listado',
        ]);
    }

    public function reporte(Request $request, RegistroAsistencia $registro, string $agrupacion)
    {
        $request->validate(RegistroAsistencia::REGLAS_FILTROS);
        $datos = $registro->reporte($request, $agrupacion);
        $pdf = Pdf::loadView('docente.asistencia-reporte-pdf', $datos)->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('clases-por-'.$agrupacion.'-periodo-'.$datos['periodo']->id.'.pdf');
    }

    public function create(RegistroAsistenciaRequest $request)
    {
        return view('dashboard-role', $request->contexto() + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'asistencia', 'asistenciaVista' => 'registro',
        ]);
    }

    public function update(RegistroAsistenciaRequest $request, RegistroAsistencia $registro)
    {
        $contexto = $request->contexto();
        $registro->guardar($contexto, $request->validated());
        $carga = $contexto['carga'];
        $clase = ($carga->asignatura?->nombre ?? $carga->titulo).' · '.$carga->curso->nombre.' '.$carga->paralelo->letra;

        return redirect()->route('docente.asistencia.index', ['periodo' => $contexto['periodo']->id, 'carga' => $carga->id])
            ->with('success', "Se grabó exitosamente la clase de {$clase} del {$contexto['fecha']->format('d/m/Y')}.");
    }
}
