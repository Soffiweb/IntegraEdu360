<?php

namespace App\Services\Directivo;

use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;

class ContextoDocente
{
    public function institucion(Request $request): Institucion
    {
        $sesion = $request->session()->get('auth_user', []);
        abort_unless(($sesion['role'] ?? null) === 'directivo', 403);
        $id = $sesion['institucion_id'] ?? null;
        if (! $id && ! empty($sesion['id'])) {
            $id = Usuario::find($sesion['id'])?->institucion_id;
        }
        $institucion = $id ? Institucion::find($id) : null;
        abort_unless($institucion, 403, 'La cuenta debe tener una institución asignada.');

        return $institucion;
    }

    public function docentes(int $institucionId): Builder
    {
        return Usuario::query()->where('institucion_id', $institucionId)
            ->whereHas('roles', fn (Builder $query) => $query->whereIn('codigo', ['DOC', 'DOCENTE', '04'])
                ->orWhereRaw('UPPER(nombre) = ?', ['DOCENTE']))
            ->whereDoesntHave('roles', fn (Builder $query) => $query->whereIn('codigo', ['SUPER', 'SUPERUSUARIO']));
    }
}
