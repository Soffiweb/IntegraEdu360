<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class AdminUsuarioCrudTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

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

        DB::table('usuarios')->insert([
            'id' => 999,
            'username' => 'admin.test',
            'password_hash' => 'test',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

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

    public function test_institutional_admin_sees_institutional_roles_and_only_own_users(): void
    {
        $this->prepareInstitutionalUsers();

        $this->get('/dashboard/admin/usuarios?institucion=2')->assertOk()
            ->assertViewHas('usuarios', fn ($usuarios) => $usuarios->pluck('username')->sort()->values()->all()
                === ['alumno.local', 'docente.local', 'representante.local'])
            ->assertViewHas('roles', fn ($roles) => $roles->pluck('codigo')->sort()->values()->all()
                === ['ADMIN', 'DOC', 'EST', 'PADRE'])
            ->assertViewHas('metricas', fn ($metricas) => $metricas['total'] === 3);

        foreach ([101, 102, 103] as $id) {
            $this->get('/dashboard/admin/usuarios?edit='.$id)->assertOk();
        }
    }

    public function test_institutional_admin_cannot_manage_other_institutions_or_superusers(): void
    {
        $this->prepareInstitutionalUsers();

        foreach ([104, 105, 106] as $id) {
            $this->get('/dashboard/admin/usuarios?edit='.$id)->assertNotFound();
            $this->put('/dashboard/admin/usuarios/'.$id, [])->assertNotFound();
            $this->delete('/dashboard/admin/usuarios/'.$id)->assertNotFound();
            $this->assertDatabaseHas('usuarios', ['id' => $id]);
        }

        $this->post('/dashboard/admin/usuarios', [
            'institucion_id' => 1,
            'username' => 'super.no.permitido',
            'rol_ids' => [1],
            'estado' => 'ACTIVO',
            'password_hash' => 'secret123',
        ])->assertStatus(422);

        $this->assertDatabaseMissing('usuarios', ['username' => 'super.no.permitido']);
    }

    public function test_edit_form_has_valid_configuration_and_opens_selected_user(): void
    {
        $this->prepareInstitutionalUsers();
        $response = $this->get('/dashboard/admin/usuarios?edit=101')->assertOk();
        $response->assertSee('id="edit-user-modal" open', false);
        preg_match('/<script type="application\/json" id="__ie_cfg">(.*?)<\/script>/s', $response->getContent(), $matches);
        $this->assertNotEmpty($matches);
        $config = json_decode($matches[1], true, 512, JSON_THROW_ON_ERROR);
        $this->assertTrue($config['autoOpenEdit']);
        $this->assertFalse($config['autoOpenCreate']);
        $this->assertSame('1', $config['fixedInstitucionId']);
    }

    public function test_institutional_admin_can_save_changes_to_a_teacher(): void
    {
        $this->prepareInstitutionalUsers();

        $this->put('/dashboard/admin/usuarios/101', [
            'persona_id' => null,
            'institucion_id' => 1,
            'username' => 'docente.actualizado',
            'email' => 'docente.actualizado@example.com',
            'rol_ids' => [2],
            'estado' => 'OBSERVACION',
            'password_hash' => '',
        ])->assertRedirect('/dashboard/admin/usuarios');

        $this->assertDatabaseHas('usuarios', [
            'id' => 101,
            'institucion_id' => 1,
            'username' => 'docente.actualizado',
            'email' => 'docente.actualizado@example.com',
            'estado' => 'OBSERVACION',
            'password_hash' => 'test',
        ]);
        $this->assertDatabaseHas('usuario_rol', [
            'usuario_id' => 101,
            'rol_id' => 2,
            'institucion_id' => 1,
        ]);
    }

    private function prepareInstitutionalUsers(): void
    {
        DB::table('roles')->insert([
            ['id' => 3, 'codigo' => 'ADMIN', 'nombre' => 'Administrador'],
            ['id' => 4, 'codigo' => 'EST', 'nombre' => 'Estudiante'],
            ['id' => 5, 'codigo' => 'PADRE', 'nombre' => 'Padre o Tutor'],
        ]);
        DB::table('instituciones')->insert([
            'id' => 2,
            'nombre' => 'Otra institucion',
            'estado' => 'ACTIVO',
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        foreach ([
            [101, 'docente.local', 1, [2]],
            [102, 'alumno.local', 1, [4]],
            [103, 'representante.local', 1, [5]],
            [104, 'docente.otra', 2, [2]],
            [105, 'super.local', 1, [1]],
            [106, 'super.docente.local', 1, [1, 2]],
        ] as [$id, $username, $institucionId, $rolIds]) {
            DB::table('usuarios')->insert([
                'id' => $id,
                'institucion_id' => $institucionId,
                'username' => $username,
                'password_hash' => 'test',
                'estado' => 'ACTIVO',
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            foreach ($rolIds as $rolId) {
                DB::table('usuario_rol')->insert([
                    'usuario_id' => $id,
                    'rol_id' => $rolId,
                    'institucion_id' => $institucionId,
                ]);
            }
        }

        $this->withSession(['auth_user' => [
            'id' => 999,
            'username' => 'admin.test',
            'role' => 'admin',
            'institucion_id' => 1,
            'display_name' => 'Administrador de prueba',
        ]]);
    }
}
