<?php

namespace Tests\Feature;

use App\Models\Agenda;
use App\Models\Institucion;
use Barryvdh\DomPDF\PDF;
use Database\Seeders\InstitucionCatalogosSeeder;
use Dompdf\Dompdf;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Tests\TestCase;

class AdminAgendaTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    private Institucion $otra;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(InstitucionCatalogosSeeder::class);
        $this->institucion = $this->crearInstitucion('Colegio de prueba', '01H00001');
        $this->otra = $this->crearInstitucion('Otro colegio', '01H00002');
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]]);
    }

    private function crearInstitucion(string $nombre, string $amie): Institucion
    {
        return Institucion::create(['nombre' => $nombre, 'codigo_amie' => $amie,
            'provincia' => 'Loja', 'canton' => 'Loja', 'estado' => 'ACTIVO',
            'sostenimiento_id' => DB::table('cat_sostenimiento')->value('id'),
            'regimen_id' => DB::table('cat_regimen_escolar')->value('id')]);
    }

    private function datos(array $cambios = []): array
    {
        return $cambios + ['fecha' => '2026-10-20', 'hora_desde' => '09:30', 'hora_hasta' => '11:00', 'descripcion' => 'Reunion de padres de familia',
            'responsable' => 'Ana Pérez', 'porcentaje_cumplimiento' => 40];
    }

    public function test_directivo_can_manage_own_institution_agenda(): void
    {
        $this->withSession(['auth_user' => ['role' => 'directivo', 'institucion_id' => $this->institucion->id]]);
        $this->get(route('roles.dashboard', 'directivo'))->assertOk()->assertSee(route('directivo.agenda.index'));
        $this->post(route('directivo.agenda.store'), $this->datos(['institucion_id' => $this->otra->id]))
            ->assertRedirect(route('directivo.agenda.index'));
        $agenda = Agenda::sole();
        $this->assertSame($this->institucion->id, $agenda->institucion_id);
        $this->get(route('directivo.agenda.index'))->assertOk()
            ->assertSee(route('directivo.agenda.store'))->assertSee('Reunion de padres de familia');
        $this->put(route('directivo.agenda.update', $agenda), $this->datos(['porcentaje_cumplimiento' => 100]))
            ->assertRedirect(route('directivo.agenda.index'));
        $this->assertSame(100, $agenda->fresh()->porcentaje_cumplimiento);
        $otraAgenda = Agenda::create($this->datos(['institucion_id' => $this->otra->id]));
        $this->get(route('directivo.agenda.index', ['edit' => $otraAgenda->id]))->assertNotFound();
        $this->put(route('directivo.agenda.update', $otraAgenda), $this->datos())->assertNotFound();
        $this->delete(route('directivo.agenda.destroy', $otraAgenda))->assertNotFound();
        $this->delete(route('directivo.agenda.destroy', $agenda))->assertRedirect(route('directivo.agenda.index'));
    }

    public function test_directivo_without_institution_cannot_access_global_agenda(): void
    {
        $this->withSession(['auth_user' => ['role' => 'directivo']]);
        $this->get(route('directivo.agenda.index'))->assertForbidden();
    }

    public function test_creates_activity_for_active_institution_even_if_another_is_sent(): void
    {
        $this->post(route('admin.agenda.store'), $this->datos(['institucion_id' => $this->otra->id]))
            ->assertRedirect(route('admin.agenda.index'));

        $agenda = Agenda::sole();
        $this->assertSame($this->institucion->id, $agenda->institucion_id);
        $this->assertSame('Ana Pérez', $agenda->responsable);
        $this->assertSame(40, $agenda->porcentaje_cumplimiento);

        $this->get(route('admin.agenda.index'))->assertOk()
            ->assertSee('Reunion de padres de familia')->assertSee('2026-10-20')->assertSee('09:30 - 11:00')->assertSee('40%');
    }

    public function test_rejects_invalid_percentage_and_missing_fields(): void
    {
        $this->post(route('admin.agenda.store'), $this->datos(['porcentaje_cumplimiento' => 120, 'responsable' => '', 'hora_hasta' => '08:00']))
            ->assertSessionHasErrors(['porcentaje_cumplimiento', 'responsable', 'hora_hasta']);
        $this->assertSame(0, Agenda::count());
    }

    public function test_updates_and_deletes_own_activity(): void
    {
        $agenda = Agenda::create($this->datos(['institucion_id' => $this->institucion->id]));

        $this->get(route('admin.agenda.index', ['edit' => $agenda->id]))->assertOk()
            ->assertSee('Editar actividad')->assertSee('value="09:30"', false)->assertSee('value="11:00"', false);

        $this->put(route('admin.agenda.update', $agenda), $this->datos(['porcentaje_cumplimiento' => 100]))
            ->assertRedirect(route('admin.agenda.index'));
        $this->assertSame(100, $agenda->fresh()->porcentaje_cumplimiento);

        $this->delete(route('admin.agenda.destroy', $agenda))->assertRedirect(route('admin.agenda.index'));
        $this->assertSame(0, Agenda::count());
    }

    public function test_cannot_see_or_modify_other_institution_activities(): void
    {
        $ajena = Agenda::create($this->datos(['institucion_id' => $this->otra->id, 'descripcion' => 'Actividad ajena']));

        $this->get(route('admin.agenda.index'))->assertOk()->assertDontSee('Actividad ajena');
        $this->put(route('admin.agenda.update', $ajena), $this->datos())->assertNotFound();
        $this->delete(route('admin.agenda.destroy', $ajena))->assertNotFound();
        $this->assertSame(1, Agenda::count());
    }

    public function test_filters_by_compliance(): void
    {
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'descripcion' => 'Actividad cumplida', 'porcentaje_cumplimiento' => 100]));
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'descripcion' => 'Actividad pendiente', 'porcentaje_cumplimiento' => 0]));

        $this->get(route('admin.agenda.index', ['cumplimiento' => 'cumplida']))->assertOk()
            ->assertSee('Actividad cumplida')->assertDontSee('Actividad pendiente');
    }

    public function test_filters_dates_inclusively_and_accepts_one_sided_ranges(): void
    {
        foreach (['2026-10-19', '2026-10-20', '2026-10-21', '2026-10-22'] as $date) {
            Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'fecha' => $date, 'descripcion' => 'Actividad '.$date]));
        }
        $this->get(route('admin.agenda.index', ['fecha_desde' => '2026-10-20', 'fecha_hasta' => '2026-10-21']))
            ->assertOk()->assertSee('Actividad 2026-10-20')->assertSee('Actividad 2026-10-21')
            ->assertDontSee('Actividad 2026-10-19')->assertDontSee('Actividad 2026-10-22');
        $this->get(route('admin.agenda.index', ['fecha_hasta' => '2026-10-20']))
            ->assertOk()->assertSee('Actividad 2026-10-19')->assertDontSee('Actividad 2026-10-21');
        $this->get(route('admin.agenda.index', ['fecha_desde' => '2026-10-21']))
            ->assertOk()->assertSee('Actividad 2026-10-22')->assertDontSee('Actividad 2026-10-20');
    }

    public function test_rejects_invalid_date_filters_on_listing_and_print(): void
    {
        foreach (['admin.agenda.index', 'admin.agenda.print'] as $route) {
            $this->get(route($route, ['fecha_desde' => '2026-10-21', 'fecha_hasta' => '2026-10-20']))
                ->assertSessionHasErrors('fecha_hasta');
            $this->get(route($route, ['fecha_desde' => 'invalid']))->assertSessionHasErrors('fecha_desde');
        }
    }

    public function test_print_uses_all_filtered_results_and_active_institution(): void
    {
        for ($i = 0; $i < 12; $i++) {
            Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'descripcion' => 'Reunion '.$i, 'porcentaje_cumplimiento' => 100]));
        }
        Agenda::create($this->datos(['institucion_id' => $this->otra->id]));
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'fecha' => '2026-10-19']));
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'porcentaje_cumplimiento' => 0]));
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id, 'descripcion' => 'Otra actividad', 'porcentaje_cumplimiento' => 100]));

        $pdf = \Mockery::mock(PDF::class);
        $pdf->shouldReceive('setPaper')->with('a4', 'portrait')->andReturnSelf();
        $pdf->shouldReceive('render')->once();
        $domPdf = \Mockery::mock(Dompdf::class);
        $pdf->shouldReceive('getDomPDF')->andReturn($domPdf);
        $domPdf->shouldReceive('getFontMetrics->getFont')->andReturn('font');
        $domPdf->shouldReceive('getCanvas->page_text')->once();
        $pdf->shouldReceive('stream')->with('agenda-institucional.pdf')->andReturn(response('pdf'));
        \Barryvdh\DomPDF\Facade\Pdf::shouldReceive('loadView')->once()
            ->with('admin.agenda.pdf', \Mockery::on(function ($data) {
                return $data['agendas']->count() === 12
                    && $data['agendas']->every(fn ($agenda) => $agenda->institucion_id === $this->institucion->id)
                    && $data['institucion']->id === $this->institucion->id;
            }))->andReturn($pdf);

        $this->get(route('admin.agenda.print', ['fecha_desde' => '2026-10-20', 'fecha_hasta' => '2026-10-20',
            'search' => 'Reunion', 'cumplimiento' => 'cumplida', 'institucion' => $this->otra->id, 'page' => 2]))->assertOk();
    }

    public function test_print_renders_pdf_and_is_restricted_to_admin(): void
    {
        Agenda::create($this->datos(['institucion_id' => $this->institucion->id]));
        $response = $this->get(route('admin.agenda.print'))->assertOk()->assertHeader('Content-Type', 'application/pdf');
        $this->assertStringStartsWith('%PDF-', $response->getContent());
        $this->withSession(['auth_user' => ['role' => 'docente']])->get(route('admin.agenda.print'))->assertRedirect();
    }

    public function test_other_roles_cannot_access(): void
    {
        $this->withSession(['auth_user' => ['role' => 'docente']])
            ->get(route('admin.agenda.index'))->assertRedirect();
    }
}
