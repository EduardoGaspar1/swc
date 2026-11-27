<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['nombre', 'codigo', 'conectada', 'company_id'])]
class Sede extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'conectada' => 'boolean',
            'company_id' => 'integer',
        ];
    }

    public function company(): BelongsTo
    {
        return $this->belongsTo(Company::class);
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
