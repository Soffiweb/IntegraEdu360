<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class CrearAdministradoresInstitucionesTest extends TestCase
{
    use RefreshDatabase;

    public function test_creates_institutional_administrators_and_preserves_existing_passwords(): void
    {
        $institucion = Institucion::create(['codigo_amie' => '01H00001', 'nombre' => 'Institución de prueba']);
        $this->artisan('instituciones:crear-administradores')->assertSuccessful();
        $usuario = Usuario::where('username', '01H00001')->firstOrFail();
        $this->assertSame($institucion->id, $usuario->institucion_id);
        $this->assertTrue(Hash::check('01H00001', $usuario->password_hash));
        $this->assertSame('ACTIVO', $usuario->estado);
        $this->assertTrue($usuario->roles()->where('codigo', 'ADMIN')->wherePivot('institucion_id', $institucion->id)->exists());
        $usuario->update(['password_hash' => Hash::make('contraseña-nueva')]);
        $this->artisan('instituciones:crear-administradores')->assertSuccessful();
        $this->assertSame(1, Usuario::count());
        $this->assertSame(1, $usuario->roles()->count());
        $this->assertTrue(Hash::check('contraseña-nueva', $usuario->fresh()->password_hash));
        $this->withSession([])->post(route('auth.post'), [
            'username' => '01H00001', 'password' => 'contraseña-nueva', 'role' => 'admin',
        ])->assertRedirect(route('roles.dashboard', 'admin'))
            ->assertSessionHas('auth_user.institucion_id', $institucion->id);
    }

    public function test_dry_run_does_not_create_accounts(): void
    {
        Institucion::create(['codigo_amie' => '01H00001', 'nombre' => 'Institución de prueba']);
        $this->artisan('instituciones:crear-administradores', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame(0, Usuario::count());
    }

    public function test_username_conflicts_abort_without_creating_accounts(): void
    {
        Institucion::create(['codigo_amie' => '01H00001', 'nombre' => 'Institución de prueba']);
        Usuario::create(['username' => '01H00001', 'password_hash' => Hash::make('original')]);
        $this->artisan('instituciones:crear-administradores')->assertFailed();
        $this->assertSame(1, Usuario::count());
        $this->assertTrue(Hash::check('original', Usuario::first()->password_hash));
    }
}
