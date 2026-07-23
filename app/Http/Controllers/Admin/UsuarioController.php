<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Schema;
use Illuminate\Validation\Rule;

class UsuarioController extends Controller
{
    public function index(Request $request)
    {
        $query = Usuario::query()->with(['persona', 'roles', 'institucion']);
        $institucionActiva = $this->activeInstitucion($request);
        $adminRoleThreshold = $this->adminRoleThresholdForSession($request);
        $institucionSeleccionadaId = $institucionActiva?->id ?: ($request->filled('institucion') ? (int) $request->input('institucion') : null);

        $this->applyAdminRoleThreshold($query, $adminRoleThreshold);

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($institucionQuery) => $institucionQuery->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('persona', function ($personaQuery) use ($search) {
                        $personaQuery
                            ->where('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('segundo_nombre', 'like', "%{$search}%")
                            ->orWhere('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('segundo_apellido', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%")
                            ->orWhere('celular', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($rolId = $request->string('rol')->toString()) {
            $query->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $rolId));
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $usuarios = $query
            ->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 WHEN estado = 'OBSERVACION' THEN 1 ELSE 2 END")
            ->orderBy('username')
            ->paginate(8)
            ->withQueryString();

        $usuarioEditando = null;

        if ($request->filled('edit')) {
            $usuarioEditando = Usuario::with(['persona', 'roles', 'institucion'])->findOrFail((int) $request->input('edit'));
            abort_unless(! $adminRoleThreshold || $this->usuarioMatchesAdminRoleThreshold($usuarioEditando, $adminRoleThreshold), 404);
            abort_unless(! $institucionActiva || (int) $usuarioEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        $roles = $this->availableRoles($adminRoleThreshold);
        $personas = $this->availablePersonas(
            $institucionSeleccionadaId,
            $usuarioEditando?->persona_id
        );

        $metricasQuery = Usuario::query();

        $this->applyAdminRoleThreshold($metricasQuery, $adminRoleThreshold);

        if ($institucionActiva) {
            $metricasQuery->where('institucion_id', $institucionActiva->id);
        }

        return view('admin.usuarios.index', [
            'usuarios' => $usuarios,
            'usuarioEditando' => $usuarioEditando,
            'roles' => $roles,
            'rolesPorInstitucion' => $this->availableRolesGroupedByInstitucion($adminRoleThreshold),
            'personas' => $personas,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'estados' => $this->estados(),
            'metricas' => [
                'total' => (clone $metricasQuery)->count(),
                'activos' => (clone $metricasQuery)->where('estado', 'ACTIVO')->count(),
                'observacion' => (clone $metricasQuery)->where('estado', 'OBSERVACION')->count(),
                'inactivos' => (clone $metricasQuery)->where('estado', 'INACTIVO')->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request, null, $this->activeInstitucion($request), $this->adminRoleThresholdForSession($request));

        $usuario = Usuario::create([
            'persona_id' => $validated['persona_id'],
            'institucion_id' => $validated['institucion_id'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'password_hash' => $validated['password_hash'],
            'estado' => $validated['estado'],
            'ultimo_acceso' => $validated['ultimo_acceso'] ?? null,
        ]);

        $this->syncUsuarioRoles($usuario, $validated['rol_ids'], (int) $validated['institucion_id']);

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario creado correctamente en la tabla usuarios.');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $institucionActiva = $this->activeInstitucion($request);
        $adminRoleThreshold = $this->adminRoleThresholdForSession($request);

        abort_unless(! $adminRoleThreshold || $this->usuarioMatchesAdminRoleThreshold($usuario, $adminRoleThreshold), 404);
        abort_unless(! $institucionActiva || (int) $usuario->institucion_id === (int) $institucionActiva->id, 404);

        $validated = $this->validatedData($request, $usuario, $institucionActiva, $adminRoleThreshold);

        $payload = [
            'persona_id' => $validated['persona_id'],
            'institucion_id' => $validated['institucion_id'],
            'username' => $validated['username'],
            'email' => $validated['email'] ?? null,
            'estado' => $validated['estado'],
            'ultimo_acceso' => $validated['ultimo_acceso'] ?? null,
        ];

        if (! empty($validated['password_hash'])) {
            $payload['password_hash'] = $validated['password_hash'];
        }

        $usuario->update($payload);
        $this->syncUsuarioRoles($usuario, $validated['rol_ids'], (int) $validated['institucion_id']);

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario actualizado correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $institucionActiva = $this->activeInstitucion(request());
        $adminRoleThreshold = $this->adminRoleThresholdForSession(request());

        abort_unless(! $adminRoleThreshold || $this->usuarioMatchesAdminRoleThreshold($usuario, $adminRoleThreshold), 404);
        abort_unless(! $institucionActiva || (int) $usuario->institucion_id === (int) $institucionActiva->id, 404);

        $usuario->roles()->detach();
        $usuario->delete();

        return redirect()
            ->route('admin.usuarios.index')
            ->with('status', 'Usuario eliminado correctamente.');
    }

    private function validatedData(
        Request $request,
        ?Usuario $usuario = null,
        ?Institucion $institucionActiva = null,
        ?Rol $adminRoleThreshold = null,
    ): array
    {
        $rules = [
            'persona_id' => ['nullable'],
            'institucion_id' => ['required'],
            'username' => [
                'required',
                'string',
                'max:255',
                Rule::unique('usuarios', 'username')->ignore($usuario?->id),
            ],
            'email' => [
                'nullable',
                'email',
                'max:255',
                Rule::unique('usuarios', 'email')->ignore($usuario?->id),
            ],
            'rol_ids' => ['required', 'array', 'min:1'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
            'ultimo_acceso' => ['nullable', 'date'],
            'password_hash' => [$usuario ? 'nullable' : 'required', 'string', 'max:255'],
        ];

        if (Schema::hasTable('personas')) {
            $rules['persona_id'][] = 'exists:personas,id';
        }

        if (Schema::hasTable('instituciones') && Schema::hasColumn('usuarios', 'institucion_id')) {
            $rules['institucion_id'][] = 'exists:instituciones,id';
        }

        if (Schema::hasTable('roles')) {
            $rules['rol_ids.*'] = ['exists:roles,id'];
        }

        $validated = $request->validate($rules, [
            'rol_ids.required' => 'Debe seleccionar al menos un rol.',
            'rol_ids.min' => 'Debe seleccionar al menos un rol.',
        ]);

        if (blank($validated['password_hash'] ?? null)) {
            unset($validated['password_hash']);
        }

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado'] = strtoupper($validated['estado']);
        $validated['rol_ids'] = array_values(array_unique(array_map('intval', $validated['rol_ids'])));

        if ($adminRoleThreshold) {
            $allowedRoleIds = $this->availableRoles($adminRoleThreshold)
                ->pluck('id')
                ->map(fn ($id) => (int) $id)
                ->all();

            foreach ($validated['rol_ids'] as $rolId) {
                abort_unless(in_array($rolId, $allowedRoleIds, true), 422, 'El rol seleccionado no esta permitido para este nivel de administrador.');
            }
        }

        if (! empty($validated['persona_id'])) {
            $institucionId = (int) $validated['institucion_id'];
            abort_unless(
                $this->personaDisponibleParaInstitucion((int) $validated['persona_id'], $institucionId, $usuario?->persona_id),
                422,
                'La persona seleccionada no esta vinculada con la institucion elegida.'
            );
        }

        return $validated;
    }

    private function syncUsuarioRoles(Usuario $usuario, array $rolIds, int $institucionId): void
    {
        $usuario->roles()->newPivotStatement()
            ->where('usuario_id', $usuario->id)
            ->delete();

        $payload = [];

        foreach ($rolIds as $rolId) {
            $payload[(int) $rolId] = ['institucion_id' => $institucionId];
        }

        $usuario->roles()->attach($payload);
    }

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'OBSERVACION' => 'Observacion',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function availableRoles(?Rol $adminRoleThreshold = null)
    {
        $query = Rol::query();

        if ($adminRoleThreshold) {
            $this->applyRoleThresholdToRolesQuery($query, $adminRoleThreshold);
        }

        return $query->orderBy('nombre')->get(['id', 'nombre', 'codigo']);
    }

    private function availableRolesGroupedByInstitucion(?Rol $adminRoleThreshold = null): array
    {
        $roleQuery = Rol::query();

        if ($adminRoleThreshold) {
            $this->applyRoleThresholdToRolesQuery($roleQuery, $adminRoleThreshold);
        }

        $allRoleIds = $roleQuery->pluck('id')->map(fn ($id) => (int) $id)->all();

        return Institucion::query()
            ->pluck('id')
            ->mapWithKeys(fn ($id) => [(string) $id => $allRoleIds])
            ->all();
    }

    private function availableInstituciones()
    {
        return Institucion::query()
            ->orderBy('nombre')
            ->get(['id', 'nombre']);
    }

    private function activeInstitucion(Request $request): ?Institucion
    {
        $sessionUser = $request->session()->get('auth_user', []);
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            return Institucion::find($institucionId);
        }

        $usuarioId = $sessionUser['id'] ?? null;

        if (! $usuarioId) {
            return null;
        }

        $usuario = Usuario::find($usuarioId);

        return $usuario?->institucion_id ? Institucion::find($usuario->institucion_id) : null;
    }

    private function availablePersonas(?int $institucionId = null, ?int $currentPersonaId = null): Collection
    {
        return Persona::query()
            ->with(['usuarios:id,persona_id,institucion_id'])
            ->when($institucionId, function (Builder $query) use ($institucionId, $currentPersonaId) {
                $query->where(function (Builder $personaQuery) use ($institucionId, $currentPersonaId) {
                    $personaQuery->whereHas('usuarios', fn (Builder $usuarioQuery) => $usuarioQuery->where('institucion_id', $institucionId));

                    if ($currentPersonaId) {
                        $personaQuery->orWhere('id', $currentPersonaId);
                    }
                });
            })
            ->orderByRaw('LOWER(COALESCE(primer_apellido, \'\'))')
            ->orderByRaw('LOWER(COALESCE(segundo_apellido, \'\'))')
            ->orderByRaw('LOWER(COALESCE(primer_nombre, \'\'))')
            ->orderByRaw('LOWER(COALESCE(segundo_nombre, \'\'))')
            ->get()
            ->map(function (Persona $persona) {
                $apellidos = trim(implode(' ', array_filter([
                    $persona->primer_apellido,
                    $persona->segundo_apellido,
                ])));
                $nombres = trim(implode(' ', array_filter([
                    $persona->primer_nombre,
                    $persona->segundo_nombre,
                ])));

                $persona->nombre_mostrar = trim(implode(', ', array_filter([$apellidos, $nombres])));
                $persona->nombre_busqueda = strtolower(trim(implode(' ', array_filter([
                    $apellidos,
                    $nombres,
                    $persona->numero_identificacion,
                    $persona->email,
                ]))));
                $persona->institucion_ids = $persona->usuarios
                    ->pluck('institucion_id')
                    ->filter()
                    ->map(fn ($id) => (int) $id)
                    ->unique()
                    ->values()
                    ->all();

                return $persona;
            });
    }

    private function personaDisponibleParaInstitucion(int $personaId, int $institucionId, ?int $currentPersonaId = null): bool
    {
        if ($currentPersonaId !== null && $personaId === (int) $currentPersonaId) {
            return true;
        }

        if (! Usuario::where('institucion_id', $institucionId)->exists()) {
            return Persona::whereKey($personaId)->exists();
        }

        return Persona::query()
            ->whereKey($personaId)
            ->whereHas('usuarios', fn (Builder $query) => $query->where('institucion_id', $institucionId))
            ->exists();
    }

    private function adminRoleThresholdForSession(Request $request): ?Rol
    {
        $sessionUser = $request->session()->get('auth_user', []);

        if (($sessionUser['role'] ?? null) !== 'admin') {
            return null;
        }

        return $this->adminRole();
    }

    private function adminRole(): ?Rol
    {
        $aliases = array_map('integraEduNormalizeRoleValue', integraEduRoleAliases('admin'));

        return Rol::query()
            ->orderBy('codigo')
            ->get()
            ->first(function (Rol $rol) use ($aliases) {
                foreach (array_filter([$rol->codigo ?? null, $rol->nombre ?? null]) as $candidate) {
                    if (in_array(integraEduNormalizeRoleValue($candidate), $aliases, true)) {
                        return true;
                    }
                }

                return false;
            });
    }

    private function applyAdminRoleThreshold(Builder $query, ?Rol $adminRoleThreshold): void
    {
        if (! $adminRoleThreshold) {
            return;
        }

        $query->whereHas('roles', function (Builder $roleQuery) use ($adminRoleThreshold) {
            $this->applyRoleThresholdToRolesQuery($roleQuery, $adminRoleThreshold);
        });
    }

    private function applyRoleThresholdToRolesQuery(Builder $query, Rol $adminRoleThreshold): void
    {
        $codigo = trim((string) ($adminRoleThreshold->codigo ?? ''));

        if ($codigo !== '' && preg_match('/^\d+$/', $codigo) === 1) {
            $query->whereRaw("roles.codigo ~ '^[0-9]+$' AND CAST(roles.codigo AS INTEGER) >= ?", [(int) $codigo]);

            return;
        }

        $query->where('roles.id', $adminRoleThreshold->id);
    }

    private function usuarioMatchesAdminRoleThreshold(Usuario $usuario, Rol $adminRoleThreshold): bool
    {
        return $usuario->roles()
            ->where(function (Builder $query) use ($adminRoleThreshold) {
                $this->applyRoleThresholdToRolesQuery($query, $adminRoleThreshold);
            })
            ->exists();
    }
}
