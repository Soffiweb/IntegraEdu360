<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Services\Docente\RegistroCalificaciones;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CalificacionController extends Controller
{
    public function index(Request $request, RegistroCalificaciones $registro)
    {
        $request->validate(RegistroCalificaciones::REGLAS_FILTROS);

        return view('dashboard-role', $registro->contexto($request) + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'calificaciones',
        ]);
    }

    public function update(Request $request, RegistroCalificaciones $registro)
    {
        $nota = ['nullable', 'decimal:0,2', 'between:0.01,10'];
        $validos = $request->validate([
            'periodo' => ['required', 'integer'], 'carga' => ['required', 'integer'], 'trimestre' => ['required', 'in:1,2,3'],
            'calificaciones' => ['required', 'array'],
            'calificaciones.*.aportes' => ['nullable', 'array'],
            'calificaciones.*.aportes.*' => $nota, 'calificaciones.*.sumativa' => $nota, 'calificaciones.*.mejora' => $nota,
            'calificaciones.*.valoracion' => ['nullable', Rule::in(array_keys(RegistroCalificaciones::ESCALA))],
        ], [
            'calificaciones.*.*.decimal' => 'Las calificaciones admiten hasta dos decimales.',
            'calificaciones.*.*.between' => 'Las calificaciones deben estar entre 0,01 y 10,00.',
            'calificaciones.*.aportes.*.decimal' => 'Las calificaciones admiten hasta dos decimales.',
            'calificaciones.*.aportes.*.between' => 'Las calificaciones deben estar entre 0,01 y 10,00.',
            'calificaciones.*.valoracion.in' => 'Seleccione una valoración de la escala cualitativa.',
        ]);
        $contexto = $registro->contexto($request);
        abort_if($contexto['tipo'] === 'destrezas', 404);
        $registro->guardar($contexto, $validos['calificaciones']);

        return redirect()->route('docente.calificaciones.index', ['periodo' => $contexto['periodo']->id, 'carga' => $contexto['carga']->id, 'trimestre' => $contexto['trimestre']])
            ->with('success', 'Se grabaron las calificaciones del '.mb_strtolower(RegistroCalificaciones::TRIMESTRES[$contexto['trimestre']]).'.');
    }

    public function pdf(Request $request, RegistroCalificaciones $registro)
    {
        $request->validate(RegistroCalificaciones::REGLAS_FILTROS);
        $datos = $registro->contexto($request);
        abort_if(! $datos['carga'] || $datos['tipo'] === 'destrezas', 404);
        $pdf = Pdf::loadView('docente.calificaciones-pdf', $datos)->setPaper('a4', 'landscape');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(750, 568, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('calificaciones-carga-'.$datos['carga']->id.'-trimestre-'.$datos['trimestre'].'.pdf');
    }
}
