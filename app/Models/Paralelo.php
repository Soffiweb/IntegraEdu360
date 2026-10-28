<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Paralelo extends Model
{
    protected $table = 'paralelos';

    protected $fillable = [
        'curso_id',
        'tutor_id',
        'letra',
        'capacidad',
        'estado',
    ];

    protected function casts(): array
    {
        return ['capacidad' => 'integer'];
    }

    public function curso(): BelongsTo
    {
        return $this->belongsTo(Curso::class);
    }

    public function tutor(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'tutor_id');
    }

    public function tutorias(): HasMany
    {
        return $this->hasMany(TutoriaParalelo::class);
    }
}
