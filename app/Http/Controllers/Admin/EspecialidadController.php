<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Especialidad;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class EspecialidadController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $query = Especialidad::query()->with('institucion');

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhere('tipo', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($tipo = $request->string('tipo')->toString()) {
            $query->where('tipo', $tipo);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $especialidades = $query
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->paginate(10)
            ->withQueryString();

        $especialidadEditando = null;

        if ($request->filled('edit')) {
            $especialidadEditando = Especialidad::findOrFail((int) $request->input('edit'));
            abort_unless(! $institucionActiva || (int) $especialidadEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        $metricasQuery = Especialidad::query();
        if ($institucionActiva) {
            $metricasQuery->where('institucion_id', $institucionActiva->id);
        }

        return view('admin.especialidades.index', [
            'especialidades' => $especialidades,
            'especialidadEditando' => $especialidadEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'tipos' => $this->tipos(),
            'estados' => $this->estados(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'activos' => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'inactivos' => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
                'tipos' => (clone $metricasQuery)->distinct('tipo')->count('tipo'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        Especialidad::create($this->validatedData($request, null, $this->activeInstitucion($request)));

        return redirect()
            ->route('admin.especialidades.index')
            ->with('status', 'Especialidad creada correctamente.');
    }

    public function update(Request $request, Especialidad $especialidad)
    {
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $especialidad->institucion_id === (int) $institucionActiva->id, 404);

        $especialidad->update($this->validatedData($request, $especialidad, $institucionActiva));

        return redirect()
            ->route('admin.especialidades.index')
            ->with('status', 'Especialidad actualizada correctamente.');
    }

    public function destroy(Especialidad $especialidad)
    {
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $especialidad->institucion_id === (int) $institucionActiva->id, 404);

        $especialidad->delete();

        return redirect()
            ->route('admin.especialidades.index')
            ->with('status', 'Especialidad eliminada correctamente.');
    }

    private function validatedData(Request $request, ?Especialidad $especialidad = null, ?Institucion $institucionActiva = null): array
    {
        $validated = $request->validate([
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'nombre' => ['required', 'string', 'max:150'],
            'codigo' => ['nullable', 'string', 'max:20'],
            'tipo' => ['required', Rule::in(array_keys($this->tipos()))],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));
        $validated['tipo'] = strtoupper(trim($validated['tipo']));
        $validated['codigo'] = isset($validated['codigo']) ? strtoupper(trim($validated['codigo'])) ?: null : null;

        return $validated;
    }

    private function availableInstituciones()
    {
        return Institucion::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function activeInstitucion(Request $request): ?Institucion
    {
        $sessionUser = $request->session()->get('auth_user', []);
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            return Institucion::find($institucionId);
        }

        $usuarioId = $sessionUser['id'] ?? null;

        if (! $usuarioId) {
            return null;
        }

        $usuario = Usuario::find($usuarioId);

        return $usuario?->institucion_id ? Institucion::find($usuario->institucion_id) : null;
    }

    private function tipos(): array
    {
        return [
            'GENERAL' => 'Bachillerato General Unificado (BGU)',
            'TECNICO' => 'Bachillerato Técnico',
            'TECNICO_PRODUCTIVO' => 'Bachillerato Técnico Productivo',
        ];
    }

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
        ];
    }
}
