<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('matriculas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->restrictOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_lectivos')->restrictOnDelete();
            $table->foreignId('estudiante_id')->constrained('usuarios')->restrictOnDelete();
            $table->foreignId('paralelo_id')->constrained('paralelos')->restrictOnDelete();
            $table->string('estado', 20)->default('ACTIVA');
            $table->timestamps();
            $table->unique(['periodo_id', 'estudiante_id']);
            $table->index(['institucion_id', 'periodo_id', 'paralelo_id', 'estado'], 'matriculas_consulta_index');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('matriculas');
    }
};
