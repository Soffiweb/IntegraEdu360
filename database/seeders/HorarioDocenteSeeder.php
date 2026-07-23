<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class HorarioDocenteSeeder extends Seeder
{
    public function run(): void
    {
        $rolDocente = Rol::query()
            ->where(function ($q) {
                $q->where('codigo', '04')
                    ->orWhereRaw('UPPER(nombre) = ?', ['DOCENTE']);
            })
            ->first();

        if (! $rolDocente) {
            $this->command->warn('Rol docente no encontrado. Seeder omitido.');
            return;
        }

        $docentes = Usuario::query()
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $rolDocente->id))
            ->where('estado', 'ACTIVO')
            ->limit(10)
            ->get();

        if ($docentes->isEmpty()) {
            $this->command->warn('No hay docentes activos. Seeder omitido.');
            return;
        }

        $firstInstitucionId = $docentes->first()->institucion_id;
        $asignaturas = Asignatura::where('institucion_id', $firstInstitucionId)->get();
        $cursos = Curso::where('institucion_id', $firstInstitucionId)->get();

        if ($asignaturas->isEmpty() || $cursos->isEmpty()) {
            $this->command->warn('No hay asignaturas o cursos. Seeder omitido.');
            return;
        }

        DB::table('horario_docente')->truncate();

        $franjas = [
            ['07:00:00', '07:40:00'],
            ['07:40:00', '08:20:00'],
            ['08:20:00', '09:00:00'],
            ['09:20:00', '10:00:00'],
            ['10:00:00', '10:40:00'],
            ['10:40:00', '11:20:00'],
            ['11:20:00', '12:00:00'],
        ];

        $aulas = ['A-101', 'A-102', 'A-103', 'B-201', 'B-202', 'Lab-1', 'Lab-2', null];

        foreach ($docentes as $docente) {
            $numBloques = rand(8, 20);
            $used = [];

            for ($i = 0; $i < $numBloques; $i++) {
                $dia = rand(1, 5);
                $franjaIdx = array_rand($franjas);
                $key = $dia . '-' . $franjaIdx;

                if (in_array($key, $used, true)) {
                    continue;
                }
                $used[] = $key;

                DB::table('horario_docente')->insert([
                    'usuario_id' => $docente->id,
                    'asignatura_id' => $asignaturas->random()->id,
                    'curso_id' => $cursos->random()->id,
                    'dia_semana' => $dia,
                    'hora_inicio' => $franjas[$franjaIdx][0],
                    'hora_fin' => $franjas[$franjaIdx][1],
                    'aula' => $aulas[array_rand($aulas)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $total = DB::table('horario_docente')->count();
        $this->command->info("Se crearon {$total} bloques horarios para docentes.");
    }
}
