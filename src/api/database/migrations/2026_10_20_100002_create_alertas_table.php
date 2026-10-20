<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('enlace_id')->nullable()->constrained('enlaces')->nullOnDelete();
            $table->string('severidad'); // critical | warning
            $table->string('mensaje');
            $table->boolean('activa')->default(true);
            $table->timestamp('ocurrio_en');
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas');
    }
};
