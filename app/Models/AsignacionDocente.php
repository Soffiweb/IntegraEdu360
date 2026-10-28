<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsignacionDocente extends Model
{
    protected $table = 'asignaciones_docentes';

    public const TIPOS = ['carga' => 'Carga horaria', 'actividad' => 'Actividad', 'comision' => 'Comisión'];

    protected $fillable = ['institucion_id', 'periodo_id', 'docente_id', 'tipo', 'titulo', 'horas_semanales', 'curso_id', 'paralelo_id', 'asignatura_id', 'observaciones', 'fecha_limite'];

    protected function casts(): array
    {
        return ['horas_semanales' => 'decimal:2', 'fecha_limite' => 'date'];
    }

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'docente_id');
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class);
    }

    public function asignatura(): BelongsTo
    {
        return $this->belongsTo(Asignatura::class);
    }
}
