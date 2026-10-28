<?php

$comunes = [
    'LLIT' => ['Lengua y Literatura', 'LENGUAJE'],
    'MAT' => ['Matemática', 'MATEMATICA'],
    'CCSS' => ['Estudios Sociales', 'CIENCIAS_SOCIALES'],
    'CCNN' => ['Ciencias Naturales', 'CIENCIAS_NATURALES'],
    'FIS' => ['Física', 'CIENCIAS_NATURALES'],
    'QUI' => ['Química', 'CIENCIAS_NATURALES'],
    'BIO' => ['Biología', 'CIENCIAS_NATURALES'],
    'HIS' => ['Historia', 'CIENCIAS_SOCIALES'],
    'EC' => ['Educación para la Ciudadanía', 'CIENCIAS_SOCIALES'],
    'FIL' => ['Filosofía', 'CIENCIAS_SOCIALES'],
    'ING' => ['Inglés', 'IDIOMAS'],
    'ECA' => ['Educación Cultural y Artística', 'EDUCACION_ARTISTICA'],
    'EF' => ['Educación Física', 'EDUCACION_FISICA'],
    'EG' => ['Emprendimiento y Gestión', 'TECNICA_TECNOLOGICA'],
    'CAI' => ['Cívica y Acompañamiento Integral en el Aula', 'CIENCIAS_SOCIALES'],
    'AL' => ['Animación a la Lectura', 'LENGUAJE'],
    'OVP' => ['Orientación Vocacional y Profesional', 'OTRAS'],
    'CI' => ['Currículo Integrador por Ámbitos de Aprendizaje', 'OTRAS'],
];

$catalogo = [];
$agregar = function (string $oferta, int $grado, array $horas, string $descripcion) use (&$catalogo, $comunes): void {
    foreach ($horas as $codigo => $carga) {
        [$nombre, $area] = $comunes[$codigo];
        $catalogo[] = [
            'codigo' => $oferta.'-'.$codigo.'-'.$grado,
            'nombre' => $nombre, 'area' => $area, 'grado' => $grado,
            'especialidad_codigo' => $oferta === 'EGB' ? null : $oferta,
            'horas_semana' => $carga, 'descripcion' => $descripcion,
        ];
    }
};

$agregar('EGB', 1, ['CI' => 25, 'ECA' => null, 'EF' => null],
    'MINEDUC-2023-00008-A: currículo integrador de Preparatoria (25 períodos); ECA y Educación Física comparten un bloque de 5 períodos.');
for ($grado = 2; $grado <= 7; $grado++) {
    $agregar('EGB', $grado, ['LLIT' => null, 'MAT' => null, 'CCSS' => null, 'CCNN' => null,
        'ECA' => null, 'EF' => null, 'ING' => 3, 'CAI' => 1, 'AL' => 1],
        'MINEDUC-2023-00008-A y reforma 2024-00060-A: Lengua, Matemática, Estudios Sociales y Ciencias Naturales comparten 20 períodos; ECA y Educación Física comparten 5. Distribución institucional pendiente.');
}
for ($grado = 8; $grado <= 10; $grado++) {
    $agregar('EGB', $grado, ['LLIT' => 6, 'MAT' => 6, 'CCSS' => 4, 'CCNN' => 4,
        'ECA' => 2, 'EF' => 2, 'ING' => 3, 'OVP' => 1, 'CAI' => 1, 'AL' => 1],
        'MINEDUC-2023-00008-A y reforma 2024-00060-A: períodos pedagógicos semanales mínimos sugeridos de EGB Superior.');
}
for ($grado = 11; $grado <= 13; $grado++) {
    $horas = $grado === 13
        ? ['MAT' => 4, 'FIS' => 2, 'QUI' => 2, 'BIO' => 2, 'HIS' => 3, 'LLIT' => 4, 'ING' => 3, 'EF' => 2, 'EG' => 2, 'CAI' => 1]
        : ['MAT' => 5, 'FIS' => 3, 'QUI' => 3, 'BIO' => 3, 'HIS' => 2, 'EC' => 2, 'FIL' => 2, 'LLIT' => 5, 'ING' => 3, 'ECA' => 2, 'EF' => 2, 'EG' => 2, 'CAI' => 1];
    $agregar('BGU', $grado, $horas,
        'MINEDUC-2023-00008-A y reforma 2024-00060-A: Bachillerato en Ciencias. En tercero, la institución debe elegir además asignaturas optativas por 10 períodos semanales.');
    $agregar('BT-GFC', $grado, array_fill_keys(['LLIT', 'MAT', 'HIS', 'EC', 'FIL', 'BIO', 'FIS', 'QUI', 'ECA', 'EF', 'ING', 'CAI', 'EG'], null),
        'MINEDEC-2025-00051-A: tronco común de 19 períodos por curso. La institución distribuye las asignaturas y sus horas entre los tres cursos; no hay carga individual fija por curso.');
}

$modulos = [
    'HIE' => ['Herramientas Informáticas Empresariales', [3, 2, 0]],
    'GCAF' => ['Gestión Contable y Administración Financiera', [5, 4, 0]],
    'ITAN' => ['Inglés Técnico Aplicado a los Negocios', [3, 2, 0]],
    'GCF' => ['Gestión y Control Financiero', [4, 4, 5]],
    'FI' => ['Financiamiento e Inversión', [0, 3, 3]],
    'LTA' => ['Legislación Tributaria Aplicada', [2, 2, 3]],
    'PCP' => ['Planificación y Control Presupuestario', [2, 2, 2]],
    'AF' => ['Análisis Financiero', [0, 0, 5]],
    'MPE' => ['Módulo Práctico Experimental', [2, 2, 3]],
];
foreach ($modulos as $codigo => [$nombre, $horas]) {
    foreach ($horas as $indice => $carga) {
        if ($carga === 0) {
            continue;
        }
        $grado = 11 + $indice;
        $catalogo[] = [
            'codigo' => 'BT-GFC-'.$codigo.'-'.$grado, 'nombre' => $nombre,
            'area' => 'TECNICA_TECNOLOGICA', 'grado' => $grado,
            'especialidad_codigo' => 'BT-GFC', 'horas_semana' => $carga,
            'descripcion' => 'Currículo oficial de la FIP Gestión Financiera y Contable (octubre 2025): módulo de formación técnica. Total de formación técnica por curso: 21 períodos semanales.',
        ];
    }
}

return $catalogo;
