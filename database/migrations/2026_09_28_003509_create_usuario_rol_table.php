<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('usuario_rol')) {
            return;
        }

        Schema::create('usuario_rol', function (Blueprint $table) {
            $table->id();
            $table->foreignId('usuario_id')->constrained('usuarios')->cascadeOnDelete();
            $table->unsignedSmallInteger('rol_id');
            $table->foreign('rol_id')->references('id')->on('roles')->cascadeOnDelete();
            $table->foreignId('institucion_id')->nullable()->constrained('instituciones')->cascadeOnDelete();
            $table->unique(['usuario_id', 'rol_id', 'institucion_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('usuario_rol');
    }
};
