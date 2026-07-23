<?php

namespace Database\Seeders;

use App\Models\Institucion;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class DocenteSeeder extends Seeder
{
    public function run(): void
    {
        $institucion = Institucion::query()->orderBy('id')->first();
        $rolDocente = $this->docenteRole();

        if (! $institucion) {
            throw new \RuntimeException('No existe ninguna institucion para asociar los docentes del seeder.');
        }

        if (! $rolDocente) {
            throw new \RuntimeException('No existe el rol Docente en la tabla roles.');
        }

        $docentesActuales = Usuario::query()
            ->whereHas('roles', fn ($query) => $query->where('roles.id', $rolDocente->id))
            ->count();

        $objetivo = 50;
        $faltantes = max(0, $objetivo - $docentesActuales);

        if ($faltantes === 0) {
            return;
        }

        $faker = fake('es_ES');
        $base = $docentesActuales + 1;

        for ($i = 0; $i < $faltantes; $i++) {
            $consecutivo = $base + $i;

            DB::transaction(function () use ($faker, $institucion, $rolDocente, $consecutivo): void {
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
                    'fecha_nacimiento' => $faker->dateTimeBetween('-60 years', '-24 years')->format('Y-m-d'),
                    'sexo' => $faker->randomElement(['M', 'F']),
                    'telefono' => '02' . str_pad((string) random_int(1000000, 9999999), 7, '0', STR_PAD_LEFT),
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
                    'password_hash' => Hash::make('docente123'),
                    'estado' => 'ACTIVO',
                    'ultimo_acceso' => now()->subDays(random_int(0, 30))->subHours(random_int(0, 23)),
                ]);

                $usuario->roles()->sync([
                    $rolDocente->id => ['institucion_id' => $institucion->id],
                ]);
            });
        }
    }

    private function docenteRole(): ?Rol
    {
        return Rol::query()
            ->get()
            ->first(function (Rol $rol): bool {
                $codigo = strtoupper(trim((string) ($rol->codigo ?? '')));
                $nombre = strtoupper(trim((string) ($rol->nombre ?? '')));

                return $codigo === '04' || $nombre === 'DOCENTE';
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

        return $value !== '' ? $value : 'docente';
    }
}
