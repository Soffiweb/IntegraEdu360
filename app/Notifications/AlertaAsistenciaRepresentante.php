<?php

namespace App\Notifications;

use App\Models\AlertaAsistencia;
use App\Services\Docente\AlertasAsistencia;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Notifications\Messages\MailMessage;
use Illuminate\Notifications\Notification;

class AlertaAsistenciaRepresentante extends Notification implements ShouldQueue
{
    use Queueable;

    public function __construct(public AlertaAsistencia $alerta)
    {
        $this->afterCommit();
    }

    public function via(object $notifiable): array
    {
        return ['mail'];
    }

    public function toMail(object $notifiable): MailMessage
    {
        $estudiante = $this->alerta->matricula->estudiante;
        $nombre = $estudiante->persona ? $estudiante->nombre_completo : $estudiante->username;
        $porcentaje = number_format($this->alerta->porcentaje, 2, ',', '.');
        $umbral = number_format(AlertasAsistencia::UMBRAL, 0);

        return (new MailMessage)
            ->subject("Alerta de asistencia: {$nombre}")
            ->greeting('Estimado/a representante:')
            ->line("La asistencia de {$nombre} es de {$porcentaje} %, por debajo del {$umbral} % esperado en el período académico.")
            ->line('Le pedimos revisar la situación del estudiante y comunicarse con la institución si necesita apoyo.')
            ->action('Ver alertas de asistencia', route('padre-tutor.alertas-asistencia.index'))
            ->salutation('Atentamente, IntegraEdu360');
    }
}
