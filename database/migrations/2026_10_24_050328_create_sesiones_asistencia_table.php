<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('sesiones_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('asignacion_docente_id')->constrained('asignaciones_docentes')->restrictOnDelete();
            $table->date('semana');
            $table->unsignedSmallInteger('numero');
            $table->date('fecha')->nullable();
            $table->timestamps();
            $table->unique(['asignacion_docente_id', 'semana', 'numero'], 'sesion_carga_semana_numero_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('sesiones_asistencia');
    }
};
