<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Database\Schema\Blueprint;
use Tests\TestCase;

class AdminUsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('telefono')->nullable();
            $table->string('celular')->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
        });

        Schema::create('roles', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('codigo');
            $table->string('nombre');
        });

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('usuario_id');
            $table->unsignedSmallInteger('rol_id');
            $table->unsignedBigInteger('institucion_id')->nullable();
        });

        DB::table('personas')->insert([
            'id' => 1,
            'primer_apellido' => 'RODRIGUEZ',
            'segundo_apellido' => 'ARMIJOS',
            'primer_nombre' => 'MARCO',
            'segundo_nombre' => 'ANTONIO',
            'telefono' => '0980456196',
            'celular' => '0980456196',
            'email' => 'marco@example.com',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('roles')->insert([
            ['id' => 1, 'codigo' => 'SUPER', 'nombre' => 'SUPERUSUARIO'],
            ['id' => 2, 'codigo' => 'DOC', 'nombre' => 'DOCENTE'],
        ]);

        if (Schema::hasTable('instituciones')) {
            DB::table('instituciones')->insert([
                'id' => 1,
                'nombre' => 'Institucion Test',
                'estado' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
        }

        $this->withSession([
            'auth_user' => [
                'id' => 999,
                'username' => 'admin.test',
                'role' => 'admin',
                'display_name' => 'Administrador de prueba',
            ],
        ]);
    }

    public function test_it_displays_the_usuario_crud_screen(): void
    {
        $response = $this->get('/dashboard/admin/usuarios');

        $response->assertOk();
        $response->assertSee('Gestion profesional de usuarios');
    }

    public function test_it_creates_a_record_in_tabla_usuarios(): void
    {
        $response = $this->post('/dashboard/admin/usuarios', [
            'persona_id' => 1,
            'institucion_id' => 1,
            'username' => 'mquintero',
            'email' => 'mquintero@example.com',
            'rol_ids' => [1],
            'estado' => 'ACTIVO',
            'ultimo_acceso' => '2026-03-26 12:00:00',
            'password_hash' => 'secret123',
        ]);

        $response->assertRedirect('/dashboard/admin/usuarios');

        $this->assertDatabaseHas('usuarios', [
            'username' => 'mquintero',
            'email' => 'mquintero@example.com',
            'estado' => 'ACTIVO',
        ]);

        $usuarioId = DB::table('usuarios')->where('username', 'mquintero')->value('id');
        $this->assertDatabaseHas('usuario_rol', [
            'usuario_id' => $usuarioId,
            'rol_id' => 1,
        ]);
    }

    public function test_it_updates_a_record_in_tabla_usuarios(): void
    {
        $id = DB::table('usuarios')->insertGetId([
            'persona_id' => 1,
            'username' => 'usuario.base',
            'email' => 'base@example.com',
            'password_hash' => 'base123',
            'estado' => 'OBSERVACION',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => $id,
            'rol_id' => 2,
        ]);

        $response = $this->put("/dashboard/admin/usuarios/{$id}", [
            'persona_id' => 1,
            'institucion_id' => 1,
            'username' => 'usuario.actualizado',
            'email' => 'actualizado@example.com',
            'rol_ids' => [1],
            'estado' => 'ACTIVO',
            'ultimo_acceso' => '2026-03-26 13:00:00',
            'password_hash' => '',
        ]);

        $response->assertRedirect('/dashboard/admin/usuarios');

        $this->assertDatabaseHas('usuarios', [
            'id' => $id,
            'username' => 'usuario.actualizado',
            'estado' => 'ACTIVO',
        ]);

        $this->assertDatabaseHas('usuario_rol', [
            'usuario_id' => $id,
            'rol_id' => 1,
        ]);
    }

    public function test_it_deletes_a_record_from_tabla_usuarios(): void
    {
        $id = DB::table('usuarios')->insertGetId([
            'persona_id' => 1,
            'username' => 'usuario.eliminar',
            'email' => 'eliminar@example.com',
            'password_hash' => 'secret123',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        DB::table('usuario_rol')->insert([
            'usuario_id' => $id,
            'rol_id' => 1,
        ]);

        $response = $this->delete("/dashboard/admin/usuarios/{$id}");

        $response->assertRedirect('/dashboard/admin/usuarios');

        $this->assertDatabaseMissing('usuarios', [
            'id' => $id,
        ]);
    }
}
