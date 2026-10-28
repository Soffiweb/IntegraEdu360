<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Matricula extends Model
{
    protected $fillable = ['institucion_id', 'periodo_id', 'estudiante_id', 'paralelo_id', 'estado'];

    protected $attributes = ['estado' => 'ACTIVA'];

    public function estudiante(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'estudiante_id');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class);
    }
}
