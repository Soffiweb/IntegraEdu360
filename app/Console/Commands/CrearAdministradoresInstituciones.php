<?php

namespace App\Console\Commands;

use App\Models\Institucion;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use RuntimeException;

class CrearAdministradoresInstituciones extends Command
{
    protected $signature = 'instituciones:crear-administradores {--dry-run : Validar sin crear cuentas} {--grupo=0 : Grupo a procesar, comenzando en cero} {--grupos=1 : Cantidad de grupos para repartir el trabajo}';

    protected $description = 'Crea administradores institucionales con usuario y contraseña inicial iguales al código AMIE';

    public function handle(): int
    {
        $grupo = filter_var($this->option('grupo'), FILTER_VALIDATE_INT);
        $grupos = filter_var($this->option('grupos'), FILTER_VALIDATE_INT);
        if ($grupo === false || $grupos === false || $grupos < 1 || $grupo < 0 || $grupo >= $grupos) {
            $this->error('La cantidad de grupos debe ser positiva y el grupo debe estar entre cero y grupos menos uno.');

            return self::FAILURE;
        }
        $instituciones = Institucion::query()->select(['id', 'codigo_amie'])->orderBy('id')->get();
        $usuarios = Usuario::query()->get(['id', 'username', 'institucion_id'])->keyBy('username');
        foreach ($instituciones as $institucion) {
            $amie = trim((string) $institucion->codigo_amie);
            if ($amie === '' || ($usuarios->has($amie) && (int) $usuarios[$amie]->institucion_id !== $institucion->id)) {
                $this->error('Código AMIE ausente o usuario existente vinculado a otra institución: institución '.$institucion->id);

                return self::FAILURE;
            }
        }

        if ($this->option('dry-run')) {
            $this->info('Validación correcta: '.$instituciones->count().' instituciones.');

            return self::SUCCESS;
        }

        $rol = Rol::firstOrCreate(['codigo' => 'ADMIN'], ['nombre' => 'Administrador']);
        $instituciones = $instituciones->filter(fn (Institucion $institucion) => $institucion->id % $grupos === $grupo);
        $creados = 0;
        $existentes = 0;
        $this->output->progressStart($instituciones->count());
        foreach ($instituciones as $institucion) {
            $amie = trim((string) $institucion->codigo_amie);
            $hash = $usuarios->has($amie) ? null : Hash::make($amie);
            $lock = fopen(storage_path('framework/instituciones-administradores.lock'), 'c');
            if ($lock === false || ! flock($lock, LOCK_EX)) {
                throw new RuntimeException('No se pudo bloquear la creación de administradores.');
            }
            try {
                $nuevo = DB::transaction(function () use ($institucion, $rol, $hash): bool {
                    $amie = trim((string) $institucion->codigo_amie);
                    $usuario = Usuario::firstOrNew(['username' => $amie]);
                    $nuevo = ! $usuario->exists;
                    if (! $nuevo && (int) $usuario->institucion_id !== $institucion->id) {
                        throw new RuntimeException('El usuario AMIE pertenece a otra institución.');
                    }
                    if ($nuevo) {
                        $usuario->fill([
                            'institucion_id' => $institucion->id,
                            'password_hash' => $hash,
                            'estado' => 'ACTIVO',
                        ])->save();
                    }
                    if (! $usuario->roles()->whereKey($rol->id)->wherePivot('institucion_id', $institucion->id)->exists()) {
                        $usuario->roles()->attach($rol->id, ['institucion_id' => $institucion->id]);
                    }

                    return $nuevo;
                });
            } finally {
                flock($lock, LOCK_UN);
                fclose($lock);
            }
            $nuevo ? $creados++ : $existentes++;
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->info("Cuentas creadas: {$creados}. Cuentas existentes conservadas: {$existentes}.");

        return self::SUCCESS;
    }
}
