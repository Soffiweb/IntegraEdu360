<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'persona_id',
        'representante_id',
        'parentesco_representante',
        'institucion_id',
        'username',
        'email',
        'password_hash',
        'estado',
        'ultimo_acceso',
    ];

    protected function casts(): array
    {
        return [
            'ultimo_acceso' => 'datetime',
        ];
    }

    public function persona(): BelongsTo
    {
        return $this->belongsTo(Persona::class, 'persona_id');
    }

    public function institucion(): BelongsTo
    {
        return $this->belongsTo(Institucion::class, 'institucion_id');
    }

    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(Rol::class, 'usuario_rol', 'usuario_id', 'rol_id')
            ->withPivot('institucion_id');
    }

    public function scopeRepresentantes(Builder $query): Builder
    {
        return $query->whereHas('roles', fn ($q) => $q->whereIn('codigo', ['REP', 'PADRE', 'PADREOTUTOR', 'TUTOR', 'REPRESENTANTE'])
            ->orWhereRaw("UPPER(REPLACE(nombre, ' ', '')) IN (?, ?, ?, ?)", ['REPRESENTANTE', 'PADREOTUTOR', 'PADRE', 'TUTOR']));
    }

    public function scopeAlumnos(Builder $query): Builder
    {
        return $query->whereHas('roles', fn ($q) => $q->whereRaw('UPPER(codigo) = ?', ['EST'])
            ->orWhereRaw('UPPER(nombre) IN (?, ?)', ['ESTUDIANTE', 'ALUMNO']));
    }

    public function representante(): BelongsTo
    {
        return $this->belongsTo(self::class, 'representante_id');
    }

    public function representados(): HasMany
    {
        return $this->hasMany(self::class, 'representante_id');
    }

    public function getNombreCompletoAttribute(): string
    {
        if (! $this->persona) {
            return 'Sin persona vinculada';
        }

        return trim(implode(' ', array_filter([
            $this->persona->primer_nombre,
            $this->persona->segundo_nombre,
            $this->persona->primer_apellido,
            $this->persona->segundo_apellido,
        ])));
    }
}
