<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['nombre', 'codigo', 'conectada'])]
class Sede extends Model
{
    protected function casts(): array
    {
        return [
            'conectada' => 'boolean',
        ];
    }

    public function enlacesComoOrigen(): HasMany
    {
        return $this->hasMany(Enlace::class, 'sede_origen_id');
    }

    public function enlacesComoDestino(): HasMany
    {
        return $this->hasMany(Enlace::class, 'sede_destino_id');
    }
}
