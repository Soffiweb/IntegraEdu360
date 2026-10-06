<?php

namespace App\Console\Commands;

use App\Services\Minedec\ImportInstituciones;
use Illuminate\Console\Command;
use RuntimeException;

class ImportInstitucionesMinedec extends Command
{
    protected $signature = 'minedec:importar-instituciones {archivo? : CSV normalizado del Ministerio} {--dry-run : Validar y mostrar resultados sin modificar la base}';

    protected $description = 'Importa instituciones del Minedec por código AMIE y las relaciona con su distrito educativo';

    public function handle(ImportInstituciones $importer): int
    {
        $path = $this->argument('archivo') ?? database_path('data/mineduc/instituciones-2025-2026.csv');
        if (! is_file($path) || ! is_readable($path)) {
            $this->error('El archivo de instituciones no existe o no se puede leer.');

            return self::FAILURE;
        }
        try {
            $result = $importer->run($path, (bool) $this->option('dry-run'));
        } catch (RuntimeException $exception) {
            $this->error($exception->getMessage());

            return self::FAILURE;
        }
        $this->info(($result['simulacion'] ? 'Simulación' : 'Importación').' — '.$result['periodo']);
        $this->table(['Registros', 'Nuevas', 'Actualizadas', 'Sin cambios', 'Sin distrito en la fuente'], [[
            $result['registros'], $result['nuevas'], $result['actualizadas'], $result['sin_cambios'], count($result['sin_distrito_en_fuente']),
        ]]);
        if ($result['sin_distrito_en_fuente'] !== []) {
            $this->warn('La fuente no identifica distrito para estos códigos AMIE: '.implode(', ', array_slice($result['sin_distrito_en_fuente'], 0, 35)));
        }

        return self::SUCCESS;
    }
}
