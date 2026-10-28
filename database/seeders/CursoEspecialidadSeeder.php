<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class CursoEspecialidadSeeder extends Seeder
{
    public function run(int $institucionId): void
    {
        Institucion::query()->findOrFail($institucionId);
        $especialidades = Especialidad::query()
            ->where('institucion_id', $institucionId)
            ->whereIn('tipo', ['GENERAL', 'TECNICO'])
            ->get(['id']);

        if ($especialidades->isEmpty()) {
            throw new \RuntimeException('La institución no tiene especialidades de bachillerato.');
        }

        $cursos = [
            11 => 'Primero de Bachillerato',
            12 => 'Segundo de Bachillerato',
            13 => 'Tercero de Bachillerato',
        ];

        DB::transaction(function () use ($institucionId, $especialidades, $cursos) {
            foreach ($especialidades as $especialidad) {
                foreach ($cursos as $grado => $nombre) {
                    Curso::firstOrCreate([
                        'institucion_id' => $institucionId,
                        'especialidad_id' => $especialidad->id,
                        'nivel' => 'BACHILLERATO',
                        'grado' => $grado,
                    ], [
                        'nombre' => $nombre,
                        'estado' => 'ACTIVO',
                    ]);
                }
            }
        });
    }
}
