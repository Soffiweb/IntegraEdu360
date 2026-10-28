<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Database\Seeders\InstitucionCatalogosSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminRepresentanteTest extends TestCase
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

    public function test_representative_can_have_multiple_students_and_cannot_orphan_them(): void
    {
        foreach ([1, 2] as $i) {
            $this->post(route('admin.estudiantes.store'), $this->datos([
                'username' => 'alumno'.$i, 'numero_identificacion' => 'DEMO-ALUMNO-'.$i,
            ]))->assertSessionHasNoErrors();
        }
        $ids = Usuario::alumnos()->pluck('id')->all();
        $payload = $this->datos([
            'username' => 'nuevo.representante', 'numero_identificacion' => 'DEMO-REP-1',
            'representados' => $ids,
        ]);
        $this->post(route('admin.representantes.store'), $payload)->assertSessionHasNoErrors();
        $representante = Usuario::where('username', 'nuevo.representante')->sole();
        $this->assertSame(2, $representante->representados()->count());
        $this->get(route('admin.representantes.index', ['edit' => $representante->id]))
            ->assertOk()->assertSee('Ver datos del representante')->assertSee('2 alumnos');
        $this->get(route('admin.representantes.excel'))->assertOk()
            ->assertHeader('content-type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
        $this->put(route('admin.representantes.update', $representante), array_replace($payload, ['representados' => [$ids[0]]]))
            ->assertSessionHasErrors('representados');
        $this->assertSame(2, $representante->representados()->count());
        $this->delete(route('admin.representantes.destroy', $representante))->assertSessionHasErrors('representados');
        $this->assertModelExists($representante);
    }

    public function test_cannot_link_student_of_another_institution_or_a_non_student(): void
    {
        $this->post(route('admin.representantes.store'), $this->datos([
            'username' => 'nuevo.rep', 'numero_identificacion' => 'DEMO-REP-1',
            'representados' => [$this->representante->id],
        ]))->assertSessionHasErrors('representados');
        $this->assertSame(1, Usuario::count());
        $otra = $this->institucion->replicate();
        $otra->codigo_amie = '04H00004';
        $otra->save();
        $this->representante->update(['institucion_id' => $otra->id]);
        $this->get(route('admin.representantes.index', ['edit' => $this->representante->id]))->assertNotFound();
        $this->post(route('admin.representantes.store'), $this->datos([
            'username' => 'nuevo.rep', 'numero_identificacion' => 'DEMO-REP-1',
            'representados' => [$this->representante->id],
        ]))->assertSessionHasErrors('representados.0');
    }
}
