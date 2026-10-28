<?php

namespace App\Services\Directivo;

use App\Models\AsignacionDocente;
use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\PeriodoAcademico;
use Illuminate\Support\Facades\DB;
use RuntimeException;
use Throwable;

class PropuestaDistribucion
{
    public const COMISIONES = [
        'Comisión técnico-pedagógica',
        'Comisión de convivencia y cultura de paz',
        'Comisión de gestión de riesgos y seguridad escolar',
        'Comisión de salud y bienestar',
        'Comisión de cultura y participación artística',
        'Comisión de deportes y recreación',
    ];

    public function __construct(private CompletarDistribucion $distribucion, private ContextoDocente $contexto) {}

    public function ejecutar(int $institucionId, int $periodoId, bool $aplicar = false): array
    {
        DB::beginTransaction();
        try {
            PeriodoAcademico::where('institucion_id', $institucionId)->lockForUpdate()->findOrFail($periodoId);
            $cursos = Curso::where('institucion_id', $institucionId)->where('estado', 'ACTIVO')
                ->whereHas('paralelos', fn ($q) => $q->where('estado', 'ACTIVO'))
                ->with(['especialidad', 'asignaturas', 'paralelos' => fn ($q) => $q->where('estado', 'ACTIVO')])->orderBy('id')->get();
            $horasConfiguradas = 0;
            foreach ($cursos as $curso) {
                $oferta = $curso->especialidad?->codigo ?? 'EGB';
                foreach ($curso->asignaturas as $asignatura) {
                    if ($asignatura->horas_semana !== null) {
                        continue;
                    }
                    $horas = $this->horasPropuestas($oferta, (int) $curso->grado, $asignatura->codigo);
                    if ($horas === null) {
                        throw new RuntimeException('No existe propuesta para '.$curso->nombre.' · '.$asignatura->nombre);
                    }
                    if ((int) $asignatura->institucion_id !== $institucionId || $asignatura->cursos()->where('cursos.id', '!=', $curso->id)->exists()) {
                        throw new RuntimeException('Una asignatura compartida requiere configurar sus horas por curso.');
                    }
                    $asignatura->update(['horas_semana' => $horas, 'descripcion' => $asignatura->descripcion.' Propuesta institucional de distribución: '.$horas.' períodos semanales.']);
                    $horasConfiguradas++;
                }
                if ($oferta === 'BGU' && (int) $curso->grado === 13) {
                    $optativas = [
                        'NCM' => ['Números Complejos y Métodos de Demostración Matemática', 'MATEMATICA', 3],
                        'ICT' => ['Investigación en Ciencia y Tecnología', 'CIENCIAS_NATURALES', 2],
                        'RC' => ['Redacción Creativa', 'LENGUAJE', 3],
                        'PMC' => ['Problemas del Mundo Contemporáneo', 'CIENCIAS_SOCIALES', 2],
                    ];
                    foreach ($optativas as $codigo => [$nombre, $area, $horas]) {
                        $optativa = Asignatura::firstOrCreate(['institucion_id' => $institucionId, 'codigo' => 'BGU-OPT-'.$codigo.'-13'], [
                            'especialidad_id' => $curso->especialidad_id, 'nombre' => $nombre, 'area' => $area, 'horas_semana' => $horas,
                            'descripcion' => 'Propuesta institucional: optativa de tercer curso de Ciencias del catálogo MINEDUC. Carga seleccionada para completar el bloque de 10 períodos.', 'estado' => 'ACTIVO',
                        ]);
                        $curso->asignaturas()->syncWithoutDetaching([$optativa->id]);
                    }
                }
                $curso->load('asignaturas');
                $esperadas = $oferta === 'BT-GFC' ? 40 : ($oferta === 'BGU' ? 35 : 30);
                if ((float) $curso->asignaturas->sum('horas_semana') !== (float) $esperadas) {
                    throw new RuntimeException('El currículo de '.$curso->nombre.' · '.$oferta.' no suma '.$esperadas.' períodos.');
                }
            }
            $resultado = $this->distribucion->ejecutar($institucionId, $periodoId, true);
            $comisiones = AsignacionDocente::where('institucion_id', $institucionId)->where('periodo_id', $periodoId)->where('tipo', 'comision')->get();
            $miembros = array_fill_keys(self::COMISIONES, 0);
            foreach ($comisiones as $comision) {
                if (array_key_exists($comision->titulo, $miembros)) {
                    $miembros[$comision->titulo]++;
                }
            }
            $nuevasComisiones = 0;
            $docentes = $this->contexto->docentes($institucionId)->where('estado', 'ACTIVO')->orderBy('id')->get();
            foreach ($docentes as $docente) {
                if ($comisiones->where('docente_id', $docente->id)->isNotEmpty()) {
                    continue;
                }
                asort($miembros);
                $titulo = array_key_first($miembros);
                AsignacionDocente::create(['institucion_id' => $institucionId, 'periodo_id' => $periodoId, 'docente_id' => $docente->id,
                    'tipo' => 'comision', 'titulo' => $titulo, 'horas_semanales' => 0,
                    'observaciones' => 'Propuesta de conformación institucional: miembro de la comisión. No computa períodos pedagógicos de clase. Coordinar el plan anual y las reuniones dentro del tiempo de gestión participativa.']);
                $miembros[$titulo]++;
                $nuevasComisiones++;
            }
            $resultado += ['horas_curriculares_configuradas' => $horasConfiguradas, 'comisiones_nuevas' => $nuevasComisiones, 'miembros_por_comision' => $miembros];
            $resultado['aplicado'] = $aplicar;
            if ($aplicar) {
                DB::commit();
            } else {
                DB::rollBack();
            }

            return $resultado;
        } catch (Throwable $e) {
            DB::rollBack();
            throw $e;
        }
    }

    private function horasPropuestas(string $oferta, int $grado, ?string $codigo): ?int
    {
        if ($oferta === 'EGB' && $grado >= 1 && $grado <= 7) {
            $distribucion = ['ECA' => 2, 'EF' => 3];
            if ($grado >= 2) {
                $distribucion += $grado <= 4 ? ['LLIT' => 8, 'MAT' => 6, 'CCSS' => 3, 'CCNN' => 3] : ['LLIT' => 6, 'MAT' => 6, 'CCSS' => 4, 'CCNN' => 4];
            }
            foreach ($distribucion as $materia => $horas) {
                if ($codigo === 'EGB-'.$materia.'-'.$grado) {
                    return $horas;
                }
            }
        }
        if ($oferta === 'BT-GFC') {
            $distribucion = ['LLIT' => 3, 'MAT' => 3, 'HIS' => 1, 'EC' => 1, 'FIL' => 1, 'BIO' => 1, 'FIS' => 1, 'QUI' => 1, 'ECA' => 1, 'EF' => 2, 'ING' => 2, 'CAI' => 1, 'EG' => 1];
            foreach ($distribucion as $materia => $horas) {
                if ($codigo === 'BT-GFC-'.$materia.'-'.$grado) {
                    return $horas;
                }
            }
        }

        return null;
    }
}
