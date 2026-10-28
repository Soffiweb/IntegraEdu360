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
        Schema::table('asignaciones_docentes', function (Blueprint $table) {
            $table->foreignId('paralelo_id')->nullable()->constrained('paralelos')->restrictOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('asignaciones_docentes', function (Blueprint $table) {
            $table->dropConstrainedForeignId('paralelo_id');
        });
    }
};
