<?php

namespace Database\Seeders;

use App\Models\Asignatura;
use App\Models\Curso;
use App\Models\Especialidad;
use App\Models\Institucion;
use Illuminate\Database\Seeder;

class AsignaturaSeeder extends Seeder
{
    public function run(): void
    {
        $instituciones = Institucion::query()->orderBy('id')->get();

        if ($instituciones->isEmpty()) {
            throw new \RuntimeException('No existe ninguna institución para asociar las asignaturas del seeder.');
        }

        $catalogo = $this->catalogoAsignaturas();

        foreach ($instituciones as $institucion) {
            // Índice de especialidades de esta institución: codigo => id
            $especialidades = Especialidad::query()
                ->where('institucion_id', $institucion->id)
                ->pluck('id', 'codigo');

            // Índice de cursos de esta institución: grado => id
            $cursos = Curso::query()
                ->where('institucion_id', $institucion->id)
                ->pluck('id', 'grado');

            foreach ($catalogo as $item) {
                $especialidadId = $item['especialidad_codigo']
                    ? ($especialidades[$item['especialidad_codigo']] ?? null)
                    : null;

                $asignatura = Asignatura::firstOrCreate(
                    [
                        'institucion_id'  => $institucion->id,
                        'codigo'          => $item['codigo'],
                    ],
                    [
                        'especialidad_id' => $especialidadId,
                        'nombre'          => $item['nombre'],
                        'area'            => $item['area'],
                        'horas_semana'    => $item['horas_semana'],
                        'descripcion'     => $item['descripcion'],
                        'estado'          => 'ACTIVO',
                    ]
                );

                // Sincroniza campos que pueden haber quedado vacíos en ejecuciones previas
                $updates = [];
                if (is_null($asignatura->especialidad_id) && $especialidadId) {
                    $updates['especialidad_id'] = $especialidadId;
                }
                if (is_null($asignatura->horas_semana) && $item['horas_semana']) {
                    $updates['horas_semana'] = $item['horas_semana'];
                }
                if ($updates) {
                    $asignatura->update($updates);
                }

                // Asocia los cursos indicados por grado
                $cursoIds = collect($item['grados'])
                    ->map(fn ($grado) => $cursos[$grado] ?? null)
                    ->filter()
                    ->values()
                    ->all();

                if ($cursoIds) {
                    $asignatura->cursos()->syncWithoutDetaching($cursoIds);
                }
            }
        }
    }

    /**
     * Catálogo basado en la malla curricular MINEDUC Ecuador
     * (Acuerdo Nro. MINEDUC-ME-2016-00020-A y actualizaciones 2023).
     *
     * especialidad_codigo null = común a toda la institución (sin especialidad específica).
     * grados: número de grado según tabla cursos (1–13).
     *   1–4  = Básica Elemental
     *   5–7  = Básica Media
     *   8–10 = Básica Superior
     *  11–13 = Bachillerato
     */
    private function catalogoAsignaturas(): array
    {
        return [
            // ── ÁREA: LENGUAJE ──────────────────────────────────────────────
            // ── LENGUAJE ────────────────────────────────────────────────────
            ['nombre' => 'Comprensión y Expresión Oral y Escrita', 'codigo' => 'CEOE',  'area' => 'LENGUAJE',            'horas_semana' => 10, 'especialidad_codigo' => null,   'grados' => [1],                              'descripcion' => 'Comunicación inicial mediante lenguaje oral, imagen y escritura emergente. (Preparatoria)'],
            ['nombre' => 'Lengua y Literatura',                    'codigo' => 'LLIT',  'area' => 'LENGUAJE',            'horas_semana' => 8,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10,11,12,13],   'descripcion' => 'Competencias comunicativas: lectura, escritura, expresión oral y comprensión crítica de textos. (EGB 2°–10° y BGU)'],

            // ── MATEMÁTICA ──────────────────────────────────────────────────
            ['nombre' => 'Relación Lógico-Matemática y Cuantificación', 'codigo' => 'RLMC', 'area' => 'MATEMATICA',     'horas_semana' => 8,  'especialidad_codigo' => null,   'grados' => [1],                              'descripcion' => 'Nociones de cantidad, clasificación, seriación y resolución de problemas concretos. (Preparatoria)'],
            ['nombre' => 'Matemática',                             'codigo' => 'MAT',   'area' => 'MATEMATICA',          'horas_semana' => 8,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10,11,12,13],   'descripcion' => 'Pensamiento lógico-matemático: números, álgebra, geometría, medida y estadística. (EGB 2°–10° y BGU)'],

