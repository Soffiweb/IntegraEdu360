<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AsignaturaController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $query = Asignatura::query()->with(['institucion', 'especialidad', 'cursos']);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('nombre', 'like', "%{$search}%")
                    ->orWhere('codigo', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($q) => $q->where('nombre', 'like', "%{$search}%"));
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($area = $request->string('area')->toString()) {
            $query->where('area', strtoupper($area));
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        if ($especialidadId = $request->string('especialidad_id')->toString()) {
            $query->where('especialidad_id', $especialidadId === 'ninguna' ? null : $especialidadId);
            if ($especialidadId === 'ninguna') {
                $query->whereNull('especialidad_id');
            }
        }

        if ($cursoId = $request->string('curso_id')->toString()) {
            $query->whereHas('cursos', fn ($q) => $q->where('cursos.id', $cursoId));
        }

        $asignaturasList = $query
            ->orderBy('nombre')
            ->get();

        // Build: especialidad → curso → [asignaturas]
        $agrupado = [];

        foreach ($asignaturasList as $asignatura) {
            $espNombre = $asignatura->especialidad?->nombre ?? 'Sin especialidad';
            $cursosDeEsta = $asignatura->cursos->sortBy('grado');

            if ($cursosDeEsta->isEmpty()) {
                $agrupado[$espNombre][0] ??= ['curso' => null, 'items' => []];
                $agrupado[$espNombre][0]['items'][] = $asignatura;
            } else {
                foreach ($cursosDeEsta as $curso) {
                    $agrupado[$espNombre][$curso->id] ??= ['curso' => $curso, 'items' => []];
                    $agrupado[$espNombre][$curso->id]['items'][] = $asignatura;
                }
            }
        }

        foreach ($agrupado as &$espGroup) {
            uasort($espGroup, fn ($a, $b) => ($a['curso']?->grado ?? PHP_INT_MAX) <=> ($b['curso']?->grado ?? PHP_INT_MAX));
        }
        unset($espGroup);

        if (isset($agrupado['Sin especialidad'])) {
            $sinEsp = $agrupado['Sin especialidad'];
            unset($agrupado['Sin especialidad']);
            $agrupado['Sin especialidad'] = $sinEsp;
        }

        $asignaturaEditando = null;

        if ($request->filled('edit')) {
            $asignaturaEditando = Asignatura::with('cursos')
                ->findOrFail((int) $request->input('edit'));
            abort_unless(! $institucionActiva || (int) $asignaturaEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        $metricasQuery = Asignatura::query();
        if ($institucionActiva) {
            $metricasQuery->where('institucion_id', $institucionActiva->id);
        }

        $instId = $institucionActiva?->id;
        $especialidades = Especialidad::query()
            ->when($instId, fn ($q) => $q->where('institucion_id', $instId))
            ->orderBy('nombre')
            ->get(['id', 'nombre', 'codigo']);

        $cursos = Curso::query()
            ->when($instId, fn ($q) => $q->where('institucion_id', $instId))
            ->orderBy('grado')
            ->get(['id', 'nombre', 'nivel', 'grado']);

        return view('admin.asignaturas.index', [
            'asignaturasAgrupadas' => $agrupado,
            'totalAsignaturas'     => $asignaturasList->count(),
            'asignaturaEditando'   => $asignaturaEditando,
            'instituciones'      => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva'  => $institucionActiva,
            'especialidades'     => $especialidades,
            'cursos'             => $cursos,
            'areas'              => $this->areas(),
            'estados'            => $this->estados(),
            'niveles'            => $this->niveles(),
            'metricas'           => [
                'total'      => (clone $metricasQuery)->count(),
                'activas'    => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'inactivas'  => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
                'areas'      => (clone $metricasQuery)->distinct('area')->count('area'),
            ],
        ]);
    }

    public function pdf(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);

        $asignaturasList = Asignatura::query()
            ->with(['especialidad', 'cursos'])
            ->when($institucionActiva, fn ($q) => $q->where('institucion_id', $institucionActiva->id))
            ->orderBy('nombre')
            ->get();

        $agrupado = [];

        foreach ($asignaturasList as $asignatura) {
            $espNombre = $asignatura->especialidad?->nombre ?? 'Sin especialidad';
            $cursosDeEsta = $asignatura->cursos->sortBy('grado');

            if ($cursosDeEsta->isEmpty()) {
                $agrupado[$espNombre][0] ??= ['curso' => null, 'items' => []];
                $agrupado[$espNombre][0]['items'][] = $asignatura;
            } else {
                foreach ($cursosDeEsta as $curso) {
                    $agrupado[$espNombre][$curso->id] ??= ['curso' => $curso, 'items' => []];
                    $agrupado[$espNombre][$curso->id]['items'][] = $asignatura;
                }
            }
        }

        foreach ($agrupado as &$espGroup) {
            uasort($espGroup, fn ($a, $b) => ($a['curso']?->grado ?? PHP_INT_MAX) <=> ($b['curso']?->grado ?? PHP_INT_MAX));
        }
        unset($espGroup);

        if (isset($agrupado['Sin especialidad'])) {
            $sinEsp = $agrupado['Sin especialidad'];
            unset($agrupado['Sin especialidad']);
            $agrupado['Sin especialidad'] = $sinEsp;
        }

        $periodoActivo = $institucionActiva
            ? PeriodoAcademico::where('institucion_id', $institucionActiva->id)->where('es_activo', true)->first()
            : null;

        $anioLectivo    = $periodoActivo?->nombre ?? date('Y');
        $nombreInst     = $institucionActiva?->nombre ?? 'General';
        $sanitize       = fn ($s) => preg_replace('/[^A-Za-z0-9\-]/', '_', $s);
        $filename       = 'Materias_' . $sanitize($anioLectivo) . '_' . $sanitize($nombreInst) . '.pdf';

        $pdf = Pdf::loadView('admin.asignaturas.pdf', [
            'asignaturasAgrupadas' => $agrupado,
            'totalAsignaturas'     => $asignaturasList->count(),
            'institucion'          => $institucionActiva,
            'anioLectivo'          => $anioLectivo,
            'areas'                => $this->areas(),
            'estados'              => $this->estados(),
            'generadoEn'           => now()->format('d/m/Y H:i'),
        ])->setPaper('letter', 'portrait');

        $domPdf = $pdf->getDomPDF();
        $domPdf->render();

        $canvas = $domPdf->getCanvas();
        $fontMetrics = $domPdf->getFontMetrics();
        $font = $fontMetrics->getFont('DejaVu Sans', 'normal');
        $fontSize = 7.5;
        $pageText = 'Pagina 999 de 999';
        $textWidth = $fontMetrics->getTextWidth($pageText, $font, $fontSize);
        $x = $canvas->get_width() - 42 - $textWidth;
        $y = $canvas->get_height() - 32;
        $color = [0.392, 0.455, 0.545];

        $canvas->page_text($x, $y, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, $fontSize, $color);

        return $pdf->download($filename);
    }

    public function store(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        ['data' => $data, 'curso_ids' => $cursoIds] = $this->validatedData($request, null, $institucionActiva);

        $asignatura = Asignatura::create($data);
        $asignatura->cursos()->sync($cursoIds);

        return redirect()
            ->route('admin.asignaturas.index')
            ->with('status', 'Asignatura creada correctamente.');
    }

    public function update(Request $request, Asignatura $asignatura)
    {
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $asignatura->institucion_id === (int) $institucionActiva->id, 404);

        ['data' => $data, 'curso_ids' => $cursoIds] = $this->validatedData($request, $asignatura, $institucionActiva);

        $asignatura->update($data);
        $asignatura->cursos()->sync($cursoIds);

        return redirect()
            ->route('admin.asignaturas.index')
            ->with('status', 'Asignatura actualizada correctamente.');
    }

    public function destroy(Asignatura $asignatura)
    {
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $asignatura->institucion_id === (int) $institucionActiva->id, 404);

        $asignatura->delete();

        return redirect()
            ->route('admin.asignaturas.index')
            ->with('status', 'Asignatura eliminada correctamente.');
    }

    private function validatedData(Request $request, ?Asignatura $asignatura = null, ?Institucion $institucionActiva = null): array
    {
        $validated = $request->validate([
            'institucion_id'  => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'especialidad_id' => ['nullable', 'integer', Rule::exists('especialidades', 'id')],
            'nombre'          => ['required', 'string', 'max:150'],
            'codigo'          => ['nullable', 'string', 'max:20'],
            'area'            => ['required', Rule::in(array_keys($this->areas()))],
            'horas_semana'    => ['nullable', 'integer', 'min:1', 'max:40'],
            'descripcion'     => ['nullable', 'string', 'max:500'],
            'estado'          => ['required', Rule::in(array_keys($this->estados()))],
            'curso_ids'       => ['nullable', 'array'],
            'curso_ids.*'     => ['integer', Rule::exists('cursos', 'id')],
        ]);

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado']          = strtoupper(trim($validated['estado']));
        $validated['area']            = strtoupper(trim($validated['area']));
        $validated['codigo']          = isset($validated['codigo']) ? strtoupper(trim($validated['codigo'])) ?: null : null;
        $validated['especialidad_id'] = $validated['especialidad_id'] ?? null;

        $cursoIds = $validated['curso_ids'] ?? [];
        unset($validated['curso_ids']);

        return ['data' => $validated, 'curso_ids' => $cursoIds];
    }

    private function availableInstituciones()
    {
        return Institucion::query()->orderBy('nombre')->get(['id', 'nombre']);
    }

    private function activeInstitucion(Request $request): ?Institucion
    {
        $sessionUser   = $request->session()->get('auth_user', []);
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

    private function areas(): array
    {
        return [
            'MATEMATICA'          => 'Matemática',
            'LENGUAJE'            => 'Lenguaje y Literatura',
            'CIENCIAS_NATURALES'  => 'Ciencias Naturales',
            'CIENCIAS_SOCIALES'   => 'Ciencias Sociales',
            'IDIOMAS'             => 'Idiomas',
            'EDUCACION_ARTISTICA' => 'Educación Artística',
            'EDUCACION_FISICA'    => 'Educación Física',
            'TECNICA_TECNOLOGICA' => 'Técnica y Tecnológica',
            'OTRAS'               => 'Otras',
        ];
    }

    private function estados(): array
    {
        return [
            'ACTIVO'   => 'Activo',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function niveles(): array
    {
        return [
            'BASICA_ELEMENTAL' => 'Básica Elemental',
            'BASICA_MEDIA'     => 'Básica Media',
            'BASICA_SUPERIOR'  => 'Básica Superior',
            'BACHILLERATO'     => 'Bachillerato',
        ];
    }
}
