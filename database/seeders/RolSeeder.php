<?php

namespace Database\Seeders;

use App\Models\Rol;
use Illuminate\Database\Seeder;

class RolSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            'SUPER' => 'Superusuario',
            'ADMIN' => 'Administrador',
            'DIRECTIVO' => 'Directivo',
            'INSPECTOR' => 'Inspector',
            'DOC' => 'Docente',
            'EST' => 'Estudiante',
            'PADRE' => 'Padre o Tutor',
            'ADMINISTRATIVO' => 'Personal Administrativo',
            'ASESORACADEMICO' => 'Asesor Académico',
            'SOPORTETECNICO' => 'Soporte Técnico',
            'COORDINADORCURSO' => 'Coordinador de Curso',
            'DECE' => 'DECE',
        ];

        foreach ($roles as $codigo => $nombre) {
            Rol::updateOrCreate(['codigo' => $codigo], ['nombre' => $nombre]);
        }
    }
}
