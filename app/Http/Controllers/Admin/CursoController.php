<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CursoController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $query = Curso::query()->with(['institucion', 'especialidad']);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nombre', 'like', "%{$search}%")
                    ->orWhere('nivel', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($nivel = $request->string('nivel')->toString()) {
            $query->where('nivel', $nivel);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $cursosList = $query
            ->orderByRaw("CASE nivel
                WHEN 'BASICA_ELEMENTAL' THEN 1
                WHEN 'BASICA_MEDIA' THEN 2
                WHEN 'BASICA_SUPERIOR' THEN 3
                WHEN 'BACHILLERATO' THEN 4
                ELSE 5 END")
            ->orderBy('grado')
            ->get();

        $cursosAgrupados = $cursosList->groupBy(fn ($c) => $c->especialidad?->nombre ?? 'Sin especialidad');

        if ($cursosAgrupados->has('Sin especialidad')) {
            $sinEsp = $cursosAgrupados->pull('Sin especialidad');
            $cursosAgrupados->put('Sin especialidad', $sinEsp);
        }

        $cursoEditando = null;

        if ($request->filled('edit')) {
            $cursoEditando = Curso::with('especialidad')->findOrFail((int) $request->input('edit'));
            abort_unless(! $institucionActiva || (int) $cursoEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        $metricasQuery = Curso::query();
        if ($institucionActiva) {
            $metricasQuery->where('institucion_id', $institucionActiva->id);
        }

        $especialidadesDisponibles = Especialidad::query()
            ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
            ->where('estado', 'ACTIVO')
            ->orderBy('tipo')
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo', 'tipo']);

        return view('admin.cursos.index', [
            'cursosAgrupados' => $cursosAgrupados,
            'totalCursos' => $cursosList->count(),
            'cursoEditando' => $cursoEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'especialidadesDisponibles' => $especialidadesDisponibles,
            'niveles' => $this->niveles(),
            'estados' => $this->estados(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'activos' => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'inactivos' => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
                'niveles' => (clone $metricasQuery)->distinct('nivel')->count('nivel'),
            ],
        ]);
    }

    public function store(Request $request)
    {
        Curso::create($this->validatedData($request, null, $this->activeInstitucion($request)));

        return redirect()
            ->route('admin.cursos.index')
            ->with('status', 'Curso creado correctamente.');
    }

    public function update(Request $request, Curso $curso)
    {
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $curso->institucion_id === (int) $institucionActiva->id, 404);

        $curso->update($this->validatedData($request, $curso, $institucionActiva));

        return redirect()
            ->route('admin.cursos.index')
            ->with('status', 'Curso actualizado correctamente.');
    }

    public function destroy(Curso $curso)
    {
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $curso->institucion_id === (int) $institucionActiva->id, 404);

        $curso->delete();

        return redirect()
            ->route('admin.cursos.index')
            ->with('status', 'Curso eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Curso $curso = null, ?Institucion $institucionActiva = null): array
    {
        $validated = $request->validate([
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'especialidad_id' => ['nullable', 'integer', Rule::exists('especialidades', 'id')],
            'nombre' => ['required', 'string', 'max:120'],
            'nivel' => ['required', Rule::in(array_keys($this->niveles()))],
            'grado' => ['required', 'integer', 'min:1', 'max:13'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));

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

    private function niveles(): array
    {
        return [
            'BASICA_ELEMENTAL' => 'Básica Elemental (1ro - 4to)',
            'BASICA_MEDIA' => 'Básica Media (5to - 7mo)',
            'BASICA_SUPERIOR' => 'Básica Superior (8vo - 10mo)',
            'BACHILLERATO' => 'Bachillerato (1ro - 3ro)',
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
