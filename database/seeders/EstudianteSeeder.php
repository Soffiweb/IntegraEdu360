<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class EstudianteSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = Institucion::query()->orderBy('id')->get();
        $rolEstudiante = $this->estudianteRole();

        if ($instituciones->isEmpty()) {
            throw new \RuntimeException('No existe ninguna institucion para asociar los estudiantes del seeder.');
        }

        if (! $rolEstudiante) {
            throw new \RuntimeException('No existe el rol Estudiante en la tabla roles.');
        }

        $objetivoTotal = 1500;
        $porInstitucion = (int) ceil($objetivoTotal / $instituciones->count());

        $faker = fake('es_ES');
        $consecutivoGlobal = Usuario::query()
            ->whereHas('roles', fn ($q) => $q->where('roles.id', $rolEstudiante->id))
            ->count() + 1;

        foreach ($instituciones as $institucion) {
            $actuales = Usuario::query()
                ->whereHas('roles', fn ($q) => $q->where('roles.id', $rolEstudiante->id))
                ->where('institucion_id', $institucion->id)
                ->count();

            $faltantes = max(0, $porInstitucion - $actuales);

            for ($i = 0; $i < $faltantes; $i++) {
                $consecutivo = $consecutivoGlobal++;

                DB::transaction(function () use ($faker, $institucion, $rolEstudiante, $consecutivo): void {
                    $primerNombre = $faker->firstName();
                    $segundoNombre = random_int(0, 1) === 1 ? $faker->firstName() : null;
                    $primerApellido = $faker->lastName();
                    $segundoApellido = random_int(0, 1) === 1 ? $faker->lastName() : null;

                    $numeroIdentificacion = $this->uniqueNumeroIdentificacion($consecutivo);
                    $username = $this->uniqueUsername($primerNombre, $primerApellido, $consecutivo);
                    $email = $this->uniqueEmail($username);

                    $persona = Persona::query()->create([
                        'numero_identificacion' => $numeroIdentificacion,
                        'primer_nombre' => $primerNombre,
                        'segundo_nombre' => $segundoNombre,
                        'primer_apellido' => $primerApellido,
                        'segundo_apellido' => $segundoApellido,
                        'fecha_nacimiento' => $faker->dateTimeBetween('-25 years', '-5 years')->format('Y-m-d'),
                        'sexo' => $faker->randomElement(['M', 'F']),
                        'telefono' => null,
                        'celular' => '09' . str_pad((string) random_int(10000000, 99999999), 8, '0', STR_PAD_LEFT),
                        'email' => $email,
                        'provincia' => $faker->state(),
                        'canton' => $faker->city(),
                        'parroquia' => $faker->citySuffix(),
                        'direccion' => $faker->streetAddress(),
                    ]);

                    $usuario = Usuario::query()->create([
                        'persona_id' => $persona->id,
                        'institucion_id' => $institucion->id,
                        'username' => $username,
                        'email' => $email,
                        'password_hash' => Hash::make('alumno123'),
                        'estado' => $faker->randomElement(['ACTIVO', 'ACTIVO', 'ACTIVO', 'OBSERVACION', 'INACTIVO']),
                        'ultimo_acceso' => random_int(0, 1) === 1
                            ? now()->subDays(random_int(0, 60))->subHours(random_int(0, 23))
                            : null,
                    ]);

                    $usuario->roles()->sync([
                        $rolEstudiante->id => ['institucion_id' => $institucion->id],
                    ]);
                });
            }
        }
    }

    private function estudianteRole(): ?Rol
    {
        return Rol::query()
            ->get()
            ->first(function (Rol $rol): bool {
                $codigo = strtoupper(trim((string) ($rol->codigo ?? '')));
                $nombre = strtoupper(trim((string) ($rol->nombre ?? '')));

                return $codigo === 'EST'
                    || in_array($nombre, ['ESTUDIANTE', 'ALUMNO'], true);
            });
    }

    private function uniqueNumeroIdentificacion(int $consecutivo): string
    {
        do {
            $numero = '17' . str_pad((string) (10000000 + $consecutivo + random_int(0, 89999999)), 8, '0', STR_PAD_LEFT);
        } while (Persona::query()->where('numero_identificacion', $numero)->exists());

        return $numero;
    }

    private function uniqueUsername(string $primerNombre, string $primerApellido, int $consecutivo): string
    {
        $base = strtolower($this->normalizeForSlug($primerNombre) . '.' . $this->normalizeForSlug($primerApellido));
        $username = $base . $consecutivo;

        while (Usuario::query()->where('username', $username)->exists()) {
            $consecutivo++;
            $username = $base . $consecutivo;
        }

        return $username;
    }

    private function uniqueEmail(string $username): string
    {
        $email = $username . '@integraedu360.test';

        while (Usuario::query()->where('email', $email)->exists() || Persona::query()->where('email', $email)->exists()) {
            $email = $username . '+' . random_int(10, 99) . '@integraedu360.test';
        }

        return $email;
    }

    private function normalizeForSlug(string $value): string
    {
        $value = iconv('UTF-8', 'ASCII//TRANSLIT//IGNORE', $value) ?: $value;
        $value = strtolower($value);
        $value = preg_replace('/[^a-z0-9]+/', '', $value) ?? '';

        return $value !== '' ? $value : 'alumno';
    }
}
