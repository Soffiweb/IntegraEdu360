<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('cat_sostenimiento', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
        });
        Schema::create('cat_regimen_escolar', function (Blueprint $table) {
            $table->id();
            $table->string('nombre', 100)->unique();
        });
        Schema::table('instituciones', function (Blueprint $table) {
            $table->string('codigo_amie', 20)->nullable()->unique();
            $table->foreignId('distrito_id')->nullable()->constrained('distritos')->restrictOnDelete();
            $table->foreignId('sostenimiento_id')->nullable()->constrained('cat_sostenimiento')->restrictOnDelete();
            $table->foreignId('regimen_id')->nullable()->constrained('cat_regimen_escolar')->restrictOnDelete();
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            $table->dropConstrainedForeignId('distrito_id');
            $table->dropConstrainedForeignId('sostenimiento_id');
            $table->dropConstrainedForeignId('regimen_id');
            $table->dropUnique(['codigo_amie']);
            $table->dropColumn('codigo_amie');
        });
        Schema::dropIfExists('cat_regimen_escolar');
        Schema::dropIfExists('cat_sostenimiento');
    }
};
