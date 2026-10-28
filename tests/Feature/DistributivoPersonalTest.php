<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DistributivoPersonalTest extends TestCase
{
    use RefreshDatabase;

    public function test_solo_muestra_carga_del_docente_y_periodo_seleccionado(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'estado' => 'ACTIVO']);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $otro = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'otro', 'password_hash' => 'test']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Actual', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01', 'es_activo' => true]);
        $anterior = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Anterior', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-07-01']);
        $base = ['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'tipo' => 'carga', 'horas_semanales' => 4.5];
        AsignacionDocente::create(['titulo' => 'Carga personal'] + $base);
        AsignacionDocente::create(['titulo' => 'Carga ajena', 'docente_id' => $otro->id] + $base);
        AsignacionDocente::create(['titulo' => 'Comisión ajena al distributivo', 'tipo' => 'comision'] + $base);
        AsignacionDocente::create(['titulo' => 'Carga anterior', 'periodo_id' => $anterior->id] + $base);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Curso de tutoría', 'nivel' => 'EGB', 'grado' => 8]);
        $paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'B']);
        TutoriaParalelo::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'paralelo_id' => $paralelo->id]);
        AsignacionDocente::create(['titulo' => 'Comisión de otro docente', 'tipo' => 'comision', 'docente_id' => $otro->id] + $base);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']]);
        $this->get(route('docente.distributivo.index', ['docente' => $otro->id]))
            ->assertOk()->assertSee('Carga personal')->assertSee('4,50')->assertSee('Sin registro')
            ->assertDontSee('Carga ajena')->assertDontSee('Carga anterior')->assertSee('Comisión ajena al distributivo')->assertSee('Curso de tutoría')->assertDontSee('Comisión de otro docente');
        $this->get(route('docente.distributivo.index', ['periodo' => $anterior->id]))
            ->assertOk()->assertSee('Carga anterior')->assertDontSee('Carga personal')
            ->assertDontSee('Curso de tutoría')->assertDontSee('Comisión ajena al distributivo')
            ->assertSee('No tiene comisiones asignadas')->assertSee('No tiene tutorías asignadas');
        $this->get(route('docente.distributivo.index', ['periodo' => 99999]))->assertNotFound();
    }

    public function test_requiere_sesion_docente(): void
    {
        $this->get(route('docente.distributivo.index'))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->get(route('docente.distributivo.index'))->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
