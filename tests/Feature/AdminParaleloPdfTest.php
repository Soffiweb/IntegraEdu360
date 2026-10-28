<?php

namespace Tests\Feature;

use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\Rol;
use App\Models\Usuario;
use Barryvdh\DomPDF\Facade\Pdf;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminParaleloPdfTest extends TestCase
{
    use RefreshDatabase;

    public function test_pdf_contains_filtered_local_parallel_and_tutor(): void
    {
        $this->seed(InstitucionCatalogosSeeder::class);
        $institucion = Institucion::create([
            'nombre' => 'Colegio prueba', 'codigo_amie' => '01H00001',
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
        ]);
        $curso = Curso::create(['institucion_id' => $institucion->id, 'nombre' => 'Primero', 'nivel' => 'PREPARATORIA', 'grado' => 1]);
        $rol = Rol::create(['codigo' => '04', 'nombre' => 'Docente']);
        $tutor = Usuario::create(['institucion_id' => $institucion->id, 'username' => 'tutor.prueba', 'password_hash' => 'clave', 'estado' => 'ACTIVO']);
        $tutor->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $institucion->id]]);
        $payload = ['curso_id' => $curso->id, 'letra' => 'A', 'capacidad' => 35, 'estado' => 'ACTIVO', 'tutor_id' => $tutor->id];
        $paralelo = Paralelo::create($payload);
        Paralelo::create(array_replace($payload, ['letra' => 'B', 'estado' => 'INACTIVO']));
        $otra = $institucion->replicate();
        $otra->codigo_amie = '01H00002';
        $otra->save();
        $otroCurso = $curso->replicate();
        $otroCurso->institucion_id = $otra->id;
        $otroCurso->save();
        Paralelo::create(array_replace($payload, ['curso_id' => $otroCurso->id, 'tutor_id' => null]));

        $wrapper = app('dompdf.wrapper');
        Pdf::shouldReceive('loadView')->once()
            ->withArgs(function ($view, $data) use ($paralelo, $institucion) {
                $this->assertSame('admin.paralelos.pdf', $view);
                $this->assertSame([$paralelo->id], $data['paralelos']->pluck('id')->all());
                $this->assertSame($institucion->id, $data['institucion']->id);
                $this->assertCount(1, $data['grupos']);
                $this->assertSame('Sin especialidad', $data['grupos'][0]['nombre']);
                $this->assertSame(35, $data['grupos'][0]['capacidad']);
                $this->assertSame(1, $data['resumen']['paralelos']);
                $this->assertSame(1, $data['resumen']['con_tutor']);
                $this->assertSame(0, $data['resumen']['sin_tutor']);
                $html = view($view, $data)->render();
                $this->assertStringContainsString('Matriculados', $html);
                $this->assertStringContainsString('Subtotal: 1 paralelos', $html);
                $this->assertStringContainsString('Resumen estadístico', $html);
                $this->assertStringContainsString('>Ord<', $html);
                $this->assertStringContainsString('width: 60%', $html);
                $this->assertStringContainsString('tutor.prueba', $html);

                return true;
            })
            ->andReturnUsing(fn ($view, $data) => $wrapper->loadView($view, $data));
        $response = $this->get(route('admin.paralelos.pdf', ['estado' => 'ACTIVO', 'search' => 'A', 'curso_id' => $curso->id, 'institucion' => $otra->id]))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->assertStringContainsString('reporte-paralelos.pdf', $response->headers->get('content-disposition'));
    }
}
