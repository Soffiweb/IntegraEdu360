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
        if (Schema::hasTable('usuarios')) {
            return;
        }

        Schema::create('usuarios', function (Blueprint $table) {
            $table->id();
            $table->foreignId('persona_id')->nullable();
            $table->string('username')->unique();
            $table->string('email')->nullable()->unique();
            $table->text('password_hash');
            $table->string('estado')->default('ACTIVO');
            $table->timestamp('ultimo_acceso')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        if (Schema::hasTable('usuarios')) {
            Schema::dropIfExists('usuarios');
        }
    }
};
