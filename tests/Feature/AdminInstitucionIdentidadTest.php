<?php

namespace Tests\Feature;

use App\Models\Institucion;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\Testing\File;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminInstitucionIdentidadTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    protected function setUp(): void
    {
        parent::setUp();
        Storage::fake('public');
        $this->seed(InstitucionCatalogosSeeder::class);
        $this->institucion = Institucion::create(['nombre' => 'Colegio de prueba', 'codigo_amie' => '01H00001',
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id')]);
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]]);
    }

    private function datos(): array
    {
        return $this->institucion->only(['codigo_amie', 'nombre', 'provincia', 'canton', 'estado', 'sostenimiento_id', 'regimen_id'])
            + ['slogan' => 'Educamos para el futuro', 'representante_nombre' => 'María Torres', 'representante_cargo' => 'Rectora'];
    }

    private function logo(string $nombre): File
    {
        return UploadedFile::fake()->createWithContent($nombre, base64_decode('iVBORw0KGgoAAAANSUhEUgAAAAEAAAABCAQAAAC1HAwCAAAAC0lEQVR42mP8/x8AAwMCAO+a7S0AAAAASUVORK5CYII='));
    }

    public function test_saves_identity_and_both_logos_and_preserves_them_without_new_uploads(): void
    {
        $this->put(route('admin.institucion.update', $this->institucion), $this->datos() + [
            'logo1' => $this->logo('colegio.png'), 'logo2' => $this->logo('secundario.png'),
        ])->assertRedirect(route('admin.institucion.index'));
        $this->institucion->refresh();
        $this->assertSame('Educamos para el futuro', $this->institucion->slogan);
        $this->assertSame('María Torres', $this->institucion->representante_nombre);
        $this->assertSame('Rectora', $this->institucion->representante_cargo);
        $logos = $this->institucion->only(['logo1', 'logo2']);
        Storage::disk('public')->assertExists(array_values($logos));
        $this->put(route('admin.institucion.update', $this->institucion), $this->datos())->assertRedirect();
        $this->assertSame($logos, $this->institucion->fresh()->only(['logo1', 'logo2']));
        $this->get(route('admin.institucion.index'))->assertOk()->assertSee('Educamos para el futuro')
            ->assertSee('María Torres')->assertSee('multipart/form-data')->assertSee('Logotipo 2');
    }

    public function test_replacement_deletes_only_replaced_logo(): void
    {
        $this->put(route('admin.institucion.update', $this->institucion), $this->datos() + [
            'logo1' => $this->logo('uno.png'), 'logo2' => $this->logo('dos.png'),
        ])->assertRedirect();
        $this->institucion->refresh();
        $anterior = $this->institucion->logo1;
        $logo2 = $this->institucion->logo2;
        $this->put(route('admin.institucion.update', $this->institucion), $this->datos() + [
            'logo1' => $this->logo('reemplazo.png'),
        ])->assertRedirect();
        Storage::disk('public')->assertMissing($anterior);
        Storage::disk('public')->assertExists([$this->institucion->fresh()->logo1, $logo2]);
    }

    public function test_rejects_invalid_uploads_and_access_to_other_institutions(): void
    {
        $this->put(route('admin.institucion.update', $this->institucion), $this->datos() + [
            'logo1' => UploadedFile::fake()->create('archivo.pdf', 20, 'application/pdf'),
            'logo2' => $this->logo('grande.png')->size(2049),
        ])->assertSessionHasErrors(['logo1', 'logo2']);
        $this->assertNull($this->institucion->fresh()->logo1);
        $otra = Institucion::create(['nombre' => 'Otra institución']);
        $this->put(route('admin.institucion.update', $otra), $this->datos())->assertForbidden();
        $this->withSession(['auth_user' => ['role' => 'admin']])->get(route('admin.institucion.index'))->assertNotFound();
        $this->assertSame([], Storage::disk('public')->allFiles());
    }
}
