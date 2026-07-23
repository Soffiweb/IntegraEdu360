<?php

namespace Database\Seeders;

use App\Models\Curso;
use App\Models\Paralelo;
use Illuminate\Database\Seeder;

class ParaleloSeeder extends Seeder
{
    public function run(): void
    {
        $cursos = Curso::query()->where('estado', 'ACTIVO')->get();

        if ($cursos->isEmpty()) {
            throw new \RuntimeException('No existen cursos activos para asociar paralelos del seeder.');
        }

        $letras = ['A', 'B', 'C'];

        foreach ($cursos as $curso) {
            foreach ($letras as $letra) {
                $existe = Paralelo::query()
                    ->where('curso_id', $curso->id)
                    ->where('letra', $letra)
                    ->exists();

                if (! $existe) {
                    Paralelo::create([
                        'curso_id' => $curso->id,
                        'letra' => $letra,
                        'capacidad' => 35,
                        'estado' => 'ACTIVO',
                    ]);
                }
            }
        }
    }
}
