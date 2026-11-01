<?php

namespace App\Models;

use Carbon\CarbonImmutable;
use Illuminate\Database\Eloquent\Casts\Attribute;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class SesionAsistencia extends Model
{
    public const MAX_HORAS = 3;

    protected $table = 'sesiones_asistencia';

    protected $fillable = ['asignacion_docente_id', 'fecha', 'horas', 'tema'];

    /** Una carga que no cabe en cinco días con MAX_HORAS (p. ej. 25 períodos de Primero de Básica) amplía el tope diario. */
    public static function maxHorasPorClase(float $horasSemanales): int
    {
        return (int) max(min(self::MAX_HORAS, ceil($horasSemanales)), ceil($horasSemanales / 5));
    }

    protected function casts(): array
    {
        return ['fecha' => 'date', 'horas' => 'integer'];
    }

    protected function fecha(): Attribute
    {
        return Attribute::make(set: fn ($valor) => CarbonImmutable::parse($valor)->toDateString());
    }

    public function asistencias(): HasMany
    {
        return $this->hasMany(AsistenciaEstudiante::class);
    }
}
