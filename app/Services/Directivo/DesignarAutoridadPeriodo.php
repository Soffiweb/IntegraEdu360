<?php

namespace App\Services\Directivo;

use App\Models\AutoridadPeriodo;
use App\Models\PeriodoAcademico;
use App\Models\Usuario;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DesignarAutoridadPeriodo
{
    public function candidatos(int $institucionId): Builder
    {
        return Usuario::where('institucion_id', $institucionId)->where('estado', 'ACTIVO')
            ->whereHas('roles', fn ($q) => $q->whereIn('codigo', ['DOC', 'DOCENTE', '04', 'DIRECTIVO', 'INSPECTOR'])
                ->orWhereRaw('UPPER(nombre) IN (?, ?, ?)', ['DOCENTE', 'DIRECTIVO', 'INSPECTOR']))
            ->whereDoesntHave('roles', fn ($q) => $q->whereIn('codigo', ['SUPER', 'SUPERUSUARIO']));
    }

    public function guardar(int $institucionId, int $periodoId, string $cargo, ?int $docenteId): void
    {
        DB::transaction(function () use ($institucionId, $periodoId, $cargo, $docenteId) {
            PeriodoAcademico::where('institucion_id', $institucionId)->lockForUpdate()->findOrFail($periodoId);
            if (! array_key_exists($cargo, AutoridadPeriodo::CARGOS)) {
                throw ValidationException::withMessages(['cargo' => 'Seleccione un cargo válido.']);
            }
            $clave = ['institucion_id' => $institucionId, 'periodo_id' => $periodoId, 'cargo' => $cargo];
            if ($docenteId === null) {
                AutoridadPeriodo::where($clave)->delete();

                return;
            }
            if (! $this->candidatos($institucionId)->whereKey($docenteId)->exists()) {
                throw ValidationException::withMessages(['docente_id' => 'Seleccione un integrante activo de su institución.']);
            }
            if (AutoridadPeriodo::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)
                ->where('docente_id', $docenteId)->where('cargo', '!=', $cargo)->exists()) {
                throw ValidationException::withMessages(['docente_id' => 'Este docente ya ocupa otro cargo en el período seleccionado.']);
            }
            AutoridadPeriodo::updateOrCreate($clave, ['docente_id' => $docenteId]);
        }, 3);
    }
}
