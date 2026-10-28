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
        Schema::create('agendas', function (Blueprint $table) {
            $table->id();
            $table->foreignId('institucion_id')->constrained('instituciones')->cascadeOnDelete();
            $table->date('fecha');
            $table->time('hora_desde');
            $table->time('hora_hasta');
            $table->text('descripcion');
            $table->string('responsable');
            $table->unsignedTinyInteger('porcentaje_cumplimiento')->default(0);
            $table->timestamps();

            $table->index(['institucion_id', 'fecha']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('agendas');
    }
};
