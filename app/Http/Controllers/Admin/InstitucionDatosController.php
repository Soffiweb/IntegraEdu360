<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

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

    public function update(Request $request, Institucion $institucion)
    {
        $institucionAsignada = $this->resolveInstitucion($request);

        abort_unless($institucionAsignada && $institucionAsignada->is($institucion), 403);

        $institucion->update($this->validatedData($request, $institucion));

        return redirect()
            ->route('admin.institucion.index')
            ->with('status', 'Los datos institucionales se actualizaron correctamente.');
    }

    private function resolveInstitucion(Request $request): ?Institucion
    {
        $sessionUser = $request->session()->get('auth_user', []);
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            return Institucion::find($institucionId);
        }

        $usuarioId = $sessionUser['id'] ?? null;

        if ($usuarioId) {
            $usuario = Usuario::find($usuarioId);

            if ($usuario?->institucion_id) {
                return Institucion::find($usuario->institucion_id);
            }
        }

        return Institucion::query()->orderBy('id')->first();
    }

    private function validatedData(Request $request, ?Institucion $institucion = null): array
    {
        $validated = $request->validate([
            'codigo_amie' => ['required', 'string', 'max:20', Rule::unique('instituciones', 'codigo_amie')->ignore($institucion?->id)],
            'nombre' => ['required', 'string', 'max:255'],
            'sostenimiento_id' => ['required', 'integer', Rule::exists('cat_sostenimiento', 'id')],
            'regimen_id' => ['required', 'integer', Rule::exists('cat_regimen_escolar', 'id')],
            'provincia' => ['required', 'string', 'max:120'],
            'canton' => ['required', 'string', 'max:120'],
            'parroquia' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('instituciones', 'email')->ignore($institucion?->id)],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        $validated['codigo_amie'] = strtoupper(trim($validated['codigo_amie']));
        $validated['estado'] = strtoupper(trim($validated['estado']));

        return $validated;
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
