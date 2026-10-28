<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Agenda extends Model
{
    protected $table = 'agendas';

    protected $fillable = [
        'institucion_id',
        'fecha',
        'hora_desde',
        'hora_hasta',
        'descripcion',
        'responsable',
        'porcentaje_cumplimiento',
    ];

    protected function casts(): array
    {
        return [
            'fecha' => 'date',
            'porcentaje_cumplimiento' => 'integer',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }
}
