<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('paralelos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('curso_id')->constrained('cursos')->cascadeOnDelete();
            $table->string('letra', 5);
            $table->unsignedSmallInteger('capacidad')->default(35);
            $table->string('estado', 20)->default('ACTIVO');
            $table->timestamps();

            $table->unique(['curso_id', 'letra']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('paralelos');
    }
};
