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
        Schema::create('autoridades_periodo', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_lectivos')->restrictOnDelete();
            $table->string('cargo', 40);
            $table->foreignId('docente_id')->constrained('usuarios')->restrictOnDelete();
            $table->unique(['institucion_id', 'periodo_id', 'cargo'], 'autoridad_cargo_unico');
            $table->unique(['institucion_id', 'periodo_id', 'docente_id'], 'autoridad_persona_unica');
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('autoridades_periodo');
    }
};
