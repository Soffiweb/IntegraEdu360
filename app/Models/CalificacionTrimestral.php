<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class CalificacionTrimestral extends Model
{
    protected $table = 'calificaciones_trimestrales';

    protected $fillable = ['asignacion_docente_id', 'matricula_id', 'trimestre', 'aportes', 'sumativa', 'sumativa_mejora', 'valoracion'];

    protected function casts(): array
    {
        return ['trimestre' => 'integer', 'aportes' => 'array', 'sumativa' => 'decimal:2', 'sumativa_mejora' => 'decimal:2'];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }
}
