<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class ConfigurarCredencialesPersonasTest extends TestCase
{
    use RefreshDatabase;

    private function cuenta(string $numero, string $codigo): Usuario
    {
        $persona = Persona::create(['numero_identificacion' => $numero, 'primer_nombre' => 'Prueba', 'primer_apellido' => 'Cuenta']);
        $usuario = Usuario::create(['persona_id' => $persona->id, 'username' => 'cuenta-'.$numero, 'password_hash' => Hash::make('anterior'), 'estado' => 'ACTIVO']);
        $rol = Rol::firstOrCreate(['codigo' => $codigo], ['nombre' => $codigo]);
        $usuario->roles()->attach($rol->id);

        return $usuario;
    }

    public function test_credentials_preserve_roles_and_family_links_and_are_repeatable(): void
    {
        $docente = $this->cuenta('0101234567', 'DOC');
        $representante = $this->cuenta('0101234568', 'PADRE');
        $alumno = $this->cuenta('0101234569', 'EST');
        $alumno->update(['representante_id' => $representante->id]);
        $administrador = $this->cuenta('0101234570', 'ADMIN');
        $demo = $this->cuenta('DEM_ALUMNO_001', 'EST');
        $this->artisan('usuarios:credenciales-identificacion', ['--dry-run' => true])->assertSuccessful();
        $this->assertSame('cuenta-0101234567', $docente->fresh()->username);
        $this->artisan('usuarios:credenciales-identificacion')->assertSuccessful();
        foreach ([$docente, $representante, $alumno] as $usuario) {
            $actual = $usuario->fresh();
            $this->assertSame($actual->persona->numero_identificacion, $actual->username);
            $this->assertTrue(Hash::check($actual->username, $actual->password_hash));
            $this->assertSame($usuario->roles->modelKeys(), $actual->roles->modelKeys());
        }
        $this->assertSame($representante->id, $alumno->fresh()->representante_id);
        $this->assertSame($administrador->username, $administrador->fresh()->username);
        $this->assertSame($demo->username, $demo->fresh()->username);
        $hash = $docente->fresh()->password_hash;
        $this->artisan('usuarios:credenciales-identificacion')->assertSuccessful();
        $this->assertSame($hash, $docente->fresh()->password_hash);
        $this->artisan('usuarios:credenciales-identificacion', ['--incluir-demo' => true])->assertSuccessful();
        $this->assertSame('DEM_ALUMNO_001', $demo->fresh()->username);
        $this->assertTrue(Hash::check('DEM_ALUMNO_001', $demo->fresh()->password_hash));
    }

    public function test_username_collision_prevents_any_changes(): void
    {
        $docente = $this->cuenta('0101234567', 'DOC');
        Usuario::create(['username' => '0101234567', 'password_hash' => Hash::make('otro'), 'estado' => 'ACTIVO']);
        $this->artisan('usuarios:credenciales-identificacion')->assertFailed();
        $this->assertSame($docente->username, $docente->fresh()->username);
    }
}
