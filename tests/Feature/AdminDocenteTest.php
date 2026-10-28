<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminDocenteTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitucionCatalogosSeeder::class);
        $this->institucion = Institucion::create([
            'nombre' => 'Colegio de prueba', 'codigo_amie' => '01H00001',
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id'),
        ]);
        Rol::create(['codigo' => '04', 'nombre' => 'DOCENTE']);
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]]);
    }

    private function datos(array $changes = []): array
    {
        return $changes + [
            'institucion_id' => $this->institucion->id, 'username' => 'ana.docente',
            'password_hash' => 'clave-prueba', 'estado' => 'ACTIVO',
            'numero_identificacion' => '1102826565', 'primer_nombre' => 'Ana',
            'primer_apellido' => 'Perez', 'fecha_nacimiento' => '1990-01-20',
            'sexo' => 'F', 'celular' => '0991234567', 'direccion' => 'Loja',
        ];
    }

    public function test_creates_teacher_with_person_account_and_role(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos())
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.docentes.index'));
        $persona = Persona::sole();
        $usuario = Usuario::sole();
        $this->assertSame('1102826565', $persona->numero_identificacion);
        $this->assertSame($persona->id, $usuario->persona_id);
        $this->assertSame($this->institucion->id, $usuario->institucion_id);
        $this->assertSame('04', $usuario->roles()->sole()->codigo);
        $this->get(route('admin.docentes.index'))->assertOk()->assertSee('Ana')->assertSee('Perez');
    }

    public function test_rejects_duplicate_identification_and_can_update_same_teacher(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos())->assertSessionHasNoErrors();
        $usuario = Usuario::sole();
        $this->post(route('admin.docentes.store'), $this->datos(['username' => 'otro.docente']))
            ->assertSessionHasErrors('numero_identificacion');
        $this->assertSame(1, Persona::count());
        $this->assertSame(1, Usuario::count());
        $this->put(route('admin.docentes.update', $usuario), $this->datos(['primer_nombre' => 'Maria']))
            ->assertSessionHasNoErrors()->assertRedirect(route('admin.docentes.index'));
        $this->assertSame('Maria', $usuario->persona->primer_nombre);
    }

    public function test_teacher_treatment_is_saved_displayed_updated_and_cleared(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos(['tratamiento' => ' Lic. ']))
            ->assertSessionHasNoErrors();
        $usuario = Usuario::sole();
        $this->assertSame('Lic.', $usuario->persona->tratamiento);
        $this->get(route('admin.docentes.index', ['edit' => $usuario->id]))
            ->assertOk()->assertSee('Lic. Ana Perez')->assertSee('value="Lic."', false);
        $this->get(route('admin.docentes.index', ['search' => 'Lic.']))
            ->assertOk()->assertSee('Lic. Ana Perez');

        $this->put(route('admin.docentes.update', $usuario), $this->datos(['tratamiento' => 'Mgtr.']))
            ->assertSessionHasNoErrors();
        $this->assertSame('Mgtr.', $usuario->persona->fresh()->tratamiento);

        $this->put(route('admin.docentes.update', $usuario), $this->datos(['tratamiento' => '']))
            ->assertSessionHasNoErrors();
        $this->assertNull($usuario->persona->fresh()->tratamiento);
    }

    public function test_rejects_treatment_longer_than_120_characters(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos(['tratamiento' => str_repeat('a', 121)]))
            ->assertSessionHasErrors('tratamiento');
        $this->assertSame(0, Persona::count());
    }

    public function test_teacher_page_size_is_selectable_and_bounded(): void
    {
        foreach ([null => 25, 50 => 50, 100 => 100, 500 => 25] as $requested => $expected) {
            $this->get(route('admin.docentes.index', ['per_page' => $requested]))
                ->assertOk()
                ->assertViewHas('docentes', fn ($docentes) => $docentes->perPage() === $expected);
        }
    }

    public function test_prints_filtered_teacher_list_for_active_institution(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos(['tratamiento' => 'Lic.']))
            ->assertSessionHasNoErrors();
        $this->get(route('admin.docentes.print', ['search' => 'Ana']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->get(route('admin.docentes.print', ['search' => 'Sin coincidencias']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_prints_teacher_signature_sheet(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos())->assertSessionHasNoErrors();
        $response = $this->get(route('admin.docentes.signatures'))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
        $this->assertStringContainsString('lista-firmas-docentes.pdf', $response->headers->get('content-disposition'));
        $this->get(route('admin.docentes.signatures', ['search' => 'Sin coincidencias']))
            ->assertOk()->assertHeader('content-type', 'application/pdf');
    }

    public function test_exports_filtered_excel_preserving_identification_and_literal_values(): void
    {
        $this->post(route('admin.docentes.store'), $this->datos([
            'numero_identificacion' => '0012345678', 'direccion' => '=1+1',
        ]))->assertSessionHasNoErrors();
        $response = $this->get(route('admin.docentes.excel', ['search' => 'Ana']))
            ->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'test-docentes-');
        try {
            file_put_contents($path, $response->getContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
            $sheet->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $this->assertCount(2, $sheet->xpath('//s:sheetData/s:row'));
            $this->assertSame('0012345678', (string) $sheet->xpath('//s:c[@r="A2"]/s:is/s:t')[0]);
            $this->assertSame('inlineStr', (string) $sheet->xpath('//s:c[@r="A2"]')[0]['t']);
            $this->assertSame('=1+1', (string) $sheet->xpath('//s:c[@r="O2"]/s:is/s:t')[0]);
            $this->assertCount(0, $sheet->xpath('//s:f'));
            $zip->close();
        } finally {
            unlink($path);
        }
    }
}
