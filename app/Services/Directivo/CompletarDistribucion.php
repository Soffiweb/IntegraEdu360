<?php

namespace App\Services\Directivo;

use App\Models\AsignacionDocente;
use App\Models\Curso;
use App\Models\PeriodoAcademico;
use App\Models\Persona;
use App\Models\Rol;
use App\Models\Usuario;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;
use RuntimeException;

class CompletarDistribucion
{
    public function ejecutar(int $institucionId, int $periodoId, bool $aplicar = false): array
    {
        return DB::transaction(function () use ($institucionId, $periodoId, $aplicar) {
            PeriodoAcademico::where('institucion_id', $institucionId)->lockForUpdate()->findOrFail($periodoId);
            $docentes = app(ContextoDocente::class)->docentes($institucionId)->where('estado', 'ACTIVO')->orderBy('id')->lockForUpdate()->get();
            $base = AsignacionDocente::where('institucion_id', $institucionId)->where('periodo_id', $periodoId);
            $existentes = (clone $base)->lockForUpdate()->get();
            $cargas = $existentes->where('tipo', 'carga');
            $totales = $cargas->groupBy('docente_id')->map(fn ($filas) => (float) $filas->sum('horas_semanales'))->all();
            foreach ($totales as $horas) {
                if ($horas > 25) {
                    throw new RuntimeException('Existe un docente con más de 25 períodos. Corrija su carga antes de completar.');
                }
            }
            if ($existentes->where('tipo', 'comision')->groupBy('docente_id')->contains(fn ($filas) => $filas->count() > 2)) {
                throw new RuntimeException('Existe un docente con más de dos comisiones.');
            }
            $cursos = Curso::where('institucion_id', $institucionId)->where('estado', 'ACTIVO')
                ->whereHas('paralelos', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->with(['especialidad', 'paralelos' => fn ($q) => $q->where('estado', 'ACTIVO')->orderBy('id'), 'asignaturas' => fn ($q) => $q->where('institucion_id', $institucionId)->where('estado', 'ACTIVO')->orderBy('id')])->orderBy('id')->get();
            $tareas = [];
            $pendientes = [];
            foreach ($cursos as $curso) {
                $contexto = $curso->nombre.' · '.($curso->especialidad?->nombre ?? 'EGB');
                if ($curso->asignaturas->isEmpty()) {
                    $pendientes[] = $contexto.': sin currículo';
                }
                if ($curso->especialidad?->codigo === 'BGU' && (int) $curso->grado === 13 && $curso->asignaturas->sum('horas_semana') < 35) {
                    $pendientes[] = $contexto.': comprobar elección institucional de 10 períodos de optativas por paralelo';
                }
                foreach ($curso->asignaturas as $asignatura) {
                    if ($asignatura->horas_semana === null) {
                        $pendientes[] = $contexto.' · '.$asignatura->nombre.': horas institucionales pendientes';

                        continue;
                    }
                    $horas = (float) $asignatura->horas_semana;
                    if ($horas <= 0) {
                        continue;
                    }
                    if ($horas > 25) {
                        throw new RuntimeException('Una asignatura supera 25 períodos y requiere una decisión sobre su reparto.');
                    }
                    foreach ($curso->paralelos as $paralelo) {
                        $previas = $cargas->where('paralelo_id', $paralelo->id)->where('asignatura_id', $asignatura->id);
                        if ($previas->isNotEmpty()) {
                            if ((float) $previas->sum('horas_semanales') !== $horas) {
                                throw new RuntimeException('La carga existente difiere del currículo: '.$contexto.' '.$paralelo->letra.' · '.$asignatura->nombre);
                            }

                            continue;
                        }
                        $tareas[] = ['curso_id' => $curso->id, 'paralelo_id' => $paralelo->id, 'asignatura_id' => $asignatura->id, 'horas_semanales' => $horas,
                            'titulo' => $asignatura->nombre.' · '.$curso->nombre.' '.$paralelo->letra.' [P'.$paralelo->id.']'];
                    }
                }
            }
            usort($tareas, fn ($a, $b) => $b['horas_semanales'] <=> $a['horas_semanales']);
            $ids = $docentes->modelKeys();
            $nuevos = 0;
            foreach ($tareas as $tarea) {
                $candidatos = array_values(array_filter($ids, fn ($id) => ($totales[$id] ?? 0) + $tarea['horas_semanales'] <= 25));
                usort($candidatos, fn ($a, $b) => (($totales[$a] ?? 0) <=> ($totales[$b] ?? 0)) ?: ($a <=> $b));
                $id = $candidatos[0] ?? null;
                if ($id === null) {
                    $nuevos++;
                    $id = -$nuevos;
                    if ($aplicar) {
                        $numero = 1;
                        do {
                            $sufijo = sprintf('%02d', $numero++);
                            $username = 'docente.nuevo.'.$institucionId.'.'.$sufijo;
                        } while (Usuario::where('username', $username)->exists());
                        $persona = Persona::create(['primer_nombre' => 'Docente', 'primer_apellido' => 'NUEVO '.$sufijo]);
                        $usuario = Usuario::create(['persona_id' => $persona->id, 'institucion_id' => $institucionId, 'username' => $username, 'password_hash' => Hash::make(Str::random(64)), 'estado' => 'ACTIVO']);
                        $rol = Rol::whereIn('codigo', ['DOC', 'DOCENTE', '04'])->firstOrFail();
                        $usuario->roles()->attach($rol->id, ['institucion_id' => $institucionId]);
                        $id = $usuario->id;
                    }
                    $ids[] = $id;
                }
                $totales[$id] = ($totales[$id] ?? 0) + $tarea['horas_semanales'];
                if ($aplicar) {
                    AsignacionDocente::create($tarea + ['institucion_id' => $institucionId, 'periodo_id' => $periodoId, 'docente_id' => $id, 'tipo' => 'carga', 'observaciones' => 'Distribución automática según las horas del currículo institucional registrado.']);
                }
            }

            return ['aplicado' => $aplicar, 'asignaciones_nuevas' => count($tareas), 'docentes_nuevos' => $nuevos, 'docentes_con_carga' => count($totales), 'horas_pedagogicas' => array_sum($totales), 'maximo_docente' => empty($totales) ? 0 : max($totales), 'pendientes' => $pendientes];
        });
    }
}
