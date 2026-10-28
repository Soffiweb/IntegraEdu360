<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tutorias_paralelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_lectivos')->restrictOnDelete();
            $table->foreignId('paralelo_id')->constrained('paralelos')->restrictOnDelete();
            $table->foreignId('docente_id')->constrained('usuarios')->restrictOnDelete();
            $table->timestamps();
            $table->unique(['periodo_id', 'paralelo_id']);
            $table->index(['institucion_id', 'periodo_id', 'docente_id']);
        });

    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tutorias_paralelos');
    }
};
