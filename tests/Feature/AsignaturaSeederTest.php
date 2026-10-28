<?php

namespace Tests\Feature;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use Database\Seeders\AsignaturaSeeder;
use Database\Seeders\CursoSeeder;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AsignaturaSeederTest extends TestCase
{
    use RefreshDatabase;

    public function test_curriculum_is_scoped_repeatable_and_matches_grades_and_specialties(): void
    {
        $this->seed(InstitucionCatalogosSeeder::class);
        $instituciones = collect(['01H00001', '01H00002'])->map(fn ($amie) => Institucion::create([
            'nombre' => 'Colegio '.$amie, 'codigo_amie' => $amie,
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
        ]));
        foreach ($instituciones as $institucion) {
            $this->app->make(CursoSeeder::class)->run($institucion->id, true);
            foreach (['BGU', 'BT-GFC', 'BT-OTRO'] as $codigo) {
                $especialidad = Especialidad::create([
                    'institucion_id' => $institucion->id, 'codigo' => $codigo,
                    'nombre' => $codigo, 'tipo' => $codigo === 'BGU' ? 'GENERAL' : 'TECNICO',
                ]);
                foreach ([11, 12, 13] as $grado) {
                    Curso::create(['institucion_id' => $institucion->id, 'especialidad_id' => $especialidad->id,
                        'nombre' => 'Curso '.$grado, 'grado' => $grado, 'nivel' => 'BACHILLERATO']);
                }
            }
        }
        $id = $instituciones->first()->id;
        $custom = Asignatura::create(['institucion_id' => $id, 'codigo' => 'PROPIA', 'nombre' => 'Asignatura institucional']);
        $seeder = $this->app->make(AsignaturaSeeder::class);
        $seeder->run($id);
        $ids = Asignatura::orderBy('id')->pluck('id')->all();
        $links = DB::table('asignatura_curso')->count();
        $seeder->run($id);
        $this->assertSame($ids, Asignatura::orderBy('id')->pluck('id')->all());
        $this->assertSame($links, DB::table('asignatura_curso')->count());
        $this->assertSame(0, Asignatura::where('institucion_id', $instituciones->last()->id)->count());
        $this->assertModelExists($custom);
        $this->assertDatabaseMissing('asignaturas', ['codigo' => 'BGU-FIL-13']);
        $this->assertDatabaseHas('asignaturas', ['codigo' => 'BGU-FIL-11', 'horas_semana' => 2]);
        $this->assertDatabaseMissing('asignaturas', ['codigo' => 'BT-GFC-AF-11']);
        $this->assertDatabaseMissing('asignaturas', ['codigo' => 'BT-GFC-HIE-13']);
        $this->assertDatabaseHas('asignaturas', ['codigo' => 'BT-GFC-AF-13', 'horas_semana' => 5]);
        foreach (Curso::where('institucion_id', $id)->with('especialidad')->get() as $curso) {
            $asignaturas = Asignatura::whereHas('cursos', fn ($q) => $q->whereKey($curso->id))->get();
            if ($curso->especialidad?->codigo === 'BT-OTRO') {
                $this->assertCount(0, $asignaturas);

                continue;
            }
            $this->assertNotEmpty($asignaturas);
            foreach ($asignaturas as $asignatura) {
                $this->assertSame($curso->especialidad_id, $asignatura->especialidad_id);
                $this->assertStringEndsWith('-'.$curso->grado, $asignatura->codigo);
            }
            if ($curso->especialidad?->codigo === 'BT-GFC') {
                $this->assertSame(21, (int) $asignaturas->where('area', 'TECNICA_TECNOLOGICA')->sum('horas_semana'));
                $this->assertNull($asignaturas->firstWhere('codigo', 'BT-GFC-MAT-'.$curso->grado)->horas_semana);
            }
        }
    }
}
