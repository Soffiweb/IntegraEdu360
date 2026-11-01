<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AsistenciaEstudiante extends Model
{
    public const ESTADOS = ['P' => 'Presente', 'F' => 'Falta', 'A' => 'Atraso', 'J' => 'Justificada'];

    protected $table = 'asistencias_estudiantes';

    protected $fillable = ['sesion_asistencia_id', 'matricula_id', 'estado', 'observacion'];

    public function scopeConteosPorMatricula(Builder $query): Builder
    {
        $conteos = collect(self::ESTADOS)->keys()->map(fn ($e) => "SUM(CASE WHEN estado = '$e' THEN 1 ELSE 0 END) as total_".strtolower($e))->implode(', ');

        return $query->selectRaw('matricula_id, '.$conteos)->groupBy('matricula_id');
    }

    public function matricula(): BelongsTo
    {
        return $this->belongsTo(Matricula::class);
    }
}
