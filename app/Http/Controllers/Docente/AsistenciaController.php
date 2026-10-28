<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\RegistroAsistenciaRequest;
use App\Services\Docente\RegistroAsistencia;

class AsistenciaController extends Controller
{
    public function index(RegistroAsistenciaRequest $request)
    {
        return view('dashboard-role', $request->contexto() + [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'], 'dashboardSection' => 'asistencia',
        ]);
    }

    public function update(RegistroAsistenciaRequest $request, RegistroAsistencia $registro)
    {
        $registro->guardar($request->contexto(), $request->validated());

        return redirect()->route('docente.asistencia.index', $request->safe()->only(['periodo', 'carga', 'semana']))
            ->with('success', 'La asistencia semanal se guardó correctamente.');
    }
}
