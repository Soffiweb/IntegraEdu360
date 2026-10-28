<?php

namespace Database\Seeders;

use App\Models\Especialidad;
use App\Models\Institucion;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class EspecialidadSeeder extends Seeder
{
    public function run(?int $institucionId = null): void
    {
        $institucionesQuery = Institucion::query()->select('id');
        if ($institucionId !== null) {
            $institucionesQuery->whereKey($institucionId);
        }

        if ((clone $institucionesQuery)->doesntExist()) {
            throw new \RuntimeException('No existe ninguna institucion para asociar las especialidades del seeder.');
        }

        $catalogo = $this->catalogoEspecialidades();

        $institucionesQuery->chunkById(200, function ($instituciones) use ($catalogo) {
            DB::transaction(function () use ($instituciones, $catalogo) {
                $existentes = Especialidad::query()
                    ->whereIn('institucion_id', $instituciones->pluck('id'))
                    ->get()
                    ->groupBy('institucion_id');
                $nuevas = [];
                $fecha = now();

                foreach ($instituciones as $institucion) {
                    $especialidades = $existentes->get($institucion->id, collect());
                    $porCodigo = $especialidades->keyBy('codigo');
                    $porNombre = $especialidades->keyBy('nombre');

                    foreach ($catalogo as $esp) {
                        $especialidad = $porCodigo->get($esp['codigo']) ?? $porNombre->get($esp['nombre']);

                        if ($especialidad) {
                            $especialidad->fill($esp);
                            if ($especialidad->isDirty()) {
                                $especialidad->save();
                            }
                        } else {
                            $nuevas[] = [
                                'institucion_id' => $institucion->id,
                                ...$esp,
                                'estado' => 'ACTIVO',
                                'created_at' => $fecha,
                                'updated_at' => $fecha,
                            ];
                        }
                    }
                }

                foreach (array_chunk($nuevas, 500) as $lote) {
                    Especialidad::query()->insert($lote);
                }
            });
        });
    }

    private function catalogoEspecialidades(): array
    {
        return [
            ['nombre' => 'Bachillerato en Ciencias', 'codigo' => 'BGU', 'tipo' => 'GENERAL'],
            ['nombre' => 'Actividad física, deporte y recreación', 'codigo' => 'BT-AFDR', 'tipo' => 'TECNICO'],
            ['nombre' => 'Gestión deportiva y cultural', 'codigo' => 'BT-GDC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Seguridad ciudadana', 'codigo' => 'BT-SC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Atención a la primera infancia', 'codigo' => 'BT-API', 'tipo' => 'TECNICO'],
            ['nombre' => 'Asistencia y cuidado a grupos prioritarios', 'codigo' => 'BT-ACGP', 'tipo' => 'TECNICO'],
            ['nombre' => 'Artes plásticas y gestión cultural', 'codigo' => 'BT-APGC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Artes escénicas y gestión cultural', 'codigo' => 'BT-AEGC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Música y gestión cultural', 'codigo' => 'BT-MGC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Diseño de modas', 'codigo' => 'BT-DM', 'tipo' => 'TECNICO'],
            ['nombre' => 'Diseño gráfico y multimedia', 'codigo' => 'BT-DGM', 'tipo' => 'TECNICO'],
            ['nombre' => 'Gestión financiera y contable', 'codigo' => 'BT-GFC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Gestión administrativa y logística', 'codigo' => 'BT-GAL', 'tipo' => 'TECNICO'],
            ['nombre' => 'Manejo de recursos hidrobiológicos', 'codigo' => 'BT-MRH', 'tipo' => 'TECNICO'],
            ['nombre' => 'Producción agropecuaria sostenible', 'codigo' => 'BT-PAS', 'tipo' => 'TECNICO'],
            ['nombre' => 'Gestión ambiental y desarrollo sostenible', 'codigo' => 'BT-GADS', 'tipo' => 'TECNICO'],
            ['nombre' => 'Conservación y manejo de áreas protegidas', 'codigo' => 'BT-CMAP', 'tipo' => 'TECNICO'],
            ['nombre' => 'Construcción de obra civil', 'codigo' => 'BT-COC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Electrónica', 'codigo' => 'BTE', 'tipo' => 'TECNICO'],
            ['nombre' => 'Mecatrónica', 'codigo' => 'BT-MEC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Fabricación en madera', 'codigo' => 'BT-FM', 'tipo' => 'TECNICO'],
            ['nombre' => 'Electromecánica industrial', 'codigo' => 'BT-EI', 'tipo' => 'TECNICO'],
            ['nombre' => 'Electromecánica automotriz', 'codigo' => 'BT-EA', 'tipo' => 'TECNICO'],
            ['nombre' => 'Conservación y procesamiento de alimentos', 'codigo' => 'BT-CPA', 'tipo' => 'TECNICO'],
            ['nombre' => 'Producción de calzado', 'codigo' => 'BT-PC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Mecánica industrial', 'codigo' => 'BT-MI', 'tipo' => 'TECNICO'],
            ['nombre' => 'Instalaciones eléctricas y automatización', 'codigo' => 'BT-IEA', 'tipo' => 'TECNICO'],
            ['nombre' => 'Climatización', 'codigo' => 'BT-CL', 'tipo' => 'TECNICO'],
            ['nombre' => 'Seguridad informática', 'codigo' => 'BT-SI', 'tipo' => 'TECNICO'],
            ['nombre' => 'Redes y telecomunicaciones', 'codigo' => 'BT-RT', 'tipo' => 'TECNICO'],
            ['nombre' => 'Ciencia de datos', 'codigo' => 'BT-CD', 'tipo' => 'TECNICO'],
            ['nombre' => 'Soporte informático', 'codigo' => 'BT-SOP', 'tipo' => 'TECNICO'],
            ['nombre' => 'Desarrollo de software', 'codigo' => 'BT-DS', 'tipo' => 'TECNICO'],
            ['nombre' => 'Hostelería y arte culinario', 'codigo' => 'BT-HAC', 'tipo' => 'TECNICO'],
            ['nombre' => 'Gestión turística', 'codigo' => 'BT-GT', 'tipo' => 'TECNICO'],
        ];
    }
}
