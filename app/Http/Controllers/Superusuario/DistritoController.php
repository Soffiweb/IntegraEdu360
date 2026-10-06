<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superusuario\ListDistritosRequest;
use App\Http\Requests\Superusuario\UpdateDistritoRequest;
use App\Models\Distrito;
use App\Models\Zona;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Symfony\Component\HttpFoundation\Response;

class DistritoController extends Controller
{
    public function index(ListDistritosRequest $request): View
    {
        $data = $request->validated();
        $zona = ! empty($data['zona_id']) ? Zona::findOrFail($data['zona_id']) : null;
        $distritoEditando = $zona && ! empty($data['edit'])
            ? $zona->distritos()->findOrFail($data['edit']) : null;
        $porProvincia = $zona
            ? $zona->distritos()->select('provincia')->selectRaw('COUNT(*) as total')->groupBy('provincia')->orderBy('provincia')->pluck('total', 'provincia')
            : collect();

        return view('dashboard-role', [
            'roleKey' => 'superusuario',
            'role' => integraEduRoles()['superusuario'],
            'dashboard' => integraEduDashboards()['superusuario'],
            'dashboardSection' => 'distritos',
            'zonas' => Zona::query()->orderBy('codigo')->get(),
            'zonaSeleccionada' => $zona,
            'distritos' => $zona?->distritos()->orderBy('codigo')->paginate(15)->appends($request->only('zona_id')),
            'distritoEditando' => $distritoEditando,
            'porProvincia' => $porProvincia,
        ]);
    }

    public function update(UpdateDistritoRequest $request, Zona $zona, Distrito $distrito): RedirectResponse
    {
        abort_unless($distrito->zona_id === $zona->id, 404);
        $distrito->update($request->validated());

        return redirect()->route('superusuario.distritos.index', ['zona_id' => $zona->id])
            ->with('success', 'Distrito actualizado correctamente.');
    }

    public function pdf(Zona $zona): Response
    {
        $distritos = $zona->distritos()->orderBy('codigo')->get();
        $pdf = Pdf::loadView('superusuario.distritos.pdf', [
            'zona' => $zona,
            'distritos' => $distritos,
            'porProvincia' => $distritos->groupBy('provincia')->map->count()->sortKeys(),
            'generadoEn' => now('America/Guayaquil'),
            'logoPath' => is_file(storage_path('app/public/logo.png')) ? storage_path('app/public/logo.png') : null,
        ])->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->download('Distritos_Zona_'.$zona->codigo.'.pdf');
    }
}
