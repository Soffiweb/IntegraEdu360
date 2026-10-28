<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminParaleloTutorTest extends TestCase
{
    use RefreshDatabase;

    public function test_tutor_can_be_assigned_displayed_and_removed_and_must_be_a_local_teacher(): void
    {
        $this->seed(InstitucionCatalogosSeeder::class);
        $institucion = Institucion::create([
            'nombre' => 'Colegio prueba', 'codigo_amie' => '01H00001',
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
        ]);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Primero', 'nivel' => 'PREPARATORIA', 'grado' => 1]);
        $rol = Rol::create(['codigo' => '04', 'nombre' => 'Docente']);
        $tutor = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'tutor.prueba', 'password_hash' => 'clave', 'estado' => 'ACTIVO']);
        $tutor->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $institucion->id]]);
        $payload = ['curso_id' => $curso->id, 'letra' => 'A', 'capacidad' => 35, 'estado' => 'ACTIVO', 'tutor_id' => $tutor->id];
        $this->post(route('admin.paralelos.store'), $payload)->assertSessionHasNoErrors();
        $paralelo = Paralelo::sole();
        $this->assertSame($tutor->id, $paralelo->tutor->id);
        $this->get(route('admin.paralelos.index', ['edit' => $paralelo->id]))
            ->assertOk()->assertSee('Tutor')->assertSee('tutor.prueba')->assertSee('edit_tutor_id');
        $this->post(route('admin.paralelos.store'), array_replace($payload, ['letra' => 'B']))->assertSessionHasErrors('tutor_id');
        $this->assertSame(1, Paralelo::count());
        $this->put(route('admin.paralelos.update', $paralelo), $payload)->assertSessionHasNoErrors();
        $this->put(route('admin.paralelos.update', $paralelo), array_replace($payload, ['tutor_id' => null]))->assertSessionHasNoErrors();
        $this->assertNull($paralelo->fresh()->tutor_id);
        $tutor->roles()->detach();
        $this->put(route('admin.paralelos.update', $paralelo), $payload)->assertSessionHasErrors('tutor_id');
        $tutor->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);
        $otra = $institucion->replicate();
        $otra->codigo_amie = '01H00002';
        $otra->save();
        $tutor->update(['institucion_id' => $otra->id]);
        $this->put(route('admin.paralelos.update', $paralelo), $payload)->assertSessionHasErrors('tutor_id');
        $this->assertNull($paralelo->fresh()->tutor_id);
    }
}
