<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'codigo_amie',
        'distrito_id',
        'nombre',
        'slogan',
        'logo1',
        'logo2',
        'representante_nombre',
        'representante_cargo',
        'sostenimiento_id',
        'regimen_id',
        'provincia',
        'canton',
        'parroquia',
        'direccion',
        'telefono',
        'email',
        'estado',
    ];

    protected function casts(): array
    {
        return ['distrito_id' => 'integer'];
    }

    public function distrito(): BelongsTo
    {
        return $this->belongsTo(Distrito::class);
    }
}
