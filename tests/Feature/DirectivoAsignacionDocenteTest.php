<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use App\Services\Directivo\ReporteDistribucion;
use App\Services\Directivo\ReporteDocentesAgrupado;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectivoAsignacionDocenteTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    private Institucion $otra;

    private Usuario $docente;

    private Usuario $otroDocente;

    private PeriodoAcademico $periodo;

    private PeriodoAcademico $otroPeriodo;

    private Curso $curso;

    private Asignatura $asignatura;

    private Paralelo $paralelo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->institucion = Institucion::create(['nombre' => 'Colegio local', 'estado' => 'ACTIVO']);
        $this->otra = Institucion::create(['nombre' => 'Colegio ajeno', 'estado' => 'ACTIVO']);
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $persona = Persona::create(['primer_nombre' => 'Ana', 'primer_apellido' => 'Pérez']);
        $this->docente = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $this->institucion->id, 'username' => 'docente.local', 'password_hash' => 'test', 'estado' => 'ACTIVO']);
        $this->otroDocente = Usuario::create(['institucion_id' => $this->otra->id, 'username' => 'docente.ajeno', 'password_hash' => 'test', 'estado' => 'ACTIVO']);
        $this->docente->roles()->attach($rol, ['institucion_id' => $this->institucion->id]);
        $this->otroDocente->roles()->attach($rol, ['institucion_id' => $this->otra->id]);
        $this->periodo = PeriodoAcademico::create(['institucion_id' => $this->institucion->id, 'nombre' => '2026–2027', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $this->otroPeriodo = PeriodoAcademico::create(['institucion_id' => $this->otra->id, 'nombre' => 'Ajeno', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01']);
        $this->curso = Curso::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $this->paralelo = Paralelo::create(['curso_id' => $this->curso->id, 'letra' => 'A', 'capacidad' => 30]);
        $this->asignatura = Asignatura::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Matemática']);
        $this->curso->asignaturas()->attach($this->asignatura);
        $this->withSession(['auth_user' => ['role' => 'directivo', 'institucion_id' => $this->institucion->id]]);
    }

    private function datos(array $cambios = []): array
    {
        return $cambios + ['docente_id' => $this->docente->id, 'periodo_id' => $this->periodo->id, 'tipo' => 'carga', 'titulo' => 'Matemática · Octavo', 'horas_semanales' => '4.50', 'curso_id' => $this->curso->id, 'paralelo_id' => $this->paralelo->id, 'asignatura_id' => $this->asignatura->id, 'fecha_limite' => '2026-12-01'];
    }

    public function test_agrupa_por_asignatura_y_area_sin_repetir_docentes_ni_sumar_comisiones(): void
    {
        $this->asignatura->update(['area' => 'MATEMATICA']);
        $segunda = Asignatura::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Matemática', 'area' => 'MATEMATICA']);
        foreach ([$this->asignatura, $segunda] as $i => $asignatura) {
            AsignacionDocente::create($this->datos(['asignatura_id' => $asignatura->id, 'titulo' => 'Carga '.$i, 'horas_semanales' => 3]) + ['institucion_id' => $this->institucion->id]);
        }
        AsignacionDocente::create($this->datos(['tipo' => 'comision', 'titulo' => 'Cultura', 'horas_semanales' => 2]) + ['institucion_id' => $this->institucion->id]);
        $servicio = app(ReporteDocentesAgrupado::class);
        foreach (['asignaturas', 'areas'] as $tipo) {
            $datos = $servicio->datos($this->institucion, $this->periodo, $tipo);
            $this->assertCount(1, $datos['grupos']);
            $this->assertSame(1, $datos['grupos']->sole()['docentes']);
            $this->assertCount(1, $datos['grupos']->sole()['filas']);
            $this->assertEquals(6, $datos['resumen']['horas']);
            $this->assertSame(1, $datos['resumen']['docentes']);
        }
        $comisiones = $servicio->datos($this->institucion, $this->periodo, 'comisiones');
        $this->assertSame('Cultura', $comisiones['grupos']->sole()['titulo']);
        $this->assertEquals(0, $comisiones['resumen']['horas']);
        $this->assertSame(1, $comisiones['resumen']['participaciones']);
        $this->assertCount(0, $servicio->datos($this->institucion, $this->periodo, 'areas', 'Inexistente')['grupos']);
    }

    public function test_tutores_ordenados_por_curso_y_paralelo_incluyen_vacantes_sin_filtrar_datos_ajenos(): void
    {
        $this->paralelo->update(['tutor_id' => $this->docente->id, 'estado' => 'ACTIVO']);
        TutoriaParalelo::create(['institucion_id' => $this->institucion->id, 'periodo_id' => $this->periodo->id, 'paralelo_id' => $this->paralelo->id, 'docente_id' => $this->docente->id]);
        $this->curso->update(['estado' => 'ACTIVO']);
        $ajeno = Paralelo::create(['curso_id' => $this->curso->id, 'letra' => 'C', 'estado' => 'ACTIVO', 'tutor_id' => $this->otroDocente->id]);
        TutoriaParalelo::create(['institucion_id' => $this->institucion->id, 'periodo_id' => $this->periodo->id, 'paralelo_id' => $ajeno->id, 'docente_id' => $this->otroDocente->id]);
        Paralelo::create(['curso_id' => $this->curso->id, 'letra' => 'B', 'estado' => 'ACTIVO']);
        $servicio = app(ReporteDocentesAgrupado::class);
        $datos = $servicio->datos($this->institucion, $this->periodo, 'tutores');
        $this->assertSame(['A', 'B', 'C'], $datos['grupos']->sole()['filas']->pluck('detalle')->all());
        $this->assertSame(1, $datos['resumen']['docentes']);
        $this->assertSame(2, $datos['resumen']['sin_tutor']);
        $this->assertStringNotContainsString('docente.ajeno', view('directivo.asignaciones.agrupado-pdf', $datos)->render());
        $filtrados = $servicio->datos($this->institucion, $this->periodo, 'tutores', 'Ana');
        $this->assertSame(1, $filtrados['resumen']['paralelos']);
        $this->assertSame(0, $filtrados['resumen']['sin_tutor']);
    }

    public function test_los_cuatro_reportes_agrupados_generan_pdf_y_validan_periodo_y_tipo(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertSessionHasNoErrors();
        $this->paralelo->update(['tutor_id' => $this->docente->id]);
        Pdf::shouldReceive('loadView')->times(4)->withArgs(function ($view, $datos) {
            $this->assertSame('directivo.asignaciones.agrupado-pdf', $view);
            $this->assertSame($this->institucion->id, $datos['institucion']->id);
            $html = view($view, $datos)->render();
            $this->assertStringContainsString('Resumen estadístico', $html);
            $this->assertStringContainsString('width: 60%', $html);
            $this->assertStringNotContainsString('docente.ajeno', $html);

            return true;
        })->andReturnUsing(fn ($view, $datos) => (new \Barryvdh\DomPDF\PDF(app('dompdf'), app('config'), app('files'), app('view')))->loadView($view, $datos));
        foreach (array_keys(ReporteDocentesAgrupado::TIPOS) as $tipo) {
            $respuesta = $this->get(route('directivo.asignaciones.agrupado.pdf', ['agrupacion' => $tipo, 'periodo' => $this->periodo->id]))
                ->assertOk()->assertHeader('content-type', 'application/pdf');
            $this->assertStringStartsWith('%PDF-', $respuesta->getContent());
        }
        $this->get(route('directivo.asignaciones.agrupado.pdf', ['agrupacion' => 'areas', 'periodo' => $this->otroPeriodo->id]))->assertNotFound();
        $this->get(route('directivo.asignaciones.agrupado.pdf', ['agrupacion' => 'invalido']))->assertNotFound();
    }

    public function test_reporte_general_pdf_filtra_docentes_y_excluye_comisiones_del_total(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos(['horas_semanales' => 6]))->assertSessionHasNoErrors();
        $this->post(route('directivo.asignaciones.store'), $this->datos(['tipo' => 'comision', 'titulo' => 'Convivencia', 'horas_semanales' => 2]))->assertSessionHasNoErrors();
        $wrapper = app('dompdf.wrapper');
        Pdf::shouldReceive('loadView')->once()->withArgs(function ($view, $datos) {
            $this->assertSame('directivo.asignaciones.pdf', $view);
            $this->assertSame([$this->docente->id], $datos['grupos']->pluck('docente.id')->all());
            $this->assertSame(6.0, $datos['resumen']['horas']);
            $this->assertSame(1, $datos['resumen']['comisiones']);
            $html = view($view, $datos)->render();
            foreach (['Ana Pérez', 'Convivencia', 'Resumen estadístico general', '>Ord<', 'width: 60%', 'INTEGRAEDU360'] as $texto) {
                $this->assertStringContainsString($texto, $html);
            }
            $this->assertStringNotContainsString('docente.ajeno', $html);

            return true;
        })->andReturnUsing(fn ($view, $datos) => $wrapper->loadView($view, $datos));
        $response = $this->get(route('directivo.asignaciones.pdf', ['periodo' => $this->periodo->id, 'search' => 'Ana', 'institucion' => $this->otra->id]))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
    }

    public function test_reporte_individual_pdf_solo_incluye_docente_seleccionado_y_valida_acceso(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertSessionHasNoErrors();
        $wrapper = app('dompdf.wrapper');
        Pdf::shouldReceive('loadView')->once()->withArgs(function ($view, $datos) {
            $this->assertTrue($datos['individual']);
            $this->assertSame(1, $datos['resumen']['docentes']);
            $this->assertSame($this->docente->id, $datos['grupos']->sole()['docente']->id);
            $this->assertSame(4.5, $datos['resumen']['horas']);

            return true;
        })->andReturnUsing(fn ($view, $datos) => $wrapper->loadView($view, $datos));
        $this->get(route('directivo.asignaciones.docente.pdf', ['docente' => $this->docente->id, 'periodo' => $this->periodo->id, 'search' => 'otro']))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('directivo.asignaciones.docente.pdf', ['docente' => $this->otroDocente->id]))->assertNotFound();
        $this->get(route('directivo.asignaciones.pdf', ['periodo' => $this->otroPeriodo->id]))->assertNotFound();
        $this->withSession(['auth_user' => ['role' => 'docente', 'institucion_id' => $this->institucion->id]])
            ->get(route('directivo.asignaciones.pdf'))->assertRedirect(route('roles.dashboard', 'docente'));
    }

    public function test_resumen_pdf_conserva_docentes_sin_carga_y_respeta_busqueda_vacia(): void
    {
        $reporte = app(ReporteDistribucion::class);
        $datos = $reporte->datos($this->institucion, $this->periodo);
        $this->assertSame(1, $datos['resumen']['docentes']);
        $this->assertSame(0, $datos['resumen']['con_carga']);
        $this->assertSame(0.0, $datos['resumen']['horas']);
        $vacio = $reporte->datos($this->institucion, $this->periodo, null, 'Inexistente');
        $this->assertSame(0, $vacio['resumen']['docentes']);
        $this->assertStringContainsString('No hay docentes con los filtros actuales.', view('directivo.asignaciones.pdf', $vacio)->render());
    }

    public function test_directivo_sees_workspace_and_menu_after_agenda(): void
    {
        $this->get(route('directivo.asignaciones.index'))->assertOk()->assertSee('Ana Pérez')->assertSee('Carga horaria')->assertSee('Comisión');
        $response = $this->get(route('roles.dashboard', 'directivo'))->assertOk();
        $this->assertLessThan(strpos($response->getContent(), route('directivo.asignaciones.index')), strpos($response->getContent(), route('directivo.agenda.index')));
    }

    public function test_creates_all_three_types_and_totals_hours(): void
    {
        foreach (['carga', 'actividad', 'comision'] as $tipo) {
            $this->post(route('directivo.asignaciones.store'), $this->datos(['tipo' => $tipo, 'titulo' => 'Responsabilidad '.$tipo, 'institucion_id' => $this->otra->id]))->assertRedirect();
        }
        $this->assertSame(3, AsignacionDocente::count());
        $this->assertSame(0, AsignacionDocente::where('institucion_id', $this->otra->id)->count());
        $this->assertNull(AsignacionDocente::where('tipo', 'comision')->sole()->curso_id);
        $this->get(route('directivo.asignaciones.index'))->assertViewHas('asignaciones', fn ($asignaciones) => (float) $asignaciones->sum('horas_semanales') === 13.5);
    }

    public function test_updates_and_removes_assignment(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertRedirect();
        $asignacion = AsignacionDocente::sole();
        $this->get(route('directivo.asignaciones.index', ['edit' => $asignacion->id]))->assertOk()->assertSee('Editar asignación');
        $this->put(route('directivo.asignaciones.update', $asignacion), $this->datos())->assertRedirect();
        $this->put(route('directivo.asignaciones.update', $asignacion), $this->datos(['tipo' => 'comision', 'horas_semanales' => '2', 'titulo' => 'Convivencia']))->assertRedirect();
        $this->assertSame('2.00', $asignacion->fresh()->horas_semanales);
        $this->assertNull($asignacion->fresh()->curso_id);
        $this->assertNull($asignacion->fresh()->paralelo_id);
        $this->assertNull($asignacion->fresh()->asignatura_id);
        $this->delete(route('directivo.asignaciones.destroy', $asignacion))->assertRedirect();
        $this->assertModelMissing($asignacion);
    }

    public function test_limita_carga_a_25_y_comisiones_a_dos_sin_sumarlas_a_clases(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos(['titulo' => 'Carga completa', 'horas_semanales' => 25]))->assertSessionHasNoErrors();
        $this->post(route('directivo.asignaciones.store'), $this->datos(['titulo' => 'Exceso', 'horas_semanales' => 1]))->assertSessionHasErrors('horas_semanales');
        foreach (['Convivencia', 'Cultura'] as $titulo) {
            $this->post(route('directivo.asignaciones.store'), $this->datos(['tipo' => 'comision', 'titulo' => $titulo, 'horas_semanales' => 0]))->assertSessionHasNoErrors();
        }
        $this->post(route('directivo.asignaciones.store'), $this->datos(['tipo' => 'comision', 'titulo' => 'Tercera', 'horas_semanales' => 0]))->assertSessionHasErrors('tipo');
        $this->get(route('directivo.asignaciones.index'))->assertViewHas('horas', fn ($horas) => (float) $horas[$this->docente->id] === 25.0);
        $carga = AsignacionDocente::where('tipo', 'carga')->sole();
        $this->put(route('directivo.asignaciones.update', $carga), $this->datos(['titulo' => 'Carga completa', 'horas_semanales' => 25]))->assertSessionHasNoErrors();
    }

    public function test_rejects_foreign_relations_and_non_teachers(): void
    {
        $sinRol = Usuario::create(['institucion_id' => $this->institucion->id, 'username' => 'sin.rol', 'password_hash' => 'test']);
        $asignaturaAjena = Asignatura::create(['institucion_id' => $this->otra->id, 'nombre' => 'Ajena']);
        $cursoAjeno = Curso::create(['institucion_id' => $this->otra->id, 'nombre' => 'Ajeno', 'nivel' => 'EGB', 'grado' => 8]);
        $paraleloAjeno = Paralelo::create(['curso_id' => $cursoAjeno->id, 'letra' => 'A', 'capacidad' => 30]);
        foreach ([['docente_id' => $this->otroDocente->id], ['periodo_id' => $this->otroPeriodo->id], ['docente_id' => $sinRol->id], ['paralelo_id' => $paraleloAjeno->id], ['asignatura_id' => $asignaturaAjena->id]] as $cambio) {
            $this->post(route('directivo.asignaciones.store'), $this->datos($cambio))->assertSessionHasErrors(array_key_first($cambio));
        }
        $this->assertSame(0, AsignacionDocente::count());
    }

    public function test_rejects_duplicates_invalid_hours_dates_and_unlinked_subjects(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertRedirect();
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertSessionHasErrors('titulo');
        $otraAsignatura = Asignatura::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Sin vínculo']);
        foreach ([['horas_semanales' => -1], ['horas_semanales' => '1.234'], ['fecha_limite' => '2028-01-01'], ['asignatura_id' => $otraAsignatura->id]] as $cambio) {
            $this->post(route('directivo.asignaciones.store'), $this->datos($cambio + ['titulo' => 'Nueva']))->assertSessionHasErrors(array_key_first($cambio));
        }
        $this->assertSame(1, AsignacionDocente::count());
    }

    public function test_cannot_access_foreign_assignments_or_periods(): void
    {
        $asignacion = AsignacionDocente::create(['institucion_id' => $this->otra->id, 'periodo_id' => $this->otroPeriodo->id, 'docente_id' => $this->otroDocente->id, 'tipo' => 'comision', 'titulo' => 'Privada', 'horas_semanales' => 3]);
        $this->get(route('directivo.asignaciones.index'))->assertDontSee('Privada')->assertDontSee('docente.ajeno');
        $this->get(route('directivo.asignaciones.index', ['periodo' => $this->otroPeriodo->id]))->assertNotFound();
        $this->get(route('directivo.asignaciones.index', ['docente' => $this->otroDocente->id]))->assertNotFound();
        $this->get(route('directivo.asignaciones.index', ['edit' => $asignacion->id]))->assertNotFound();
        $this->put(route('directivo.asignaciones.update', $asignacion), $this->datos())->assertForbidden();
        $this->delete(route('directivo.asignaciones.destroy', $asignacion))->assertNotFound();
        $this->assertModelExists($asignacion);
    }

    public function test_periods_have_independent_assignments(): void
    {
        $this->post(route('directivo.asignaciones.store'), $this->datos())->assertRedirect();
        $nuevoPeriodo = PeriodoAcademico::create(['institucion_id' => $this->institucion->id, 'nombre' => '2027–2028', 'fecha_inicio' => '2027-09-01', 'fecha_fin' => '2028-07-01']);
        $this->get(route('directivo.asignaciones.index', ['periodo' => $nuevoPeriodo->id]))->assertViewHas('asignaciones', fn ($asignaciones) => $asignaciones->isEmpty());
    }

    public function test_loads_curriculum_and_persists_selected_parallel(): void
    {
        $segundo = Paralelo::create(['curso_id' => $this->curso->id, 'letra' => 'B', 'capacidad' => 30]);
        $this->get(route('directivo.asignaciones.index'))->assertOk()->assertSee('Curso y paralelo')
            ->assertViewHas('cursos', fn ($cursos) => $cursos->sole()->paralelos->count() === 2 && $cursos->sole()->asignaturas->sole()->is($this->asignatura));
        $this->post(route('directivo.asignaciones.store'), $this->datos(['paralelo_id' => $segundo->id]))->assertSessionHasNoErrors();
        $asignacion = AsignacionDocente::sole();
        $this->assertSame($segundo->id, $asignacion->paralelo_id);
        $this->assertSame($this->curso->id, $asignacion->curso_id);
        $this->post(route('directivo.asignaciones.store'), $this->datos(['paralelo_id' => '', 'titulo' => 'Sin paralelo']))->assertSessionHasErrors('paralelo_id');
        $this->put(route('directivo.asignaciones.update', $asignacion), $this->datos())->assertSessionHasNoErrors();
        $this->assertSame($this->paralelo->id, $asignacion->fresh()->paralelo_id);
    }

    public function test_requires_directivo_and_an_assigned_institution(): void
    {
        $this->withSession(['auth_user' => ['role' => 'directivo']]);
        $this->get(route('directivo.asignaciones.index'))->assertForbidden();
        $this->withSession(['auth_user' => ['role' => 'docente', 'institucion_id' => $this->institucion->id]]);
        $this->get(route('directivo.asignaciones.index'))->assertRedirect(route('roles.dashboard', 'docente'));
    }
}
