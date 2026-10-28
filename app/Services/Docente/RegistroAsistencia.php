<?php

namespace App\Services\Docente;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaEstudiante;
use App\Models\Matricula;
use App\Models\PeriodoAcademico;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use Carbon\CarbonImmutable;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class RegistroAsistencia
{
    public function contexto(Request $request): array
    {
        $docente = Usuario::findOrFail($request->session()->get('auth_user.id'));
        abort_unless($docente->institucion_id, 403);
        $periodos = PeriodoAcademico::where('institucion_id', $docente->institucion_id)
            ->orderByDesc('es_activo')->orderByDesc('fecha_inicio')->get();
        $periodo = $request->filled('periodo') ? $periodos->firstWhere('id', $request->integer('periodo')) : $periodos->first();
        abort_if($request->filled('periodo') && ! $periodo, 404);
        $cargas = AsignacionDocente::where('docente_id', $docente->id)->where('institucion_id', $docente->institucion_id)
            ->where('periodo_id', $periodo?->id)->where('tipo', 'carga')->where('horas_semanales', '>', 0)
            ->whereHas('curso', fn ($q) => $q->where('institucion_id', $docente->institucion_id))
            ->whereHas('paralelo')->with(['curso.especialidad', 'paralelo', 'asignatura'])->orderBy('curso_id')->orderBy('paralelo_id')->get()
            ->filter(fn ($c) => (int) $c->paralelo->curso_id === (int) $c->curso_id)->values();
        $carga = $request->filled('carga') ? $cargas->firstWhere('id', $request->integer('carga')) : $cargas->first();
        abort_if($request->filled('carga') && ! $carga, 404);
        $referencia = CarbonImmutable::today('America/Guayaquil');
        if ($periodo) {
            $referencia = $referencia->max(CarbonImmutable::parse($periodo->fecha_inicio))->min(CarbonImmutable::parse($periodo->fecha_fin));
        }
        $semana = CarbonImmutable::parse($request->input('semana', $referencia->toDateString()))->startOfWeek();
        if ($periodo && ($semana->greaterThan($periodo->fecha_fin) || $semana->endOfWeek()->lessThan($periodo->fecha_inicio))) {
            throw ValidationException::withMessages(['semana' => 'Seleccione una semana dentro del período académico.']);
        }
        $cantidad = $carga ? (int) ceil((float) $carga->horas_semanales) : 0;
        $matriculas = Matricula::where('institucion_id', $docente->institucion_id)->where('periodo_id', $periodo?->id)
            ->where('paralelo_id', $carga?->paralelo_id)->where('estado', 'ACTIVA')
            ->whereHas('estudiante', fn ($q) => $q->alumnos()->where('institucion_id', $docente->institucion_id))
            ->with('estudiante.persona')->get()
            ->sortBy(fn ($m) => ($m->estudiante->persona?->primer_apellido ?? '').' '.($m->estudiante->persona?->segundo_apellido ?? '').' '.$m->estudiante->nombre_completo, SORT_NATURAL | SORT_FLAG_CASE)->values();
        $sesiones = SesionAsistencia::where('asignacion_docente_id', $carga?->id)->whereDate('semana', $semana)
            ->with('asistencias')->get()->keyBy('numero');

        return compact('periodos', 'periodo', 'cargas', 'carga', 'semana', 'cantidad', 'matriculas', 'sesiones') + ['estados' => AsistenciaEstudiante::ESTADOS];
    }

    public function guardar(array $contexto, array $datos): void
    {
        DB::transaction(function () use ($contexto, $datos) {
            foreach ($datos['sesiones'] as $numero => $entrada) {
                $sesion = SesionAsistencia::firstOrCreate([
                    'asignacion_docente_id' => $contexto['carga']->id,
                    'semana' => $contexto['semana']->toDateString(), 'numero' => $numero,
                ]);
                $sesion = SesionAsistencia::whereKey($sesion->id)->lockForUpdate()->firstOrFail();
                $sesion->update(['fecha' => $entrada['fecha'] ?? null]);
                foreach ($entrada['estudiantes'] ?? [] as $matricula => $estado) {
                    $clave = ['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $matricula];
                    if ($estado) {
                        AsistenciaEstudiante::updateOrCreate($clave, ['estado' => $estado]);
                    } else {
                        AsistenciaEstudiante::where($clave)->delete();
                    }
                }
            }
        });
    }
}
