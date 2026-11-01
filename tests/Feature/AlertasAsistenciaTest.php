<?php

namespace Tests\Feature;

use App\Models\AlertaAsistencia;
use App\Models\AsignacionDocente;
use App\Models\AsistenciaEstudiante;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use App\Notifications\AlertaAsistenciaRepresentante;
use App\Services\Docente\AlertasAsistencia;
use App\Services\Docente\BoletaAlertaAsistencia;
use Carbon\CarbonImmutable;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Notification;
use Tests\TestCase;

class AlertasAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(?string $correoRepresentante = 'rep@example.test'): array
    {
        $institucion = Institucion::create(['nombre' => 'Colegio']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $carga = AsignacionDocente::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'curso_id' => $curso->id, 'paralelo_id' => $paralelo->id, 'tipo' => 'carga', 'titulo' => 'Matemática', 'horas_semanales' => 2]);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $representante = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'rep', 'email' => $correoRepresentante, 'password_hash' => 'test']);
        $persona = Persona::create(['primer_nombre' => 'Ana', 'primer_apellido' => 'Paz']);
        $alumno = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $institucion->id, 'username' => 'ana', 'password_hash' => 'test', 'representante_id' => $representante->id]);
        $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
        $matricula = Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);

        return compact('periodo', 'docente', 'carga', 'representante', 'matricula', 'paralelo', 'institucion');
    }

    /** Crea clases directamente en la base, sin pasar por la evaluación del registro. */
    private function historial(array $datos, int $presentes, int $faltas = 0): void
    {
        $fecha = CarbonImmutable::parse('2026-09-01');
        foreach (array_merge(array_fill(0, $presentes, 'P'), array_fill(0, $faltas, 'F')) as $indice => $estado) {
            $sesion = SesionAsistencia::create(['asignacion_docente_id' => $datos['carga']->id, 'fecha' => $fecha->addDays($indice)->toDateString(), 'horas' => 1]);
            AsistenciaEstudiante::create(['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $datos['matricula']->id, 'estado' => $estado]);
        }
    }

    private function guardar(array $datos, string $fecha, string $estado)
    {
        return $this->put(route('docente.asistencia.update'), ['periodo' => $datos['periodo']->id, 'carga' => $datos['carga']->id, 'fecha' => $fecha, 'horas' => 1,
            'estudiantes' => [$datos['matricula']->id => $estado]])->assertSessionHasNoErrors();
    }

    public function test_notifica_al_representante_una_vez_al_bajar_del_noventa_por_ciento(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 9);
        $this->guardar($datos, '2026-12-01', 'P');
        Notification::assertNothingSent();
        $this->assertSame(0, AlertaAsistencia::count());

        $this->guardar($datos, '2026-12-02', 'F');
        $this->assertSame(0, AlertaAsistencia::count(), '10 de 11 = 90,91 % no genera alerta');
        $this->guardar($datos, '2026-12-03', 'F');
        $alerta = AlertaAsistencia::sole();
        $this->assertSame([83.33, 12, $datos['representante']->id], [$alerta->porcentaje, $alerta->registros, $alerta->representante_id]);
        $this->assertNotNull($alerta->notificada_at);
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, 1);

        $this->guardar($datos, '2026-12-04', 'F');
        $this->assertSame(76.92, AlertaAsistencia::sole()->porcentaje);
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, 1);
    }

    public function test_exactamente_noventa_por_ciento_no_alerta_y_exige_registros_minimos(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 9, 1);
        $this->assertSame(['evaluadas' => 1, 'en_riesgo' => 0], collect(app(AlertasAsistencia::class)->evaluar([$datos['matricula']->id]))->only(['evaluadas', 'en_riesgo'])->all());
        SesionAsistencia::query()->each(fn ($s) => $s->asistencias()->delete());
        SesionAsistencia::query()->delete();
        $this->historial($datos, 0, 9);
        $this->assertSame(0, app(AlertasAsistencia::class)->evaluar([$datos['matricula']->id])['en_riesgo']);
        Notification::assertNothingSent();
    }

    public function test_al_recuperarse_resuelve_y_una_nueva_caida_notifica_otra_vez(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        $this->assertNull(AlertaAsistencia::sole()->resuelta_at);
        foreach (range(2, 29) as $dia) {
            $this->guardar($datos, CarbonImmutable::parse('2026-12-01')->addDays($dia - 1)->toDateString(), 'P');
        }
        $this->assertNotNull(AlertaAsistencia::sole()->resuelta_at);
        foreach (['2027-01-04', '2027-01-05', '2027-01-06', '2027-01-07'] as $fecha) {
            $this->guardar($datos, $fecha, 'F');
        }
        $this->assertSame(2, AlertaAsistencia::count());
        $this->assertSame(1, AlertaAsistencia::whereNull('resuelta_at')->count());
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, 2);
    }

    public function test_sin_correo_queda_pendiente_y_notifica_cuando_se_registra(): void
    {
        Notification::fake();
        $datos = $this->escenario(null);
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        $this->assertNull(AlertaAsistencia::sole()->notificada_at);
        Notification::assertNothingSent();
        $this->get(route('docente.informes-asistencia.index', ['tab' => 'alertas']))->assertOk()->assertSee('Paz Ana')->assertSee('Sin correo del representante');

        $datos['representante']->update(['email' => 'rep@example.test']);
        $this->guardar($datos, '2026-12-02', 'F');
        $this->assertNotNull(AlertaAsistencia::sole()->notificada_at);
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, 1);
    }

    public function test_correo_incluye_estudiante_y_porcentaje(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, function ($notificacion) use ($datos) {
            $correo = $notificacion->toMail($datos['representante']);

            return $correo->subject === 'Alerta de asistencia: Ana Paz' && str_contains($correo->introLines[0], '66,67 %') && str_contains($correo->introLines[0], '90 %');
        });
    }

    public function test_informes_muestran_alertas_y_registro_las_cuenta(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        $filas = $this->get(route('docente.informes-asistencia.index', ['tab' => 'alertas']))->assertOk()
            ->assertSee('Alertas de asistencia')->assertSee('66,67')->assertSee('Notificado')->viewData('filas');
        $this->assertSame([12, 4], [$filas->items()[0]['registros'], $filas->items()[0]['estados']['F']]);
        $this->get(route('docente.informes-asistencia.index'))->assertViewHas('alertas', 1)->assertSee('Menos de 90 %');
        $this->get(route('docente.asistencia.registro', ['carga' => $datos['carga']->id]))->assertOk()->assertSee('Menos de 90 %')
            ->assertViewHas('resumen', fn ($r) => $r['alertas'] === 1);
    }

    public function test_representante_solo_ve_sus_alertas(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        $this->withSession(['auth_user' => ['id' => $datos['representante']->id, 'role' => 'padre-tutor']]);
        $this->get(route('padre-tutor.alertas-asistencia.index'))->assertOk()->assertSee('Ana Paz')->assertSee('66,67 %')->assertSee('Activa')->assertSee('Alertas de asistencia');
        $otro = Usuario::create(['institucion_id' => $datos['institucion']->id, 'username' => 'otro.rep', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'padre-tutor']]);
        $this->get(route('padre-tutor.alertas-asistencia.index'))->assertOk()->assertDontSee('Ana Paz')->assertSee('No hay alertas de asistencia.');
        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $this->get(route('padre-tutor.alertas-asistencia.index'))->assertRedirect();
    }

    public function test_docente_genera_boleta_solo_de_sus_paralelos_y_mientras_haya_alerta(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $boleta = route('docente.informes-asistencia.boleta', $datos['matricula']->id);
        $this->get(route('docente.informes-asistencia.index', ['tab' => 'alertas']))->assertOk()->assertSee(e($boleta), false);
        $respuesta = $this->get($boleta)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('boleta-asistencia-matricula-'.$datos['matricula']->id.'.pdf', $respuesta->headers->get('content-disposition'));
        $this->assertSame(0, AlertaAsistencia::count(), 'Generar la boleta no registra ni notifica la alerta');
        Notification::assertNothingSent();

        $paraleloB = Paralelo::create(['curso_id' => $datos['carga']->curso_id, 'letra' => 'B']);
        $otro = Usuario::create(['institucion_id' => $datos['institucion']->id, 'username' => 'otro.docente', 'password_hash' => 'test']);
        AsignacionDocente::create($datos['carga']->only(['institucion_id', 'periodo_id', 'curso_id', 'tipo', 'titulo', 'horas_semanales']) + ['docente_id' => $otro->id, 'paralelo_id' => $paraleloB->id]);
        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'docente']]);
        $this->get($boleta)->assertNotFound();

        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $datos['matricula']->update(['estado' => 'RETIRADA']);
        $this->get($boleta)->assertNotFound();
        $datos['matricula']->update(['estado' => 'ACTIVA']);
        AsistenciaEstudiante::where('estado', 'F')->update(['estado' => 'P']);
        $this->get($boleta)->assertNotFound();
    }

    public function test_boleta_detalla_asistencia_por_asignatura_y_resumen(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $lengua = AsignacionDocente::create($datos['carga']->only(['institucion_id', 'periodo_id', 'docente_id', 'curso_id', 'paralelo_id', 'tipo', 'horas_semanales']) + ['titulo' => 'Lengua']);
        $sesion = SesionAsistencia::create(['asignacion_docente_id' => $lengua->id, 'fecha' => '2026-11-02', 'horas' => 1]);
        AsistenciaEstudiante::create(['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $datos['matricula']->id, 'estado' => 'J']);

        $boleta = app(BoletaAlertaAsistencia::class)->datos($datos['matricula']);
        $this->assertSame(['Lengua', 'Matemática'], $boleta['asignaturas']->pluck('nombre')->all());
        $this->assertSame(['P' => 8, 'F' => 3, 'A' => 0, 'J' => 0], $boleta['asignaturas'][1]['estados']);
        $this->assertSame(12, $boleta['asistencia']['registros']);

        $html = view('docente.boleta-alerta-asistencia-pdf', $boleta)->render();
        foreach (['Boleta de notificación de asistencia', 'Ana Paz', '66,67 %', '72,73 %', 'Ord', 'Resumen estadístico', 'Firma de recibido'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }
    }

    public function test_representante_descarga_boleta_solo_de_sus_alertas_activas(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->guardar($datos, '2026-12-01', 'F');
        $alerta = AlertaAsistencia::sole();
        $boleta = route('padre-tutor.alertas-asistencia.boleta', $alerta->id);
        $this->withSession(['auth_user' => ['id' => $datos['representante']->id, 'role' => 'padre-tutor']]);
        $this->get(route('padre-tutor.alertas-asistencia.index'))->assertOk()->assertSee(e($boleta), false);
        $this->get($boleta)->assertOk()->assertHeader('content-type', 'application/pdf');

        $otro = Usuario::create(['institucion_id' => $datos['institucion']->id, 'username' => 'otro.rep', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'padre-tutor']]);
        $this->get($boleta)->assertNotFound();

        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $this->get($boleta)->assertRedirect();

        $this->withSession(['auth_user' => ['id' => $datos['representante']->id, 'role' => 'padre-tutor']]);
        $alerta->update(['resuelta_at' => now()]);
        $this->get(route('padre-tutor.alertas-asistencia.index'))->assertOk()->assertDontSee(e($boleta), false);
        $this->get($boleta)->assertNotFound();
    }

    public function test_comando_simula_sin_escribir_y_aplica_para_clases_existentes(): void
    {
        Notification::fake();
        $datos = $this->escenario();
        $this->historial($datos, 8, 3);
        $this->artisan('asistencias:evaluar-alertas', ['institucion' => $datos['institucion']->id, 'periodo' => $datos['periodo']->id])->assertSuccessful();
        $this->assertSame(0, AlertaAsistencia::count());
        Notification::assertNothingSent();
        $this->artisan('asistencias:evaluar-alertas', ['institucion' => $datos['institucion']->id, 'periodo' => $datos['periodo']->id, '--aplicar' => true])->assertSuccessful();
        $this->artisan('asistencias:evaluar-alertas', ['institucion' => $datos['institucion']->id, 'periodo' => $datos['periodo']->id, '--aplicar' => true])->assertSuccessful();
        $this->assertSame(1, AlertaAsistencia::count());
        Notification::assertSentTo($datos['representante'], AlertaAsistenciaRepresentante::class, 1);
    }
}
