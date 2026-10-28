<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use App\Services\Directivo\AsignarTutorParalelo;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;
use Illuminate\Validation\ValidationException;

class ParaleloController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $paralelosList = $this->filteredQuery($request, $institucionActiva)->get();

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
            'tutoresDisponibles' => $this->docentesQuery()
                ->with('persona')
                ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
                ->orderBy('username')->get(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'activos' => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'inactivos' => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
                'cursos' => (clone $metricasQuery)->distinct('curso_id')->count('curso_id'),
            ],
        ]);
    }

    public function pdf(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $institucion = $institucionActiva ?? ($request->filled('institucion') ? Institucion::find($request->input('institucion')) : null);
        $paralelos = $this->filteredQuery($request, $institucionActiva)->get();
        $grupos = $paralelos->groupBy(fn ($p) => $p->curso->institucion_id.':'.($p->curso->especialidad_id ?? 'sin'))
            ->map(function ($items) {
                $curso = $items->first()->curso;

                return [
                    'nombre' => $curso->especialidad?->nombre ?? 'Sin especialidad',
                    'institucion' => $curso->institucion?->nombre,
                    'paralelos' => $items,
                    'total' => $items->count(),
                    'cursos' => $items->pluck('curso_id')->unique()->count(),
                    'capacidad' => $items->sum('capacidad'),
                    'con_tutor' => $items->whereNotNull('tutor_id')->count(),
                ];
            })->sortBy('nombre')->values();
        $resumen = [
            'paralelos' => $paralelos->count(),
            'cursos' => $paralelos->pluck('curso_id')->unique()->count(),
            'especialidades' => $paralelos->pluck('curso.especialidad_id')->filter()->unique()->count(),
            'capacidad' => $paralelos->sum('capacidad'),
            'activos' => $paralelos->where('estado', 'ACTIVO')->count(),
            'inactivos' => $paralelos->where('estado', 'INACTIVO')->count(),
            'con_tutor' => $paralelos->whereNotNull('tutor_id')->count(),
            'sin_tutor' => $paralelos->whereNull('tutor_id')->count(),
        ];
        $pdf = Pdf::loadView('admin.paralelos.pdf', [
            'paralelos' => $paralelos,
            'grupos' => $grupos,
            'resumen' => $resumen,
            'institucion' => $institucion,
            'estados' => $this->estados(),
            'filtros' => $request->only(['search', 'estado', 'curso_id']),
            'cursoFiltrado' => $request->filled('curso_id') ? Curso::query()
                ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
                ->find($request->input('curso_id')) : null,
            'generadoEn' => now()->timezone('America/Guayaquil'),
        ])->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('reporte-paralelos.pdf');
    }

    private function filteredQuery(Request $request, ?Institucion $institucionActiva): Builder
    {
        $query = Paralelo::query()->with(['curso.institucion', 'curso.especialidad', 'tutor.persona']);

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

        return $query
            ->orderByRaw("(SELECT CASE nivel
                WHEN 'PREPARATORIA' THEN 0
                WHEN 'BASICA_ELEMENTAL' THEN 1
                WHEN 'BASICA_MEDIA' THEN 2
                WHEN 'BASICA_SUPERIOR' THEN 3
                WHEN 'BACHILLERATO' THEN 4
                ELSE 5 END FROM cursos WHERE cursos.id = paralelos.curso_id)")
            ->orderByRaw('(SELECT grado FROM cursos WHERE cursos.id = paralelos.curso_id)')
            ->orderBy('letra');
    }

    public function store(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $validated = $this->validatedData($request, null, $institucionActiva);

        DB::transaction(function () use ($validated) {
            $this->validarTutorDisponible($validated);
            $paralelo = Paralelo::create($validated);
            $this->sincronizarTutorVigente($paralelo);
        }, 3);

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
        DB::transaction(function () use ($paralelo, $validated) {
            $this->validarTutorDisponible($validated, $paralelo);
            $paralelo->update($validated);
            $this->sincronizarTutorVigente($paralelo);
        }, 3);

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

    private function validarTutorDisponible(array $datos, ?Paralelo $paralelo = null): void
    {
        if (empty($datos['tutor_id'])) {
            return;
        }

        Usuario::whereKey($datos['tutor_id'])->lockForUpdate()->firstOrFail();
        $institucionId = Curso::findOrFail($datos['curso_id'])->institucion_id;
        if (Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionId))
            ->where('tutor_id', $datos['tutor_id'])
            ->when($paralelo, fn ($q) => $q->where('id', '!=', $paralelo->id))->exists()) {
            throw ValidationException::withMessages(['tutor_id' => 'Este docente ya es tutor de otro curso o paralelo.']);
        }
    }

    private function sincronizarTutorVigente(Paralelo $paralelo): void
    {
        $institucionId = $paralelo->curso->institucion_id;
        $periodo = PeriodoAcademico::where('institucion_id', $institucionId)->where('es_activo', true)->orderByDesc('fecha_inicio')->first();
        if ($periodo) {
            try {
                app(AsignarTutorParalelo::class)->guardar($institucionId, $periodo->id, $paralelo->id, $paralelo->tutor_id ? (int) $paralelo->tutor_id : null);
            } catch (ValidationException $exception) {
                throw ValidationException::withMessages(['tutor_id' => $exception->errors()['docente_id'] ?? $exception->getMessage()]);
            }
        }
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
            'tutor_id' => ['nullable', 'integer', Rule::exists('usuarios', 'id')],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        $curso = Curso::findOrFail($validated['curso_id']);
        if (! empty($validated['tutor_id']) && ! $this->docentesQuery()
            ->whereKey($validated['tutor_id'])->where('institucion_id', $curso->institucion_id)->exists()) {
            throw ValidationException::withMessages(['tutor_id' => 'Seleccione un docente de la misma institución del curso.']);
        }

        $validated['tutor_id'] = $validated['tutor_id'] ?? null;

        if ($institucionActiva) {
            abort_unless((int) $curso->institucion_id === (int) $institucionActiva->id, 403);
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));
        $validated['letra'] = strtoupper(trim($validated['letra']));

        return $validated;
    }

    private function docentesQuery(): Builder
    {
        return Usuario::query()->whereHas('roles', fn ($q) => $q
            ->where('codigo', '04')->orWhereRaw('UPPER(nombre) = ?', ['DOCENTE']));
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
