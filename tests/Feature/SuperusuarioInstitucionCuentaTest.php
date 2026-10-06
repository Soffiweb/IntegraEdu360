<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Usuario;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Tests\TestCase;

class SuperusuarioInstitucionCuentaTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitucionCatalogosSeeder::class);
        $this->withSession(['auth_user' => ['role' => 'superusuario']]);
    }

    private function payload(): array
    {
        return ['codigo_amie' => ' 01h00001 ', 'nombre' => 'Institución nueva',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO'];
    }

    public function test_creation_generates_associated_administrator_account(): void
    {
        $this->post(route('superusuario.instituciones.store'), $this->payload())
            ->assertRedirect(route('superusuario.instituciones.index'));
        $institucion = Institucion::where('codigo_amie', '01H00001')->firstOrFail();
        $usuario = Usuario::where('username', '01H00001')->firstOrFail();
        $this->assertSame($institucion->id, $usuario->institucion_id);
        $this->assertSame('ACTIVO', $usuario->estado);
        $this->assertTrue(Hash::check('01H00001', $usuario->password_hash));
        $this->assertTrue($usuario->roles()->where('codigo', 'ADMIN')->wherePivot('institucion_id', $institucion->id)->exists());
        $this->withSession([])->post(route('auth.post'), ['username' => '01H00001', 'password' => '01H00001', 'role' => 'admin'])
            ->assertRedirect(route('roles.dashboard', 'admin'))
            ->assertSessionHas('auth_user.institucion_id', $institucion->id);
    }

    public function test_existing_username_rejects_creation_without_overwriting_account(): void
    {
        $usuario = Usuario::create(['username' => '01H00001', 'password_hash' => Hash::make('conservar')]);
        $this->post(route('superusuario.instituciones.store'), $this->payload())->assertSessionHasErrors('codigo_amie');
        $this->assertSame(0, Institucion::count());
        $this->assertSame(1, Usuario::count());
        $this->assertTrue(Hash::check('conservar', $usuario->fresh()->password_hash));
    }

    public function test_failure_to_create_account_rolls_back_institution(): void
    {
        Usuario::creating(fn () => throw new \RuntimeException('Fallo de cuenta de prueba'));
        try {
            $this->post(route('superusuario.instituciones.store'), $this->payload())->assertStatus(500);
            $this->assertSame(0, Institucion::count());
            $this->assertSame(0, Usuario::count());
        } finally {
            Usuario::flushEventListeners();
        }
    }
}
