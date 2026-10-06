<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class SuperusuarioUsuarioTest extends TestCase
{
    use RefreshDatabase;

    public function test_search_works_without_personas_table_even_with_a_persona_id(): void
    {
        Schema::drop('personas');
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $institucion = Institucion::create(['codigo_amie' => '01H00001', 'nombre' => '27 de Febrero']);
        $usuario = Usuario::create(['username' => '01H00001', 'persona_id' => 123,
            'institucion_id' => $institucion->id, 'password_hash' => 'test']);
        $usuario->roles()->attach($rol);

        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        foreach (['27 de febrero', '01H00001'] as $busqueda) {
            $this->get(route('superusuario.usuarios.index', ['q' => $busqueda]))
                ->assertOk()
                ->assertSee('01H00001')
                ->assertSee('27 de Febrero')
                ->assertSee('Sin persona vinculada')
                ->assertViewHas('usuariosAdministradores', fn ($usuarios) => $usuarios->total() === 1);
        }
        $this->get(route('superusuario.usuarios.index'))->assertOk()->assertSee('01H00001');
        $this->get(route('superusuario.usuarios.index', ['q' => 'inexistente']))
            ->assertOk()->assertSee('No se encontraron administradores con los filtros seleccionados.');
    }

    public function test_administrator_can_be_edited_blocked_and_unblocked(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $usuario = Usuario::create(['username' => 'admin-prueba', 'password_hash' => Hash::make('original123')]);
        $usuario->roles()->attach($rol);
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        $this->get(route('superusuario.usuarios.index', ['edit' => $usuario->id]))
            ->assertOk()->assertSee('Editar usuario')->assertSee('Bloquear');
        $hash = $usuario->password_hash;
        $this->put(route('superusuario.usuarios.update', $usuario), ['username' => 'admin-editado', 'email' => 'admin@example.test'])
            ->assertRedirect(route('superusuario.usuarios.index'));
        $this->assertSame('admin-editado', $usuario->fresh()->username);
        $this->assertSame($hash, $usuario->fresh()->password_hash);
        $this->put(route('superusuario.usuarios.update', $usuario), ['username' => 'admin-editado', 'password' => 'nueva12345', 'password_confirmation' => 'nueva12345'])
            ->assertRedirect();
        $this->assertTrue(Hash::check('nueva12345', $usuario->fresh()->password_hash));
        $this->patch(route('superusuario.usuarios.bloquear', $usuario))->assertRedirect();
        $this->assertSame('INACTIVO', $usuario->fresh()->estado);
        $this->get(route('superusuario.usuarios.index'))->assertSee('Desbloquear');
        $this->patch(route('superusuario.usuarios.desbloquear', $usuario))->assertRedirect();
        $this->assertSame('ACTIVO', $usuario->fresh()->estado);
    }

    public function test_blocked_accounts_cannot_login_or_use_existing_sessions(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $usuario = Usuario::create(['username' => 'bloqueado', 'password_hash' => Hash::make('password123'), 'estado' => 'INACTIVO']);
        $usuario->roles()->attach($rol);
        $this->post(route('auth.post'), ['username' => 'bloqueado', 'password' => 'password123', 'role' => 'admin'])
            ->assertRedirect(route('roles.access', 'admin'))->assertSessionMissing('auth_user');
        foreach ([route('roles.dashboard', 'admin'), route('admin.usuarios.index')] as $url) {
            $this->withSession(['auth_user' => ['id' => $usuario->id, 'role' => 'admin']])
                ->get($url)->assertRedirect()->assertSessionMissing('auth_user');
        }
    }

    public function test_other_roles_and_non_administrator_targets_are_protected(): void
    {
        $usuario = Usuario::create(['username' => 'otro', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->patch(route('superusuario.usuarios.bloquear', $usuario))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        $this->patch(route('superusuario.usuarios.bloquear', $usuario))->assertNotFound();
        $this->put(route('superusuario.usuarios.update', $usuario), ['username' => 'cambiado'])->assertNotFound();
        $this->assertSame('ACTIVO', $usuario->fresh()->estado);
        $this->assertSame('otro', $usuario->fresh()->username);
    }

    protected function setUp(): void
    {
        parent::setUp();
        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('primer_nombre');
            $table->string('segundo_nombre')->nullable();
            $table->string('primer_apellido');
            $table->string('segundo_apellido')->nullable();
            $table->timestamps();
        });
    }

    public function test_results_are_paginated_by_twenty_and_keep_filters(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        for ($i = 1; $i <= 21; $i++) {
            $usuario = Usuario::create(['username' => sprintf('amie-%02d', $i), 'password_hash' => 'test']);
            $usuario->roles()->attach($rol);
        }
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.usuarios.index', ['q' => 'amie', 'estado' => 'ACTIVO']))
            ->assertOk()
            ->assertViewHas('usuariosAdministradores', fn ($usuarios) => $usuarios->count() === 20
                && $usuarios->total() === 21 && str_contains($usuarios->nextPageUrl(), 'q=amie')
                && str_contains($usuarios->nextPageUrl(), 'estado=ACTIVO'))
            ->assertSee('Página 1 de 2')
            ->assertDontSee('amie-21');
        $this->get(route('superusuario.usuarios.index', ['q' => 'amie', 'estado' => 'ACTIVO', 'page' => 2]))
            ->assertOk()
            ->assertViewHas('usuariosAdministradores', fn ($usuarios) => $usuarios->count() === 1)
            ->assertSee('amie-21');
    }

    public function test_searches_account_person_and_institution_and_combines_status(): void
    {
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $institucion = Institucion::create(['codigo_amie' => '01H12345', 'nombre' => 'Colegio Horizonte']);
        $persona = Persona::create(['primer_nombre' => 'Mariana', 'primer_apellido' => 'Torres']);
        $usuario = Usuario::create(['username' => 'cuenta-prueba', 'email' => 'contacto@example.test',
            'persona_id' => $persona->id, 'institucion_id' => $institucion->id, 'password_hash' => 'test', 'estado' => 'INACTIVO']);
        $usuario->roles()->attach($rol);
        Usuario::create(['username' => 'cuenta-sin-rol', 'email' => 'otro@example.test', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        foreach (['CUENTA', 'contacto', 'Mariana', 'Torres', 'Horizonte', '01H12345'] as $busqueda) {
            $this->get(route('superusuario.usuarios.index', ['q' => $busqueda, 'estado' => 'INACTIVO']))
                ->assertOk()
                ->assertViewHas('usuariosAdministradores', fn ($usuarios) => $usuarios->total() === 1)
                ->assertSee('cuenta-prueba')
                ->assertDontSee('cuenta-sin-rol');
        }
        $this->get(route('superusuario.usuarios.index', ['q' => 'cuenta', 'estado' => 'ACTIVO']))
            ->assertOk()
            ->assertSee('No se encontraron administradores con los filtros seleccionados.');
    }

    public function test_listing_includes_only_administrators_without_duplicates(): void
    {
        $admin = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $alias = Rol::create(['codigo' => 'ADM', 'nombre' => 'Administrador alternativo']);
        $docente = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $usuario = Usuario::create(['username' => 'administrador.principal', 'password_hash' => 'test']);
        $usuario->roles()->attach([$admin->id, $alias->id, $docente->id]);
        $inactivo = Usuario::create(['username' => 'administrador.inactivo', 'password_hash' => 'test', 'estado' => 'INACTIVO']);
        $inactivo->roles()->attach($alias);
        $otro = Usuario::create(['username' => 'docente.excluido', 'password_hash' => 'test']);
        $otro->roles()->attach($docente);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.usuarios.index'))
            ->assertOk()
            ->assertViewHas('dashboardSection', 'usuarios')
            ->assertViewHas('usuariosAdministradores', fn ($usuarios) => $usuarios->count() === 2)
            ->assertSee('administrador.principal')
            ->assertSee('administrador.inactivo')
            ->assertDontSee('docente.excluido')
            ->assertSee('Sin institución asignada');

        $this->get(route('roles.dashboard', 'superusuario'))
            ->assertOk()
            ->assertSeeInOrder(['Roles de Usuario', 'Gestión de Usuarios'])
            ->assertSee(route('superusuario.usuarios.index'), false);
    }

    public function test_empty_listing_displays_an_empty_state(): void
    {
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.usuarios.index'))
            ->assertOk()
            ->assertSee('No hay usuarios con rol de administrador registrados.');
    }

    public function test_guests_and_other_roles_cannot_consult_administrators(): void
    {
        $this->get(route('superusuario.usuarios.index'))->assertRedirect();
        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->get(route('superusuario.usuarios.index'))
            ->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
