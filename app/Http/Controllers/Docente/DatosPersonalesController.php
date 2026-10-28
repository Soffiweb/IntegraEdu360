<?php

namespace App\Http\Controllers\Docente;

use App\Http\Controllers\Controller;
use App\Http\Requests\Docente\ActualizarDatosRequest;
use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class DatosPersonalesController extends Controller
{
    public function edit(Request $request)
    {
        $docente = Usuario::with('persona')->findOrFail($request->session()->get('auth_user.id'));

        return view('dashboard-role', [
            'roleKey' => 'docente', 'role' => integraEduRoles()['docente'],
            'dashboard' => integraEduDashboards()['docente'],
            'dashboardSection' => 'actualizar-datos', 'persona' => $docente->persona,
        ]);
    }

    public function update(ActualizarDatosRequest $request)
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        DB::transaction(function () use ($docente, $request) {
            $persona = $docente->persona ?? new Persona;
            $persona->fill($request->validated())->save();
            $docente->persona()->associate($persona);
            $docente->save();
        });
        $request->session()->put('auth_user.display_name', $docente->nombre_completo);

        return redirect()->route('docente.datos.edit')->with('success', 'Sus datos se actualizaron correctamente.');
    }
}
