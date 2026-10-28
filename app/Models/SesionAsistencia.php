<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionAsistencia extends Model
{
    protected $table = 'sesiones_asistencia';

    protected $fillable = ['asignacion_docente_id', 'semana', 'numero', 'fecha'];

    protected function casts(): array
    {
        return ['semana' => 'date', 'fecha' => 'date'];
    }

    protected function semana(): Attribute
    {
        return Attribute::make(set: fn ($valor) => CarbonImmutable::parse($valor)->toDateString());
    }

    protected function fecha(): Attribute
    {
        return Attribute::make(set: fn ($valor) => $valor ? CarbonImmutable::parse($valor)->toDateString() : null);
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class);
    }
}
