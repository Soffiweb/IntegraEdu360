<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (Schema::hasColumn('instituciones', 'ruc')) {
                $table->dropColumn('ruc');
            }

            if (Schema::hasColumn('instituciones', 'ciudad')) {
                $table->dropColumn('ciudad');
            }

            if (! Schema::hasColumn('instituciones', 'provincia')) {
                $table->string('provincia')->nullable();
            }

            if (! Schema::hasColumn('instituciones', 'canton')) {
                $table->string('canton')->nullable();
            }

            if (! Schema::hasColumn('instituciones', 'parroquia')) {
                $table->string('parroquia')->nullable();
            }
        });
    }

    public function down(): void
    {
        Schema::table('instituciones', function (Blueprint $table) {
            if (Schema::hasColumn('instituciones', 'provincia')) {
                $table->dropColumn('provincia');
            }

            if (Schema::hasColumn('instituciones', 'canton')) {
                $table->dropColumn('canton');
            }

            if (Schema::hasColumn('instituciones', 'parroquia')) {
                $table->dropColumn('parroquia');
            }

            if (! Schema::hasColumn('instituciones', 'ciudad')) {
                $table->string('ciudad')->nullable();
            }

            if (! Schema::hasColumn('instituciones', 'ruc')) {
                $table->string('ruc')->nullable();
            }
        });
    }
};
