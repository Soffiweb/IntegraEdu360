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
        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('slogan')->nullable();
            $table->string('logo1')->nullable();
            $table->string('logo2')->nullable();
            $table->string('representante_nombre')->nullable();
            $table->string('representante_cargo', 120)->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropColumn(['slogan', 'logo1', 'logo2', 'representante_nombre', 'representante_cargo']);
        });
    }
};
