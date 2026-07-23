<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;

class DistribucionHorasSeeder extends Seeder
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
            ->with('persona')
            ->limit(10)
            ->get();

        if ($docentes->isEmpty()) {
            $this->command->warn('No hay docentes registrados. Seeder omitido.');
            return;
        }

        $firstInstitucionId = $docentes->first()->institucion_id;

        $asignaturas = Asignatura::where('institucion_id', $firstInstitucionId)->get();
        $cursos = Curso::where('institucion_id', $firstInstitucionId)->get();

        if ($asignaturas->isEmpty() || $cursos->isEmpty()) {
            $this->command->warn('No hay asignaturas o cursos para la institucion. Seeder omitido.');
            return;
        }

        DB::table('docente_asignatura_curso')->truncate();

        $horasOptions = [2, 3, 4, 5, 6];

        foreach ($docentes as $docente) {
            $numAsignaciones = min(rand(2, 4), $asignaturas->count());
            $selectedAsignaturas = $asignaturas->random($numAsignaciones);

            foreach ($selectedAsignaturas as $asignatura) {
                $curso = $cursos->random();

                $exists = DB::table('docente_asignatura_curso')
                    ->where('usuario_id', $docente->id)
                    ->where('asignatura_id', $asignatura->id)
                    ->where('curso_id', $curso->id)
                    ->exists();

                if ($exists) {
                    continue;
                }

                DB::table('docente_asignatura_curso')->insert([
                    'usuario_id' => $docente->id,
                    'asignatura_id' => $asignatura->id,
                    'curso_id' => $curso->id,
                    'horas_asignadas' => $horasOptions[array_rand($horasOptions)],
                    'created_at' => now(),
                    'updated_at' => now(),
                ]);
            }
        }

        $total = DB::table('docente_asignatura_curso')->count();
        $this->command->info("Se crearon {$total} asignaciones de horas docentes.");
    }
}
