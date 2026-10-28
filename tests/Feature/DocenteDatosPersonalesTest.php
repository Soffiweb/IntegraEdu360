<?php

namespace Tests\Feature;

use App\Models\Persona;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DocenteDatosPersonalesTest extends TestCase
{
    use RefreshDatabase;

    public function test_actualiza_solo_sus_datos_y_muestra_opcion_despues_del_resumen(): void
    {
        $persona = Persona::create(['primer_nombre' => 'Ana', 'primer_apellido' => 'Pérez']);
        $otra = Persona::create(['primer_nombre' => 'Otra', 'primer_apellido' => 'Persona']);
        $docente = Usuario::create(['username' => 'docente', 'password_hash' => 'test', 'persona_id' => $persona->id]);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']])
            ->get(route('docente.datos.edit'))->assertOk()
            ->assertSeeInOrder(['Resumen ejecutivo', 'Actualizar datos', 'Distributivo Personal'])
            ->assertSee('Ana');
        $this->put(route('docente.datos.update'), [
            'numero_identificacion' => '0912345678', 'primer_nombre' => 'Ana María',
            'primer_apellido' => 'Pérez', 'celular' => '0991234567',
            'persona_id' => $otra->id, 'estado' => 'INACTIVO', 'institucion_id' => 999,
        ])->assertRedirect(route('docente.datos.edit'))->assertSessionHas('success');
        $this->assertSame('Ana María', $persona->fresh()->primer_nombre);
        $this->assertSame('Otra', $otra->fresh()->primer_nombre);
        $this->assertSame($persona->id, $docente->fresh()->persona_id);
        $this->assertSame('ACTIVO', $docente->fresh()->estado);
    }

    public function test_valida_identificacion_unica_y_datos_obligatorios(): void
    {
        Persona::create(['primer_nombre' => 'Otra', 'primer_apellido' => 'Persona', 'numero_identificacion' => '12345']);
        $docente = Usuario::create(['username' => 'docente', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $docente->id, 'role' => 'docente']])
            ->put(route('docente.datos.update'), ['numero_identificacion' => '12345', 'email' => 'incorrecto'])
            ->assertSessionHasErrors(['numero_identificacion', 'primer_nombre', 'primer_apellido', 'email']);
        $this->assertNull($docente->fresh()->persona_id);
        $this->put(route('docente.datos.update'), ['numero_identificacion' => '67890', 'primer_nombre' => 'Nuevo', 'primer_apellido' => 'Docente'])
            ->assertSessionHasNoErrors();
        $this->assertSame('67890', $docente->fresh()->persona->numero_identificacion);
    }

    public function test_restringe_acceso_al_rol_docente(): void
    {
        $this->get(route('docente.datos.edit'))->assertRedirect(route('roles.access', 'docente'));
        $this->put(route('docente.datos.update'), [])->assertRedirect();
        $admin = Usuario::create(['username' => 'admin', 'password_hash' => 'test']);
        $this->withSession(['auth_user' => ['id' => $admin->id, 'role' => 'admin']])
            ->put(route('docente.datos.update'), [])->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
