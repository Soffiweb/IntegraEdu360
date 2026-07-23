<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->foreignId('especialidad_id')
                ->nullable()
                ->after('institucion_id')
                ->constrained('especialidades')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('cursos', function (Blueprint $table) {
            $table->dropForeignIdFor(\App\Models\Especialidad::class);
            $table->dropColumn('especialidad_id');
        });
    }
};
