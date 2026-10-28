<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class TutoriaParalelo extends Model
{
    protected $table = 'tutorias_paralelos';

    protected $fillable = ['institucion_id', 'periodo_id', 'paralelo_id', 'docente_id'];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'docente_id');
    }

    public function periodo(): BelongsTo
    {
        return $this->belongsTo(PeriodoAcademico::class, 'periodo_id');
    }

    public function paralelo(): BelongsTo
    {
        return $this->belongsTo(Paralelo::class);
    }
}
