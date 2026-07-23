<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Usuario extends Model
{
    protected $table = 'usuarios';

    protected $fillable = [
        'persona_id',
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
