<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminEstudianteTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    private Usuario $representante;

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
        Rol::create(['codigo' => 'EST', 'nombre' => 'Estudiante']);
        $rol = Rol::create(['codigo' => 'PADRE', 'nombre' => 'Padre o Tutor']);
        $this->representante = Usuario::create(['institucion_id' => $this->institucion->id, 'username' => 'representante.prueba', 'password_hash' => 'clave', 'estado' => 'ACTIVO']);
        $this->representante->roles()->attach($rol->id, ['institucion_id' => $this->institucion->id]);
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]]);
    }

    private function datos(array $changes = []): array
    {
        return $changes + [
            'representante_id' => $this->representante->id,
            'parentesco_representante' => 'MADRE',
            'institucion_id' => $this->institucion->id, 'username' => 'ana.alumno',
            'password_hash' => 'clave-prueba', 'estado' => 'ACTIVO',
            'numero_identificacion' => '1102826565', 'primer_nombre' => 'Ana',
            'primer_apellido' => 'Perez', 'fecha_nacimiento' => '1990-01-20',
            'sexo' => 'F', 'celular' => '0991234567', 'direccion' => 'Loja',
        ];
    }

    public function test_student_details_and_filtered_excel_are_available(): void
    {
        $this->post(route('admin.estudiantes.store'), $this->datos(['numero_identificacion' => '0012345678']))
            ->assertSessionHasNoErrors();
        $alumno = Usuario::alumnos()->sole();
        $this->post(route('admin.estudiantes.store'), $this->datos([
            'username' => 'otro.alumno', 'numero_identificacion' => '0012345679', 'estado' => 'INACTIVO',
        ]))->assertSessionHasNoErrors();
        $otraInstitucion = $this->institucion->replicate();
        $otraInstitucion->codigo_amie = '02H00002';
        $otraInstitucion->save();
        $otroAlumno = $alumno->replicate();
        $otroAlumno->username = 'alumno.otra.institucion';
        $otroAlumno->institucion_id = $otraInstitucion->id;
        $otroAlumno->save();
        $otroAlumno->roles()->attach(Rol::where('codigo', 'EST')->sole()->id, ['institucion_id' => $otraInstitucion->id]);

        $this->get(route('admin.estudiantes.index'))
            ->assertOk()->assertSee('Ver datos del alumno')->assertSee('Exportar a Excel')
            ->assertSee('datos-alumno-'.$alumno->id)->assertSee('Datos del alumno');
        $response = $this->get(route('admin.estudiantes.excel', ['estado' => 'ACTIVO', 'institucion' => $otraInstitucion->id]))
            ->assertOk()->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $path = tempnam(sys_get_temp_dir(), 'test-alumnos-');
        try {
            file_put_contents($path, $response->getContent());
            $zip = new \ZipArchive;
            $this->assertTrue($zip->open($path));
            $sheet = simplexml_load_string($zip->getFromName('xl/worksheets/sheet1.xml'));
            $sheet->registerXPathNamespace('s', 'http://schemas.openxmlformats.org/spreadsheetml/2006/main');
            $this->assertCount(2, $sheet->xpath('//s:sheetData/s:row'));
            $this->assertSame('0012345678', (string) $sheet->xpath('//s:c[@r="A2"]/s:is/s:t')[0]);
            $this->assertStringContainsString('name="Alumnos"', $zip->getFromName('xl/workbook.xml'));
            $zip->close();
        } finally {
            unlink($path);
        }
    }

    public function test_student_page_size_is_selectable_and_bounded(): void
    {
        foreach ([25 => 25, 50 => 50, 100 => 100, 500 => 25] as $requested => $expected) {
            $this->get(route('admin.estudiantes.index', ['per_page' => $requested]))
                ->assertOk()->assertViewHas('estudiantes', fn ($estudiantes) => $estudiantes->perPage() === $expected);
        }
    }

    public function test_relationship_is_saved_and_can_be_changed(): void
    {
        $this->post(route('admin.estudiantes.store'), $this->datos())
            ->assertSessionHasNoErrors();
        $alumno = Usuario::alumnos()->sole();
        $this->assertSame('MADRE', $alumno->parentesco_representante);

        $this->get(route('admin.estudiantes.index', ['edit' => $alumno->id]))
            ->assertOk()->assertSee('create_parentesco_representante')
            ->assertSee('edit_parentesco_representante')
            ->assertSee('value="MADRE" selected', false);

        $this->put(route('admin.estudiantes.update', $alumno), $this->datos(['parentesco_representante' => 'TIA']))
            ->assertSessionHasNoErrors();
        $this->assertSame('TIA', $alumno->fresh()->parentesco_representante);
    }

    public function test_requires_a_valid_relationship(): void
    {
        foreach ([null, 'DESCONOCIDO'] as $parentesco) {
            $this->post(route('admin.estudiantes.store'), $this->datos(['parentesco_representante' => $parentesco]))
                ->assertSessionHasErrors('parentesco_representante');
        }
        $this->assertSame(0, Usuario::alumnos()->count());
    }

    public function test_requires_a_representative_from_same_institution(): void
    {
        $this->post(route('admin.estudiantes.store'), $this->datos(['representante_id' => null]))
            ->assertSessionHasErrors('representante_id');
        $otra = $this->institucion->replicate();
        $otra->codigo_amie = '03H00003';
        $otra->save();
        $this->representante->update(['institucion_id' => $otra->id]);
        $this->post(route('admin.estudiantes.store'), $this->datos())
            ->assertSessionHasErrors('representante_id');
        $this->assertSame(0, Usuario::alumnos()->count());
    }
}
