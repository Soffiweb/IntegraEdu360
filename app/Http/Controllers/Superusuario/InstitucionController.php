<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institucion::query();

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('codigo_amie', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('provincia', 'like', "%{$search}%")
                    ->orWhere('canton', 'like', "%{$search}%")
                    ->orWhere('parroquia', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $instituciones = $query
            ->orderBy('nombre')
            ->paginate(8)
            ->withQueryString();

        $institucionEditando = null;

        if ($request->filled('edit')) {
            $institucionEditando = Institucion::findOrFail((int) $request->input('edit'));
        }

        return view('superusuario.instituciones.index', [
            'instituciones' => $instituciones,
            'institucionEditando' => $institucionEditando,
            'estados' => $this->estados(),
            'sostenimientos' => $this->sostenimientos(),
            'regimenes' => $this->regimenes(),
            'metricas' => [
                'total' => Institucion::count(),
                'activos' => Institucion::whereIn('estado', ['ACTIVO', 'ACTIVA'])->count(),
                'inactivos' => Institucion::whereIn('estado', ['INACTIVO', 'INACTIVA'])->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        Institucion::create($validated);

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institucion creada correctamente.');
    }

    public function update(Request $request, Institucion $institucion)
    {
        $validated = $this->validatedData($request, $institucion);

        $institucion->update($validated);

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institucion actualizada correctamente.');
    }

    public function destroy(Institucion $institucion)
    {
        $institucion->delete();

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institucion eliminada correctamente.');
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
