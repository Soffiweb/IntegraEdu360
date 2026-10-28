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
use Illuminate\Foundation\Testing\RefreshDatabase;
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
        $payload = ['periodo' => $periodo->id, 'carga' => $carga->id, 'semana' => '2026-10-19', 'sesiones' => [
            1 => ['fecha' => '2026-10-19', 'estudiantes' => [$matriculas[0]->id => 'P']],
            2 => ['fecha' => '2026-10-19', 'estudiantes' => [$matriculas[0]->id => 'F']],
        ]];

        return compact('carga', 'docente', 'otro', 'matriculas', 'payload');
    }

    public function test_muestra_carga_guarda_corrige_y_separa_semanas(): void
    {
        $datos = $this->escenario();
        $payload = $datos['payload'];
        $this->get(route('docente.asistencia.index', collect($payload)->only(['periodo', 'carga', 'semana'])->all()))
            ->assertOk()->assertSee('Registro de asistencia')->assertSee('Período 2')->assertDontSee('Período 3')
            ->assertSee('alumno.A')->assertDontSee('alumno.B');
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors()->assertRedirect();
        $this->assertSame(2, AsistenciaEstudiante::count());
        $this->assertSame(2, SesionAsistencia::count());
        $payload['sesiones'][1]['estudiantes'][$datos['matriculas'][0]->id] = 'J';
        $payload['sesiones'][2]['estudiantes'][$datos['matriculas'][0]->id] = '';
        $this->put(route('docente.asistencia.update'), $payload)->assertRedirect()->assertSessionHasNoErrors();
        $this->assertSame(1, AsistenciaEstudiante::count());
        $this->assertSame('J', AsistenciaEstudiante::first()->estado);
        $this->get(route('docente.asistencia.index', collect($payload)->only(['periodo', 'carga', 'semana'])->all()))
            ->assertOk()->assertSee('value="J" selected', false);
        $payload['semana'] = '2026-10-26';
        $payload['sesiones'][1]['fecha'] = '2026-10-26';
        $payload['sesiones'][2]['fecha'] = '2026-10-27';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasNoErrors();
        $this->assertSame(2, AsistenciaEstudiante::count());
        $this->assertSame(4, SesionAsistencia::count());
    }

    public function test_rechaza_matriculas_ajenas_fechas_invalidas_y_exceso_de_periodos(): void
    {
        $datos = $this->escenario();
        $payload = $datos['payload'];
        $payload['sesiones'][1]['estudiantes'] = [$datos['matriculas'][1]->id => 'P'];
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasErrors('sesiones');
        $payload = $datos['payload'];
        $payload['sesiones'][1]['fecha'] = '2026-10-26';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasErrors('sesiones.1.fecha');
        $payload['sesiones'][1]['fecha'] = '';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasErrors('sesiones.1.fecha');
        $payload = $datos['payload'];
        $payload['sesiones'][3] = $payload['sesiones'][1];
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasErrors('sesiones');
        $payload = $datos['payload'];
        $payload['sesiones'][1]['estudiantes'][$datos['matriculas'][0]->id] = 'X';
        $this->put(route('docente.asistencia.update'), $payload)->assertSessionHasErrors();
        $this->assertSame(0, AsistenciaEstudiante::count());
        $this->assertSame(0, SesionAsistencia::count());
    }

    public function test_restringe_carga_docente_periodo_y_sesion(): void
    {
        $datos = $this->escenario();
        $this->withSession(['auth_user' => ['id' => $datos['otro']->id, 'role' => 'docente']]);
        $this->get(route('docente.asistencia.index', ['carga' => $datos['carga']->id]))->assertNotFound();
        $this->put(route('docente.asistencia.update'), $datos['payload'])->assertNotFound();
        $this->withSession(['auth_user' => ['id' => $datos['docente']->id, 'role' => 'docente']]);
        $this->get(route('docente.asistencia.index', ['periodo' => 999]))->assertNotFound();
        $this->get(route('docente.asistencia.index', ['semana' => '2025-01-01']))->assertSessionHasErrors('semana');
        $this->get(route('docente.asistencia.index', ['semana' => 'invalida']))->assertSessionHasErrors('semana');
        $this->withSession(['auth_user' => ['id' => $datos['otro']->id, 'role' => 'admin']])
            ->put(route('docente.asistencia.update'), $datos['payload'])->assertRedirect(route('roles.dashboard', 'admin'));
        $this->flushSession();
        $this->get(route('docente.asistencia.index'))->assertRedirect(route('roles.access', 'docente'));
    }
}
