<?php

namespace Tests\Feature;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Carbon;
use Tests\TestCase;

class SuperusuarioResumenTest extends TestCase
{
    use RefreshDatabase;

    public function test_summary_uses_real_counts_and_ecuador_access_windows(): void
    {
        $this->travelTo(Carbon::parse('2026-10-05 15:00:00', 'America/Guayaquil'));
        $rol = Rol::create(['codigo' => 'ADMIN', 'nombre' => 'Administrador']);
        $otroRol = Rol::create(['codigo' => 'DOC', 'nombre' => 'Docente']);
        $institucion = Institucion::create(['nombre' => 'Institución actual', 'estado' => 'ACTIVA']);
        Institucion::create(['nombre' => 'Institución sin administrador', 'estado' => 'INACTIVO']);
        foreach ([['hoy', 'ACTIVO', '2026-10-05 05:00:00'], ['ayer', 'INACTIVO', '2026-10-05 04:59:59'],
            ['semana', 'ACTIVO', '2026-09-29 05:00:00'], ['anterior', 'ACTIVO', '2026-09-29 04:59:59'],
            ['sin-acceso', 'ACTIVO', null]] as [$username, $estado, $ultimoAcceso]) {
            $usuario = Usuario::create(['username' => $username, 'password_hash' => 'test', 'estado' => $estado,
                'institucion_id' => $institucion->id, 'ultimo_acceso' => $ultimoAcceso]);
            $usuario->roles()->attach($rol);
        }
        $docente = Usuario::create(['username' => 'docente-excluido', 'password_hash' => 'test', 'ultimo_acceso' => now()]);
        $docente->roles()->attach($otroRol);

        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('roles.dashboard', 'superusuario'))->assertOk()
            ->assertViewHas('dashboardSection', 'resumen')
            ->assertViewHas('resumen', fn ($datos) => $datos['institucionesTotal'] === 2
                && $datos['institucionesActivas'] === 1 && $datos['administradoresTotal'] === 5
                && $datos['administradoresActivos'] === 4 && $datos['administradoresBloqueados'] === 1
                && $datos['accesosHoy'] === 1 && $datos['accesosSemana'] === 3
                && $datos['sinAcceso'] === 1 && $datos['institucionesSinAdministrador'] === 1)
            ->assertSee('Institución actual')->assertSee('Últimos accesos de administradores')
            ->assertDontSee('docente-excluido')->assertDontSee('<h3>Agenda del día</h3>', false)
            ->assertDontSee('Solicitudes pendientes')->assertDontSee('Revision pendiente');
    }

    public function test_empty_database_displays_zero_counts_and_empty_accesses(): void
    {
        $this->withSession(['auth_user' => ['role' => 'superusuario']])
            ->get(route('roles.dashboard', 'superusuario'))->assertOk()
            ->assertViewHas('resumen', fn ($datos) => $datos['institucionesTotal'] === 0
                && $datos['administradoresTotal'] === 0 && $datos['accesosHoy'] === 0)
            ->assertSee('No hay accesos de administradores registrados.');
    }

    public function test_guest_cannot_access_superusuario_summary(): void
    {
        $this->get(route('roles.dashboard', 'superusuario'))->assertRedirect();
    }
}
