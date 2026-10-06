<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Zona extends Model
{
    protected $table = 'zonas';

    protected $fillable = ['codigo', 'nombre', 'cobertura'];

    public function distritos(): HasMany
    {
        return $this->hasMany(Distrito::class);
    }

    protected function casts(): array
    {
        return ['codigo' => 'integer'];
    }
}
