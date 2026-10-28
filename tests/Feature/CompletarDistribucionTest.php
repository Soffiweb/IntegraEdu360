<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\Usuario;
use App\Services\Directivo\CompletarDistribucion;
use App\Services\Directivo\PropuestaDistribucion;
use Database\Seeders\AsignaturaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class CompletarDistribucionTest extends TestCase
{
    use RefreshDatabase;

    public function test_completa_propuesta_curricular_comisiones_y_simula_sin_guardar(): void
    {
        $institucion = Institucion::create(['nombre' => 'Propuesta', 'estado' => 'ACTIVO']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31']);
        Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        foreach (['EGB', 'BGU', 'BT-GFC'] as $oferta) {
            $especialidad = $oferta === 'EGB' ? null : Especialidad::create(['institucion_id' => $institucion->id, 'codigo' => $oferta, 'nombre' => $oferta]);
            foreach ($oferta === 'EGB' ? range(1, 10) : range(11, 13) as $grado) {
                $curso = Curso::create(['institucion_id' => $institucion->id, 'especialidad_id' => $especialidad?->id, 'nombre' => 'Curso '.$grado, 'nivel' => 'EGB', 'grado' => $grado, 'estado' => 'ACTIVO']);
                Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A', 'estado' => 'ACTIVO']);
            }
        }
        app(AsignaturaSeeder::class)->run($institucion->id);
        $servicio = app(PropuestaDistribucion::class);
        $simulacion = $servicio->ejecutar($institucion->id, $periodo->id);
        $this->assertEquals(525, $simulacion['horas_pedagogicas']);
        $this->assertSame(0, Usuario::count());
        $this->assertSame(0, AsignacionDocente::count());
        $this->assertSame(77, Asignatura::whereNull('horas_semana')->count());
        $resultado = $servicio->ejecutar($institucion->id, $periodo->id, true);
        $this->assertSame([], $resultado['pendientes']);
        $this->assertEquals(525, $resultado['horas_pedagogicas']);
        $this->assertLessThanOrEqual(25, $resultado['maximo_docente']);
        $this->assertSame(Usuario::count(), AsignacionDocente::where('tipo', 'comision')->count());
        $this->assertEquals(0, AsignacionDocente::where('tipo', 'comision')->sum('horas_semanales'));
        $repeticion = $servicio->ejecutar($institucion->id, $periodo->id, true);
        $this->assertSame(0, $repeticion['asignaciones_nuevas']);
        $this->assertSame(0, $repeticion['comisiones_nuevas']);
        $this->assertSame(0, $repeticion['docentes_nuevos']);
    }

    public function test_distribuye_sin_exceder_limite_conserva_comisiones_y_es_idempotente(): void
    {
        $institucion = Institucion::create(['nombre' => 'Local', 'estado' => 'ACTIVO']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-01-01', 'fecha_fin' => '2026-12-31']);
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'existente', 'password_hash' => 'test', 'estado' => 'ACTIVO']);
        $docente->roles()->attach($rol, ['institucion_id' => $institucion->id]);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Primero', 'nivel' => 'EGB', 'grado' => 1, 'estado' => 'ACTIVO']);
        foreach (['A', 'B'] as $letra) {
            Paralelo::create(['curso_id' => $curso->id, 'letra' => $letra, 'estado' => 'ACTIVO']);
        }
        $asignatura = Asignatura::create(['institucion_id' => $institucion->id, 'nombre' => 'Integrador', 'horas_semana' => 25, 'estado' => 'ACTIVO']);
        $pendiente = Asignatura::create(['institucion_id' => $institucion->id, 'nombre' => 'ECA', 'estado' => 'ACTIVO']);
        $curso->asignaturas()->attach([$asignatura->id, $pendiente->id]);
        AsignacionDocente::create(['institucion_id' => $institucion->id, 'periodo_id' => $periodo->id, 'docente_id' => $docente->id, 'tipo' => 'comision', 'titulo' => 'Convivencia', 'horas_semanales' => 2]);
        $servicio = app(CompletarDistribucion::class);
        $simulacion = $servicio->ejecutar($institucion->id, $periodo->id);
        $this->assertSame(1, $simulacion['docentes_nuevos']);
        $this->assertSame(1, Usuario::count());
        $resultado = $servicio->ejecutar($institucion->id, $periodo->id, true);
        $this->assertEquals(50, $resultado['horas_pedagogicas']);
        $this->assertEquals(25, $resultado['maximo_docente']);
        $this->assertCount(1, $resultado['pendientes']);
        $this->assertSame('Docente NUEVO 01', Usuario::where('id', '!=', $docente->id)->with('persona')->sole()->nombre_completo);
        $this->assertSame(2, AsignacionDocente::where('tipo', 'carga')->count());
        $repeticion = $servicio->ejecutar($institucion->id, $periodo->id, true);
        $this->assertSame(0, $repeticion['asignaciones_nuevas']);
        $this->assertSame(0, $repeticion['docentes_nuevos']);
        $this->assertSame(3, AsignacionDocente::count());
    }
}
