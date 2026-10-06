<?php

namespace Tests\Feature;

use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\RolSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperusuarioRolTest extends TestCase
{
    use RefreshDatabase;

    public function test_assigned_roles_cannot_be_deleted(): void
    {
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $usuario = Usuario::create(['username' => 'docente', 'password_hash' => 'test']);
        $usuario->roles()->attach($rol);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->delete(route('superusuario.roles.destroy', $rol))
            ->assertRedirect(route('superusuario.roles.index'))
            ->assertSessionHasErrors('rol');

        $this->assertModelExists($rol);
        $this->assertTrue($usuario->roles()->whereKey($rol->id)->exists());
    }

    public function test_superusuario_can_create_edit_and_delete_an_unassigned_role(): void
    {
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        $this->get(route('superusuario.roles.index', ['create' => 1]))->assertOk()->assertSee('Agregar Nuevo');
        $this->post(route('superusuario.roles.store'), ['codigo' => 'NUEVO', 'nombre' => 'Rol nuevo'])
            ->assertRedirect(route('superusuario.roles.index'));
        $rol = Rol::where('codigo', 'NUEVO')->firstOrFail();
        $this->get(route('superusuario.roles.index', ['edit' => $rol->id]))->assertOk()->assertSee('Editar rol');
        $this->put(route('superusuario.roles.update', $rol), ['codigo' => 'NUEVO', 'nombre' => 'Rol actualizado'])
            ->assertRedirect(route('superusuario.roles.index'));
        $this->assertSame('Rol actualizado', $rol->fresh()->nombre);
        $this->delete(route('superusuario.roles.destroy', $rol))->assertRedirect(route('superusuario.roles.index'));
        $this->assertModelMissing($rol);
    }

    public function test_duplicate_codes_and_changes_to_existing_codes_are_rejected(): void
    {
        $rol = Rol::create(['codigo' => 'SUPER', 'nombre' => 'Superusuario']);
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
        $this->post(route('superusuario.roles.store'), ['codigo' => 'SUPER', 'nombre' => 'Duplicado'])
            ->assertSessionHasErrors('codigo');
        $this->put(route('superusuario.roles.update', $rol), ['codigo' => 'OTRO', 'nombre' => 'Cambio'])
            ->assertSessionHasErrors('codigo');
        $this->assertSame('SUPER', $rol->fresh()->codigo);
    }

    public function test_other_roles_cannot_write_to_the_catalog(): void
    {
        $rol = Rol::create(['codigo' => 'NUEVO', 'nombre' => 'Rol nuevo']);
        $this->withSession(['auth_user' => ['role' => 'admin']]);
        $this->post(route('superusuario.roles.store'), ['codigo' => 'OTRO', 'nombre' => 'Otro'])
            ->assertRedirect(route('roles.dashboard', 'admin'));
        $this->put(route('superusuario.roles.update', $rol), ['codigo' => 'NUEVO', 'nombre' => 'Cambio'])
            ->assertRedirect(route('roles.dashboard', 'admin'));
        $this->delete(route('superusuario.roles.destroy', $rol))->assertRedirect(route('roles.dashboard', 'admin'));
        $this->assertSame('Rol nuevo', $rol->fresh()->nombre);
    }

    public function test_superusuario_can_consult_roles_and_find_the_navigation_link(): void
    {
        $this->seed(RolSeeder::class);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.roles.index'))
            ->assertOk()
            ->assertViewIs('dashboard-role')
            ->assertViewHas('dashboardSection', 'roles')
            ->assertViewHas('rolesUsuario', fn ($roles) => $roles->count() === 12)
            ->assertSee('Roles de Usuario')
            ->assertSee('ASESORACADEMICO')
            ->assertSee('Asesor Académico');

        $this->get(route('roles.dashboard', 'superusuario'))
            ->assertOk()
            ->assertSee(route('superusuario.roles.index'), false);
    }

    public function test_empty_catalog_displays_an_empty_state(): void
    {
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.roles.index'))
            ->assertOk()
            ->assertSee('No hay roles de usuario registrados.');
    }

    public function test_guest_and_other_roles_cannot_consult_roles(): void
    {
        $this->get(route('superusuario.roles.index'))->assertRedirect();

        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->get(route('superusuario.roles.index'))
            ->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
