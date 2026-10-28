<?php

namespace App\Services\Directivo;

use App\Models\Paralelo;
use App\Models\PeriodoAcademico;
use App\Models\TutoriaParalelo;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class AsignarTutorParalelo
{
    public function __construct(private ContextoDocente $contexto) {}

    public function guardar(int $institucionId, int $periodoId, int $paraleloId, ?int $docenteId): void
    {
        DB::transaction(function () use ($institucionId, $periodoId, $paraleloId, $docenteId) {
            if ($docenteId !== null) {
                Usuario::whereKey($docenteId)->lockForUpdate()->firstOrFail();
            }
            $periodo = PeriodoAcademico::where('institucion_id', $institucionId)->lockForUpdate()->findOrFail($periodoId);
            $paralelo = Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionId))->with('curso')->lockForUpdate()->findOrFail($paraleloId);
            if ($docenteId !== null && ! $this->contexto->docentes($institucionId)->whereKey($docenteId)->where('estado', 'ACTIVO')->exists()) {
                throw ValidationException::withMessages(['docente_id' => 'Seleccione un docente activo de su institución.']);
            }
            if ($docenteId !== null && ($paralelo->estado !== 'ACTIVO' || $paralelo->curso->estado !== 'ACTIVO')) {
                throw ValidationException::withMessages(['docente_id' => 'Solo puede asignar tutores a cursos y paralelos activos.']);
            }
            if ($docenteId !== null) {
                $ocupado = TutoriaParalelo::where('institucion_id', $institucionId)
                    ->where('periodo_id', $periodoId)->where('docente_id', $docenteId)
                    ->where('paralelo_id', '!=', $paraleloId)->exists();
                if ($periodo->es_activo) {
                    $ocupado = $ocupado || Paralelo::whereHas('curso', fn ($q) => $q->where('institucion_id', $institucionId))
                        ->where('tutor_id', $docenteId)->where('id', '!=', $paraleloId)->exists();
                }
                if ($ocupado) {
                    throw ValidationException::withMessages(['docente_id' => 'Este docente ya es tutor de otro curso o paralelo en el período seleccionado.']);
                }
            }
            $clave = ['institucion_id' => $institucionId, 'periodo_id' => $periodoId, 'paralelo_id' => $paraleloId];
            if ($docenteId === null) {
                TutoriaParalelo::where($clave)->delete();
            } else {
                TutoriaParalelo::updateOrCreate($clave, ['docente_id' => $docenteId]);
            }
            if ($periodo->es_activo) {
                $paralelo->update(['tutor_id' => $docenteId]);
            }
        });
    }
}
