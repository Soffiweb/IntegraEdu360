<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('roles')) {
            return;
        }

        Schema::create('roles', function (Blueprint $table) {
            $table->smallIncrements('id');
            $table->string('codigo')->unique();
            $table->string('nombre');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};
