<?php

namespace Tests\Feature;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\Institucion;
use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use App\Services\Directivo\ReporteDocentesAgrupado;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectivoTutoriaTest extends TestCase
{
    use RefreshDatabase;

    private Institucion $institucion;

    private PeriodoAcademico $vigente;

    private PeriodoAcademico $anterior;

    private Paralelo $paralelo;

    private Usuario $docente;

    private Usuario $reemplazo;

    protected function setUp(): void
    {
        parent::setUp();
        $this->institucion = Institucion::create(['nombre' => 'Colegio local', 'estado' => 'ACTIVO']);
        $this->vigente = PeriodoAcademico::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Vigente', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30', 'es_activo' => true]);
        $this->anterior = PeriodoAcademico::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Anterior', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30', 'es_activo' => false]);
        $curso = Curso::create(['institucion_id' => $this->institucion->id, 'nombre' => 'Octavo', 'grado' => 8, 'nivel' => 'EGB', 'estado' => 'ACTIVO']);
        $this->paralelo = Paralelo::create(['curso_id' => $curso->id, 'letra' => 'A', 'estado' => 'ACTIVO']);
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        foreach (['Ana', 'Luis'] as $nombre) {
            $persona = Persona::create(['primer_nombre' => $nombre, 'primer_apellido' => 'Pérez']);
            $usuario = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $this->institucion->id, 'username' => $nombre, 'estado' => 'ACTIVO', 'password_hash' => 'test']);
            $usuario->roles()->attach($rol->id, ['institucion_id' => $this->institucion->id]);
            if ($nombre === 'Ana') {
                $this->docente = $usuario;
            } else {
                $this->reemplazo = $usuario;
            }
        }
        $this->withSession(['auth_user' => ['role' => 'directivo', 'institucion_id' => $this->institucion->id]]);
    }

    private function guardar(PeriodoAcademico $periodo, ?Usuario $docente)
    {
        return $this->put(route('directivo.tutorias.update', $this->paralelo), ['periodo_id' => $periodo->id, 'docente_id' => $docente?->id]);
    }

    public function test_selecciona_vigente_y_permite_asignar_cambiar_y_retirar_sin_afectar_otros_periodos(): void
    {
        $this->get(route('directivo.tutorias.index'))->assertOk()->assertViewHas('periodo', fn ($p) => $p->id === $this->vigente->id)->assertSee('Ana Pérez')->assertSee('Docentes tutores');
        $this->guardar($this->anterior, $this->docente)->assertSessionHasNoErrors()->assertRedirect(route('directivo.tutorias.index', ['periodo' => $this->anterior->id]));
        $this->assertNull($this->paralelo->fresh()->tutor_id);
        $this->guardar($this->vigente, $this->reemplazo)->assertSessionHasNoErrors();
        $this->assertSame($this->reemplazo->id, $this->paralelo->fresh()->tutor_id);
        $this->guardar($this->vigente, $this->docente)->assertSessionHasNoErrors();
        $this->assertSame(2, TutoriaParalelo::count());
        $this->guardar($this->vigente, null)->assertSessionHasNoErrors();
        $this->assertNull($this->paralelo->fresh()->tutor_id);
        $this->assertSame($this->docente->id, TutoriaParalelo::sole()->docente_id);
        $this->assertSame($this->anterior->id, TutoriaParalelo::sole()->periodo_id);
        $this->assertSame(0, AsignacionDocente::count());
    }

    public function test_reporte_y_pantalla_consultan_tutor_del_periodo_sin_usar_el_tutor_global(): void
    {
        $this->guardar($this->vigente, $this->docente)->assertSessionHasNoErrors();
        $this->guardar($this->anterior, $this->reemplazo)->assertSessionHasNoErrors();
        $reporte = app(ReporteDocentesAgrupado::class);
        foreach ([[$this->vigente, $this->docente], [$this->anterior, $this->reemplazo]] as [$periodo, $docente]) {
            $datos = $reporte->datos($this->institucion, $periodo, 'tutores');
            $this->assertSame($docente->id, $datos['grupos']->sole()['filas']->sole()['docente_id']);
            $this->get(route('directivo.tutorias.index', ['periodo' => $periodo->id]))->assertOk()
                ->assertViewHas('paralelos', fn ($filas) => $filas->sole()->tutorias->sole()->docente_id === $docente->id);
        }
    }

    public function test_rechaza_docentes_ajenos_inactivos_sin_rol_y_periodos_ajenos(): void
    {
        $otra = Institucion::create(['nombre' => 'Ajena', 'estado' => 'ACTIVO']);
        $periodoAjeno = PeriodoAcademico::create(['institucion_id' => $otra->id, 'nombre' => 'Ajeno', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']);
        $this->guardar($periodoAjeno, $this->docente)->assertSessionHasErrors('periodo_id');
        $this->docente->update(['estado' => 'INACTIVO']);
        $this->guardar($this->vigente, $this->docente)->assertSessionHasErrors('docente_id');
        $this->docente->update(['estado' => 'ACTIVO', 'institucion_id' => $otra->id]);
        $this->guardar($this->vigente, $this->docente)->assertSessionHasErrors('docente_id');
        $this->reemplazo->roles()->detach();
        $this->guardar($this->vigente, $this->reemplazo)->assertSessionHasErrors('docente_id');
        $this->get(route('directivo.tutorias.index', ['periodo' => $periodoAjeno->id]))->assertNotFound();
        $this->assertSame(0, TutoriaParalelo::count());
    }

    public function test_bloquea_paralelos_ajenos_inactivos_y_acceso_de_otro_rol(): void
    {
        $this->paralelo->update(['estado' => 'INACTIVO']);
        $this->guardar($this->vigente, $this->docente)->assertSessionHasErrors('docente_id');
        $otra = Institucion::create(['nombre' => 'Ajena', 'estado' => 'ACTIVO']);
        $this->paralelo->curso->update(['institucion_id' => $otra->id]);
        $this->guardar($this->vigente, $this->docente)->assertForbidden();
        $this->withSession(['auth_user' => ['role' => 'docente', 'institucion_id' => $this->institucion->id]])
            ->get(route('directivo.tutorias.index'))->assertRedirect(route('roles.dashboard', 'docente'));
        $this->assertSame(0, TutoriaParalelo::count());
    }

    public function test_no_quita_un_tutor_si_la_peticion_omite_el_campo_docente(): void
    {
        $this->guardar($this->vigente, $this->docente)->assertSessionHasNoErrors();
        $this->put(route('directivo.tutorias.update', $this->paralelo), ['periodo_id' => $this->vigente->id])->assertSessionHasErrors('docente_id');
        $this->assertSame($this->docente->id, TutoriaParalelo::sole()->docente_id);
    }

    public function test_admin_sincroniza_tutor_del_vigente_sin_modificar_historico(): void
    {
        $this->guardar($this->anterior, $this->reemplazo)->assertSessionHasNoErrors();
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]])
            ->put(route('admin.paralelos.update', $this->paralelo), ['curso_id' => $this->paralelo->curso_id, 'letra' => 'A', 'estado' => 'ACTIVO', 'capacidad' => 30, 'tutor_id' => $this->docente->id])->assertSessionHasNoErrors();
        $this->assertSame($this->docente->id, TutoriaParalelo::where('periodo_id', $this->vigente->id)->sole()->docente_id);
        $this->assertSame($this->reemplazo->id, TutoriaParalelo::where('periodo_id', $this->anterior->id)->sole()->docente_id);
    }

    public function test_tutor_no_puede_tener_dos_paralelos_en_el_mismo_periodo(): void
    {
        $otro = Paralelo::create(['curso_id' => $this->paralelo->curso_id, 'letra' => 'B', 'estado' => 'ACTIVO']);
        $this->guardar($this->vigente, $this->docente)->assertSessionHasNoErrors();
        $this->guardar($this->vigente, $this->docente)->assertSessionHasNoErrors();
        $this->put(route('directivo.tutorias.update', $otro), ['periodo_id' => $this->vigente->id, 'docente_id' => $this->docente->id])
            ->assertSessionHasErrors('docente_id');
        $this->assertNull($otro->fresh()->tutor_id);
        $this->assertSame(1, TutoriaParalelo::count());
        $this->put(route('directivo.tutorias.update', $otro), ['periodo_id' => $this->anterior->id, 'docente_id' => $this->docente->id])
            ->assertSessionHasNoErrors();
        $this->withSession(['auth_user' => ['role' => 'admin', 'institucion_id' => $this->institucion->id]])
            ->put(route('admin.paralelos.update', $otro), ['curso_id' => $otro->curso_id, 'letra' => 'B', 'estado' => 'ACTIVO', 'capacidad' => 30, 'tutor_id' => $this->docente->id])
            ->assertSessionHasErrors('tutor_id');
        $this->assertNull($otro->fresh()->tutor_id);
    }

    public function test_sin_periodos_muestra_instruccion_para_registrarlos(): void
    {
        $this->vigente->delete();
        $this->anterior->delete();
        $this->get(route('directivo.tutorias.index'))->assertOk()->assertSee('Primero necesitas un período lectivo');
    }
}
