<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (! Schema::hasTable('personas')) {
            return;
        }

        DB::statement("
            select setval(
                pg_get_serial_sequence('personas', 'id'),
                coalesce((select max(id) from personas), 1),
                true
            )
        ");
    }

    public function down(): void
    {
        // No-op: solo re-sincroniza la secuencia al valor actual de la tabla.
    }
};
