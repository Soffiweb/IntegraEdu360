<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asistencias_estudiantes', function (Blueprint $table) {
            $table->id();
            $table->foreignId('sesion_asistencia_id')->constrained('sesiones_asistencia')->restrictOnDelete();
            $table->foreignId('matricula_id')->constrained('matriculas')->restrictOnDelete();
            $table->string('estado', 1);
            $table->timestamps();
            $table->unique(['sesion_asistencia_id', 'matricula_id'], 'asistencia_sesion_matricula_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asistencias_estudiantes');
    }
};
