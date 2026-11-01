<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('calificaciones_trimestrales', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_docente_id')->constrained('asignaciones_docentes')->restrictOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->restrictOnDelete();
            $table->unsignedTinyInteger('trimestre');
            $table->json('aportes')->nullable();
            $table->decimal('sumativa', 4, 2)->nullable();
            $table->decimal('sumativa_mejora', 4, 2)->nullable();
            $table->string('valoracion', 2)->nullable();
            $table->timestamps();
            $table->unique(['asignacion_docente_id', 'matricula_id', 'trimestre'], 'calificacion_carga_matricula_trimestre_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('calificaciones_trimestrales');
    }
};
