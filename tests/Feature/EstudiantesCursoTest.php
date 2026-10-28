<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class EstudiantesCursoTest extends TestCase
{
    use RefreshDatabase;

    public function test_filtra_estudiantes_y_restringe_grupos_del_docente(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'estado' => 'ACTIVO']);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Actual', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $otroCurso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Noveno', 'nivel' => 'EGB', 'grado' => 9]);
        $a = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A']);
        $b = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'B']);
        $c = Paralelo::create(['curso_id' => $otroCurso->id, 'letra' => 'C']);
        AsignacionDocente::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'tipo' => 'carga', 'titulo' => 'Carga', 'horas_semanales' => 4, 'curso_id' => $curso->id, 'paralelo_id' => $a->id]);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        foreach (['alumno.visible' => $a, 'alumno.oculto' => $b] as $nombre => $paralelo) {
            $alumno = Usuario::create(['institucion_id' => $institucion->id, 'username' => $nombre, 'password_hash' => 'test']);
            $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
            Matricula::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'estudiante_id' => $alumno->id, 'paralelo_id' => $paralelo->id]);
        }
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);
        $this->get(route('docente.estudiantes.index'))->assertOk()->assertSee('alumno.visible')->assertDontSee('alumno.oculto')->assertDontSee('Noveno');
        $this->get(route('docente.estudiantes.index', ['curso' => $otroCurso->id]))->assertNotFound();
        $this->get(route('docente.estudiantes.index', ['paralelo' => $b->id]))->assertNotFound();
        $carga = AsignacionDocente::firstOrFail();
        $urlPdf = route('docente.distributivo.estudiantes.pdf', $carga->id);
        $this->get(route('docente.distributivo.index'))->assertOk()->assertSee('Listado PDF')->assertSee($urlPdf);
        $wrapper = app('dompdf.wrapper');
        Pdf::shouldReceive('loadView')->once()->withArgs(function ($view, $datos) {
            $this->assertSame('docente.estudiantes-pdf', $view);
            $this->assertSame(['alumno.visible'], $datos['matriculas']->pluck('estudiante.username')->all());
            $this->assertSame(1, $datos['resumen']['total']);
            $html = view($view, $datos)->render();
            $this->assertStringContainsString('Resumen estadístico', $html);
            $this->assertStringContainsString('>Ord<', $html);
            $this->assertStringContainsString('width: 60%', $html);
            $this->assertStringNotContainsString('alumno.oculto', $html);

            return true;
        })->andReturnUsing(fn ($view, $datos) => $wrapper->loadView($view, $datos));
        $pdf = $this->get($urlPdf.'?paralelo='.$b->id)->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $pdf->getContent());
        Pdf::clearResolvedInstances();
        $otroDocente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'otro.docente', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $otroDocente->id, 'role' => 'docente']])->get($urlPdf)->assertNotFound();
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);
        TutoriaParalelo::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'paralelo_id' => $b->id]);
        $this->get(route('docente.estudiantes.index', ['curso' => $curso->id, 'paralelo' => $b->id]))->assertOk()->assertSee('alumno.oculto')->assertDontSee('alumno.visible');
        $this->get(route('docente.estudiantes.index', ['periodo' => 99999]))->assertNotFound();
        $this->withSession(['auth_user' => ['role' => 'admin']])->get(route('docente.estudiantes.index'))->assertRedirect();
    }
}
