<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_is_scoped_to_administrators_institution(): void
    {
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $institucion = Institucion::create(['nombre' => 'Institución propia']);
        $otra = Institucion::create(['nombre' => 'Institución ajena']);
        $usuario = Usuario::create(['username' => 'docente-propio', 'institucion_id' => $institucion->id, 'password_hash' => 'test', 'ultimo_acceso' => now()]);
        $usuario->roles()->attach($rol);
        $ajeno = Usuario::create(['username' => 'usuario-ajeno', 'institucion_id' => $otra->id, 'password_hash' => 'test', 'ultimo_acceso' => now()]);
        $ajeno->roles()->attach($rol);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Curso propio', 'nivel' => 'BASICA', 'grado' => 1, 'estado' => 'ACTIVO']);
        $cursoAjeno = Curso::create(['institucion_id' => $otra->id, 'nombre' => 'Curso ajeno', 'nivel' => 'BASICA', 'grado' => 2]);
        Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A', 'capacidad' => 30, 'estado' => 'ACTIVO']);
        Paralelo::create(['curso_id' => $cursoAjeno->id, 'letra' => 'B', 'capacidad' => 99, 'estado' => 'ACTIVO']);
        $asignatura = Asignatura::create(['institucion_id' => $institucion->id, 'nombre' => 'Matemática', 'codigo' => 'MAT', 'estado' => 'ACTIVO']);
        PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Período propio', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31', 'es_activo' => true]);
        PeriodoAcademico::create(['institucion_id' => $otra->id, 'nombre' => 'Período ajeno', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31', 'es_activo' => true]);
        DB::table('docente_asignatura_curso')->insert([
            ['usuario_id' => $usuario->id, 'asignatura_id' => $asignatura->id, 'curso_id' => $curso->id, 'horas_asignadas' => 4],
            ['usuario_id' => $ajeno->id, 'asignatura_id' => $asignatura->id, 'curso_id' => $curso->id, 'horas_asignadas' => 50],
        ]);

        $this->withSession(['auth_user' => ['role' => 'admin', 'id' => $usuario->id, 'institucion_id' => $otra->id]]);
        foreach ([route('admin.resumen.index'), route('roles.dashboard', 'admin')] as $url) {
            $this->get($url)->assertOk()->assertViewHas('resumen', fn ($datos) => $datos['usuarios'] === 1
                && $datos['porRol']['docente']['total'] === 1 && $datos['cursos'] === 1
                && $datos['paralelos'] === 1 && $datos['capacidad'] === 30
                && $datos['asignaturas'] === 1 && $datos['asignaciones'] === 1 && $datos['horasAsignadas'] === 4)
                ->assertSee('Período propio')->assertSee('docente-propio')
                ->assertDontSee('usuario-ajeno')->assertDontSee('Período ajeno')
                ->assertSeeInOrder(['Resumen Ejecutivo', 'Datos institucionales']);
        }
    }

    public function test_unassigned_account_does_not_receive_global_statistics(): void
    {
        Institucion::create(['nombre' => 'Institución ajena']);
        $this->withSession(['auth_user' => ['role' => 'admin']])->get(route('admin.resumen.index'))
            ->assertOk()->assertViewHas('resumen', null)->assertSee('No hay una institución asociada');
    }

    public function test_empty_institution_summary_and_access_protection(): void
    {
        $institucion = Institucion::create(['nombre' => 'Institución vacía']);
        $this->get(route('admin.resumen.index'))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'docente']])->get(route('admin.resumen.index'))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $institucion->id]])
            ->get(route('admin.resumen.index'))->assertOk()
            ->assertViewHas('resumen', fn ($datos) => $datos['usuarios'] === 0 && $datos['cursos'] === 0)
            ->assertSee('No hay un período académico activo.')
            ->assertSee('No hay accesos registrados en esta institución.');
    }
}
