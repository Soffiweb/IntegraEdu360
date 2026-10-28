<?php

namespace App\Services\Directivo;

use App\Models\DirectorArea;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class DesignarDirectorArea
{
    public function guardar(int $institucionId, int $periodoId, string $area, ?int $docenteId): void
    {
        DB::transaction(function () use ($institucionId, $periodoId, $area, $docenteId) {
            PeriodoAcademico::where('institucion_id', $institucionId)->lockForUpdate()->findOrFail($periodoId);
            if (! array_key_exists($area, DirectorArea::AREAS)) {
                throw ValidationException::withMessages(['area' => 'Seleccione un área válida.']);
            }
            $clave = ['institucion_id' => $institucionId, 'periodo_id' => $periodoId, 'area' => $area];
            if ($docenteId === null) {
                DirectorArea::where($clave)->delete();

                return;
            }
            if (! app(ContextoDocente::class)->docentes($institucionId)->where('estado', 'ACTIVO')->whereKey($docenteId)->exists()) {
                throw ValidationException::withMessages(['docente_id' => 'Seleccione un docente activo de su institución.']);
            }
            if (DirectorArea::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)
                ->where('docente_id', $docenteId)->where('area', '!=', $area)->exists()) {
                throw ValidationException::withMessages(['docente_id' => 'Este docente ya dirige otra área en el período seleccionado.']);
            }
            DirectorArea::updateOrCreate($clave, ['docente_id' => $docenteId]);
        }, 3);
    }
}
