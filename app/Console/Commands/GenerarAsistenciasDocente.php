<?php

namespace App\Console\Commands;

use App\Services\Docente\GenerarAsistencias;
use Illuminate\Console\Command;

class GenerarAsistenciasDocente extends Command
{
    protected $signature = 'asistencias:generar {institucion} {periodo} {--aplicar : Guardar las clases; por defecto solo simula}';

    protected $description = 'Genera clases y asistencias de septiembre a diciembre de 2026 según la carga semanal, conservando las clases registradas';

    public function handle(GenerarAsistencias $generador): int
    {
        $resultado = $generador->ejecutar((int) $this->argument('institucion'), (int) $this->argument('periodo'), (bool) $this->option('aplicar'));
        $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
