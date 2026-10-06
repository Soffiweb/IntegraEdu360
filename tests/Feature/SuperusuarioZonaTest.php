<?php

namespace Tests\Feature;

use App\Models\Zona;
use Database\Seeders\ZonaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperusuarioZonaTest extends TestCase
{
    use RefreshDatabase;

    public function test_superusuario_can_view_the_catalog_and_dashboard_link(): void
    {
        $this->seed(ZonaSeeder::class);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.zonas.index'))
            ->assertOk()
            ->assertViewIs('dashboard-role')
            ->assertViewHas('dashboardSection', 'zonas')
            ->assertSee('app-shell')
            ->assertSee('Zona 1')
            ->assertSee('Zona 9')
            ->assertSee('Distrito Metropolitano de Quito.');

        $this->get(route('roles.dashboard', 'superusuario'))
            ->assertOk()
            ->assertSee(route('superusuario.zonas.index'), false);
    }

    public function test_superusuario_can_edit_a_zone_inside_the_dashboard(): void
    {
        $this->seed(ZonaSeeder::class);
        $zona = Zona::where('codigo', 1)->firstOrFail();
        $editUrl = route('superusuario.zonas.index', ['edit' => $zona->id]);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get($editUrl)
            ->assertOk()
            ->assertViewIs('dashboard-role')
            ->assertSee('Modificar zona')
            ->assertSee(route('superusuario.zonas.update', $zona), false);

        $this->from($editUrl)->put(route('superusuario.zonas.update', $zona), [
            'codigo' => 1, 'nombre' => 'Zona actualizada', 'cobertura' => 'Cobertura actualizada',
        ])->assertRedirect(route('superusuario.zonas.index'))->assertSessionHas('success');

        $this->assertDatabaseHas('zonas', [
            'id' => $zona->id, 'codigo' => 1, 'nombre' => 'Zona actualizada', 'cobertura' => 'Cobertura actualizada',
        ]);
    }

    public function test_zone_update_rejects_duplicate_codes_and_invalid_fields(): void
    {
        $this->seed(ZonaSeeder::class);
        $zona = Zona::where('codigo', 1)->firstOrFail();
        $editUrl = route('superusuario.zonas.index', ['edit' => $zona->id]);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->from($editUrl)->put(route('superusuario.zonas.update', $zona), [
                'codigo' => 2, 'nombre' => '', 'cobertura' => '',
            ])->assertRedirect($editUrl)->assertSessionHasErrors(['codigo', 'nombre', 'cobertura']);

        $this->assertSame(1, $zona->fresh()->codigo);
        $this->get($editUrl)->assertOk()->assertSee('aria-invalid="true"', false);
    }

    public function test_other_roles_cannot_modify_zones(): void
    {
        $this->seed(ZonaSeeder::class);
        $zona = Zona::firstOrFail();
        $originalName = $zona->nombre;

        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->put(route('superusuario.zonas.update', $zona), [
                'codigo' => 1, 'nombre' => 'Cambio sin permiso', 'cobertura' => 'Cambio',
            ])->assertRedirect(route('roles.dashboard', 'admin'));

        $this->assertSame($originalName, $zona->fresh()->nombre);
    }

    public function test_guest_and_other_roles_cannot_view_zonas(): void
    {
        $this->get(route('superusuario.zonas.index'))->assertRedirect();

        $this->withSession(['auth_user' => ['role' => 'admin']])
            ->get(route('superusuario.zonas.index'))
            ->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
