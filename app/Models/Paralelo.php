<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Paralelo extends Model
{
    protected $table = 'paralelos';

    protected $fillable = [
        'curso_id',
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
}
