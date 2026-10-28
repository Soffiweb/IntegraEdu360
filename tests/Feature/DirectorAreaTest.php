<?php

namespace Tests\Feature;

use App\Models\DirectorArea;
use App\Models\Institucion;
use App\Models\PeriodoAcademico;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DirectorAreaTest extends TestCase
{
    use RefreshDatabase;

    public function test_designaciones_son_unicas_por_periodo_y_permiten_reemplazar_y_retirar(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'estado' => 'ACTIVO']);
        $periodo = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Vigente', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30', 'es_activo' => true]);
        $anterior = PeriodoAcademico::create(['institucion_id' => $institucion->id, 'nombre' => 'Anterior', 'fecha_inicio' => '2025-09-01', 'fecha_fin' => '2026-06-30']);
        $rol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $docentes = collect(['Ana', 'Luis'])->map(function ($nombre) use ($institucion, $rol) {
            $docente = Usuario::create(['institucion_id' => $institucion->id, 'username' => $nombre, 'password_hash' => 'test', 'estado' => 'ACTIVO']);
            $docente->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);

            return $docente;
        });
        $this->withSession(['auth_user' => ['role' => 'directivo', 'institucion_id' => $institucion->id]]);
        $ruta = route('directivo.directores-area.update');
        $datos = ['periodo_id' => $periodo->id, 'area' => 'MATEMATICA', 'docente_id' => $docentes[0]->id];
        $this->get(route('directivo.directores-area.index'))->assertOk()->assertSee('Directores de área')->assertViewHas('periodo', fn ($p) => $p->id === $periodo->id);
        $this->put($ruta, $datos)->assertSessionHasNoErrors();
        $this->put($ruta, $datos)->assertSessionHasNoErrors();
        $this->assertSame(1, DirectorArea::count());
        $this->put($ruta, array_replace($datos, ['area' => 'IDIOMAS']))->assertSessionHasErrors('docente_id');
        $this->assertSame(1, DirectorArea::count());
        $this->put($ruta, array_replace($datos, ['area' => 'IDIOMAS', 'periodo_id' => $anterior->id]))->assertSessionHasNoErrors();
        $this->put($ruta, array_replace($datos, ['docente_id' => $docentes[1]->id]))->assertSessionHasNoErrors();
        $this->assertSame($docentes[1]->id, DirectorArea::where('periodo_id', $periodo->id)->sole()->docente_id);
        $this->get(route('directivo.directores-area.index', ['periodo' => $anterior->id]))->assertOk()->assertViewHas('designaciones', fn ($d) => $d->get('IDIOMAS')->docente_id === $docentes[0]->id);
        $this->put($ruta, array_replace($datos, ['docente_id' => null]))->assertSessionHasNoErrors();
        $this->assertSame($anterior->id, DirectorArea::sole()->periodo_id);
        $this->put($ruta, array_replace($datos, ['area' => 'INVALIDA']))->assertSessionHasErrors('area');
        $docentes[0]->update(['estado' => 'INACTIVO']);
        $this->put($ruta, $datos)->assertSessionHasErrors('docente_id');
        $docentes[1]->roles()->detach();
        $this->put($ruta, array_replace($datos, ['docente_id' => $docentes[1]->id]))->assertSessionHasErrors('docente_id');
        $otra = Institucion::create(['nombre' => 'Ajena', 'estado' => 'ACTIVO']);
        $docentes[0]->update(['institucion_id' => $otra->id, 'estado' => 'ACTIVO']);
        $this->put($ruta, $datos)->assertSessionHasErrors('docente_id');
        $ajeno = PeriodoAcademico::create(['institucion_id' => $otra->id, 'nombre' => 'Ajeno', 'fecha_inicio' => '2026-09-01', 'fecha_fin' => '2027-06-30']);
        $this->put($ruta, array_replace($datos, ['periodo_id' => $ajeno->id, 'docente_id' => null]))->assertSessionHasErrors('periodo_id');
        $this->get(route('directivo.directores-area.index', ['periodo' => $ajeno->id]))->assertNotFound();
        $this->withSession(['auth_user' => ['role' => 'docente', 'institucion_id' => $institucion->id]])->put($ruta, $datos)->assertRedirect();
    }

    public function test_sin_periodos_presenta_instruccion_para_registrarlos(): void
    {
        $institucion = Institucion::create(['nombre' => 'Colegio', 'estado' => 'ACTIVO']);
        $this->withSession(['auth_user' => ['role' => 'directivo', 'institucion_id' => $institucion->id]])
            ->get(route('directivo.directores-area.index'))->assertOk()->assertSee('Primero necesitas un período lectivo');
    }
}
