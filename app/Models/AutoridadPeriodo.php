<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AutoridadPeriodo extends Model
{
    protected $table = 'autoridades_periodo';

    protected $fillable = ['institucion_id', 'periodo_id', 'cargo', 'docente_id'];

    public const CARGOS = [
        'RECTOR' => 'Rector / Director',
        'VICERRECTOR' => 'Vicerrector / Subdirector',
        'INSPECTOR' => 'Inspector',
        'VOCAL_1' => 'Primer vocal principal',
        'SUPLENTE_1' => 'Suplente del primer vocal',
        'VOCAL_2' => 'Segundo vocal principal',
        'SUPLENTE_2' => 'Suplente del segundo vocal',
        'VOCAL_3' => 'Tercer vocal principal',
        'SUPLENTE_3' => 'Suplente del tercer vocal',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'docente_id');
    }
}