            // ── CIENCIAS NATURALES ──────────────────────────────────────────
            ['nombre' => 'Descubrimiento del Medio Natural y Cultural', 'codigo' => 'DMNC', 'area' => 'CIENCIAS_NATURALES', 'horas_semana' => 3, 'especialidad_codigo' => null, 'grados' => [1],                             'descripcion' => 'Exploración sensorial del entorno natural, cultural y social inmediato. (Preparatoria)'],
            ['nombre' => 'Ciencias Naturales',                     'codigo' => 'CCNN',  'area' => 'CIENCIAS_NATURALES',  'horas_semana' => 5,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10],            'descripcion' => 'Entorno natural, seres vivos, materia, energía y ambiente. (EGB 2°–10°)'],
            ['nombre' => 'Biología',                               'codigo' => 'BIO',   'area' => 'CIENCIAS_NATURALES',  'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [11,12,13],                       'descripcion' => 'Célula, genética, evolución, ecología y biodiversidad. (BGU Tronco Común)'],
            ['nombre' => 'Química',                                'codigo' => 'QUI',   'area' => 'CIENCIAS_NATURALES',  'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [11,12,13],                       'descripcion' => 'Estructura de la materia, tabla periódica, reacciones y compuestos químicos. (BGU Tronco Común)'],
            ['nombre' => 'Física',                                 'codigo' => 'FIS',   'area' => 'CIENCIAS_NATURALES',  'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [11,12,13],                       'descripcion' => 'Mecánica, termodinámica, ondas, electricidad y física moderna. (BGU Tronco Común)'],

            // ── CIENCIAS SOCIALES ───────────────────────────────────────────
            ['nombre' => 'Convivencia',                            'codigo' => 'CONV',  'area' => 'CIENCIAS_SOCIALES',   'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1],                              'descripcion' => 'Normas básicas de convivencia, identidad personal y relaciones sociales. (Preparatoria)'],
            ['nombre' => 'Ciencias Sociales',                      'codigo' => 'CCSS',  'area' => 'CIENCIAS_SOCIALES',   'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10],            'descripcion' => 'Historia, geografía e identidad ciudadana. (EGB 2°–10°)'],
            ['nombre' => 'Historia',                               'codigo' => 'HIS',   'area' => 'CIENCIAS_SOCIALES',   'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [11,12,13],                       'descripcion' => 'Historia universal, de América Latina y del Ecuador contemporáneo. (BGU Tronco Común)'],
            ['nombre' => 'Educación para la Ciudadanía',           'codigo' => 'EC',    'area' => 'CIENCIAS_SOCIALES',   'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [11,12,13],                       'descripcion' => 'Derechos humanos, participación democrática, interculturalidad y Estado ecuatoriano. (BGU Tronco Común)'],
            ['nombre' => 'Filosofía',                              'codigo' => 'FIL',   'area' => 'CIENCIAS_SOCIALES',   'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [12,13],                          'descripcion' => 'Pensamiento crítico, epistemología, lógica, ética y filosofía política. (BGU 2° y 3°)'],

            // ── IDIOMAS ─────────────────────────────────────────────────────
            ['nombre' => 'Inglés',                                 'codigo' => 'ING',   'area' => 'IDIOMAS',             'horas_semana' => 5,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10,11,12,13],   'descripcion' => 'Lengua extranjera: habilidades comunicativas según estándares MCER. (EGB 2°–10° y BGU)'],
            ['nombre' => 'Kichwa',                                 'codigo' => 'KIC',   'area' => 'IDIOMAS',             'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1,2,3,4,5,6,7,8,9,10,11,12,13], 'descripcion' => 'Lengua ancestral para instituciones en zonas de influencia intercultural bilingüe.'],

            // ── EDUCACIÓN ARTÍSTICA ──────────────────────────────────────────
            ['nombre' => 'Expresión Artística',                    'codigo' => 'EART',  'area' => 'EDUCACION_ARTISTICA', 'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1],                              'descripcion' => 'Exploración de materiales y técnicas de expresión plástica y musical. (Preparatoria)'],
            ['nombre' => 'Educación Cultural y Artística',         'codigo' => 'ECA',   'area' => 'EDUCACION_ARTISTICA', 'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10,11,12,13],   'descripcion' => 'Expresión plástica, música, danza, teatro y apreciación del patrimonio cultural. (EGB y BGU)'],

            // ── EDUCACIÓN FÍSICA ─────────────────────────────────────────────
            ['nombre' => 'Expresión Corporal',                     'codigo' => 'ECOR',  'area' => 'EDUCACION_FISICA',    'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1],                              'descripcion' => 'Esquema corporal, movimiento, coordinación y juego simbólico. (Preparatoria)'],
            ['nombre' => 'Educación Física',                       'codigo' => 'EF',    'area' => 'EDUCACION_FISICA',    'horas_semana' => 3,  'especialidad_codigo' => null,   'grados' => [2,3,4,5,6,7,8,9,10,11,12,13],   'descripcion' => 'Habilidades motrices, deportes, actividad física y hábitos de vida saludable. (EGB y BGU)'],

            // ── TÉCNICA / TECNOLÓGICA — comunes ─────────────────────────────
            ['nombre' => 'Emprendimiento y Gestión',               'codigo' => 'EG',    'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => null,   'grados' => [8,9,10,11,12,13],                'descripcion' => 'Competencias emprendedoras, gestión de proyectos y cultura financiera básica. (EGB Superior y BGU)'],
            ['nombre' => 'Informática Aplicada',                   'codigo' => 'INFAP', 'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 3,  'especialidad_codigo' => null,   'grados' => [8,9,10,11,12,13],                'descripcion' => 'Herramientas ofimáticas, ciudadanía digital y tecnología como medio de aprendizaje.'],

            // ── TÉCNICA / TECNOLÓGICA — BTI ─────────────────────────────────
            ['nombre' => 'Programación',                           'codigo' => 'PROG',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 5,  'especialidad_codigo' => 'BTI',  'grados' => [11,12,13],                       'descripcion' => 'Fundamentos de programación estructurada y orientada a objetos. (Bachillerato Técnico en Informática)'],
            ['nombre' => 'Redes y Comunicaciones',                 'codigo' => 'RCOM',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTI',  'grados' => [11,12,13],                       'descripcion' => 'Topologías de red, protocolos TCP/IP, seguridad y administración de sistemas. (Bachillerato Técnico en Informática)'],
            ['nombre' => 'Base de Datos',                          'codigo' => 'BDD',   'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTI',  'grados' => [11,12,13],                       'descripcion' => 'Diseño relacional, SQL y gestión de sistemas de información. (Bachillerato Técnico en Informática)'],
            ['nombre' => 'Soporte Técnico',                        'codigo' => 'STEC',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTI',  'grados' => [11,12,13],                       'descripcion' => 'Mantenimiento de equipos, instalación de software y resolución de incidencias. (Bachillerato Técnico en Informática)'],

            // ── TÉCNICA / TECNOLÓGICA — BTCA ────────────────────────────────
            ['nombre' => 'Contabilidad General',                   'codigo' => 'CONT',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 5,  'especialidad_codigo' => 'BTCA', 'grados' => [11,12,13],                       'descripcion' => 'Principios contables NIIF, ciclo contable y estados financieros. (Bachillerato Técnico en Contabilidad)'],
            ['nombre' => 'Administración de Empresas',             'codigo' => 'ADM',   'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTCA', 'grados' => [11,12,13],                       'descripcion' => 'Fundamentos de gestión empresarial, planificación y organización. (Bachillerato Técnico en Contabilidad y Administración)'],
            ['nombre' => 'Tributación',                            'codigo' => 'TRIB',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTCA', 'grados' => [11,12,13],                       'descripcion' => 'Sistema tributario ecuatoriano, declaraciones al SRI y obligaciones fiscales. (Bachillerato Técnico en Contabilidad)'],

            // ── TÉCNICA / TECNOLÓGICA — BTE ─────────────────────────────────
            ['nombre' => 'Electrónica Básica',                     'codigo' => 'ELECB', 'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 5,  'especialidad_codigo' => 'BTE',  'grados' => [11,12,13],                       'descripcion' => 'Circuitos de CC y CA, componentes electrónicos pasivos y activos. (Bachillerato Técnico en Electrónica)'],
            ['nombre' => 'Automatización y Control',               'codigo' => 'AUTC',  'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 5,  'especialidad_codigo' => 'BTE',  'grados' => [11,12,13],                       'descripcion' => 'PLC, sensores, actuadores y control de procesos industriales. (Bachillerato Técnico en Electrónica)'],

            // ── TÉCNICA / TECNOLÓGICA — BTPA ────────────────────────────────
            ['nombre' => 'Producción Agropecuaria',                'codigo' => 'PAGRO', 'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 6,  'especialidad_codigo' => 'BTPA', 'grados' => [11,12,13],                       'descripcion' => 'Técnicas agrícolas y pecuarias, manejo de suelos y producción sostenible. (Bachillerato Técnico Productivo Agropecuario)'],
            ['nombre' => 'Agroecología',                           'codigo' => 'AGROE', 'area' => 'TECNICA_TECNOLOGICA', 'horas_semana' => 4,  'especialidad_codigo' => 'BTPA', 'grados' => [11,12,13],                       'descripcion' => 'Producción orgánica, permacultura y manejo ambiental. (Bachillerato Técnico Productivo Agropecuario)'],

            // ── OTRAS ────────────────────────────────────────────────────────
            ['nombre' => 'Ética y Valores',                        'codigo' => 'EV',    'area' => 'OTRAS',               'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1,2,3,4,5,6,7,8,9,10,11,12,13], 'descripcion' => 'Formación en valores, ética personal, social y cívica.'],
            ['nombre' => 'Proyectos Escolares',                    'codigo' => 'PESC',  'area' => 'OTRAS',               'horas_semana' => 2,  'especialidad_codigo' => null,   'grados' => [1,2,3,4,5,6,7,8,9,10,11,12,13], 'descripcion' => 'Proyectos interdisciplinarios orientados a la solución de problemas del entorno.'],
            ['nombre' => 'Orientación y Tutoría',                  'codigo' => 'OT',    'area' => 'OTRAS',               'horas_semana' => 1,  'especialidad_codigo' => null,   'grados' => [1,2,3,4,5,6,7,8,9,10,11,12,13], 'descripcion' => 'Acompañamiento socio-emocional, proyecto de vida y habilidades para la convivencia.'],
        ];
    }
}
