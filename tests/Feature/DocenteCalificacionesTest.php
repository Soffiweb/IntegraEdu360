<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\CalificacionTrimestral;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Docente\RegistroCalificaciones;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class DocenteCalificacionesTest extends TestCase
{
    use RefreshDatabase;

    private function escenario(int $grado = 8, float $horas = 2, string $fin = '2027-07-01', ?int $regimen = null): array
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'regimen_id' => $regimen]);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026-2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => $fin, 'es_activo' => true]);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Curso', 'nivel' => 'EGB', 'grado' => $grado]);
        $paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $carga = AsignacionDocente::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'curso_id' => $curso->id, 'paralelo_id' => $paralelo->id, 'tipo' => 'carga', 'titulo' => 'Matemática', 'horas_semanales' => $horas]);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $persona = Persona::create(['primer_nombre' => 'Ana', 'primer_apellido' => 'Paz']);
        $alumno = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $institucion->id, 'username' => 'ana', 'password_hash' => 'test']);
        $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
        $matricula = Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);

        return compact('institucion', 'periodo', 'docente', 'curso', 'paralelo', 'carga', 'matricula');
    }

    private function guardar(array $datos, array $fila, int $trimestre = 1, ?AsignacionDocente $carga = null)
    {
        return $this->put(route('docente.calificaciones.update'), ['periodo' => $datos['periodo']->id, 'carga' => ($carga ?? $datos['carga'])->id, 'trimestre' => $trimestre,
            'calificaciones' => [$datos['matricula']->id => $fila]]);
    }

    private function consultar(array $datos, int|string $trimestre = 1)
    {
        return $this->get(route('docente.calificaciones.index', ['periodo' => $datos['periodo']->id, 'carga' => $datos['carga']->id, 'trimestre' => $trimestre]))->assertOk();
    }

    public function test_nota_trimestral_pondera_70_30_y_trunca_sin_redondear(): void
    {
        $datos = $this->escenario();
        $this->guardar($datos, ['aportes' => ['8.75', '9.33', '7.01'], 'sumativa' => '9.99'])->assertSessionHasNoErrors();
        $fila = $this->consultar($datos)->assertSee('Se grabaron', false)->viewData('filas')->sole();
        $this->assertSame([836, 999, 884, '8,84'], [$fila['formativa'], $fila['sumativa_final'], $fila['nota'], $fila['nota_texto']]);
        $this->assertFalse($fila['alerta']);
    }

    public function test_aportes_minimos_segun_periodos_semanales(): void
    {
        $registro = app(RegistroCalificaciones::class);
        $this->assertSame([3, 3, 5, 5, 9, 9], array_map(fn ($horas) => $registro->minimoAportes($horas), [1, 3, 4, 5, 6, 8]));

        $datos = $this->escenario(8, 4);
        $this->guardar($datos, ['aportes' => ['8', '8', '8', '8'], 'sumativa' => '8'])->assertSessionHasNoErrors();
        $fila = $this->consultar($datos)->assertViewHas('columnas', 5)->viewData('filas')->sole();
        $this->assertNull($fila['nota'], 'Con menos aportes del mínimo no se calcula la nota');
        $this->guardar($datos, ['aportes' => ['8', '8', '8', '8', '8'], 'sumativa' => '8'])->assertSessionHasNoErrors();
        $this->assertSame(800, $this->consultar($datos)->viewData('filas')->sole()['nota']);
    }

    public function test_alerta_temprana_con_formativa_bajo_siete(): void
    {
        $datos = $this->escenario();
        $this->guardar($datos, ['aportes' => ['6.99']])->assertSessionHasNoErrors();
        $fila = $this->consultar($datos)->viewData('filas')->sole();
        $this->assertTrue($fila['alerta'], 'La alerta no espera al mínimo de aportes');
        $this->assertNull($fila['formativa']);
        $this->guardar($datos, ['aportes' => ['7', '7', '7']])->assertSessionHasNoErrors();
        $this->assertFalse($this->consultar($datos)->viewData('filas')->sole()['alerta']);
    }

    public function test_mejora_aplica_topes_y_rangos(): void
    {
        $datos = $this->escenario();
        $aportes = ['aportes' => ['8', '8', '8']];
        $this->guardar($datos, $aportes + ['sumativa' => '8', 'mejora' => '10'])->assertSessionHasNoErrors();
        $this->assertSame(900, $this->consultar($datos)->viewData('filas')->sole()['sumativa_final']);
        $this->guardar($datos, $aportes + ['sumativa' => '5', 'mejora' => '10'])->assertSessionHasNoErrors();
        $this->assertSame(700, $this->consultar($datos)->viewData('filas')->sole()['sumativa_final']);
        $this->guardar($datos, $aportes + ['sumativa' => '8', 'mejora' => '6'])->assertSessionHasNoErrors();
        $this->assertSame(700, $this->consultar($datos)->viewData('filas')->sole()['sumativa_final'], 'El art. 12 toma el promedio como nueva calificación');
        foreach (['7', '9', '9.5'] as $fuera) {
            $this->guardar($datos, $aportes + ['sumativa' => $fuera, 'mejora' => '10'])->assertSessionHasErrors('calificaciones');
        }
        $this->guardar($datos, $aportes + ['mejora' => '10'])->assertSessionHasErrors('calificaciones');
    }

    public function test_mejora_respeta_limites_por_asignatura_y_totales(): void
    {
        $datos = $this->escenario();
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 1)->assertSessionHasNoErrors();
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 2)->assertSessionHasErrors('calificaciones');
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 1)->assertSessionHasNoErrors();

        $this->guardar($datos, ['sumativa' => '5', 'mejora' => '6'], 2)->assertSessionHasNoErrors();
        $this->guardar($datos, ['sumativa' => '5', 'mejora' => '6'], 3)->assertSessionHasNoErrors();

        $cargas = collect(['Lengua', 'Ciencias', 'Historia'])->map(fn ($titulo) => AsignacionDocente::create($datos['carga']->only(['institucion_id', 'periodo_id', 'docente_id', 'curso_id', 'paralelo_id', 'tipo', 'horas_semanales']) + ['titulo' => $titulo]));
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 1, $cargas[0])->assertSessionHasNoErrors();
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 1, $cargas[1])->assertSessionHasNoErrors();
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '9'], 1, $cargas[2])->assertSessionHasErrors('calificaciones');
    }

    public function test_consolidado_anual_promedia_y_define_situacion(): void
    {
        $datos = $this->escenario();
        $aportes = fn ($nota) => ['aportes' => [$nota, $nota, $nota], 'sumativa' => $nota];
        $this->guardar($datos, $aportes('7'), 1);
        $this->guardar($datos, $aportes('7'), 2);
        $this->assertSame('Pendiente', $this->consultar($datos, 'anual')->viewData('filas')->sole()['situacion']);
        $this->guardar($datos, $aportes('7.01'), 3);
        $fila = $this->consultar($datos, 'anual')->assertSee('Consolidado anual')->viewData('filas')->sole();
        $this->assertSame([700, '7,00', 'Promoción directa'], [$fila['promedio'], $fila['promedio_texto'], $fila['situacion']]);

        $registro = app(RegistroCalificaciones::class);
        $this->assertSame('Evaluación supletoria', $registro->situacionFinal(699, 8));
        $this->assertSame('Evaluación supletoria', $registro->situacionFinal(401, 8));
        $this->assertSame('Repitencia directa', $registro->situacionFinal(400, 8));
        $this->assertSame('Refuerzo y compromisos', $registro->situacionFinal(400, 6));
    }

    public function test_elemental_registra_letras_y_promedia_segun_el_acuerdo(): void
    {
        $datos = $this->escenario(3);
        foreach ([1 => 'A+', 2 => 'B+', 3 => 'C+'] as $trimestre => $letra) {
            $this->guardar($datos, ['valoracion' => $letra], $trimestre)->assertSessionHasNoErrors();
        }
        $this->assertTrue($this->consultar($datos, 3)->assertDontSee('Formativa 70 %')->viewData('filas')->sole()['alerta']);
        $this->assertFalse($this->consultar($datos, 2)->viewData('filas')->sole()['alerta']);
        $fila = $this->consultar($datos, 'anual')->viewData('filas')->sole();
        $this->assertSame(['B+', 'Promoción automática'], [$fila['promedio_texto'], $fila['situacion']]);
        $this->guardar($datos, ['valoracion' => 'F'])->assertSessionHasErrors();
    }

    public function test_preparatoria_no_admite_notas(): void
    {
        $datos = $this->escenario(1);
        $this->consultar($datos)->assertSee('por ámbito y destreza')->assertDontSee('Guardar calificaciones');
        $this->guardar($datos, ['valoracion' => 'A+'])->assertNotFound();
        $this->get(route('docente.calificaciones.pdf', ['carga' => $datos['carga']->id]))->assertNotFound();
    }

    public function test_costa_2026_conserva_mejora_del_acuerdo_anterior(): void
    {
        $regimen = DB::table('cat_regimen_escolar')->insertGetId(['nombre' => 'Costa']);
        $datos = $this->escenario(8, 2, '2027-03-01', $regimen);
        $this->consultar($datos)->assertViewHas('mejoraDisponible', false)->assertDontSee('Evaluación de mejora');
        $this->guardar($datos, ['sumativa' => '8', 'mejora' => '10'])->assertSessionHasNoErrors();
        $this->assertNull(CalificacionTrimestral::sole()->sumativa_mejora);
    }

    public function test_validacion_vaciado_y_conservacion_de_columnas(): void
    {
        $datos = $this->escenario();
        foreach (['0', '10.01', '8.123'] as $invalida) {
            $this->guardar($datos, ['sumativa' => $invalida])->assertSessionHasErrors();
        }
        $this->guardar($datos, ['aportes' => ['8', null, '9', '7', '6', null]])->assertSessionHasNoErrors();
        $this->assertSame(['8', null, '9', '7', '6'], CalificacionTrimestral::sole()->aportes);
        $this->consultar($datos)->assertViewHas('columnas', 5);
        $this->guardar($datos, ['aportes' => [null, null, null], 'sumativa' => null])->assertSessionHasNoErrors();
        $this->assertSame(0, CalificacionTrimestral::count());
    }

    public function test_solo_el_docente_de_la_clase_accede_y_genera_pdf(): void
    {
        $datos = $this->escenario();
        $this->guardar($datos, ['aportes' => ['8', '8', '8'], 'sumativa' => '8'])->assertSessionHasNoErrors();
        $this->consultar($datos)->assertSee('Registro de calificaciones')->assertSee('Ana', false);
        foreach ([1, 'anual'] as $trimestre) {
            $this->get(route('docente.calificaciones.pdf', ['carga' => $datos['carga']->id, 'trimestre' => $trimestre]))->assertOk()->assertHeader('content-type', 'application/pdf');
        }
        $request = Request::create('/', 'GET', ['carga' => $datos['carga']->id]);
        $request->setLaravelSession(tap(app('session')->driver())->put('auth_user', ['id' => $datos['docente']->id, 'role' => 'docente']));
        $html = view('docente.calificaciones-pdf', app(RegistroCalificaciones::class)->contexto($request))->render();
        foreach (['Ord', 'Resumen estadístico', 'Paz Ana', '8,00'] as $texto) {
            $this->assertStringContainsString($texto, $html);
        }

        $otro = Usuario::create(['institucion_id' => $datos['institucion']->id, 'username' => 'otro', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'docente']]);
        $this->get(route('docente.calificaciones.index', ['carga' => $datos['carga']->id]))->assertNotFound();
        $this->guardar($datos, ['sumativa' => '1'])->assertNotFound();
        $this->get(route('docente.calificaciones.pdf', ['carga' => $datos['carga']->id]))->assertNotFound();
        $this->assertSame('8.00', CalificacionTrimestral::sole()->sumativa);

        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'padre-tutor']]);
        $this->get(route('docente.calificaciones.index'))->assertRedirect();
    }
}
