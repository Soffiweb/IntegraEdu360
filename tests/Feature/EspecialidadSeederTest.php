<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use Database\Seeders\EspecialidadSeeder;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class EspecialidadSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_catalog_is_complete_repeatable_and_preserves_existing_links(): void
    {
        $this->seed(InstitucionCatalogosSeeder::class);
        foreach (['01H00001', '01H00002'] as $amie) {
            Institucion::create([
                'nombre' => 'Colegio '.$amie, 'codigo_amie' => $amie,
                'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
                'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
                'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
            ]);
        }
        $institucion = Institucion::firstOrFail();
        $electronica = Especialidad::create([
            'institucion_id' => $institucion->id, 'nombre' => 'Bachillerato Técnico en Electrónica',
            'codigo' => 'BTE', 'tipo' => 'TECNICO', 'estado' => 'INACTIVO',
        ]);
        $legacy = Especialidad::create([
            'institucion_id' => $institucion->id, 'nombre' => 'Informática anterior',
            'codigo' => 'BTI', 'tipo' => 'TECNICO', 'estado' => 'ACTIVO',
        ]);
        $curso = Curso::create([
            'institucion_id' => $institucion->id, 'especialidad_id' => $electronica->id,
            'nombre' => 'Primero de Bachillerato', 'nivel' => 'BACHILLERATO', 'grado' => 11,
        ]);

        $this->app->make(EspecialidadSeeder::class)->__invoke(['institucionId' => $institucion->id]);
        $this->assertSame(36, Especialidad::where('institucion_id', $institucion->id)->count());
        $this->assertSame(0, Especialidad::where('institucion_id', '!=', $institucion->id)->count());

        $this->seed(EspecialidadSeeder::class);
        $ids = Especialidad::orderBy('id')->pluck('id')->all();
        $this->seed(EspecialidadSeeder::class);

        $this->assertSame($ids, Especialidad::orderBy('id')->pluck('id')->all());
        $this->assertSame(71, Especialidad::count());
        $this->assertSame(34, Especialidad::where('institucion_id', '!=', $institucion->id)->where('tipo', 'TECNICO')->count());
        $this->assertSame('Electrónica', $electronica->fresh()->nombre);
        $this->assertSame('INACTIVO', $electronica->fresh()->estado);
        $this->assertSame($electronica->id, $curso->fresh()->especialidad_id);
        $this->assertModelExists($legacy);
        $this->assertDatabaseHas('especialidades', ['nombre' => 'Gestión financiera y contable', 'tipo' => 'TECNICO']);
        $this->assertDatabaseHas('especialidades', ['nombre' => 'Bachillerato en Ciencias', 'tipo' => 'GENERAL']);
    }
}
