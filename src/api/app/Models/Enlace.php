<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Attributes\Fillable;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

#[Fillable(['sede_origen_id', 'sede_destino_id', 'estado', 'latencia_ms', 'perdida_pct'])]
class Enlace extends Model
{
    protected function casts(): array
    {
        return [
            'latencia_ms' => 'integer',
            'perdida_pct' => 'integer',
        ];
    }

    public function sedeOrigen(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_origen_id');
    }

    public function sedeDestino(): BelongsTo
    {
        return $this->belongsTo(Sede::class, 'sede_destino_id');
    }

    public function alertas(): HasMany
    {
        return $this->hasMany(Alerta::class);
    }

    public function getNombreAttribute(): string
    {
        $origen = $this->sedeOrigen?->nombre ?? '?';
        $destino = $this->sedeDestino?->nombre ?? '?';

        return "{$origen} ↔ {$destino}";
    }

    public function estaActivo(): bool
    {
        return $this->estado !== 'caido';
    }
}
