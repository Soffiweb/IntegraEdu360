<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('asignatura_curso', function (Blueprint $table) {
            $table->foreignId('asignatura_id')->constrained('asignaturas')->cascadeOnDelete();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->primary(['asignatura_id', 'curso_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('asignatura_curso');
    }
};
