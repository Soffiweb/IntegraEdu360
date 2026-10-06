<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superusuario\UpdateZonaRequest;
use App\Models\Zona;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class ZonaController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard-role', [
            'roleKey' => 'superusuario',
            'role' => integraEduRoles()['superusuario'],
            'dashboard' => integraEduDashboards()['superusuario'],
            'dashboardSection' => 'zonas',
            'zonaEditando' => $request->filled('edit') ? Zona::findOrFail($request->integer('edit')) : null,
            'zonas' => Zona::query()->orderBy('codigo')->get(),
        ]);
    }

    public function update(UpdateZonaRequest $request, Zona $zona): RedirectResponse
    {
        $zona->update($request->validated());

        return redirect()->route('superusuario.zonas.index')->with('success', 'Zona actualizada correctamente.');
    }
}
