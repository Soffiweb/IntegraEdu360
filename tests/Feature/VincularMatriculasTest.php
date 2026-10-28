<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Matricula;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class VincularMatriculasTest extends TestCase
{
    use RefreshDatabase;

    public function test_simula_distribuye_por_edad_y_no_duplica_matriculas(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => '2026', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-07-01']);
        $rol = Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Primero', 'grado' => 1, 'nivel' => 'EGB', 'estado' => 'ACTIVO']);
        $a = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A', 'capacidad' => 2, 'estado' => 'ACTIVO']);
        $b = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'B', 'capacidad' => 2, 'estado' => 'ACTIVO']);
        foreach (['2015-01-01', '2020-01-01', '2019-01-01'] as $i => $fecha) {
            $persona = Persona::create(['primer_nombre' => 'Alumno', 'primer_apellido' => (string) $i, 'fecha_nacimiento' => $fecha]);
            $alumno = Usuario::create(['institucion_id' => $institucion->id, 'persona_id' => $persona->id, 'username' => 'alumno'.$i, 'password_hash' => 'test', 'estado' => 'ACTIVO']);
            $alumno->roles()->attach($rol, ['institucion_id' => $institucion->id]);
        }
        $args = ['institucion' => $institucion->id, 'periodo' => $periodo->id];
        $this->artisan('matriculas:vincular', $args)->assertSuccessful();
        $this->assertSame(0, Matricula::count());
        $this->artisan('matriculas:vincular', $args + ['--aplicar' => true])->assertSuccessful();
        $this->assertSame(3, Matricula::count());
        $this->assertSame(2, Matricula::where('paralelo_id', $a->id)->count());
        $this->assertSame(1, Matricula::where('paralelo_id', $b->id)->count());
        $this->assertSame('2015-01-01', Matricula::where('paralelo_id', $b->id)->first()->estudiante->persona->fecha_nacimiento);
        $this->artisan('matriculas:vincular', $args + ['--aplicar' => true])->assertSuccessful();
        $this->assertSame(3, Matricula::count());
    }
}
