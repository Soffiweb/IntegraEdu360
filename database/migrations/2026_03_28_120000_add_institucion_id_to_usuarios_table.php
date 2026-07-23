<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('usuarios') || Schema::hasColumn('usuarios', 'institucion_id')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->foreignId('institucion_id')
                ->nullable()
                ->after('persona_id')
                ->constrained('instituciones')
                ->nullOnDelete();
        });
    }

    public function down(): void
    {
        if (! Schema::hasTable('usuarios') || ! Schema::hasColumn('usuarios', 'institucion_id')) {
            return;
        }

        Schema::table('usuarios', function (Blueprint $table) {
            $table->dropConstrainedForeignId('institucion_id');
        });
    }
};
