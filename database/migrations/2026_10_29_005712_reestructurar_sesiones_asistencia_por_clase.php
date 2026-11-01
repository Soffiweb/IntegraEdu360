<?php

use Carbon\CarbonImmutable;
use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('sesiones_asistencia', function (Blueprint $table) {
            $table->unsignedTinyInteger('horas')->default(1)->after('fecha');
            $table->string('tema', 255)->nullable()->after('horas');
        });
        Schema::table('asistencias_estudiantes', function (Blueprint $table) {
            $table->string('observacion', 500)->nullable()->after('estado');
        });

        // El esquema anterior guardaba un registro por hora semanal; se consolida en una sesión por clase y fecha.
        $sesiones = DB::table('sesiones_asistencia')->orderBy('id')->get();
        foreach ($sesiones->whereNull('fecha') as $sesion) {
            DB::table('asistencias_estudiantes')->where('sesion_asistencia_id', $sesion->id)->delete();
            DB::table('sesiones_asistencia')->where('id', $sesion->id)->delete();
        }
        foreach ($sesiones->whereNotNull('fecha')->groupBy(fn ($s) => $s->asignacion_docente_id.'|'.$s->fecha) as $grupo) {
            $base = $grupo->first();
            foreach ($grupo->slice(1) as $sesion) {
                $ocupadas = DB::table('asistencias_estudiantes')->where('sesion_asistencia_id', $base->id)->pluck('matricula_id');
                DB::table('asistencias_estudiantes')->where('sesion_asistencia_id', $sesion->id)->whereNotIn('matricula_id', $ocupadas)
                    ->update(['sesion_asistencia_id' => $base->id]);
                DB::table('asistencias_estudiantes')->where('sesion_asistencia_id', $sesion->id)->delete();
                DB::table('sesiones_asistencia')->where('id', $sesion->id)->delete();
            }
            DB::table('sesiones_asistencia')->where('id', $base->id)->update(['horas' => min(3, $grupo->count())]);
        }

        Schema::table('sesiones_asistencia', function (Blueprint $table) {
            $table->dropUnique('sesion_carga_semana_numero_unique');
        });
        Schema::table('sesiones_asistencia', function (Blueprint $table) {
            $table->dropColumn(['semana', 'numero']);
            $table->date('fecha')->nullable(false)->change();
            $table->unique(['asignacion_docente_id', 'fecha'], 'sesion_carga_fecha_unique');
        });
    }

    public function down(): void
    {
        Schema::table('sesiones_asistencia', function (Blueprint $table) {
            $table->dropUnique('sesion_carga_fecha_unique');
            $table->date('semana')->nullable()->after('asignacion_docente_id');
            $table->unsignedSmallInteger('numero')->default(1)->after('semana');
        });
        $siguiente = [];
        foreach (DB::table('sesiones_asistencia')->orderBy('fecha')->orderBy('id')->get() as $sesion) {
            $semana = CarbonImmutable::parse($sesion->fecha)->startOfWeek()->toDateString();
            $clave = $sesion->asignacion_docente_id.'|'.$semana;
            $siguiente[$clave] = ($siguiente[$clave] ?? 0) + 1;
            DB::table('sesiones_asistencia')->where('id', $sesion->id)->update(['semana' => $semana, 'numero' => $siguiente[$clave]]);
        }
        Schema::table('sesiones_asistencia', function (Blueprint $table) {
            $table->dropColumn(['horas', 'tema']);
            $table->date('semana')->nullable(false)->change();
            $table->date('fecha')->nullable()->change();
            $table->unique(['asignacion_docente_id', 'semana', 'numero'], 'sesion_carga_semana_numero_unique');
        });
        Schema::table('asistencias_estudiantes', function (Blueprint $table) {
            $table->dropColumn('observacion');
        });
    }
};
