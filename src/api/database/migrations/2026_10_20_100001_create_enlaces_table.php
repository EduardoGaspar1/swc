<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('enlaces', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sede_origen_id')->constrained('sedes')->cascadeOnDelete();
            $table->foreignId('sede_destino_id')->constrained('sedes')->cascadeOnDelete();
            $table->string('estado'); // operativo | caido | ruta_alternativa
            $table->unsignedInteger('latencia_ms')->nullable();
            $table->unsignedTinyInteger('perdida_pct')->default(0);
            $table->timestamps();

            $table->unique(['sede_origen_id', 'sede_destino_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('enlaces');
    }
};
