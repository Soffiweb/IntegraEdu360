<?php

namespace App\Console\Commands;

use App\Services\Docente\AlertasAsistencia;
use Illuminate\Console\Command;

class EvaluarAlertasAsistencia extends Command
{
    protected $signature = 'asistencias:evaluar-alertas {institucion} {periodo} {--aplicar : Guardar las alertas y notificar a los representantes; por defecto solo simula}';

    protected $description = 'Evalúa la asistencia de las matrículas activas y notifica a los representantes de quienes están bajo el umbral';

    public function handle(AlertasAsistencia $alertas): int
    {
        $resultado = $alertas->evaluarPeriodo((int) $this->argument('institucion'), (int) $this->argument('periodo'), (bool) $this->option('aplicar'));
        $this->line(json_encode($resultado, JSON_PRETTY_PRINT | JSON_UNESCAPED_UNICODE));

        return self::SUCCESS;
    }
}
