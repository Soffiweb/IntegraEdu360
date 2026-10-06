<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superusuario\SaveRolRequest;
use App\Models\Rol;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;

class RolController extends Controller
{
    public function index(Request $request): View
    {
        return view('dashboard-role', [
            'roleKey' => 'superusuario',
            'role' => integraEduRoles()['superusuario'],
            'dashboard' => integraEduDashboards()['superusuario'],
            'dashboardSection' => 'roles',
            'rolesUsuario' => Rol::query()->select(['id', 'codigo', 'nombre'])->orderBy('nombre')->get(),
            'rolEditando' => $request->filled('edit') ? Rol::findOrFail($request->integer('edit')) : null,
            'crearRol' => $request->boolean('create'),
        ]);
    }

    public function store(SaveRolRequest $request): RedirectResponse
    {
        Rol::create($request->validated());

        return redirect()->route('superusuario.roles.index')->with('success', 'Rol creado correctamente.');
    }

    public function update(SaveRolRequest $request, Rol $rol): RedirectResponse
    {
        $rol->update($request->validated());

        return redirect()->route('superusuario.roles.index')->with('success', 'Rol actualizado correctamente.');
    }

    public function destroy(Rol $rol): RedirectResponse
    {
        if ($rol->usuarios()->exists()) {
            return redirect()->route('superusuario.roles.index')->withErrors(['rol' => 'No se puede eliminar un rol asignado a usuarios.']);
        }

        $rol->delete();

        return redirect()->route('superusuario.roles.index')->with('success', 'Rol eliminado correctamente.');
    }
}
