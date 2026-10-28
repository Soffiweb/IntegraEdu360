<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DirectorArea extends Model
{
    protected $table = 'directores_area';

    protected $fillable = ['institucion_id', 'periodo_id', 'area', 'docente_id'];

    public const AREAS = [
        'MATEMATICA' => 'Matemática', 'LENGUAJE' => 'Lenguaje y Literatura',
        'CIENCIAS_NATURALES' => 'Ciencias Naturales', 'CIENCIAS_SOCIALES' => 'Ciencias Sociales',
        'IDIOMAS' => 'Idiomas', 'EDUCACION_ARTISTICA' => 'Educación Artística',
        'EDUCACION_FISICA' => 'Educación Física', 'TECNICA_TECNOLOGICA' => 'Técnica y Tecnológica', 'OTRAS' => 'Otras',
    ];

    public function docente(): BelongsTo
    {
        return $this->belongsTo(Usuario::class, 'docente_id');
    }
}
