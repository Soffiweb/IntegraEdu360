<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class HorarioPersonalTest extends TestCase
{
    use RefreshDatabase;

    public function test_organiza_bloques_y_excluye_horarios_ajenos(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'estado' => 'ACTIVO']);
        $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'docente', 'password_hash' => 'test']);
        $otro = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'otro', 'password_hash' => 'test']);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Octavo', 'nivel' => 'EGB', 'grado' => 8]);
        $asignatura = Asignatura::create(['institucion_id' => $institucion->id, 'nombre' => 'Matemática']);
        $base = ['usuario_id' => $docente->id, 'curso_id' => $curso->id, 'asignatura_id' => $asignatura->id, 'dia_semana' => 1, 'hora_inicio' => '07:00:00', 'hora_fin' => '07:40:00'];
        DB::table('horario_docente')->insert(['aula' => 'Aula personal'] + $base);
        DB::table('horario_docente')->insert(['aula' => 'Aula ajena', 'usuario_id' => $otro->id] + $base);
        DB::table('horario_docente')->insert(['aula' => 'Clase sábado', 'dia_semana' => 6, 'hora_inicio' => '08:00:00', 'hora_fin' => '08:40:00'] + $base);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']])
            ->get(route('docente.horario.index', ['usuario' => $otro->id]))
            ->assertOk()->assertSee('Horario Personal')->assertSee('Matemática')->assertSee('Octavo')
            ->assertSee('Aula personal')->assertSee('Sábado')->assertSee('Clase sábado')
            ->assertSeeInOrder(['07:00', '08:00'])->assertDontSee('Aula ajena');
        $this->withSession(['auth_user' => ['id' => $otro->id, 'role' => 'docente']])
            ->get(route('docente.distributivo.index'))->assertOk()->assertSee(route('docente.horario.index'));
        DB::table('horario_docente')->where('usuario_id', $otro->id)->delete();
        $this->get(route('docente.horario.index'))->assertOk()->assertSee('Sin horario asignado');
    }

    public function test_requiere_rol_docente(): void
    {
        $this->get(route('docente.horario.index'))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->get(route('docente.horario.index'))->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
