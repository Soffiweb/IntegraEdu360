<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Http\Requests\Admin\UpdateInstitucionDatosRequest;
use App\Models\Institucion;
use App\Models\Usuario;
use App\Services\Admin\ActualizarDatosInstitucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class InstitucionDatosController extends Controller
{
    public function index(Request $request)
    {
        $institucion = $this->resolveInstitucion($request);

        abort_unless($institucion, 404, 'No se encontró una institución asociada al usuario administrador.');

        return view('admin.institucion.index', [
            'institucion' => $institucion,
            'estados' => $this->estados(),
            'sostenimientos' => $this->sostenimientos(),
            'regimenes' => $this->regimenes(),
        ]);
    }

    public function update(UpdateInstitucionDatosRequest $request, Institucion $institucion, ActualizarDatosInstitucion $service)
    {
        $institucionAsignada = $this->resolveInstitucion($request);

        abort_unless($institucionAsignada && $institucionAsignada->is($institucion), 403);

        $service->guardar($institucion, $request->validated());

        return redirect()
            ->route('admin.institucion.index')
            ->with('status', 'Los datos institucionales se actualizaron correctamente.');
    }

    private function resolveInstitucion(Request $request): ?Institucion
    {
        $sessionUser = $request->session()->get('auth_user', []);
        if (! empty($sessionUser['id'])) {
            $institucionId = Usuario::find($sessionUser['id'])?->institucion_id;

            return $institucionId ? Institucion::find($institucionId) : null;
        }
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            return Institucion::find($institucionId);
        }

        return null;
    }

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function sostenimientos(): array
    {
        return DB::table('cat_sostenimiento')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    private function regimenes(): array
    {
        return DB::table('cat_regimen_escolar')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }
}
