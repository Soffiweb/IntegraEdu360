<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\Rule;

class DocenteController extends Controller
{
    public function index(Request $request)
    {
        $rolDocente = $this->docenteRole();
        $institucionActiva = $this->activeInstitucion($request);

        $query = Usuario::query()
            ->with(['persona', 'institucion', 'roles'])
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $rolDocente->id));

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('username', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhereHas('institucion', fn ($institucionQuery) => $institucionQuery->where('nombre', 'like', "%{$search}%"))
                    ->orWhereHas('persona', function ($personaQuery) use ($search) {
                        $personaQuery
                            ->where('numero_identificacion', 'like', "%{$search}%")
                            ->orWhere('primer_nombre', 'like', "%{$search}%")
                            ->orWhere('segundo_nombre', 'like', "%{$search}%")
                            ->orWhere('primer_apellido', 'like', "%{$search}%")
                            ->orWhere('segundo_apellido', 'like', "%{$search}%")
                            ->orWhere('telefono', 'like', "%{$search}%")
                            ->orWhere('celular', 'like', "%{$search}%")
                            ->orWhere('email', 'like', "%{$search}%");
                    });
            });
        }

        if ($institucionActiva) {
            $query->where('institucion_id', $institucionActiva->id);
        } elseif ($institucionId = $request->string('institucion')->toString()) {
            $query->where('institucion_id', $institucionId);
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $docentes = $query
            ->orderByRaw("CASE WHEN estado = 'ACTIVO' THEN 0 WHEN estado = 'OBSERVACION' THEN 1 ELSE 2 END")
            ->orderBy('username')
            ->paginate(8)
            ->withQueryString();

        $docenteEditando = null;

        if ($request->filled('edit')) {
            $docenteEditando = Usuario::query()
                ->with(['persona', 'institucion', 'roles'])
                ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $rolDocente->id))
                ->findOrFail((int) $request->input('edit'));

            abort_unless(! $institucionActiva || (int) $docenteEditando->institucion_id === (int) $institucionActiva->id, 404);
        }

        $metricasQuery = Usuario::query()
            ->whereHas('roles', fn ($roleQuery) => $roleQuery->where('roles.id', $rolDocente->id));

        if ($institucionActiva) {
            $metricasQuery->where('institucion_id', $institucionActiva->id);
        }

        return view('admin.docentes.index', [
            'docentes' => $docentes,
            'docenteEditando' => $docenteEditando,
            'instituciones' => $institucionActiva ? collect([$institucionActiva]) : $this->availableInstituciones(),
            'institucionActiva' => $institucionActiva,
            'estados' => $this->estados(),
            'sexos' => $this->sexos(),
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
        $validated = $this->validatedData($request, null, $this->activeInstitucion($request));
        $rolDocente = $this->docenteRole();

        DB::transaction(function () use ($validated, $rolDocente) {
            $persona = Persona::create($this->personaPayload($validated));
            $usuario = Usuario::create($this->usuarioPayload($validated, $persona->id));

            $this->syncDocenteRole($usuario, $rolDocente, (int) $validated['institucion_id']);
        });

        return redirect()
            ->route('admin.docentes.index')
            ->with('status', 'Docente creado correctamente.');
    }

    public function update(Request $request, Usuario $usuario)
    {
        $rolDocente = $this->docenteRole();
        $this->ensureDocenteUsuario($usuario, $rolDocente->id);
        $institucionActiva = $this->activeInstitucion($request);
        abort_unless(! $institucionActiva || (int) $usuario->institucion_id === (int) $institucionActiva->id, 404);

        $validated = $this->validatedData($request, $usuario, $institucionActiva);

        DB::transaction(function () use ($validated, $usuario, $rolDocente) {
            $persona = $usuario->persona ?? new Persona();

            $persona->fill($this->personaPayload($validated));
            $persona->save();

            $usuario->update($this->usuarioPayload($validated, $persona->id));
            $this->syncDocenteRole($usuario, $rolDocente, (int) $validated['institucion_id']);
        });

        return redirect()
            ->route('admin.docentes.index')
            ->with('status', 'Docente actualizado correctamente.');
    }

    public function destroy(Usuario $usuario)
    {
        $rolDocente = $this->docenteRole();
        $this->ensureDocenteUsuario($usuario, $rolDocente->id);
        $institucionActiva = $this->activeInstitucion(request());
        abort_unless(! $institucionActiva || (int) $usuario->institucion_id === (int) $institucionActiva->id, 404);

        DB::transaction(function () use ($usuario) {
            $personaId = $usuario->persona_id;

            $usuario->roles()->detach();
            $usuario->delete();

            if ($personaId && Usuario::where('persona_id', $personaId)->doesntExist()) {
                Persona::whereKey($personaId)->delete();
            }
        });

        return redirect()
            ->route('admin.docentes.index')
            ->with('status', 'Docente eliminado correctamente.');
    }

    private function validatedData(Request $request, ?Usuario $usuario = null, ?Institucion $institucionActiva = null): array
    {
        $personaId = $usuario?->persona_id;

        $validated = $request->validate([
            'institucion_id' => ['required', 'integer', Rule::exists('instituciones', 'id')],
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
            'password_hash' => [$usuario ? 'nullable' : 'required', 'string', 'max:255'],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
            'ultimo_acceso' => ['nullable', 'date'],
            'numero_identificacion' => [
                'required',
                'string',
                'max:30',
                Rule::unique('personas', 'numero_identificacion')->ignore($personaId),
            ],
            'primer_nombre' => ['required', 'string', 'max:120'],
            'segundo_nombre' => ['nullable', 'string', 'max:120'],
            'primer_apellido' => ['required', 'string', 'max:120'],
            'segundo_apellido' => ['nullable', 'string', 'max:120'],
            'fecha_nacimiento' => ['nullable', 'date'],
            'sexo' => ['nullable', Rule::in(array_keys($this->sexos()))],
            'telefono' => ['nullable', 'string', 'max:50'],
            'celular' => ['nullable', 'string', 'max:50'],
            'email_persona' => ['nullable', 'email', 'max:255'],
            'provincia' => ['nullable', 'string', 'max:120'],
            'canton' => ['nullable', 'string', 'max:120'],
            'parroquia' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
        ]);

        if (blank($validated['password_hash'] ?? null)) {
            unset($validated['password_hash']);
        }

        if ($institucionActiva) {
            $validated['institucion_id'] = $institucionActiva->id;
        }

        $validated['estado'] = strtoupper(trim($validated['estado']));

        return $validated;
    }

    private function personaPayload(array $validated): array
    {
        return [
            'numero_identificacion' => trim($validated['numero_identificacion']),
            'primer_nombre' => trim($validated['primer_nombre']),
            'segundo_nombre' => $this->nullableTrim($validated['segundo_nombre'] ?? null),
            'primer_apellido' => trim($validated['primer_apellido']),
            'segundo_apellido' => $this->nullableTrim($validated['segundo_apellido'] ?? null),
            'fecha_nacimiento' => $validated['fecha_nacimiento'] ?? null,
            'sexo' => $validated['sexo'] ?? null,
            'telefono' => $this->nullableTrim($validated['telefono'] ?? null),
            'celular' => $this->nullableTrim($validated['celular'] ?? null),
            'email' => $this->nullableTrim($validated['email_persona'] ?? null),
            'provincia' => $this->nullableTrim($validated['provincia'] ?? null),
            'canton' => $this->nullableTrim($validated['canton'] ?? null),
            'parroquia' => $this->nullableTrim($validated['parroquia'] ?? null),
            'direccion' => $this->nullableTrim($validated['direccion'] ?? null),
        ];
    }

    private function usuarioPayload(array $validated, int $personaId): array
    {
        $payload = [
            'persona_id' => $personaId,
            'institucion_id' => (int) $validated['institucion_id'],
            'username' => trim($validated['username']),
            'email' => $this->nullableTrim($validated['email'] ?? null),
            'estado' => $validated['estado'],
            'ultimo_acceso' => $validated['ultimo_acceso'] ?? null,
        ];

        if (isset($validated['password_hash'])) {
            $payload['password_hash'] = $validated['password_hash'];
        }

        return $payload;
    }

    private function syncDocenteRole(Usuario $usuario, Rol $rolDocente, int $institucionId): void
    {
        $usuario->roles()->sync([
            $rolDocente->id => ['institucion_id' => $institucionId],
        ]);
    }

    private function ensureDocenteUsuario(Usuario $usuario, int $rolDocenteId): void
    {
        abort_unless($usuario->roles()->where('roles.id', $rolDocenteId)->exists(), 404);
    }

    private function docenteRole(): Rol
    {
        return Rol::query()
            ->where(function ($query) {
                $query
                    ->where('codigo', '04')
                    ->orWhereRaw('UPPER(nombre) = ?', ['DOCENTE']);
            })
            ->firstOrFail();
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

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'OBSERVACION' => 'Observacion',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function sexos(): array
    {
        return [
            'M' => 'Masculino',
            'F' => 'Femenino',
            'O' => 'Otro',
        ];
    }

    private function nullableTrim(?string $value): ?string
    {
        $value = is_string($value) ? trim($value) : null;

        return $value === '' ? null : $value;
    }
}

