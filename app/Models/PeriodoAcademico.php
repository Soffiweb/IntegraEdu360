<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PeriodoAcademico extends Model
{
    protected $table = 'periodos_lectivos';

    public $timestamps = false;

    protected $fillable = [
        'institucion_id',
        'nombre',
        'fecha_inicio',
        'fecha_fin',
        'estado',
        'es_activo',
    ];

    protected function casts(): array
    {
        return [
            'fecha_inicio' => 'date',
            'fecha_fin' => 'date',
            'es_activo' => 'boolean',
        ];
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }
}
