<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Institucion extends Model
{
    protected $table = 'instituciones';

    protected $fillable = [
        'codigo_amie',
        'nombre',
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
}
