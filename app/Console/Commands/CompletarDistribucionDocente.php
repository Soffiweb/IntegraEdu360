<?php

namespace App\Console\Commands;

use App\Services\Directivo\CompletarDistribucion;
use App\Services\Directivo\PropuestaDistribucion;
use Illuminate\Console\Command;

class CompletarDistribucionDocente extends Command
{
    protected $signature = 'docentes:completar-distribucion {institucion} {periodo} {--aplicar : Guardar la distribución; por defecto solo simula} {--propuesta-institucional : Completar bloques institucionales, optativas y comisiones}';

    protected $description = 'Completa cargas curriculares conocidas sin superar 25 períodos ni reemplazar asignaciones existentes';

    public function handle(CompletarDistribucion $distribucion, PropuestaDistribucion $propuesta): int
    {
        if ($this->option('propuesta-institucional')) {
            $distribucion = $propuesta;
        }
        $resultado = $distribucion->ejecutar((int) $this->argument('institucion'), (int) $this->argument('periodo'), (bool) $this->option('aplicar'));
        $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
