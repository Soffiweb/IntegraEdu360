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
        if (Schema::hasTable('personas')) {
            return;
        }

        Schema::create('personas', function (Blueprint $table) {
            $table->id();
            $table->string('numero_identificacion', 30)->nullable()->unique();
            $table->string('primer_nombre', 120);
            $table->string('segundo_nombre', 120)->nullable();
            $table->string('primer_apellido', 120);
            $table->string('segundo_apellido', 120)->nullable();
            $table->date('fecha_nacimiento')->nullable();
            $table->string('sexo', 1)->nullable();
            $table->string('telefono', 50)->nullable();
            $table->string('celular', 50)->nullable();
            $table->string('email')->nullable();
            $table->string('provincia', 120)->nullable();
            $table->string('canton', 120)->nullable();
            $table->string('parroquia', 120)->nullable();
            $table->string('direccion')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personas');
    }
};
