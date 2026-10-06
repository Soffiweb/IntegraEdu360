<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Http\Requests\Superusuario\ListUsuariosRequest;
use App\Http\Requests\Superusuario\UpdateUsuarioRequest;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Contracts\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Schema;

class UsuarioController extends Controller
{
    public function index(ListUsuariosRequest $request): View
    {
        $filtros = $request->validated();
        $busqueda = trim($filtros['q'] ?? '');
        $hayPersonas = Schema::hasTable((new Persona)->getTable());
        $usuarioEditando = ! empty($filtros['edit']) ? Usuario::with('roles')->findOrFail($filtros['edit']) : null;
        if ($usuarioEditando) {
            $this->ensureAdministrator($usuarioEditando);
        }
        $rolesAdministradores = Rol::query()->select(['id', 'codigo', 'nombre'])->get()
            ->filter(fn (Rol $rol) => in_array(integraEduNormalizeRoleValue($rol->codigo), integraEduRoleAliases('admin'), true)
                || in_array(integraEduNormalizeRoleValue($rol->nombre), integraEduRoleAliases('admin'), true))
            ->pluck('id');

        return view('dashboard-role', [
            'roleKey' => 'superusuario',
            'role' => integraEduRoles()['superusuario'],
            'dashboard' => integraEduDashboards()['superusuario'],
            'dashboardSection' => 'usuarios',
            'usuarioEditando' => $usuarioEditando,
            'filtrosUsuarios' => ['q' => $busqueda, 'estado' => $filtros['estado'] ?? ''],
            'usuariosAdministradores' => Usuario::query()
                ->select(['id', 'persona_id', 'institucion_id', 'username', 'email', 'estado', 'ultimo_acceso'])
                ->with($hayPersonas ? ['persona', 'institucion'] : ['institucion'])
                ->whereHas('roles', fn ($query) => $query->whereIn('roles.id', $rolesAdministradores))
                ->when($busqueda !== '', function ($query) use ($busqueda, $hayPersonas) {
                    $patron = '%'.mb_strtolower($busqueda).'%';
                    $query->where(function ($query) use ($patron, $hayPersonas) {
                        $query->whereRaw('LOWER(username) LIKE ?', [$patron])
                            ->orWhereRaw('LOWER(email) LIKE ?', [$patron])
                            ->orWhereHas('institucion', fn ($institucion) => $institucion
                                ->whereRaw('LOWER(nombre) LIKE ?', [$patron])
                                ->orWhereRaw('LOWER(codigo_amie) LIKE ?', [$patron]));
                        if ($hayPersonas) {
                            $query->orWhereHas('persona', function ($persona) use ($patron) {
                                $persona->where(function ($persona) use ($patron) {
                                    foreach (['primer_nombre', 'segundo_nombre', 'primer_apellido', 'segundo_apellido'] as $campo) {
                                        $persona->orWhereRaw('LOWER('.$campo.') LIKE ?', [$patron]);
                                    }
                                });
                            });
                        }
                    });
                })
                ->when(! empty($filtros['estado']), fn ($query) => $query->where('estado', $filtros['estado']))
                ->orderBy('username')
                ->paginate(20)
                ->appends($request->only(['q', 'estado']))
                ->through(fn (Usuario $usuario) => $hayPersonas ? $usuario : $usuario->setRelation('persona', null)),
        ]);
    }

    public function update(UpdateUsuarioRequest $request, Usuario $usuario): RedirectResponse
    {
        $this->ensureAdministrator($usuario);
        $data = $request->safe()->only(['username', 'email']);
        if ($request->filled('password')) {
            $data['password_hash'] = Hash::make($request->validated('password'));
        }
        $usuario->update($data);

        return redirect()->route('superusuario.usuarios.index')->with('success', 'Usuario actualizado correctamente.');
    }

    public function bloquear(Usuario $usuario): RedirectResponse
    {
        $this->ensureAdministrator($usuario);
        $usuario->update(['estado' => 'INACTIVO']);

        return redirect()->route('superusuario.usuarios.index')->with('success', 'Usuario bloqueado correctamente.');
    }

    public function desbloquear(Usuario $usuario): RedirectResponse
    {
        $this->ensureAdministrator($usuario);
        $usuario->update(['estado' => 'ACTIVO']);

        return redirect()->route('superusuario.usuarios.index')->with('success', 'Usuario desbloqueado correctamente.');
    }

    private function ensureAdministrator(Usuario $usuario): void
    {
        $usuario->loadMissing('roles');
        abort_unless(integraEduUsuarioTieneRol($usuario, 'admin'), 404);
    }
}
