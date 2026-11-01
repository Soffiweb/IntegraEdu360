<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\AsistenciaEstudiante;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use App\Services\Docente\GenerarAsistencias;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class GenerarAsistenciasTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $institucion = Institucion::create(['nombre' => 'Colegio']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-25', 'es_activo' => true]);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        foreach (range(1, 6) as $numero) {
            $alumno = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'alumno.'.$numero, 'password_hash' => 'test']);
            $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
            Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        }
        $cargas = collect([1, 3, 6, 25])->mapWithKeys(fn ($horas) => [$horas => AsignacionDocente::create([
            'institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'curso_id' => $curso->id,
            'paralelo_id' => $paralelo->id, 'tipo' => 'carga', 'titulo' => "Carga $horas", 'horas_semanales' => $horas,
        ])]);

        return compact('institucion', 'periodo', 'docente', 'cargas');
    }

    private function generar(array $datos, bool $aplicar = true): array
    {
        return app(GenerarAsistencias::class)->ejecutar($datos['institucion']->id, $datos['periodo']->id, $aplicar);
    }

    public function test_simulacion_no_guarda_y_anticipa_lo_que_aplicar_crea(): void
    {
        $datos = $this->escenario();
        $simulado = $this->generar($datos, false);
        $this->assertSame([0, 0], [SesionAsistencia::count(), AsistenciaEstudiante::count()]);
        $aplicado = $this->generar($datos);
        $this->assertSame($simulado['clases_nuevas'], $aplicado['clases_nuevas']);
        $this->assertSame($simulado['clases_nuevas'], SesionAsistencia::count());
        $this->assertSame($simulado['clases_nuevas'] * 6, AsistenciaEstudiante::count());
    }

    public function test_respeta_carga_semanal_en_semanas_completas_y_excluye_dias_sin_clase(): void
    {
        $datos = $this->escenario();
        $this->generar($datos);
        $sesiones = SesionAsistencia::all();
        $fechas = $sesiones->map(fn ($s) => $s->fecha->toDateString());
        $this->assertSame('2026-09-01', $fechas->min());
        $this->assertLessThan(GenerarAsistencias::INICIO_VACACIONES, $fechas->max());
        $this->assertEmpty($fechas->intersect(GenerarAsistencias::FERIADOS));
        $this->assertFalse($sesiones->contains(fn ($s) => $s->fecha->isWeekend()));
        foreach ($datos['cargas'] as $horas => $carga) {
            $porSemana = $sesiones->where('asignacion_docente_id', $carga->id)
                ->groupBy(fn ($s) => CarbonImmutable::parse($s->fecha)->startOfWeek()->toDateString())->map->sum('horas');
            $this->assertSame($horas, $porSemana['2026-09-14'], "Carga de $horas");
            $this->assertLessThanOrEqual($horas, $porSemana->max());
        }
    }

    public function test_conserva_clases_registradas_descuenta_sus_horas_y_es_idempotente(): void
    {
        $datos = $this->escenario();
        $carga = $datos['cargas'][3];
        $previa = SesionAsistencia::create(['asignacion_docente_id' => $carga->id, 'fecha' => '2026-09-16', 'horas' => 2, 'tema' => 'Registrada por el docente']);
        $this->generar($datos);
        $this->assertSame('Registrada por el docente', $previa->fresh()->tema);
        $this->assertSame(0, AsistenciaEstudiante::where('sesion_asistencia_id', $previa->id)->count());
        $semana = SesionAsistencia::where('asignacion_docente_id', $carga->id)->whereBetween('fecha', ['2026-09-14', '2026-09-18'])->sum('horas');
        $this->assertSame(3, (int) $semana);
        $total = SesionAsistencia::count();
        $this->assertSame(0, $this->generar($datos)['clases_nuevas']);
        $this->assertSame($total, SesionAsistencia::count());
    }

    public function test_falta_de_dia_completo_afecta_todas_las_clases_de_esa_fecha(): void
    {
        $datos = $this->escenario();
        $this->generar($datos);
        $this->assertSame(['A', 'F', 'J', 'P'], AsistenciaEstudiante::distinct()->orderBy('estado')->pluck('estado')->all());
        $porFecha = AsistenciaEstudiante::join('sesiones_asistencia as s', 's.id', '=', 'asistencias_estudiantes.sesion_asistencia_id')
            ->where('estado', 'J')->get(['matricula_id', 's.fecha', 'estado']);
        foreach ($porFecha->groupBy(fn ($r) => $r->matricula_id.'|'.$r->fecha) as $clave => $grupo) {
            [, $fecha] = explode('|', $clave);
            $this->assertSame(SesionAsistencia::whereDate('fecha', $fecha)->count(), $grupo->count());
        }
    }

    public function test_docente_puede_abrir_y_guardar_una_clase_generada_de_cinco_horas(): void
    {
        $datos = $this->escenario();
        $this->generar($datos);
        $carga = $datos['cargas'][25];
        $sesion = SesionAsistencia::where('asignacion_docente_id', $carga->id)->orderBy('fecha')->first();
        $this->assertSame(5, $sesion->horas);
        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $this->get(route('docente.asistencia.registro', ['periodo' => $datos['periodo']->id, 'carga' => $carga->id, 'fecha' => $sesion->fecha->toDateString()]))
            ->assertOk()->assertSee('<option value="5" selected>5 horas</option>', false)->assertDontSee('6 horas');
        $estudiantes = Matricula::pluck('id')->mapWithKeys(fn ($id) => [$id => 'P'])->all();
        $payload = ['periodo' => $datos['periodo']->id, 'carga' => $carga->id, 'fecha' => $sesion->fecha->toDateString(), 'estudiantes' => $estudiantes];
        $this->put(route('docente.asistencia.update'), $payload + ['horas' => 5])->assertSessionHasNoErrors();
        $this->put(route('docente.asistencia.update'), $payload + ['horas' => 6])->assertSessionHasErrors('horas');
    }
}
