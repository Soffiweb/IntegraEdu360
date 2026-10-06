<?php

namespace Tests\Feature;

use App\Models\Distrito;
use App\Models\Institucion;
use App\Services\Minedec\ImportInstituciones;
use Database\Seeders\DistritoSeeder;
use Database\Seeders\InstitucionCatalogosSeeder;
use Database\Seeders\ZonaSeeder;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Tests\TestCase;

class MinedecInstitucionImportTest extends TestCase
{
    use RefreshDatabase;

    private array $files = [];

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed([ZonaSeeder::class, DistritoSeeder::class, InstitucionCatalogosSeeder::class]);
    }

    protected function tearDown(): void
    {
        foreach ($this->files as $file) {
            unlink($file);
        }
        parent::tearDown();
    }

    private function csv(array $rows): string
    {
        $path = tempnam(sys_get_temp_dir(), 'minedec-instituciones-');
        $this->files[] = $path;
        $file = fopen($path, 'w');
        fputcsv($file, ImportInstituciones::COLUMNS, ',', '"', '');
        foreach ($rows as $row) {
            fputcsv($file, array_replace([
                '08H00001', 'Institución oficial', '08D01', '1', 'ESMERALDAS', 'ESMERALDAS', 'ESMERALDAS', 'Fiscal', 'Costa', '2025-2026 Inicio',
            ], $row), ',', '"', '');
        }
        fclose($file);

        return $path;
    }

    public function test_official_snapshot_is_valid_without_writing_to_the_database(): void
    {
        $result = app(ImportInstituciones::class)->run(database_path('data/mineduc/instituciones-2025-2026.csv'), true);
        $this->assertSame('2025-2026 Inicio', $result['periodo']);
        $this->assertSame(16215, $result['registros']);
        $this->assertCount(33, $result['sin_distrito_en_fuente']);
        $this->assertDatabaseCount('instituciones', 0);
    }

    public function test_import_relates_institutions_to_districts_and_is_idempotent(): void
    {
        $path = $this->csv([[], [0 => '15H00001', 1 => 'Institución Tena', 2 => '15D01', 3 => '2', 4 => 'NAPO', 5 => 'TENA', 8 => 'Sierra']]);
        $first = app(ImportInstituciones::class)->run($path);
        $this->assertSame(2, $first['nuevas']);
        $institucion = Institucion::where('codigo_amie', '08H00001')->firstOrFail();
        $this->assertSame('08D01', $institucion->distrito->codigo);
        $this->assertSame(1, $institucion->distrito->zona->codigo);
        $this->assertTrue($institucion->distrito->instituciones->contains($institucion));
        $this->assertNotNull($institucion->sostenimiento_id);
        $this->assertNotNull($institucion->regimen_id);
        $timestamp = $institucion->updated_at;

        $this->travel(1)->days();
        $second = app(ImportInstituciones::class)->run($path);
        $this->assertSame(0, $second['nuevas']);
        $this->assertSame(0, $second['actualizadas']);
        $this->assertSame(2, $second['sin_cambios']);
        $this->assertDatabaseCount('instituciones', 2);
        $this->assertTrue($timestamp->equalTo($institucion->fresh()->updated_at));
    }

    public function test_existing_ids_contacts_and_local_state_are_preserved(): void
    {
        $existing = Institucion::create([
            'codigo_amie' => '08H00001', 'nombre' => 'Nombre anterior', 'telefono' => '0990000000',
            'email' => 'local@example.test', 'direccion' => 'Dirección local', 'estado' => 'INACTIVO',
        ]);
        $legacy = Institucion::create(['nombre' => 'Registro anterior sin AMIE']);
        $result = app(ImportInstituciones::class)->run($this->csv([[]]));
        $this->assertSame(1, $result['actualizadas']);
        $this->assertDatabaseHas('instituciones', [
            'id' => $existing->id, 'codigo_amie' => '08H00001', 'nombre' => 'Institución oficial',
            'telefono' => '0990000000', 'email' => 'local@example.test', 'direccion' => 'Dirección local', 'estado' => 'INACTIVO',
        ]);
        $this->assertModelExists($legacy);
        $this->assertDatabaseCount('instituciones', 2);
    }

    public function test_unidentified_district_is_reported_and_does_not_erase_a_local_assignment(): void
    {
        $district = Distrito::where('codigo', '08D01')->firstOrFail();
        $existing = Institucion::create(['codigo_amie' => '08H00001', 'nombre' => 'Existente', 'distrito_id' => $district->id]);
        $result = app(ImportInstituciones::class)->run($this->csv([[2 => ''], [0 => '08H00002', 2 => '']]));
        $this->assertSame(['08H00001', '08H00002'], $result['sin_distrito_en_fuente']);
        $this->assertSame($district->id, $existing->fresh()->distrito_id);
        $this->assertNull(Institucion::where('codigo_amie', '08H00002')->firstOrFail()->distrito_id);
    }

    public function test_dry_run_makes_no_changes(): void
    {
        $path = $this->csv([[]]);
        $this->artisan('minedec:importar-instituciones', ['archivo' => $path, '--dry-run' => true])->assertSuccessful();
        $this->assertDatabaseCount('instituciones', 0);
    }

    public function test_inconsistent_district_zone_is_rejected_before_writing_any_row(): void
    {
        try {
            app(ImportInstituciones::class)->run($this->csv([[], [0 => '08H00002', 3 => '2']]));
            $this->fail('Se aceptó un distrito de otra zona.');
        } catch (RuntimeException $exception) {
            $this->assertStringContainsString('zona inconsistente', $exception->getMessage());
        }
        $this->assertDatabaseCount('instituciones', 0);
    }

    public function test_duplicate_amie_and_unknown_district_are_rejected(): void
    {
        foreach ([$this->csv([[], []]), $this->csv([[2 => '99D99']])] as $path) {
            $this->artisan('minedec:importar-instituciones', ['archivo' => $path])->assertFailed();
        }
        $this->assertDatabaseCount('instituciones', 0);
    }

    public function test_invalid_catalogs_and_multiple_periods_are_rejected(): void
    {
        foreach ([$this->csv([[7 => 'Inventado']]), $this->csv([[], [0 => '08H00002', 9 => '2024-2025 Inicio']])] as $path) {
            $this->artisan('minedec:importar-instituciones', ['archivo' => $path])->assertFailed();
        }
        $this->assertDatabaseCount('instituciones', 0);
    }

    public function test_superusuario_can_see_and_modify_the_district_on_the_institution_screen(): void
    {
        app(ImportInstituciones::class)->run($this->csv([[]]));
        $institucion = Institucion::where('codigo_amie', '08H00001')->firstOrFail();
        $other = Distrito::where('codigo', '08D02')->firstOrFail();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('superusuario.instituciones.index'))
            ->assertOk()->assertViewIs('dashboard-role')
            ->assertViewHas('dashboardSection', 'instituciones')
            ->assertSee('Zonas educativas')->assertSee('Distritos Educativos')
            ->assertDontSee('Dashboard institucional');
        $this->get(route('superusuario.instituciones.index', ['edit' => $institucion->id]))
            ->assertOk()->assertViewIs('dashboard-role')->assertViewHas('dashboardSection', 'instituciones')
            ->assertSee('Zonas educativas')->assertSee('Distritos Educativos')
            ->assertSee('Distrito educativo')->assertSee('08D01')->assertSee('edit_distrito_id');
        $this->put(route('superusuario.instituciones.update', $institucion), [
            'codigo_amie' => $institucion->codigo_amie, 'nombre' => $institucion->nombre,
            'distrito_id' => $other->id, 'sostenimiento_id' => $institucion->sostenimiento_id,
            'regimen_id' => $institucion->regimen_id, 'provincia' => 'Esmeraldas', 'canton' => 'Eloy Alfaro', 'estado' => 'ACTIVO',
        ])->assertRedirect(route('superusuario.instituciones.index'))->assertSessionHasNoErrors();
        $this->assertSame($other->id, $institucion->fresh()->distrito_id);
    }

    public function test_institution_requires_an_existing_district_when_assigned(): void
    {
        app(ImportInstituciones::class)->run($this->csv([[]]));
        $institucion = Institucion::firstOrFail();
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->putJson(route('superusuario.instituciones.update', $institucion), [
                'codigo_amie' => $institucion->codigo_amie, 'nombre' => $institucion->nombre,
                'distrito_id' => 99999, 'sostenimiento_id' => $institucion->sostenimiento_id,
                'regimen_id' => $institucion->regimen_id, 'provincia' => 'Esmeraldas', 'canton' => 'Esmeraldas', 'estado' => 'ACTIVO',
            ])->assertUnprocessable()->assertJsonValidationErrors('distrito_id');
    }

    public function test_database_foreign_key_prevents_invalid_district_assignments(): void
    {
        $this->expectException(QueryException::class);
        DB::table('instituciones')->insert(['nombre' => 'Relación inválida', 'distrito_id' => 99999]);
    }
}
