<?php

namespace App\Http\Controllers\Inspector;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Collection;
use Illuminate\Support\Str;

class NovedadController extends Controller
{
    private const TIPOS = [
        'Disciplinaria',
        'Convivencia escolar',
        'Presentacion personal',
        'Asistencia y puntualidad',
        'Derivacion DECE',
    ];

    private const GRAVEDADES = ['Leve', 'Moderada', 'Grave'];

    private const ESTADOS = ['Abierto', 'En seguimiento', 'Derivado a DECE', 'Cerrado'];

    public function index(Request $request)
    {
        $novedades = $this->novedadesEjemplo();

        $stats = [
            'total' => $novedades->count(),
            'abiertas' => $novedades->whereIn('estado', ['Abierto', 'En seguimiento'])->count(),
            'derivadas_dece' => $novedades->where('estado', 'Derivado a DECE')->count(),
            'cerradas' => $novedades->where('estado', 'Cerrado')->count(),
        ];

        $search = trim((string) $request->string('search'));
        $tipo = (string) $request->string('tipo');
        $gravedad = (string) $request->string('gravedad');
        $estado = (string) $request->string('estado');

        $filtradas = $novedades
            ->when($search !== '', fn (Collection $c) => $c->filter(
                fn (array $n) => Str::contains(Str::lower($n['estudiante']), Str::lower($search))
                    || Str::contains(Str::lower($n['curso']), Str::lower($search))
            ))
            ->when($tipo !== '', fn (Collection $c) => $c->where('tipo', $tipo))
            ->when($gravedad !== '', fn (Collection $c) => $c->where('gravedad', $gravedad))
            ->when($estado !== '', fn (Collection $c) => $c->where('estado', $estado))
            ->values();

        return view('inspector.novedades.index', [
            'novedades' => $filtradas,
            'stats' => $stats,
            'tipos' => self::TIPOS,
            'gravedades' => self::GRAVEDADES,
            'estados' => self::ESTADOS,
            'filtros' => compact('search', 'tipo', 'gravedad', 'estado'),
        ]);
    }

    private function novedadesEjemplo(): Collection
    {
        return collect([
            ['estudiante' => 'Ana Cedeño', 'curso' => '9no B', 'tipo' => 'Convivencia escolar', 'gravedad' => 'Moderada', 'fecha' => '2026-07-03', 'estado' => 'En seguimiento', 'responsable' => 'Insp. Ramirez', 'accion' => 'Reunion con representante programada.'],
            ['estudiante' => 'Marco Paredes', 'curso' => '8vo C', 'tipo' => 'Asistencia y puntualidad', 'gravedad' => 'Leve', 'fecha' => '2026-07-03', 'estado' => 'Abierto', 'responsable' => 'Insp. Ramirez', 'accion' => 'Registro de atraso, pendiente justificacion.'],
            ['estudiante' => 'Julia Mera', 'curso' => '10mo A', 'tipo' => 'Disciplinaria', 'gravedad' => 'Grave', 'fecha' => '2026-07-02', 'estado' => 'Derivado a DECE', 'responsable' => 'Insp. Salazar', 'accion' => 'Incidente derivado a Consejeria Estudiantil.'],
            ['estudiante' => 'Carlos Velez', 'curso' => '1ro BGU', 'tipo' => 'Presentacion personal', 'gravedad' => 'Leve', 'fecha' => '2026-07-02', 'estado' => 'Cerrado', 'responsable' => 'Insp. Salazar', 'accion' => 'Llamado de atencion verbal, sin reincidencia.'],
            ['estudiante' => 'Estefania Loor', 'curso' => '9no B', 'tipo' => 'Convivencia escolar', 'gravedad' => 'Moderada', 'fecha' => '2026-07-01', 'estado' => 'En seguimiento', 'responsable' => 'Insp. Ramirez', 'accion' => 'Acta de compromiso firmada con representante.'],
            ['estudiante' => 'Diego Zambrano', 'curso' => '2do BGU', 'tipo' => 'Disciplinaria', 'gravedad' => 'Moderada', 'fecha' => '2026-06-30', 'estado' => 'Abierto', 'responsable' => 'Insp. Salazar', 'accion' => 'Pendiente entrevista con el estudiante.'],
            ['estudiante' => 'Ruth Suarez', 'curso' => '8vo C', 'tipo' => 'Asistencia y puntualidad', 'gravedad' => 'Leve', 'fecha' => '2026-06-29', 'estado' => 'Cerrado', 'responsable' => 'Insp. Ramirez', 'accion' => 'Falta justificada por representante.'],
            ['estudiante' => 'Pablo Leon', 'curso' => '10mo A', 'tipo' => 'Derivacion DECE', 'gravedad' => 'Grave', 'fecha' => '2026-06-28', 'estado' => 'Derivado a DECE', 'responsable' => 'Insp. Salazar', 'accion' => 'Seguimiento conjunto con DECE en curso.'],
        ]);
    }
}
