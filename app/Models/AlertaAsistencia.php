<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AlertaAsistencia extends Model
{
    protected $table = 'alertas_asistencia';

    protected $fillable = ['matricula_id', 'representante_id', 'porcentaje', 'registros', 'notificada_at', 'resuelta_at'];

    protected function casts(): array
    {
        return ['porcentaje' => 'float', 'registros' => 'integer', 'notificada_at' => 'datetime', 'resuelta_at' => 'datetime'];
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }

    public function representante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'representante_id');
    }
}
