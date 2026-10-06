<?php

namespace Database\Seeders;

use App\Models\Zona;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class ZonaSeeder extends Seeder
{
    public function run(): void
    {
        $zonas = [
            1 => 'Esmeraldas, Carchi, Imbabura y Sucumbíos.',
            2 => 'Pichincha (excepto el Distrito Metropolitano de Quito), Napo y Orellana.',
            3 => 'Cotopaxi, Tungurahua, Chimborazo y Pastaza.',
            4 => 'Manabí y Santo Domingo de los Tsáchilas.',
            5 => 'Bolívar, Los Ríos, Santa Elena, Galápagos y Guayas (excepto Guayaquil, Durán y Samborondón).',
            6 => 'Azuay, Cañar y Morona Santiago.',
            7 => 'El Oro, Loja y Zamora Chinchipe.',
            8 => 'Guayaquil, Durán y Samborondón.',
            9 => 'Distrito Metropolitano de Quito.',
        ];

        DB::transaction(function () use ($zonas) {
            foreach ($zonas as $codigo => $cobertura) {
                Zona::updateOrCreate(
                    ['codigo' => $codigo],
                    ['nombre' => 'Zona '.$codigo, 'cobertura' => $cobertura],
                );
            }
        });
    }
}
