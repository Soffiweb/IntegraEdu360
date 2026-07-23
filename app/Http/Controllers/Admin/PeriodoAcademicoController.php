<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class PeriodoAcademicoController extends Controller
{
    public function index(Request $request)
    {
        $query = PeriodoAcademico::query()->with('institucion');
        $institucionActiva = $this->activeInstitucion($request);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nombre', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($institucionQuery) => $institucionQuery->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $periodos = $query
            ->orderByDesc('fecha_inicio')
            ->paginate(8)
            ->withQueryString();

        $periodoEditando = null;

        if ($request->filled('edit')) {
            $periodoEditando = PeriodoAcademico::findOrFail((int) $request->input('edit'));
            abort_unless(! $institucionActiva || (int) $periodoEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        return view('admin.periodos-academicos.index', [
            'periodos' => $periodos,
            'periodoEditando' => $periodoEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'estados' => $this->estados(),
            'metricas' => [
                'total' => (clone $query)->count(),
                'activos' => (clone $query)->where('estado', 'ACTIVO')->count(),
                'planificados' => (clone $query)->where('estado', 'PLANIFICADO')->count(),
                'cerrados' => (clone $query)->where('estado', 'CERRADO')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        PeriodoAcademico::create($this->validatedData($request, null, $this->activeInstitucion($request)));

        return redirect()
            ->route('admin.periodos.index')
            ->with('status', 'Periodo academico creado correctamente.');
    }

    public function update(Request $request, PeriodoAcademico $periodo)
    {
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $periodo->institucion_id === (int) $institucionActiva->id, 404);

        $periodo->update($this->validatedData($request, $periodo, $institucionActiva));

        return redirect()
            ->route('admin.periodos.index')
            ->with('status', 'Periodo academico actualizado correctamente.');
    }

    public function destroy(PeriodoAcademico $periodo)
    {
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $periodo->institucion_id === (int) $institucionActiva->id, 404);

        $periodo->delete();

        return redirect()
            ->route('admin.periodos.index')
            ->with('status', 'Periodo academico eliminado correctamente.');
    }

    private function validatedData(Request $request, ?PeriodoAcademico $periodo = null, ?Institucion $institucionActiva = null): array
    {
        $validated = $request->validate([
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'nombre' => ['required', 'string', 'max:255'],
            'fecha_inicio' => ['required', 'date'],
            'fecha_fin' => ['required', 'date', 'after_or_equal:fecha_inicio'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));
        $validated['es_activo'] = $validated['estado'] === 'ACTIVO';

        return $validated;
    }

    private function availableInstituciones()
    {
        return Institucion::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
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
            'PLANIFICADO' => 'Planificado',
            'ACTIVO' => 'Activo',
            'CERRADO' => 'Cerrado',
        ];
    }
}
