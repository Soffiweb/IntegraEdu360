<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class EspecialidadSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = Institucion::query()->orderBy('id')->get();

        if ($instituciones->isEmpty()) {
            throw new \RuntimeException('No existe ninguna institucion para asociar las especialidades del seeder.');
        }

        $catalogo = $this->catalogoEspecialidades();

        foreach ($instituciones as $institucion) {
            foreach ($catalogo as $esp) {
                $existe = Especialidad::query()
                    ->where('institucion_id', $institucion->id)
                    ->where('nombre', $esp['nombre'])
                    ->exists();

                if (! $existe) {
                    Especialidad::create([
                        'institucion_id' => $institucion->id,
                        'nombre' => $esp['nombre'],
                        'codigo' => $esp['codigo'],
                        'tipo' => $esp['tipo'],
                        'estado' => 'ACTIVO',
                    ]);
                }
            }
        }
    }

    private function catalogoEspecialidades(): array
    {
        return [
            [
                'nombre' => 'Bachillerato General Unificado',
                'codigo' => 'BGU',
                'tipo' => 'GENERAL',
            ],
            [
                'nombre' => 'Bachillerato Técnico en Informática',
                'codigo' => 'BTI',
                'tipo' => 'TECNICO',
            ],
            [
                'nombre' => 'Bachillerato Técnico en Contabilidad y Administración',
                'codigo' => 'BTCA',
                'tipo' => 'TECNICO',
            ],
            [
                'nombre' => 'Bachillerato Técnico en Electrónica',
                'codigo' => 'BTE',
                'tipo' => 'TECNICO',
            ],
            [
                'nombre' => 'Bachillerato Técnico Productivo Agropecuario',
                'codigo' => 'BTPA',
                'tipo' => 'TECNICO_PRODUCTIVO',
            ],
        ];
    }
}
