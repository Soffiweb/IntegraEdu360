<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Agenda;
use App\Models\Institucion;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class AgendaController extends Controller
{
    public function index(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $query = $this->filteredQuery($request, $institucionActiva);

        $metricasQuery = clone $query;
        $this->applyCompliance($query, $request);

        $agendas = $query
            ->orderBy('fecha')
            ->orderBy('hora_desde')
            ->paginate(10)
            ->withQueryString();

        $agendaEditando = null;

        if ($request->filled('edit')) {
            $agendaEditando = Agenda::findOrFail((int) $request->input('edit'));
            abort_unless(! $institucionActiva || (int) $agendaEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        return view('admin.agenda.index', [
            'agendaRole' => $request->routeIs('directivo.agenda.*') ? 'directivo' : 'admin',
            'agendaRoutePrefix' => $request->routeIs('directivo.agenda.*') ? 'directivo.agenda.' : 'admin.agenda.',
            'agendas' => $agendas,
            'agendaEditando' => $agendaEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'cumplimientos' => $this->cumplimientos(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'pendientes' => (clone $metricasQuery)->where('porcentaje_cumplimiento', 0)->count(),
                'en_progreso' => (clone $metricasQuery)->whereBetween('porcentaje_cumplimiento', [1, 99])->count(),
                'cumplidas' => (clone $metricasQuery)->where('porcentaje_cumplimiento', 100)->count(),
            ],
        ]);
    }

    public function print(Request $request)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $query = $this->filteredQuery($request, $institucionActiva);
        $this->applyCompliance($query, $request);
        $institucion = $institucionActiva ?? ($request->filled('institucion') ? Institucion::find($request->input('institucion')) : null);
        $logoPath = $institucion?->logo1 ? storage_path('app/public/'.$institucion->logo1) : null;

        $pdf = Pdf::loadView('admin.agenda.pdf', [
            'agendas' => $query->orderBy('fecha')->orderBy('hora_desde')->get(),
            'institucion' => $institucion,
            'logoPath' => $logoPath && is_file($logoPath) ? $logoPath : null,
            'filtros' => $request->only(['search', 'fecha_desde', 'fecha_hasta', 'cumplimiento']),
            'cumplimientos' => $this->cumplimientos(),
            'generadoEn' => now(),
        ])->setPaper('a4', 'portrait');
        $pdf->render();
        $domPdf = $pdf->getDomPDF();
        $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'normal');
        $domPdf->getCanvas()->page_text(480, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

        return $pdf->stream('agenda-institucional.pdf');
    }

    private function filteredQuery(Request $request, ?Institucion $institucionActiva): Builder
    {
        $dates = $request->validate([
            'fecha_desde' => ['nullable', 'date_format:Y-m-d'],
            'fecha_hasta' => ['nullable', 'date_format:Y-m-d', Rule::when($request->filled('fecha_desde'), 'after_or_equal:fecha_desde')],
        ]);
        $query = Agenda::query()->with('institucion');

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('descripcion', 'like', "%{$search}%")
                    ->orWhere('responsable', 'like', "%{$search}%");
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if (! empty($dates['fecha_desde'])) {
            $query->whereDate('fecha', '>=', $dates['fecha_desde']);
        }
        if (! empty($dates['fecha_hasta'])) {
            $query->whereDate('fecha', '<=', $dates['fecha_hasta']);
        }

        return $query;
    }

    private function applyCompliance(Builder $query, Request $request): void
    {
        match ($request->string('cumplimiento')->toString()) {
            'pendiente' => $query->where('porcentaje_cumplimiento', 0),
            'en-progreso' => $query->whereBetween('porcentaje_cumplimiento', [1, 99]),
            'cumplida' => $query->where('porcentaje_cumplimiento', 100),
            default => null,
        };

    }

    public function store(Request $request)
    {
        Agenda::create($this->validatedData($request, $this->activeInstitucion($request)));

        return redirect()
            ->route(request()->routeIs('directivo.agenda.*') ? 'directivo.agenda.index' : 'admin.agenda.index')
            ->with('status', 'Actividad agendada correctamente.');
    }

    public function update(Request $request, Agenda $agenda)
    {
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $agenda->institucion_id === (int) $institucionActiva->id, 404);

        $agenda->update($this->validatedData($request, $institucionActiva));

        return redirect()
            ->route(request()->routeIs('directivo.agenda.*') ? 'directivo.agenda.index' : 'admin.agenda.index')
            ->with('status', 'Actividad actualizada correctamente.');
    }

    public function destroy(Agenda $agenda)
    {
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $agenda->institucion_id === (int) $institucionActiva->id, 404);

        $agenda->delete();

        return redirect()
            ->route(request()->routeIs('directivo.agenda.*') ? 'directivo.agenda.index' : 'admin.agenda.index')
            ->with('status', 'Actividad eliminada correctamente.');
    }

    private function validatedData(Request $request, ?Institucion $institucionActiva = null): array
    {
        if ($institucionActiva) {
            $request->merge(['institucion_id' => $institucionActiva->id]);
        }

        return $request->validate([
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
            'fecha' => ['required', 'date'],
            'hora_desde' => ['required', 'date_format:H:i'],
            'hora_hasta' => ['required', 'date_format:H:i', 'after:hora_desde'],
            'descripcion' => ['required', 'string', 'max:2000'],
            'responsable' => ['required', 'string', 'max:255'],
            'porcentaje_cumplimiento' => ['required', 'integer', 'between:0,100'],
        ]);
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
            $institucion = Institucion::find($institucionId);
            abort_if(($sessionUser['role'] ?? null) === 'directivo' && ! $institucion, 403, 'La cuenta debe tener una institución asignada.');

            return $institucion;
        }

        $usuarioId = $sessionUser['id'] ?? null;

        if (! $usuarioId) {
            abort_if(($sessionUser['role'] ?? null) === 'directivo', 403, 'La cuenta debe tener una institución asignada.');

            return null;
        }

        $usuario = Usuario::find($usuarioId);

        $institucion = $usuario?->institucion_id ? Institucion::find($usuario->institucion_id) : null;
        abort_if(($sessionUser['role'] ?? null) === 'directivo' && ! $institucion, 403, 'La cuenta debe tener una institución asignada.');

        return $institucion;
    }

    private function cumplimientos(): array
    {
        return [
            'pendiente' => 'Pendiente (0%)',
            'en-progreso' => 'En progreso',
            'cumplida' => 'Cumplida (100%)',
        ];
    }
}
