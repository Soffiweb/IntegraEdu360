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
        Schema::create('asignaciones_docentes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->foreignId('periodo_id')->constrained('periodos_lectivos')->restrictOnDelete();
            $table->foreignId('docente_id')->constrained('usuarios')->restrictOnDelete();
            $table->string('tipo', 20);
            $table->string('titulo', 255);
            $table->decimal('horas_semanales', 6, 2);
            $table->foreignId('curso_id')->nullable()->constrained('cursos')->restrictOnDelete();
            $table->foreignId('asignatura_id')->nullable()->constrained('asignaturas')->restrictOnDelete();
            $table->text('observaciones')->nullable();
            $table->date('fecha_limite')->nullable();
            $table->timestamps();
            $table->index(['institucion_id', 'periodo_id', 'docente_id'], 'asignaciones_docentes_contexto');
            $table->unique(['periodo_id', 'docente_id', 'tipo', 'titulo'], 'asignaciones_docentes_unica');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('asignaciones_docentes');
    }
};
