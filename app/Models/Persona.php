<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Persona extends Model
{
    protected $table = 'personas';

    protected $guarded = [];

    public function usuarios(): HasMany
    {
        return $this->hasMany(Usuario::class, 'persona_id');
    }
}
