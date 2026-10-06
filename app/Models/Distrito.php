<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Distrito extends Model
{
    protected $table = 'distritos';

    protected $fillable = ['zona_id', 'codigo', 'nombre', 'provincia'];

    protected function casts(): array
    {
        return ['zona_id' => 'integer'];
    }

    public function instituciones(): HasMany
    {
        return $this->hasMany(Institucion::class);
    }

    public function zona(): BelongsTo
    {
        return $this->belongsTo(Zona::class);
    }
}
