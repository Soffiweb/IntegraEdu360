<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class ParaleloController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $query = Paralelo::query()->with(['curso.institucion', 'curso.especialidad']);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('letra', 'like', "%{$search}%")
                    ->orWhereHas('curso', function ($q) use ($search) {
                        $q->where('nombre', 'like', "%{$search}%")
                          ->orWhere('nivel', 'like', "%{$search}%")
                          ->orWhereHas('institucion', fn ($qi) => $qi->where('nombre', 'like', "%{$search}%"));
                    });
            });
        }

        if ($institucionActiva) {
            $query->whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionActiva->id));
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionId));
        }

        if ($cursoId = $request->string('curso_id')->toString()) {
            $query->where('curso_id', $cursoId);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $paralelosList = $query
            ->orderByRaw("(SELECT CASE nivel
                WHEN 'BASICA_ELEMENTAL' THEN 1
                WHEN 'BASICA_MEDIA' THEN 2
                WHEN 'BASICA_SUPERIOR' THEN 3
                WHEN 'BACHILLERATO' THEN 4
                ELSE 5 END FROM cursos WHERE cursos.id = paralelos.curso_id)")
            ->orderByRaw('(SELECT grado FROM cursos WHERE cursos.id = paralelos.curso_id)')
            ->orderBy('letra')
            ->get();

        $paralelosAgrupados = $paralelosList->groupBy([
            fn ($p) => $p->curso?->especialidad?->nombre ?? 'Sin especialidad',
            fn ($p) => $p->curso?->nombre ?? 'Sin curso',
        ]);

        if ($paralelosAgrupados->has('Sin especialidad')) {
            $sinEsp = $paralelosAgrupados->pull('Sin especialidad');
            $paralelosAgrupados->put('Sin especialidad', $sinEsp);
        }

        $paraleloEditando = null;

        if ($request->filled('edit')) {
            $paraleloEditando = Paralelo::with('curso.institucion')->findOrFail((int) $request->input('edit'));

            if ($institucionActiva) {
                abort_unless((int) $paraleloEditando->curso->institucion_id === (int) $institucionActiva->id, 404);
            }
        }

        $metricasQuery = Paralelo::query();
        if ($institucionActiva) {
            $metricasQuery->whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionActiva->id));
        }

        $cursosDisponibles = Curso::query()
            ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
            ->where('estado', 'ACTIVO')
            ->orderByRaw("CASE nivel
                WHEN 'BASICA_ELEMENTAL' THEN 1
                WHEN 'BASICA_MEDIA' THEN 2
                WHEN 'BASICA_SUPERIOR' THEN 3
                WHEN 'BACHILLERATO' THEN 4
                ELSE 5 END")
            ->orderBy('grado')
            ->get(['id', 'nombre', 'nivel', 'grado', 'institucion_id']);

        return view('admin.paralelos.index', [
            'paralelosAgrupados' => $paralelosAgrupados,
            'totalParalelos' => $paralelosList->count(),
            'paraleloEditando' => $paraleloEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'cursosDisponibles' => $cursosDisponibles,
            'estados' => $this->estados(),
            'letras' => $this->letras(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'activos' => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'inactivos' => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
                'cursos' => (clone $metricasQuery)->distinct('curso_id')->count('curso_id'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $validated = $this->validatedData($request, null, $institucionActiva);

        Paralelo::create($validated);

        return redirect()
            ->route('admin.paralelos.index')
            ->with('status', 'Paralelo creado correctamente.');
    }

    public function update(Request $request, Paralelo $paralelo)
    {
        $institucionActiva = $this->activeInstitucion($request);

        if ($institucionActiva) {
            abort_unless((int) $paralelo->curso->institucion_id === (int) $institucionActiva->id, 404);
        }

        $validated = $this->validatedData($request, $paralelo, $institucionActiva);
        $paralelo->update($validated);

        return redirect()
            ->route('admin.paralelos.index')
            ->with('status', 'Paralelo actualizado correctamente.');
    }

    public function destroy(Paralelo $paralelo)
    {
        $institucionActiva = $this->activeInstitucion(request());

        if ($institucionActiva) {
            $paralelo->loadMissing('curso');
            abort_unless((int) $paralelo->curso->institucion_id === (int) $institucionActiva->id, 404);
        }

        $paralelo->delete();

        return redirect()
            ->route('admin.paralelos.index')
            ->with('status', 'Paralelo eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Paralelo $paralelo = null, ?Institucion $institucionActiva = null): array
    {
        $validated = $request->validate([
            'curso_id' => [
                'required',
                'integer',
                Rule::exists('cursos', 'id'),
            ],
            'letra' => [
                'required',
                'string',
                'max:5',
                Rule::unique('paralelos')->where('curso_id', $request->input('curso_id'))->ignore($paralelo?->id),
            ],
            'capacidad' => ['required', 'integer', 'min:1', 'max:100'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        if ($institucionActiva) {
            $curso = Curso::findOrFail($validated['curso_id']);
            abort_unless((int) $curso->institucion_id === (int) $institucionActiva->id, 403);
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));
        $validated['letra'] = strtoupper(trim($validated['letra']));

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

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function letras(): array
    {
        return ['A', 'B', 'C', 'D', 'E', 'F'];
    }
}
