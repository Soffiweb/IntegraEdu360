<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\Zona;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\DistritoSeeder;
use Database\Seeders\ZonaSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class SuperusuarioDistritoTest extends TestCase
{
    use RefreshDatabase;

    private function seedCatalog(): void
    {
        $this->seed([ZonaSeeder::class, DistritoSeeder::class]);
    }

    public function test_official_catalog_has_140_districts_related_to_the_correct_zones_and_is_repeatable(): void
    {
        $this->seedCatalog();
        $this->assertDatabaseCount('distritos', 140);
        $this->assertSame([16, 8, 19, 15, 25, 17, 19, 12, 9], Zona::orderBy('codigo')->withCount('distritos')->get()->pluck('distritos_count')->all());
        $this->assertSame(4, Distrito::where('codigo', '23D03')->firstOrFail()->zona->codigo);
        $this->assertSame(8, Distrito::where('codigo', '09D24')->firstOrFail()->zona->codigo);
        $this->assertSame(9, Distrito::where('codigo', '17D09')->firstOrFail()->zona->codigo);
        $this->assertSame('DIRECCION DISTRITAL 15D01 ARCHIDONA CARLOS JULIO AROSEMENA TOLA TENA - EDUCACION', Distrito::where('codigo', '15D01')->firstOrFail()->nombre);

        $distrito = Distrito::where('codigo', '08D01')->firstOrFail();
        $distrito->update(['nombre' => 'Nombre editado localmente']);
        $this->seed(DistritoSeeder::class);
        $this->assertDatabaseCount('distritos', 140);
        $this->assertSame('Nombre editado localmente', $distrito->fresh()->nombre);
    }

    public function test_districts_are_shown_only_for_the_selected_zone_in_the_existing_dashboard(): void
    {
        $this->seedCatalog();
        $zona = Zona::where('codigo', 1)->firstOrFail();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.distritos.index'))->assertOk()
            ->assertViewIs('dashboard-role')->assertViewHas('distritos', null)
            ->assertDontSee('08D01')->assertDontSee('Generar reporte PDF');

        $this->get(route('superusuario.distritos.index', ['zona_id' => $zona->id]))
            ->assertOk()->assertViewIs('dashboard-role')->assertViewHas('dashboardSection', 'distritos')
            ->assertSee('08D01')->assertDontSee('15D01')->assertSee('16 distritos')
            ->assertSee('4 provincias')->assertSee('Esmeraldas: 6')
            ->assertSee(route('superusuario.distritos.pdf', $zona), false)
            ->assertSeeInOrder(['Zonas educativas', 'Distritos Educativos', 'Instituciones']);

        $this->get(route('superusuario.distritos.index', ['zona_id' => $zona->id, 'page' => 2]))
            ->assertOk()->assertSee('21D04')->assertDontSee('04D01')
            ->assertSee('16 distritos')->assertSee('4 provincias');
    }

    public function test_edit_and_update_preserve_the_selected_zone(): void
    {
        $this->seedCatalog();
        $distrito = Distrito::where('codigo', '08D01')->firstOrFail();
        $zona = $distrito->zona;
        $editUrl = route('superusuario.distritos.index', ['zona_id' => $zona->id, 'edit' => $distrito->id]);
        $this->withSession(['auth_user' => ['role' => 'superusuario']])->get($editUrl)
            ->assertOk()->assertViewIs('dashboard-role')->assertSee('Editar distrito')
            ->assertSee($distrito->nombre)->assertSee('Cancelar');

        $this->from($editUrl)->put(route('superusuario.distritos.update', ['zona' => $zona, 'distrito' => $distrito]), [
            'codigo' => '08D01', 'nombre' => 'Distrito actualizado', 'provincia' => 'Esmeraldas',
            'zona_id' => Zona::where('codigo', 2)->firstOrFail()->id,
        ])->assertRedirect(route('superusuario.distritos.index', ['zona_id' => $zona->id]))->assertSessionHas('success');

        $this->assertDatabaseHas('distritos', ['id' => $distrito->id, 'nombre' => 'Distrito actualizado', 'zona_id' => $zona->id]);
    }

    public function test_edit_and_update_cannot_access_a_district_from_another_zone(): void
    {
        $this->seedCatalog();
        $distrito = Distrito::where('codigo', '08D01')->firstOrFail();
        $otraZona = Zona::where('codigo', 2)->firstOrFail();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.distritos.index', ['zona_id' => $otraZona->id, 'edit' => $distrito->id]))->assertNotFound();

        $this->put(route('superusuario.distritos.update', ['zona' => $otraZona, 'distrito' => $distrito]), [
            'codigo' => '08D01', 'nombre' => 'Intento de cambio', 'provincia' => 'Esmeraldas',
        ])->assertNotFound();
        $this->assertSame($distrito->nombre, $distrito->fresh()->nombre);
    }

    public function test_update_validates_unique_code_format_and_required_fields(): void
    {
        $this->seedCatalog();
        $distrito = Distrito::where('codigo', '08D01')->firstOrFail();
        $editUrl = route('superusuario.distritos.index', ['zona_id' => $distrito->zona_id, 'edit' => $distrito->id]);
        $updateUrl = route('superusuario.distritos.update', ['zona' => $distrito->zona_id, 'distrito' => $distrito]);
        $this->withSession(['auth_user' => ['role' => 'superusuario']])->from($editUrl)->put($updateUrl, [
            'codigo' => '08D02', 'nombre' => '', 'provincia' => '',
        ])->assertRedirect($editUrl)->assertSessionHasErrors(['codigo', 'nombre', 'provincia']);
        $this->get($editUrl)->assertOk()->assertSee('Este código ya pertenece a otro distrito.');

        $this->putJson($updateUrl, ['codigo' => 'INVALIDO', 'nombre' => 'Nombre', 'provincia' => 'Esmeraldas'])
            ->assertUnprocessable()->assertJsonValidationErrors('codigo');
        $this->assertSame('08D01', $distrito->fresh()->codigo);
    }

    public function test_zone_selection_and_edit_parameters_are_validated(): void
    {
        $this->seedCatalog();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->getJson(route('superusuario.distritos.index', ['zona_id' => 99999]))
            ->assertUnprocessable()->assertJsonValidationErrors('zona_id');
        $this->getJson(route('superusuario.distritos.index', ['edit' => 1]))
            ->assertUnprocessable()->assertJsonValidationErrors('zona_id');
        $this->getJson(route('superusuario.distritos.index', ['zona_id' => [1]]))
            ->assertUnprocessable()->assertJsonValidationErrors('zona_id');
    }

    public function test_pdf_contains_all_selected_zone_districts_beyond_the_first_page(): void
    {
        $this->seedCatalog();
        $zona = Zona::where('codigo', 1)->firstOrFail();
        $renderer = app('dompdf.wrapper');
        Pdf::shouldReceive('loadView')->once()->andReturnUsing(function ($view, $data) use ($zona, $renderer) {
            $this->assertSame('superusuario.distritos.pdf', $view);
            $this->assertCount(16, $data['distritos']);
            $this->assertTrue($data['distritos']->every(fn ($distrito) => $distrito->zona_id === $zona->id));
            $this->assertContains('21D04', $data['distritos']->pluck('codigo')->all());
            $this->assertSame(6, $data['porProvincia']['Esmeraldas']);

            return $renderer->loadView($view, $data);
        });

        $response = $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.distritos.pdf', $zona))->assertOk()
            ->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('Distritos_Zona_1.pdf', $response->headers->get('content-disposition'));
    }

    public function test_empty_zone_and_missing_zone_are_handled_in_the_view_and_report(): void
    {
        $this->seed(ZonaSeeder::class);
        $zona = Zona::where('codigo', 1)->firstOrFail();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.distritos.index', ['zona_id' => $zona->id]))
            ->assertOk()->assertSee('No hay distritos registrados')->assertSee('0 distritos');
        $this->get(route('superusuario.distritos.pdf', $zona))->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('superusuario.distritos.pdf', 99999))->assertNotFound();
    }

    public function test_guests_and_other_roles_cannot_list_edit_or_export_districts(): void
    {
        $this->seedCatalog();
        $distrito = Distrito::firstOrFail();
        $updateUrl = route('superusuario.distritos.update', ['zona' => $distrito->zona_id, 'distrito' => $distrito]);
        foreach ([route('superusuario.distritos.index'), route('superusuario.distritos.pdf', $distrito->zona_id)] as $url) {
            $this->get($url)->assertRedirect();
        }
        $this->put($updateUrl, [])->assertRedirect();

        $this->withSession(['auth_user' => ['role' => 'admin']]);
        $this->get(route('superusuario.distritos.index'))->assertRedirect(route('roles.dashboard', 'admin'));
        $this->get(route('superusuario.distritos.pdf', $distrito->zona_id))->assertRedirect(route('roles.dashboard', 'admin'));
        $this->put($updateUrl, [])->assertRedirect(route('roles.dashboard', 'admin'));
    }
}
