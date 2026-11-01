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
use App\Models\Persona;
use App\Models\Rol;
use App\Models\SesionAsistencia;
use App\Models\Usuario;
use App\Services\Docente\InformesAsistencia;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteInformesAsistenciaTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(): array
    {
        $institucion = Institucion::create(['nombre' => 'Colegio']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $otro = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'otro', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $asignatura = Asignatura::create(['institucion_id' => $institucion->id, 'nombre' => 'Matemática']);
        $base = ['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'curso_id' => $curso->id, 'paralelo_id' => $paralelo->id, 'tipo' => 'carga', 'horas_semanales' => 2];
        $carga = AsignacionDocente::create($base + ['docente_id' => $docente->id, 'titulo' => 'Matemática', 'asignatura_id' => $asignatura->id]);
        $ajena = AsignacionDocente::create($base + ['docente_id' => $otro->id, 'titulo' => 'Química ajena']);
        $matriculas = collect(['Ana', 'Luis'])->map(function ($nombre) use ($institucion, $periodo, $paralelo, $rol) {
            $persona = Persona::create(['primer_nombre' => $nombre, 'primer_apellido' => 'Paz']);
            $alumno = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $institucion->id, 'username' => 'alumno.'.$nombre, 'password_hash' => 'test']);
            $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);

            return Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        });
        foreach (['2026-09-07', '2026-09-14', '2026-10-05'] as $indice => $fecha) {
            $sesion = SesionAsistencia::create(['asignacion_docente_id' => $carga->id, 'fecha' => $fecha, 'horas' => 2, 'tema' => 'Tema '.$indice]);
            AsistenciaEstudiante::create(['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $matriculas[0]->id, 'estado' => 'F']);
            AsistenciaEstudiante::create(['sesion_asistencia_id' => $sesion->id, 'matricula_id' => $matriculas[1]->id, 'estado' => 'P', 'observacion' => $indice === 0 ? 'Participa activamente' : null]);
        }
        $sesionAjena = SesionAsistencia::create(['asignacion_docente_id' => $ajena->id, 'fecha' => '2026-09-08', 'horas' => 1, 'tema' => 'Ajeno']);
        AsistenciaEstudiante::create(['sesion_asistencia_id' => $sesionAjena->id, 'matricula_id' => $matriculas[1]->id, 'estado' => 'F', 'observacion' => 'Observación ajena']);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);

        return compact('carga', 'ajena', 'periodo');
    }

    private function informe(string $tab, array $filtros = [])
    {
        return $this->get(route('docente.informes-asistencia.index', ['tab' => $tab] + $filtros))->assertOk();
    }

    public function test_menu_y_todas_las_pestanas_se_muestran(): void
    {
        $this->escenario();
        $respuesta = $this->get(route('docente.informes-asistencia.index'))->assertOk()->assertSee('Informes de asistencia');
        $this->assertLessThan(strpos($respuesta->getContent(), 'Estudiantes por curso'), strpos($respuesta->getContent(), '<span>Informes de asistencia</span>'));
        $this->assertLessThan(strpos($respuesta->getContent(), '<span>Informes de asistencia</span>'), strpos($respuesta->getContent(), '<span>Registro de asistencia</span>'));
        foreach (['Por clase', 'Por curso', 'Por semana', 'Por mes', 'Por estudiante', 'Alertas de asistencia', 'Observaciones'] as $tab) {
            $respuesta->assertSee($tab);
        }
        foreach (array_keys(InformesAsistencia::TABS) as $tab) {
            $this->informe($tab);
        }
        $this->get(route('docente.informes-asistencia.index', ['tab' => 'otra']))->assertNotFound();
    }

    public function test_calcula_informes_agregados_solo_con_clases_del_docente(): void
    {
        $this->escenario();
        $this->informe('resumen')->assertViewHas('resumen', ['clases' => 3, 'horas' => 6, 'estados' => ['P' => 3, 'F' => 3, 'A' => 0, 'J' => 0], 'asistencia' => 50.0])
            ->assertViewHas('alertas', 0)->assertViewHas('estudiantes', 2);
        $this->informe('clases')->assertSee('Matemática · Octavo A')->assertDontSee('Química ajena');
        $this->informe('cursos')->assertSee('Octavo A · EGB');
        $this->informe('semanas')->assertSee('Semana del 07/09/2026 al 13/09/2026')->assertSee('2 / 2')->assertDontSee('Ajeno');
        $meses = $this->informe('meses')->assertSee('Septiembre 2026')->assertSee('Octubre 2026');
        $this->assertSame([2, 1], $meses->viewData('filas')->pluck('clases')->all());
    }

    public function test_informes_por_estudiante_y_observaciones(): void
    {
        $this->escenario();
        $filas = $this->informe('estudiantes')->assertSee('Paz Ana')->assertSee('Paz Luis')->viewData('filas');
        $this->assertSame([0.0, 100.0], collect($filas->items())->pluck('asistencia')->all());
        $this->assertSame(['P' => 3, 'F' => 0, 'A' => 0, 'J' => 0], $filas->items()[1]['estados']);
        $this->informe('observaciones')->assertSee('Participa activamente')->assertDontSee('Observación ajena');
    }

    public function test_filtra_por_fechas_y_clase_y_conserva_filtros_en_pestanas(): void
    {
        $datos = $this->escenario();
        $filtros = ['desde' => '2026-09-10', 'hasta' => '2026-09-30'];
        $this->informe('resumen', $filtros)->assertViewHas('alertas', 0)
            ->assertSee(e(route('docente.informes-asistencia.index', ['tab' => 'semanas', 'periodo' => $datos['periodo']->id] + $filtros)), false);
        $this->assertSame(1, $this->informe('semanas', $filtros)->viewData('resumen')['clases']);
        $this->informe('cursos', $filtros)->assertSee(e(route('docente.asistencia.reporte', ['agrupacion' => 'cursos', 'periodo' => $datos['periodo']->id] + $filtros)), false);
        $this->get(route('docente.asistencia.reporte', ['agrupacion' => 'semanas'] + $filtros))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('docente.informes-asistencia.index', ['carga' => $datos['ajena']->id]))->assertNotFound();
        $this->get(route('docente.informes-asistencia.index', ['desde' => 'invalida']))->assertSessionHasErrors('desde');
        $this->get(route('docente.informes-asistencia.index', ['desde' => '2026-10-01', 'hasta' => '2026-09-01']))->assertSessionHasErrors('hasta');
    }

    public function test_requiere_rol_docente(): void
    {
        $this->get(route('docente.informes-asistencia.index'))->assertRedirect(route('roles.access', 'docente'));
    }
}
