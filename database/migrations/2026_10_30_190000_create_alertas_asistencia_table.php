<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('alertas_asistencia', function (Blueprint $table) {
            $table->id();
            $table->foreignId('matricula_id')->constrained('matriculas')->restrictOnDelete();
            $table->foreignId('representante_id')->nullable()->constrained('usuarios')->nullOnDelete();
            $table->decimal('porcentaje', 5, 2);
            $table->unsignedInteger('registros');
            $table->timestamp('notificada_at')->nullable();
            $table->timestamp('resuelta_at')->nullable();
            $table->timestamps();
            $table->index(['matricula_id', 'resuelta_at']);
            $table->index(['representante_id', 'resuelta_at']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('alertas_asistencia');
    }
};
