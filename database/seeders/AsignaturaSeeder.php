<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Curso;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class AsignaturaSeeder extends Seeder
{
    public function run(?int $institucionId = null): void
    {
        $catalogo = require database_path('data/mineduc/asignaturas.php');
        $cursos = Curso::query()->with('especialidad:id,codigo')
            ->when($institucionId !== null, fn ($query) => $query->where('institucion_id', $institucionId))
            ->where(function ($query) {
                $query->where(function ($egb) {
                    $egb->whereNull('especialidad_id')->whereBetween('grado', [1, 10]);
                })->orWhereHas('especialidad', fn ($especialidad) => $especialidad->whereIn('codigo', ['BGU', 'BT-GFC']));
            })->get()->groupBy('institucion_id');

        if ($cursos->isEmpty()) {
            throw new \RuntimeException('No existen cursos de EGB, Ciencias o Gestión Financiera y Contable para cargar asignaturas.');
        }

        foreach ($cursos as $id => $cursosInstitucion) {
            DB::transaction(function () use ($id, $cursosInstitucion, $catalogo) {
                foreach ($catalogo as $item) {
                    $destinos = $cursosInstitucion->filter(fn (Curso $curso) => (int) $curso->grado === $item['grado']
                        && $curso->especialidad?->codigo === $item['especialidad_codigo']);
                    if ($destinos->isEmpty()) {
                        continue;
                    }
                    $asignatura = Asignatura::firstOrCreate([
                        'institucion_id' => $id, 'codigo' => $item['codigo'],
                    ], [
                        'especialidad_id' => $destinos->first()->especialidad_id,
                        'nombre' => $item['nombre'], 'area' => $item['area'],
                        'horas_semana' => $item['horas_semana'],
                        'descripcion' => $item['descripcion'], 'estado' => 'ACTIVO',
                    ]);
                    $asignatura->cursos()->syncWithoutDetaching($destinos->modelKeys());
                }
            });
        }
    }
}
