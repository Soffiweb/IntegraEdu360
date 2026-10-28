<?php

namespace App\Console\Commands;

use App\Models\Usuario;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;

class ConfigurarCredencialesPersonas extends Command
{
    protected $signature = 'usuarios:credenciales-identificacion {--institucion= : Institución a procesar} {--incluir-demo : Usar también identificadores DEM de demostración} {--dry-run : Validar sin modificar cuentas}';

    protected $description = 'Configura usuario y contraseña inicial con la identificación para docentes, representantes y alumnos existentes';

    public function handle(): int
    {
        $institucion = $this->option('institucion');
        if ($institucion !== null && (! ctype_digit((string) $institucion) || (int) $institucion < 1)) {
            $this->error('La institución debe ser un identificador positivo.');

            return self::FAILURE;
        }
        $usuarios = Usuario::query()->with('persona')
            ->when($institucion !== null, fn ($q) => $q->where('institucion_id', $institucion))
            ->whereHas('roles', fn ($q) => $q->whereIn('codigo', [
                '04', 'DOC', 'DOCENTE', 'EST', 'REP', 'PADRE', 'PADREOTUTOR', 'TUTOR', 'REPRESENTANTE',
            ])->orWhereRaw('UPPER(nombre) IN (?, ?, ?, ?, ?)', ['DOCENTE', 'ESTUDIANTE', 'ALUMNO', 'REPRESENTANTE', 'PADRE O TUTOR']))
            ->orderBy('id')->get();
        $incluirDemo = (bool) $this->option('incluir-demo');
        $elegibles = $usuarios->filter(function (Usuario $usuario) use ($incluirDemo) {
            $numero = trim((string) $usuario->persona?->numero_identificacion);

            return preg_match('/^\d{10}$/D', $numero)
                || ($incluirDemo && preg_match('/^DEM[A-Za-z0-9_-]+$/D', $numero));
        });
        $omitidos = $usuarios->count() - $elegibles->count();
        $ocupados = Usuario::query()->pluck('id', 'username');
        $destinos = [];
        foreach ($elegibles as $usuario) {
            $numero = trim($usuario->persona->numero_identificacion);
            if (isset($destinos[$numero]) || ($ocupados->has($numero) && (int) $ocupados[$numero] !== $usuario->id)) {
                $this->error('Existe una identificación duplicada o un nombre de usuario ocupado. Cuenta: '.$usuario->id.'. No se realizaron cambios.');

                return self::FAILURE;
            }
            $destinos[$numero] = $usuario->id;
        }
        $this->info('Cuentas elegibles: '.$elegibles->count().'. Omitidas sin identificación compatible: '.$omitidos.'.');
        if ($this->option('dry-run')) {
            return self::SUCCESS;
        }
        $actualizados = 0;
        $conservados = 0;
        $this->output->progressStart($elegibles->count());
        foreach ($elegibles as $usuario) {
            $numero = trim($usuario->persona->numero_identificacion);
            $coincide = false;
            try {
                $coincide = Hash::check($numero, $usuario->password_hash);
            } catch (\RuntimeException) {
                // Las cuentas antiguas pueden tener contraseñas sin hash.
            }
            if ($usuario->username === $numero && $coincide) {
                $conservados++;
            } else {
                DB::transaction(function () use ($usuario, $numero, $coincide) {
                    $usuario->update([
                        'username' => $numero,
                        'password_hash' => $coincide ? $usuario->password_hash : Hash::make($numero),
                    ]);
                });
                $actualizados++;
            }
            $this->output->progressAdvance();
        }
        $this->output->progressFinish();
        $this->info("Cuentas actualizadas: {$actualizados}. Ya configuradas: {$conservados}. Roles y relaciones conservados.");

        return self::SUCCESS;
    }
}
