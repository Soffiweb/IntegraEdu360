<?php

namespace App\Http\Controllers\Superusuario;

use App\Http\Controllers\Controller;
use App\Models\Distrito;
use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rule;

class InstitucionController extends Controller
{
    public function index(Request $request)
    {
        $query = Institucion::query()->with('distrito.zona');

        if ($search = trim((string) $request->string('search'))) {
            $query->where(function ($builder) use ($search) {
                $builder
                    ->where('codigo_amie', 'like', "%{$search}%")
                    ->orWhere('nombre', 'like', "%{$search}%")
                    ->orWhere('email', 'like', "%{$search}%")
                    ->orWhere('provincia', 'like', "%{$search}%")
                    ->orWhere('canton', 'like', "%{$search}%")
                    ->orWhere('parroquia', 'like', "%{$search}%")
                    ->orWhere('telefono', 'like', "%{$search}%");
            });
        }

        if ($estado = $request->string('estado')->toString()) {
            $query->where('estado', strtoupper($estado));
        }

        $instituciones = $query
            ->orderBy('nombre')
            ->paginate(8)
            ->withQueryString();

        $institucionEditando = null;

        if ($request->filled('edit')) {
            $institucionEditando = Institucion::findOrFail((int) $request->input('edit'));
        }

        return view('dashboard-role', [
            'roleKey' => 'superusuario',
            'role' => integraEduRoles()['superusuario'],
            'dashboard' => integraEduDashboards()['superusuario'],
            'dashboardSection' => 'instituciones',
            'instituciones' => $instituciones,
            'distritos' => Distrito::query()->with('zona')->orderBy('codigo')->get(),
            'institucionEditando' => $institucionEditando,
            'estados' => $this->estados(),
            'sostenimientos' => $this->sostenimientos(),
            'regimenes' => $this->regimenes(),
            'metricas' => [
                'total' => Institucion::count(),
                'activos' => Institucion::whereIn('estado', ['ACTIVO', 'ACTIVA'])->count(),
                'inactivos' => Institucion::whereIn('estado', ['INACTIVO', 'INACTIVA'])->count(),
            ],
        ]);
    }

    public function store(Request $request)
    {
        $validated = $this->validatedData($request);

        $passwordHash = Hash::make($validated['codigo_amie']);
        DB::transaction(function () use ($validated, $passwordHash) {
            $institucion = Institucion::create($validated);
            $rol = Rol::firstOrCreate(['codigo' => 'ADMIN'], ['nombre' => 'Administrador']);
            $usuario = Usuario::create([
                'username' => $institucion->codigo_amie,
                'password_hash' => $passwordHash,
                'institucion_id' => $institucion->id,
                'estado' => 'ACTIVO',
            ]);
            $usuario->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);
        });

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institución y cuenta de administrador creadas correctamente. El usuario y la contraseña inicial corresponden al código AMIE.');
    }

    public function update(Request $request, Institucion $institucion)
    {
        $validated = $this->validatedData($request, $institucion);

        $institucion->update($validated);

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institucion actualizada correctamente.');
    }

    public function destroy(Institucion $institucion)
    {
        $institucion->delete();

        return redirect()
            ->route('superusuario.instituciones.index')
            ->with('status', 'Institucion eliminada correctamente.');
    }

    private function validatedData(Request $request, ?Institucion $institucion = null): array
    {
        if (is_string($request->input('codigo_amie'))) {
            $request->merge(['codigo_amie' => strtoupper(trim($request->input('codigo_amie')))]);
        }
        $codigoRules = ['required', 'string', 'max:20', Rule::unique('instituciones', 'codigo_amie')->ignore($institucion?->id)];
        if (! $institucion) {
            $codigoRules[] = Rule::unique('usuarios', 'username');
        }
        $validated = $request->validate([
            'distrito_id' => ['nullable', 'integer', Rule::exists('distritos', 'id')],
            'codigo_amie' => $codigoRules,
            'nombre' => ['required', 'string', 'max:255'],
            'sostenimiento_id' => ['required', 'integer', Rule::exists('cat_sostenimiento', 'id')],
            'regimen_id' => ['required', 'integer', Rule::exists('cat_regimen_escolar', 'id')],
            'provincia' => ['required', 'string', 'max:120'],
            'canton' => ['required', 'string', 'max:120'],
            'parroquia' => ['nullable', 'string', 'max:120'],
            'direccion' => ['nullable', 'string', 'max:255'],
            'telefono' => ['nullable', 'string', 'max:50'],
            'email' => ['nullable', 'email', 'max:255', Rule::unique('instituciones', 'email')->ignore($institucion?->id)],
            'estado' => ['required', Rule::in(array_keys($this->estados()))],
        ]);

        $validated['codigo_amie'] = strtoupper(trim($validated['codigo_amie']));
        $validated['estado'] = strtoupper(trim($validated['estado']));

        return $validated;
    }

    private function estados(): array
    {
        return [
            'ACTIVO' => 'Activo',
            'INACTIVO' => 'Inactivo',
        ];
    }

    private function sostenimientos(): array
    {
        return DB::table('cat_sostenimiento')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }

    private function regimenes(): array
    {
        return DB::table('cat_regimen_escolar')
            ->orderBy('nombre')
            ->pluck('nombre', 'id')
            ->all();
    }
}
