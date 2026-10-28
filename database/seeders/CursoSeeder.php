<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class CursoSeeder extends Seeder
{
    public function run(?int $institucionId = null, bool $soloEgb = false): void
    {
        $instituciones = Institucion::query()
            ->when($institucionId !== null, fn ($query) => $query->whereKey($institucionId))
            ->orderBy('id')->get();

        if ($instituciones->isEmpty()) {
            throw new \RuntimeException('No existe ninguna institucion para asociar los cursos del seeder.');
        }

        $catalogo = $this->catalogoCursos();

        foreach ($instituciones as $institucion) {
            foreach ($catalogo as $curso) {
                if ($soloEgb && $curso['grado'] > 10) {
                    continue;
                }

                if ($curso['grado'] === 1) {
                    Curso::query()->where('institucion_id', $institucion->id)
                        ->whereNull('especialidad_id')->where('grado', 1)
                        ->where('nivel', 'BASICA_ELEMENTAL')
                        ->update(['nivel' => 'PREPARATORIA']);
                }

                $existe = Curso::query()
                    ->where('institucion_id', $institucion->id)
                    ->whereNull('especialidad_id')
                    ->where('nivel', $curso['nivel'])
                    ->where('grado', $curso['grado'])
                    ->exists();

                if (! $existe) {
                    Curso::create([
                        'institucion_id' => $institucion->id,
                        'nombre' => $curso['nombre'],
                        'nivel' => $curso['nivel'],
                        'grado' => $curso['grado'],
                        'estado' => 'ACTIVO',
                    ]);
                }
            }
        }
    }

    private function catalogoCursos(): array
    {
        return [
            // Preparatoria
            ['nombre' => 'Primero de Básica',    'nivel' => 'PREPARATORIA', 'grado' => 1],
            // Básica Elemental
            ['nombre' => 'Segundo de Básica',    'nivel' => 'BASICA_ELEMENTAL', 'grado' => 2],
            ['nombre' => 'Tercero de Básica',    'nivel' => 'BASICA_ELEMENTAL', 'grado' => 3],
            ['nombre' => 'Cuarto de Básica',     'nivel' => 'BASICA_ELEMENTAL', 'grado' => 4],
            // Básica Media
            ['nombre' => 'Quinto de Básica',     'nivel' => 'BASICA_MEDIA',     'grado' => 5],
            ['nombre' => 'Sexto de Básica',      'nivel' => 'BASICA_MEDIA',     'grado' => 6],
            ['nombre' => 'Séptimo de Básica',    'nivel' => 'BASICA_MEDIA',     'grado' => 7],
            // Básica Superior
            ['nombre' => 'Octavo de Básica',     'nivel' => 'BASICA_SUPERIOR',  'grado' => 8],
            ['nombre' => 'Noveno de Básica',     'nivel' => 'BASICA_SUPERIOR',  'grado' => 9],
            ['nombre' => 'Décimo de Básica',     'nivel' => 'BASICA_SUPERIOR',  'grado' => 10],
            // Bachillerato
            ['nombre' => 'Primero de Bachillerato',  'nivel' => 'BACHILLERATO', 'grado' => 11],
            ['nombre' => 'Segundo de Bachillerato',  'nivel' => 'BACHILLERATO', 'grado' => 12],
            ['nombre' => 'Tercero de Bachillerato',  'nivel' => 'BACHILLERATO', 'grado' => 13],
        ];
    }
}
