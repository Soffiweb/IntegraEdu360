<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('distritos', function (Blueprint $table) {
            $table->id();
            $table->foreignId('zona_id')->constrained('zonas')->restrictOnDelete();
            $table->string('codigo', 5)->unique();
            $table->string('nombre');
            $table->string('provincia', 100);
            $table->timestamps();
            $table->index(['zona_id', 'codigo']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('distritos');
    }
};
