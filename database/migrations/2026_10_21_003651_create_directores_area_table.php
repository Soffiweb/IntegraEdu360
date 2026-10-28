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
        Schema::create('directores_area', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_lectivos')->restrictOnDelete();
            $table->string('area', 40);
            $table->foreignId('docente_id')->constrained('usuarios')->restrictOnDelete();
            $table->unique(['institucion_id', 'periodo_id', 'area'], 'director_area_unica');
            $table->unique(['institucion_id', 'periodo_id', 'docente_id'], 'director_docente_unico');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('directores_area');
    }
};
