<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\SoftDeletes;

#[Fillable(['enlace_id', 'severidad', 'mensaje', 'activa', 'ocurrio_en'])]
class Alerta extends Model
{
    use SoftDeletes;

    protected function casts(): array
    {
        return [
            'activa' => 'boolean',
            'ocurrio_en' => 'datetime',
        ];
    }

    public function enlace(): BelongsTo
    {
        return $this->belongsTo(Enlace::class);
    }
}
