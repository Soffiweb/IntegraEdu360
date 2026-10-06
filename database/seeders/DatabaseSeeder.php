<?php

namespace Database\Seeders;

use App\Models\User;
use App\Models\Usuario;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class DatabaseSeeder extends Seeder
{
    use WithoutModelEvents;

    /**
     * Seed the application's database.
     */
    public function run(): void
    {
        $this->call(RolSeeder::class);
        $this->call(ZonaSeeder::class);
        $this->call(DistritoSeeder::class);
        $this->call(InstitucionCatalogosSeeder::class);

        if (User::count() === 0) {
            User::factory()->create([
                'name' => 'Test User',
                'email' => 'test@example.com',
            ]);
        }

        if (class_exists(Usuario::class) && Usuario::count() === 0) {
            Usuario::create([
                'persona_id' => 1,
                'username' => 'admin.principal',
                'email' => 'admin@integraedu360.test',
                'password_hash' => 'admin12345',
                'estado' => 'ACTIVO',
                'ultimo_acceso' => now(),
            ]);

            Usuario::create([
                'persona_id' => 1,
                'username' => 'coord.academica',
                'email' => 'coordinacion@integraedu360.test',
                'password_hash' => 'coord12345',
                'estado' => 'ACTIVO',
                'ultimo_acceso' => now()->subHours(3),
            ]);

            Usuario::create([
                'persona_id' => 1,
                'username' => 'secretaria.general',
                'email' => 'secretaria@integraedu360.test',
                'password_hash' => 'secret12345',
                'estado' => 'OBSERVACION',
                'ultimo_acceso' => now()->subDay(),
            ]);

            Usuario::create([
                'persona_id' => 1,
                'username' => 'soporte.norte',
                'email' => 'soporte@integraedu360.test',
                'password_hash' => 'soporte12345',
                'estado' => 'ACTIVO',
                'ultimo_acceso' => now()->subHours(6),
            ]);
        }

        $this->call([
            DocenteSeeder::class,
            EstudianteSeeder::class,
            CursoSeeder::class,
            ParaleloSeeder::class,
            EspecialidadSeeder::class,
            AsignaturaSeeder::class,
        ]);
    }
}
