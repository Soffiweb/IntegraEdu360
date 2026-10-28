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
        Schema::table('paralelos', function (Blueprint $table) {
            $table->foreignId('tutor_id')->nullable()->constrained('usuarios')->nullOnDelete();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('paralelos', function (Blueprint $table) {
            $table->dropConstrainedForeignId('tutor_id');
        });
    }
};
