<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\AsistenciaEstudiante;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use App\Services\Docente\RegistroAsistencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Tests\TestCase;

class DocenteAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $institucion = Institucion::create(['nombre' => 'Colegio']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $otro = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'otro', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $a = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $b = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'B']);
        $carga = AsignacionDocente::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'curso_id' => $curso->id, 'paralelo_id' => $a->id, 'tipo' => 'carga', 'titulo' => 'Matemática', 'horas_semanales' => 2]);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $matriculas = [];
        foreach ([$a, $b] as $paralelo) {
            $alumno = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'alumno.'.$paralelo->letra, 'password_hash' => 'test']);
            $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
            $matriculas[] = Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        }
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);
        $payload = ['periodo' => $periodo->id, 'carga' => $carga->id, 'fecha' => '2026-10-19', 'horas' => 2, 'tema' => 'Fracciones equivalentes',
            'estudiantes' => [$matriculas[0]->id => 'P'], 'observaciones' => [$matriculas[0]->id => 'Participa activamente']];

        return compact('carga', 'docente', 'otro', 'matriculas', 'payload');
    }

    public function test_muestra_carga_guarda_corrige_y_separa_clases(): void
    {
        $datos = $this->escenario();
        $payload = $datos['payload'];
        $matricula = $datos['matriculas'][0]->id;
        $consulta = fn () => $this->get(route('docente.asistencia.registro', collect($payload)->only(['periodo', 'carga', 'fecha'])->all()));
        $consulta()->assertOk()->assertSee('Registro de asistencia')->assertSee('Período de clase')->assertSee('Tema a tratar')
            ->assertSee('Observaciones')->assertSee('2 horas')->assertDontSee('3 horas')->assertSee('alumno.A')->assertDontSee('alumno.B');
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors()
            ->assertRedirect(route('docente.asistencia.index', ['periodo' => $payload['periodo'], 'carga' => $payload['carga']]))
            ->assertSessionHas('success', 'Se grabó exitosamente la clase de Matemática · Octavo A del 19/10/2026.');
        $this->get(route('docente.asistencia.index'))->assertOk()->assertSee('Clases registradas · 1')->assertSee('Fracciones equivalentes')
            ->assertSee(e(route('docente.asistencia.registro', collect($payload)->only(['periodo', 'carga', 'fecha'])->all())), false)->assertSee('Se grabó exitosamente la clase de Matemática · Octavo A del 19/10/2026.');
        $sesion = SesionAsistencia::sole();
        $this->assertSame([2, 'Fracciones equivalentes'], [$sesion->horas, $sesion->tema]);
        $this->assertSame(['P', 'Participa activamente'], [AsistenciaEstudiante::sole()->estado, AsistenciaEstudiante::sole()->observacion]);
        $consulta()->assertViewHas('resumen', ['clases' => 1, 'alumnos' => 1, 'promedio' => 100.0, 'alertas' => 0])
            ->assertSee('value="P" checked', false)->assertSee('Participa activamente');
        $payload['estudiantes'][$matricula] = 'J';
        $payload['observaciones'][$matricula] = '';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(['J', null], [AsistenciaEstudiante::sole()->estado, AsistenciaEstudiante::sole()->observacion]);
        $payload['estudiantes'][$matricula] = '';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(0, AsistenciaEstudiante::count());
        $payload['fecha'] = '2026-10-21';
        $payload['horas'] = 1;
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(2, SesionAsistencia::count());
    }

    public function test_lista_clases_registradas_y_filtra_por_asignatura_curso_y_paralelo(): void
    {
        $datos = $this->escenario();
        $carga = $datos['carga'];
        $matematica = Asignatura::create(['institucion_id' => $carga->institucion_id, 'nombre' => 'Matemática']);
        $carga->update(['asignatura_id' => $matematica->id]);
        $paraleloB = Paralelo::where('curso_id', $carga->curso_id)->where('letra', 'B')->sole();
        $otra = AsignacionDocente::create($carga->only(['institucion_id', 'periodo_id', 'docente_id', 'curso_id', 'tipo']) + [
            'paralelo_id' => $paraleloB->id, 'asignatura_id' => $matematica->id, 'titulo' => 'Matemática B', 'horas_semanales' => 2,
        ]);
        SesionAsistencia::create(['asignacion_docente_id' => $carga->id, 'fecha' => '2026-10-19', 'horas' => 1, 'tema' => 'Fracciones']);
        SesionAsistencia::create(['asignacion_docente_id' => $otra->id, 'fecha' => '2026-10-20', 'horas' => 1, 'tema' => 'Cinemática']);
        $this->get(route('docente.asistencia.index'))->assertOk()->assertSee('Clases registradas · 2')->assertSee('Fracciones')->assertSee('Cinemática')
            ->assertSee('Asignatura · curso · paralelo')->assertSee('Matemática · Octavo A')->assertSee('Matemática · Octavo B');
        $this->get(route('docente.asistencia.index', ['carga' => $otra->id]))->assertOk()
            ->assertSee('Clases registradas · 1 · Matemática · Octavo B')->assertSee('Cinemática')->assertDontSee('Fracciones')
            ->assertSee(e(route('docente.asistencia.registro', ['periodo' => $carga->periodo_id, 'carga' => $otra->id])), false);
        $ajena = AsignacionDocente::create($carga->only(['institucion_id', 'periodo_id', 'curso_id', 'paralelo_id', 'tipo']) + [
            'docente_id' => $datos['otro']->id, 'titulo' => 'Ajena', 'horas_semanales' => 2,
        ]);
        $this->get(route('docente.asistencia.index', ['carga' => $ajena->id]))->assertNotFound();
    }

    public function test_reportes_agrupan_clases_por_curso_y_por_semana(): void
    {
        $datos = $this->escenario();
        $carga = $datos['carga'];
        $matematica = Asignatura::create(['institucion_id' => $carga->institucion_id, 'nombre' => 'Matemática']);
        $carga->update(['asignatura_id' => $matematica->id]);
        $paraleloB = Paralelo::where('curso_id', $carga->curso_id)->where('letra', 'B')->sole();
        $otra = AsignacionDocente::create($carga->only(['institucion_id', 'periodo_id', 'docente_id', 'curso_id', 'tipo']) + [
            'paralelo_id' => $paraleloB->id, 'asignatura_id' => $matematica->id, 'titulo' => 'Matemática B', 'horas_semanales' => 2,
        ]);
        $ajena = AsignacionDocente::create($carga->only(['institucion_id', 'periodo_id', 'curso_id', 'paralelo_id', 'tipo']) + [
            'docente_id' => $datos['otro']->id, 'titulo' => 'Ajena', 'horas_semanales' => 2,
        ]);
        $lunes = SesionAsistencia::create(['asignacion_docente_id' => $carga->id, 'fecha' => '2026-10-19', 'horas' => 2, 'tema' => 'Fracciones']);
        SesionAsistencia::create(['asignacion_docente_id' => $carga->id, 'fecha' => '2026-10-26', 'horas' => 1, 'tema' => 'Decimales']);
        SesionAsistencia::create(['asignacion_docente_id' => $otra->id, 'fecha' => '2026-10-20', 'horas' => 1, 'tema' => 'Cinemática']);
        SesionAsistencia::create(['asignacion_docente_id' => $ajena->id, 'fecha' => '2026-10-21', 'horas' => 2, 'tema' => 'De otro docente']);
        AsistenciaEstudiante::create(['sesion_asistencia_id' => $lunes->id, 'matricula_id' => $datos['matriculas'][0]->id, 'estado' => 'F']);

        $this->get(route('docente.asistencia.index'))->assertOk()
            ->assertSee(e(route('docente.asistencia.reporte', ['agrupacion' => 'cursos', 'periodo' => $carga->periodo_id])), false)
            ->assertSee('Reporte por semana');
        foreach (['cursos', 'semanas'] as $agrupacion) {
            $this->get(route('docente.asistencia.reporte', ['agrupacion' => $agrupacion]))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
        $this->get(route('docente.asistencia.reporte', ['agrupacion' => 'cursos', 'carga' => $ajena->id]))->assertNotFound();
        $this->get('/dashboard/docente/asistencia/reporte/otra')->assertNotFound();

        $reporte = function (string $agrupacion, array $filtros = []) use ($datos) {
            $request = Request::create('/', 'GET', $filtros);
            $request->setLaravelSession(tap(app('session')->driver())->put('auth_user', ['id' => $datos['docente']->id, 'role' => 'docente']));

            return app(RegistroAsistencia::class)->reporte($request, $agrupacion);
        };
        $cursos = $reporte('cursos');
        $this->assertSame(['Octavo A · EGB', 'Octavo B · EGB'], $cursos['grupos']->pluck('titulo')->all());
        $this->assertSame([[2, 3], [1, 1]], $cursos['grupos']->map(fn ($g) => [$g['clases'], $g['horas']])->all());
        $this->assertSame(1, $cursos['grupos'][0]['estados']['F']);
        $this->assertSame(['clases' => 3, 'horas' => 4], collect($cursos['resumen'])->only(['clases', 'horas'])->all());
        $semanas = $reporte('semanas');
        $this->assertSame(['Semana del 19/10/2026 al 25/10/2026', 'Semana del 26/10/2026 al 01/11/2026'], $semanas['grupos']->pluck('titulo')->all());
        $this->assertSame([3.0, 1.0], $semanas['grupos']->map(fn ($g) => (float) $g['horas'])->all());
        $this->assertSame(4.0, (float) $semanas['cargaSemanal']);
        $this->assertSame(1, $reporte('semanas', ['carga' => $otra->id])['grupos']->count());
    }

    public function test_rechaza_matriculas_ajenas_fechas_horas_y_observaciones_invalidas(): void
    {
        $datos = $this->escenario();
        $matricula = $datos['matriculas'][0]->id;
        $casos = [
            'estudiantes' => ['estudiantes' => [$datos['matriculas'][1]->id => 'P'], 'observaciones' => []],
            'fecha' => ['fecha' => '2027-08-02'],
            'horas' => ['horas' => 3],
            'tema' => ['tema' => str_repeat('a', 256)],
            'estudiantes.'.$matricula => ['estudiantes' => [$matricula => 'X']],
            'observaciones.'.$matricula => ['estudiantes' => [$matricula => ''], 'observaciones' => [$matricula => 'Interrumpe la clase']],
        ];
        foreach ($casos as $error => $cambios) {
            $this->put(route('docente.asistencia.update'), array_replace($datos['payload'], $cambios))->assertSessionHasErrors($error);
        }
        $this->assertSame(0, AsistenciaEstudiante::count());
        $this->assertSame(0, SesionAsistencia::count());
    }

    public function test_restringe_carga_docente_periodo_y_sesion(): void
    {
        $datos = $this->escenario();
        AsignacionDocente::create($datos['carga']->only(['institucion_id', 'periodo_id', 'curso_id', 'paralelo_id', 'tipo']) + [
            'docente_id' => $datos['otro']->id, 'titulo' => 'Química ajena', 'horas_semanales' => 3,
        ]);
        $this->get(route('docente.asistencia.registro'))->assertOk()->assertSee('Matemática')->assertDontSee('Química ajena');
        $this->withSession(['auth_user' => ['id' => $datos['otro']->id, 'role' => 'docente']]);
        $this->get(route('docente.asistencia.registro'))->assertOk()->assertSee('Química ajena')->assertDontSee('Matemática');
        $this->get(route('docente.asistencia.registro', ['carga' => $datos['carga']->id]))->assertNotFound();
        $this->put(route('docente.asistencia.update'), $datos['payload'])->assertNotFound();
        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $this->get(route('docente.asistencia.index', ['periodo' => 999]))->assertNotFound();
        $this->get(route('docente.asistencia.registro', ['fecha' => '2025-01-01']))->assertSessionHasErrors('fecha');
        $this->get(route('docente.asistencia.registro', ['fecha' => 'invalida']))->assertSessionHasErrors('fecha');
        $this->withSession(['auth_user' => ['id' => $datos['otro']->id, 'role' => 'admin']])
            ->put(route('docente.asistencia.update'), $datos['payload'])->assertRedirect(route('roles.dashboard', 'admin'));
        $this->flushSession();
        $this->get(route('docente.asistencia.index'))->assertRedirect(route('roles.access', 'docente'));
    }
}
