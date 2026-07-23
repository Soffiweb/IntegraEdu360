<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('docente_asignatura_curso', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->foreignId('asignatura_id')->constrained('asignaturas')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->unsignedSmallInteger('horas_asignadas');
            $table->timestamps();

            $table->unique(['usuario_id', 'asignatura_id', 'curso_id'], 'dac_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('docente_asignatura_curso');
    }
};
