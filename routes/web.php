<?php

use App\Http\Controllers\Admin\AsignaturaController;
use App\Http\Controllers\Admin\CursoController;
use App\Http\Controllers\Admin\EspecialidadController;
use App\Http\Controllers\Admin\EstudianteController;
use App\Http\Controllers\Admin\InstitucionDatosController;
use App\Http\Controllers\Admin\ParaleloController;
use App\Http\Controllers\Admin\PeriodoAcademicoController;
use App\Http\Controllers\Admin\UsuarioController;
use App\Http\Controllers\Inspector\DistribucionHorasController;
use App\Http\Controllers\Inspector\HorarioDocenteController;
use App\Http\Controllers\Inspector\NovedadController;
use App\Http\Controllers\Superusuario\DocenteController;
use App\Http\Controllers\Superusuario\DistritoController;
use App\Http\Controllers\Superusuario\InstitucionController;
use App\Http\Controllers\Superusuario\RolController;
use App\Http\Controllers\Superusuario\UsuarioController as SuperusuarioUsuarioController;
use App\Http\Controllers\Superusuario\ZonaController;
use App\Models\Institucion;
use App\Models\Usuario;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Route;

if (! function_exists('integraEduRoles')) {
    function integraEduRoles(): array
    {
        return [
            'superusuario' => [
                'name' => 'Superusuario',
                'short' => 'SU',
                'category' => 'Gobierno institucional',
                'summary' => 'Supervision de instituciones, usuarios y parametros criticos.',
                'description' => 'Gestione instituciones, reglas maestras y configuraciones estrategicas desde el nivel mas alto.',
                'accent' => '#7a2fc1',
            ],
            'admin' => [
                'name' => 'Administrador',
                'short' => 'AD',
                'category' => 'Gesti??n central',
                'summary' => 'Supervisi??n global de usuarios, configuraci??n institucional, permisos y control operativo.',
                'description' => 'Administre la plataforma institucional, organice permisos y supervise el desempe??o acad??mico y administrativo desde un entorno centralizado.',
                'accent' => '#d9a441',
            ],        'docente' => [
            ],
            'directivo' => [
                'name' => 'Directivo',
                'short' => 'DI',
                'category' => 'Alta dirección',
                'summary' => 'Supervisión estratégica y análisis de indicadores institucionales.',
                'description' => 'Acceda a reportes gerenciales, métricas de cumplimiento y visión global del ecosistema educativo para la toma de decisiones.',
                'accent' => '#1e40af',
            ],
            'inspector' => [
                'name' => 'Inspector',
                'short' => 'IN',
                'category' => 'Supervision operativa',
                'summary' => 'Seguimiento de convivencia, control disciplinario y supervision del cumplimiento institucional.',
                'description' => 'Supervise novedades disciplinarias, acompanamiento estudiantil y control operativo diario con una vista clara para la gestion institucional.',
                'accent' => '#0f766e',
            ],
            'docente' => [
                'name' => 'Docente',
                'short' => 'DO',
                'category' => 'Área académica',
                'summary' => 'Acceso a clases, planificación, evaluación, seguimiento académico y comunicación formativa.',
                'description' => 'Gestione sus asignaturas, planifique experiencias de aprendizaje y acompañe el progreso de cada estudiante con herramientas claras y ágiles.',
                'accent' => '#2f80c1',
            ],
            'estudiante' => [
                'name' => 'Estudiante',
                'short' => 'ES',
                'category' => 'Vida estudiantil',
                'summary' => 'Consulta de asignaturas, tareas, calificaciones, asistencia y recursos de aprendizaje.',
                'description' => 'Acceda a su experiencia académica diaria con un panel ordenado para revisar clases, evidencias, tareas y comunicaciones importantes.',
                'accent' => '#4c9a5f',
            ],
            'padre-tutor' => [
                'name' => 'Padre o Tutor',
                'short' => 'PT',
                'category' => 'Acompañamiento',
                'summary' => 'Monitoreo del progreso del estudiante, avisos institucionales y seguimiento familiar.',
                'description' => 'Acompañe el desarrollo del estudiante con información confiable sobre asistencia, rendimiento y novedades relevantes de la institución.',
                'accent' => '#b86a3b',
            ],
            'administrativo' => [
                'name' => 'Personal Administrativo',
                'short' => 'PA',
                'category' => 'Operación interna',
                'summary' => 'Procesos de matrícula, documentación, reportes internos y soporte a la operación diaria.',
                'description' => 'Optimice procesos internos de matrícula, archivo y control documental mediante una vista estructurada para la gestión institucional.',
                'accent' => '#6a5acd',
            ],
            'asesor-academico' => [
                'name' => 'Asesor Académico',
                'short' => 'AA',
                'category' => 'Seguimiento',
                'summary' => 'Orientación curricular, análisis de desempeño y acompañamiento para decisiones pedagógicas.',
                'description' => 'Analice el avance académico y fortalezca decisiones pedagógicas con trazabilidad, contexto y apoyo estratégico.',
                'accent' => '#008b8b',
            ],
            'soporte-tecnico' => [
                'name' => 'Soporte Técnico',
                'short' => 'ST',
                'category' => 'Infraestructura',
                'summary' => 'Atención a incidencias, continuidad tecnológica y mantenimiento del ecosistema digital.',
                'description' => 'Mantenga la continuidad de los servicios tecnológicos mediante accesos rápidos para monitoreo, incidentes y soporte institucional.',
                'accent' => '#7a8793',
            ],
            'coordinador-curso' => [
                'name' => 'Coordinador de Curso',
                'short' => 'CC',
                'category' => 'Coordinación',
                'summary' => 'Supervisión de grupos, seguimiento disciplinario y articulación entre áreas y familias.',
                'description' => 'Coordine grupos y articule acciones entre docentes, familias y áreas de apoyo con una visión integral del curso.',
                'accent' => '#d97706',
            ],
            'dece' => [
                'name' => 'DECE',
                'short' => 'DE',
                'category' => 'Bienestar',
                'summary' => 'Apoyo socioemocional, observación integral y acompañamiento especializado al estudiante.',
                'description' => 'Brinde seguimiento especializado a bienestar estudiantil, observaciones y acompañamiento socioemocional.',
                'accent' => '#c2417a',
            ],
        ];
    }
}

if (! function_exists('integraEduDashboards')) {
    function integraEduDashboards(): array
    {
        return [
            'superusuario' => [
                'greeting' => 'Centro de gobierno institucional',
                'headline' => 'Controle instituciones, parametros globales y operaciones sensibles.',
                'kpis' => [
                    ['value' => '24', 'label' => 'Instituciones activas', 'trend' => '2 nuevas este mes'],
                    ['value' => '6', 'label' => 'Solicitudes pendientes', 'trend' => 'En revision'],
                    ['value' => '99%', 'label' => 'Disponibilidad', 'trend' => 'Estable'],
                ],
                'focus' => [
                    'title' => 'Prioridad estrategica',
                    'body' => 'Mantenga consistencia entre instituciones, usuarios y configuraciones criticas.',
                ],
                'highlights' => [
                    ['title' => 'Gobierno global', 'body' => 'Supervision de instituciones y reglas maestras.'],
                    ['title' => 'Estandarizacion', 'body' => 'Alinee parametros operativos entre sedes.'],
                    ['title' => 'Control de acceso', 'body' => 'Audite accesos y cambios sensibles.'],
                ],
                'timeline' => [
                    ['time' => '08:00', 'title' => 'Revision de instituciones', 'detail' => 'Actualizacion de datos y estados.'],
                    ['time' => '12:30', 'title' => 'Validacion de cambios', 'detail' => 'Parametros maestros en revision.'],
                    ['time' => '17:10', 'title' => 'Cierre operativo', 'detail' => 'Reporte consolidado por instituciones.'],
                ],
                'actions' => [
                    ['label' => 'Administrar instituciones', 'description' => 'CRUD central de instituciones.', 'slug' => 'instituciones'],
                    ['label' => 'Zonas educativas', 'description' => 'Consultar las zonas y su cobertura territorial.', 'slug' => 'zonas'],
                    ['label' => 'Supervisar usuarios', 'description' => 'Control de cuentas y roles clave.', 'slug' => 'usuarios'],

                ],
                'alerts' => [
                    ['title' => 'Revision pendiente', 'detail' => '2 instituciones requieren validacion de datos.'],
                    ['title' => 'Cambio critico', 'detail' => 'Se aprobo un ajuste en reglas maestras.'],
                ],
            ],
            'admin' => [
                'greeting' => 'Centro de control institucional',
                'headline' => '',
                'kpis' => [
                    ['value' => '1,284', 'label' => 'Usuarios activos', 'trend' => '+8% este mes'],
                    ['value' => '42', 'label' => 'M??dulos configurados', 'trend' => '3 ajustes pendientes'],
                    ['value' => '97%', 'label' => 'Disponibilidad de servicios', 'trend' => 'Estable'],
                ],
                'focus' => [
                    'title' => 'Prioridad institucional',
                    'body' => 'Revise permisos sensibles, monitoree la plataforma y coordine acciones estrat??gicas entre ??reas acad??micas y administrativas.',
                ],
                'highlights' => [
                    ['title' => 'Gobierno del sistema', 'body' => 'Gesti??n de roles, seguridad, parametrizaci??n y control de accesos cr??ticos.'],
                    ['title' => 'Indicadores ejecutivos', 'body' => 'Vista consolidada de rendimiento acad??mico, operaci??n y uso de plataforma.'],
                    ['title' => 'Supervisi??n transversal', 'body' => 'Seguimiento a incidencias, comunicaciones masivas y cumplimiento institucional.'],
                ],
                'timeline' => [
                    ['time' => '07:30', 'title' => 'Revisi??n de alertas de seguridad', 'detail' => 'Validaci??n de accesos recientes y eventos cr??ticos.'],
                    ['time' => '10:00', 'title' => 'Comit?? de indicadores', 'detail' => 'An??lisis de matr??cula, desempe??o y asistencia global.'],
                    ['time' => '15:30', 'title' => 'Aprobaci??n de nuevos permisos', 'detail' => 'Autorizaci??n de perfiles para coordinaciones y soporte.'],
                ],
                'actions' => [
                    ['label' => 'Datos institucionales', 'description' => 'Editar la informacion principal de la institucion asignada.', 'slug' => 'datos-institucionales'],
                    ['label' => 'Periodos academicos', 'description' => 'Crear y editar periodos academicos de la institucion activa.', 'slug' => 'periodos'],
                    ['label' => 'Administracion de usuarios', 'description' => 'Gestionar usuarios de la institucion activa.', 'slug' => 'usuarios'],
                    ['label' => 'Administracion de docentes', 'description' => 'Gestionar docentes de la institucion activa.', 'slug' => 'docentes'],
                    ['label' => 'Administracion de alumnos', 'description' => 'Consultar el espacio de gestion de alumnos.', 'slug' => 'estudiantes'],
                    ['label' => 'Administracion de cursos', 'description' => 'Consultar el espacio de gestion de cursos.', 'slug' => 'cursos'],
                    ['label' => 'Administracion de paralelos', 'description' => 'Consultar el espacio de gestion de paralelos.', 'slug' => 'paralelos'],
                    ['label' => 'Administracion de especialidades', 'description' => 'Consultar el espacio de gestion de especialidades.', 'slug' => 'especialidades'],
                    ['label' => 'Administracion de asignaturas', 'description' => 'Consultar el espacio de gestion de asignaturas.', 'slug' => 'asignaturas'],
                    ['label' => 'Cerrar sesion', 'description' => 'Salir de la sesion actual de forma segura.', 'slug' => 'cerrar-sesion'],
                ],
                'alerts' => [
                    ['title' => 'Respaldo programado', 'detail' => 'Hoy 23:00 se ejecutar?? mantenimiento preventivo de base de datos.'],
                    ['title' => 'Permisos por validar', 'detail' => '5 solicitudes de ampliaci??n de acceso esperan aprobaci??n.'],
                ],
            ],        'docente' => [
            ],
            'directivo' => [
                'greeting' => 'Panel de Alta Dirección',
                'headline' => 'Visión estratégica y control de indicadores clave.',
                'kpis' => [
                    ['value' => '94%', 'label' => 'Cumplimiento global', 'trend' => '+2% este periodo'],
                    ['value' => '1,284', 'label' => 'Estudiantes matriculados', 'trend' => '92% de capacidad'],
                    ['value' => '8.6', 'label' => 'Promedio académico', 'trend' => 'Estable'],
                ],
                'focus' => [
                    'title' => 'Prioridad directiva',
                    'body' => 'Monitoree el cumplimiento de metas institucionales y analice tendencias para la toma de decisiones.',
                ],
                'highlights' => [
                    ['title' => 'Gestión estratégica', 'body' => 'Dashboard consolidado de todas las áreas operativas.'],
                    ['title' => 'Reportes gerenciales', 'body' => 'Exportación de métricas para consejos directivos.'],
                ],
                'timeline' => [
                    ['time' => '09:00', 'title' => 'Revisión de indicadores', 'detail' => 'Análisis de matrícula y estados financieros.'],
                    ['time' => '11:30', 'title' => 'Comité ejecutivo', 'detail' => 'Presentación de resultados parciales.'],
                ],
                'actions' => [
                    ['label' => 'Ver indicadores', 'description' => 'KPIs de rendimiento y operación.', 'slug' => 'indicadores'],
                    ['label' => 'Reportes', 'description' => 'Consolidado de áreas.', 'slug' => 'reportes'],
                ],
                'alerts' => [
                    ['title' => 'Cierre de periodo', 'detail' => 'Faltan 3 días para el corte de reportes trimestrales.'],
                ],
            ],
            'inspector' => [
                'greeting' => 'Panel de inspeccion institucional',
                'headline' => 'Controle novedades disciplinarias, seguimiento estudiantil y supervision operativa diaria.',
                'kpis' => [
                    ['value' => '18', 'label' => 'Novedades registradas', 'trend' => '3 pendientes de seguimiento'],
                    ['value' => '7', 'label' => 'Casos activos', 'trend' => '2 prioritarios hoy'],
                    ['value' => '96%', 'label' => 'Cobertura de control', 'trend' => 'Jornada estable'],
                ],
                'focus' => [
                    'title' => 'Prioridad del inspector',
                    'body' => 'Mantenga trazabilidad de observaciones, acompanamiento disciplinario y coordinacion con directivos, docentes y familias.',
                ],
                'highlights' => [
                    ['title' => 'Convivencia institucional', 'body' => 'Seguimiento de reportes, observaciones y medidas formativas.'],
                    ['title' => 'Control operativo', 'body' => 'Revision diaria de novedades y cumplimiento de protocolos.'],
                    ['title' => 'Coordinacion inmediata', 'body' => 'Articulacion con tutoria, DECE y autoridades cuando un caso lo requiere.'],
                ],
                'timeline' => [
                    ['time' => '07:15', 'title' => 'Revision de ingreso', 'detail' => 'Control inicial de novedades y asistencia general.'],
                    ['time' => '10:30', 'title' => 'Seguimiento de incidencias', 'detail' => 'Validacion de observaciones registradas durante la jornada.'],
                    ['time' => '13:45', 'title' => 'Cierre de reporte diario', 'detail' => 'Consolidado de casos y acciones ejecutadas.'],
                ],
                'actions' => [
                    ['label' => 'Registrar novedad', 'description' => 'Documente observaciones disciplinarias o de convivencia.', 'slug' => 'novedades'],
                    ['label' => 'Ver seguimiento', 'description' => 'Revise casos activos y compromisos pendientes.', 'slug' => 'seguimiento'],
                    ['label' => 'Asistencia Docentes', 'description' => 'Supervise la presencia diaria del personal docente asignado.', 'slug' => 'asistencia-docentes'],
                    ['label' => 'Asistencia Estudiantes', 'description' => 'Revise la asistencia estudiantil por paralelo y jornada.', 'slug' => 'asistencia-estudiantes'],
                ],
                'alerts' => [
                    ['title' => 'Caso prioritario', 'detail' => 'Una observacion disciplinaria requiere validacion con coordinacion.'],
                    ['title' => 'Seguimiento del dia', 'detail' => 'Dos estudiantes mantienen compromisos abiertos de convivencia.'],
                ],
            ],
            'docente' => [
                'greeting' => 'Panel pedagógico',
                'headline' => 'Organice clases, evalúe avances y acompañe el aprendizaje en tiempo real.',
                'kpis' => [
                    ['value' => '6', 'label' => 'Cursos asignados', 'trend' => '2 con evaluación esta semana'],
                    ['value' => '184', 'label' => 'Estudiantes a cargo', 'trend' => '93% asistencia promedio'],
                    ['value' => '18', 'label' => 'Tareas por revisar', 'trend' => 'Carga media'],
                ],
                'focus' => [
                    'title' => 'Prioridad docente',
                    'body' => 'Priorice revisión de evidencias, planificación semanal y seguimiento de estudiantes con alertas tempranas.',
                ],
                'highlights' => [
                    ['title' => 'Planificación académica', 'body' => 'Acceda a cronogramas, contenidos y recursos por asignatura.'],
                    ['title' => 'Evaluación continua', 'body' => 'Califique tareas, rúbricas y observaciones con rapidez.'],
                    ['title' => 'Acompañamiento del aula', 'body' => 'Detecte estudiantes con bajo desempeño o inasistencias repetidas.'],
                ],
                'timeline' => [
                    ['time' => '08:00', 'title' => 'Clase 10mo A - Matemática', 'detail' => 'Repaso de funciones lineales y actividad guiada.'],
                    ['time' => '11:20', 'title' => 'Entrega de calificaciones', 'detail' => 'Publicación del parcial de Física.'],
                    ['time' => '14:30', 'title' => 'Tutoría académica', 'detail' => 'Seguimiento a estudiantes con refuerzo.'],
                ],
                'actions' => [
                    ['label' => 'Registrar asistencia', 'description' => 'Complete novedades diarias por curso.', 'slug' => 'asistencia'],
                    ['label' => 'Asistencia Estudiantes', 'description' => 'Revise y registre asistencia estudiantil por paralelo y jornada.', 'slug' => 'asistencia-estudiantes'],
                    ['label' => 'Asistencia Docentes', 'description' => 'Controle la presencia diaria del personal docente asignado.', 'slug' => 'asistencia-docentes'],
                    ['label' => 'Asistencia Administrativos', 'description' => 'Consulte novedades y control de ingreso del personal administrativo.', 'slug' => 'asistencia-administrativos'],
                    ['label' => 'Asistencias P. Servicio', 'description' => 'Monitoree asistencia y novedades del personal de servicio.', 'slug' => 'asistencia-personal-servicio'],
                    ['label' => 'Calificar actividades', 'description' => 'Revise entregas pendientes y retroalimente.', 'slug' => 'calificaciones'],
                    ['label' => 'Publicar recursos', 'description' => 'Comparta material y tareas por clase.', 'slug' => 'recursos'],
                ],
                'alerts' => [
                    ['title' => 'Entrega pendiente', 'detail' => '3 estudiantes de 9no B no enviaron la actividad de Ciencias.'],
                    ['title' => 'Observación prioritaria', 'detail' => 'Un estudiante supera el umbral de inasistencias del período.'],
                ],
            ],
            'estudiante' => [
                'greeting' => 'Mi espacio académico',
                'headline' => 'Revise sus clases, tareas y progreso desde un panel claro y motivador.',
                'kpis' => [
                    ['value' => '8.94', 'label' => 'Promedio general', 'trend' => '+0.3 vs. mes anterior'],
                    ['value' => '4', 'label' => 'Tareas próximas', 'trend' => '2 vencen hoy'],
                    ['value' => '96%', 'label' => 'Asistencia acumulada', 'trend' => 'Excelente'],
                ],
                'focus' => [
                    'title' => 'Prioridad del estudiante',
                    'body' => 'Organice sus entregas, consulte retroalimentación y mantenga al día su asistencia y rendimiento.',
                ],
                'highlights' => [
                    ['title' => 'Clases del día', 'body' => 'Acceso rápido al horario, enlaces y recursos activos.'],
                    ['title' => 'Tareas y evaluaciones', 'body' => 'Identifique entregas próximas y pendientes por prioridad.'],
                    ['title' => 'Progreso personal', 'body' => 'Visualice su rendimiento y seguimiento por asignatura.'],
                ],
                'timeline' => [
                    ['time' => '07:10', 'title' => 'Lengua y Literatura', 'detail' => 'Ensayo argumentativo y lectura guiada.'],
                    ['time' => '10:40', 'title' => 'Laboratorio de Ciencias', 'detail' => 'Práctica sobre densidad y registro.'],
                    ['time' => '19:00', 'title' => 'Entrega de proyecto', 'detail' => 'Subir presentación final de Historia.'],
                ],
                'actions' => [
                    ['label' => 'Ver tareas', 'description' => 'Consulte prioridades y fechas de entrega.', 'slug' => 'tareas'],
                    ['label' => 'Entrar a clases', 'description' => 'Acceda a sus recursos y contenidos del día.', 'slug' => 'clases'],
                    ['label' => 'Revisar calificaciones', 'description' => 'Analice su avance por materia.', 'slug' => 'progreso'],
                ],
                'alerts' => [
                    ['title' => 'Entrega hoy', 'detail' => 'Proyecto de Historia vence a las 19:00.'],
                    ['title' => 'Mensaje nuevo', 'detail' => 'Su docente dejó retroalimentación en la tarea de Lengua.'],
                ],
            ],
            'padre-tutor' => [
                'greeting' => 'Seguimiento familiar',
                'headline' => 'Acompañe el progreso académico y formativo del estudiante con información clara.',
                'kpis' => [
                    ['value' => '96%', 'label' => 'Asistencia del representado', 'trend' => 'Dentro del objetivo'],
                    ['value' => '8.71', 'label' => 'Promedio actual', 'trend' => 'Leve mejora'],
                    ['value' => '3', 'label' => 'Avisos nuevos', 'trend' => '2 institucionales'],
                ],
                'focus' => [
                    'title' => 'Prioridad familiar',
                    'body' => 'Mantenga comunicación activa con la institución y supervise tareas, asistencia y desempeño del estudiante.',
                ],
                'highlights' => [
                    ['title' => 'Rendimiento consolidado', 'body' => 'Resumen de notas, asistencia y observaciones clave.'],
                    ['title' => 'Comunicación institucional', 'body' => 'Avisos, citaciones y mensajes relevantes de docentes y coordinación.'],
                    ['title' => 'Acompañamiento preventivo', 'body' => 'Identificación temprana de riesgos académicos o de convivencia.'],
                ],
                'timeline' => [
                    ['time' => '08:00', 'title' => 'Reporte de asistencia actualizado', 'detail' => 'Sin novedades disciplinarias hoy.'],
                    ['time' => '13:00', 'title' => 'Aviso de tutoría', 'detail' => 'Reunión breve con docente de Matemática.'],
                    ['time' => '18:30', 'title' => 'Revisión de tareas', 'detail' => 'Se recomienda acompañar la actividad de Ciencias.'],
                ],
                'actions' => [
                    ['label' => 'Consultar progreso', 'description' => 'Revise notas, asistencia y observaciones.'],
                    ['label' => 'Leer avisos', 'description' => 'Abra comunicaciones institucionales recientes.'],
                    ['label' => 'Solicitar reunión', 'description' => 'Coordine seguimiento con docentes o tutoría.'],
                ],
                'alerts' => [
                    ['title' => 'Comunicación nueva', 'detail' => 'Se publicó el cronograma del siguiente período evaluativo.'],
                    ['title' => 'Acción sugerida', 'detail' => 'Refuerzo recomendado en Matemática para esta semana.'],
                ],
            ],
            'administrativo' => [
                'greeting' => 'Operación administrativa',
                'headline' => 'Gestione matrículas, documentos y procesos internos con trazabilidad.',
                'kpis' => [
                    ['value' => '128', 'label' => 'Trámites en curso', 'trend' => '14 prioritarios'],
                    ['value' => '32', 'label' => 'Matrículas por confirmar', 'trend' => 'Corte 16:00'],
                    ['value' => '99%', 'label' => 'Documentación validada', 'trend' => '2 casos observados'],
                ],
                'focus' => [
                    'title' => 'Prioridad administrativa',
                    'body' => 'Agilice validaciones, organice expedientes y mantenga actualizada la información institucional.',
                ],
                'highlights' => [
                    ['title' => 'Gestión documental', 'body' => 'Control de archivos, certificados y reportes administrativos.'],
                    ['title' => 'Matrícula y admisiones', 'body' => 'Seguimiento a cupos, pagos y estados de inscripción.'],
                    ['title' => 'Soporte interno', 'body' => 'Atención a requerimientos operativos de coordinación y rectorado.'],
                ],
                'timeline' => [
                    ['time' => '08:15', 'title' => 'Validación de expedientes', 'detail' => 'Revisión de documentos faltantes en nuevas matrículas.'],
                    ['time' => '11:45', 'title' => 'Emisión de certificados', 'detail' => 'Bloque de 12 solicitudes en cola.'],
                    ['time' => '16:00', 'title' => 'Cierre de novedades', 'detail' => 'Consolidación diaria para rectorado.'],
                ],
                'actions' => [
                    ['label' => 'Procesar matrículas', 'description' => 'Revise estados, documentos y observaciones.'],
                    ['label' => 'Emitir certificados', 'description' => 'Genere constancias y reportes oficiales.'],
                    ['label' => 'Actualizar expedientes', 'description' => 'Mantenga registro completo por estudiante.'],
                ],
                'alerts' => [
                    ['title' => 'Documento pendiente', 'detail' => '2 expedientes carecen de firma de autorización.'],
                    ['title' => 'Vencimiento operativo', 'detail' => 'Hoy cierra la carga de novedades administrativas.'],
                ],
            ],
            'asesor-academico' => [
                'greeting' => 'Análisis y acompañamiento',
                'headline' => 'Convierta datos académicos en decisiones pedagógicas oportunas.',
                'kpis' => [
                    ['value' => '27', 'label' => 'Casos en seguimiento', 'trend' => '6 prioritarios'],
                    ['value' => '12', 'label' => 'Informes por emitir', 'trend' => 'Corte semanal'],
                    ['value' => '84%', 'label' => 'Cumplimiento de planes', 'trend' => 'En mejora'],
                ],
                'focus' => [
                    'title' => 'Prioridad de asesoría',
                    'body' => 'Identifique brechas de aprendizaje, coordine intervenciones y refuerce planes pedagógicos por curso.',
                ],
                'highlights' => [
                    ['title' => 'Trazabilidad académica', 'body' => 'Cruce de rendimiento, asistencia y evolución por período.'],
                    ['title' => 'Intervención focalizada', 'body' => 'Priorización de cursos y estudiantes con alertas de desempeño.'],
                    ['title' => 'Apoyo a docentes', 'body' => 'Recomendaciones didácticas basadas en evidencia institucional.'],
                ],
                'timeline' => [
                    ['time' => '09:00', 'title' => 'Revisión de cohortes críticas', 'detail' => 'Comparativa entre 8vo y 9no de básica superior.'],
                    ['time' => '12:30', 'title' => 'Mesa técnica con coordinación', 'detail' => 'Análisis de alertas académicas del período.'],
                    ['time' => '17:00', 'title' => 'Entrega de informe pedagógico', 'detail' => 'Recomendaciones para refuerzo en Matemática.'],
                ],
                'actions' => [
                    ['label' => 'Abrir informes', 'description' => 'Revise reportes por curso y asignatura.'],
                    ['label' => 'Analizar alertas', 'description' => 'Priorice casos por nivel de riesgo.'],
                    ['label' => 'Coordinar intervención', 'description' => 'Programe acompañamiento con docentes y familias.'],
                ],
                'alerts' => [
                    ['title' => 'Riesgo académico', 'detail' => 'El curso 9no B muestra descenso sostenido en razonamiento numérico.'],
                    ['title' => 'Informe requerido', 'detail' => 'Se solicita consolidado para consejo académico mañana.'],
                ],
            ],
            'soporte-tecnico' => [
                'greeting' => 'Centro de soporte tecnológico',
                'headline' => 'Monitoree servicios, atienda incidencias y sostenga la continuidad operativa.',
                'kpis' => [
                    ['value' => '14', 'label' => 'Tickets abiertos', 'trend' => '4 críticos'],
                    ['value' => '99.2%', 'label' => 'Uptime de plataforma', 'trend' => 'Normal'],
                    ['value' => '6 min', 'label' => 'Tiempo medio de respuesta', 'trend' => '-2 min'],
                ],
                'focus' => [
                    'title' => 'Prioridad técnica',
                    'body' => 'Mantenga estabilidad de red, servicios y acceso a plataforma mientras reduce tiempos de atención.',
                ],
                'highlights' => [
                    ['title' => 'Incidencias activas', 'body' => 'Seguimiento a tickets, prioridades y tiempos de resolución.'],
                    ['title' => 'Salud de infraestructura', 'body' => 'Visión rápida de disponibilidad, capacidad y mantenimiento.'],
                    ['title' => 'Continuidad institucional', 'body' => 'Soporte a laboratorios, aulas y equipos administrativos.'],
                ],
                'timeline' => [
                    ['time' => '07:00', 'title' => 'Chequeo de servidores', 'detail' => 'Monitoreo preventivo y revisión de respaldos.'],
                    ['time' => '10:15', 'title' => 'Atención ticket crítico', 'detail' => 'Incidencia de impresión en secretaría.'],
                    ['time' => '18:00', 'title' => 'Ventana de mantenimiento', 'detail' => 'Actualización de seguridad en portal interno.'],
                ],
                'actions' => [
                    ['label' => 'Gestionar tickets', 'description' => 'Priorice y asigne incidencias abiertas.'],
                    ['label' => 'Ver monitoreo', 'description' => 'Consulte servicios, red y capacidad.'],
                    ['label' => 'Programar mantenimiento', 'description' => 'Organice intervenciones preventivas.'],
                ],
                'alerts' => [
                    ['title' => 'Ticket crítico', 'detail' => 'Secretaría reporta indisponibilidad parcial de impresión.'],
                    ['title' => 'Mantenimiento nocturno', 'detail' => 'Hay una actualización programada para las 22:30.'],
                ],
            ],
            'coordinador-curso' => [
                'greeting' => 'Seguimiento de curso',
                'headline' => 'Coordine convivencia, desempeño y comunicación entre docentes y familias.',
                'kpis' => [
                    ['value' => '4', 'label' => 'Cursos bajo supervisión', 'trend' => '1 en seguimiento especial'],
                    ['value' => '11', 'label' => 'Alertas de convivencia', 'trend' => '3 nuevas'],
                    ['value' => '91%', 'label' => 'Asistencia promedio', 'trend' => '-2% semanal'],
                ],
                'focus' => [
                    'title' => 'Prioridad de coordinación',
                    'body' => 'Monitoree convivencia, puntualidad y alertas académicas para activar acompañamiento oportuno.',
                ],
                'highlights' => [
                    ['title' => 'Visión integral del curso', 'body' => 'Cruce de asistencia, disciplina y desempeño por paralelo.'],
                    ['title' => 'Articulación con familias', 'body' => 'Registro de novedades y reuniones de seguimiento.'],
                    ['title' => 'Intervención temprana', 'body' => 'Detección de casos que requieren tutoría o derivación.'],
                ],
                'timeline' => [
                    ['time' => '08:20', 'title' => 'Revisión de novedades', 'detail' => 'Consolidado de reportes de docentes del día anterior.'],
                    ['time' => '12:00', 'title' => 'Llamada a representantes', 'detail' => 'Seguimiento de inasistencias reiteradas.'],
                    ['time' => '15:45', 'title' => 'Cierre de tutorías', 'detail' => 'Actualización de acuerdos por curso.'],
                ],
                'actions' => [
                    ['label' => 'Ver alertas', 'description' => 'Revise disciplina, ausencias y desempeño.'],
                    ['label' => 'Programar tutorías', 'description' => 'Organice intervenciones con estudiantes y familias.'],
                    ['label' => 'Coordinar con docentes', 'description' => 'Consolide reportes y acciones conjuntas.'],
                ],
                'alerts' => [
                    ['title' => 'Caso prioritario', 'detail' => 'Un estudiante acumula tres reportes de convivencia esta semana.'],
                    ['title' => 'Seguimiento familiar', 'detail' => 'Dos representantes aún no confirman reunión de acompañamiento.'],
                ],
            ],
            'dece' => [
                'greeting' => 'Bienestar y acompañamiento',
                'headline' => 'Active el seguimiento socioemocional con sensibilidad, orden y trazabilidad.',
                'kpis' => [
                    ['value' => '19', 'label' => 'Casos activos', 'trend' => '5 de atención prioritaria'],
                    ['value' => '7', 'label' => 'Entrevistas del día', 'trend' => 'Agenda completa'],
                    ['value' => '3', 'label' => 'Derivaciones nuevas', 'trend' => '2 internas'],
                ],
                'focus' => [
                    'title' => 'Prioridad DECE',
                    'body' => 'Acompañe casos sensibles, documente intervenciones y coordine acciones de bienestar con familia y docentes.',
                ],
                'highlights' => [
                    ['title' => 'Seguimiento socioemocional', 'body' => 'Historial de observaciones, entrevistas y acuerdos.'],
                    ['title' => 'Red de apoyo', 'body' => 'Coordinación con docentes, familias y autoridades institucionales.'],
                    ['title' => 'Atención prioritaria', 'body' => 'Clasificación de casos por nivel de urgencia y plan de acción.'],
                ],
                'timeline' => [
                    ['time' => '08:30', 'title' => 'Entrevista individual', 'detail' => 'Acompañamiento a estudiante derivado por tutoría.'],
                    ['time' => '11:00', 'title' => 'Reunión interdisciplinaria', 'detail' => 'Articulación con coordinación de curso y familia.'],
                    ['time' => '16:10', 'title' => 'Registro de intervención', 'detail' => 'Actualización de acuerdos y seguimiento.'],
                ],
                'actions' => [
                    ['label' => 'Abrir casos', 'description' => 'Revise expedientes y nivel de prioridad.'],
                    ['label' => 'Registrar intervención', 'description' => 'Documente entrevistas, acuerdos y observaciones.'],
                    ['label' => 'Coordinar derivación', 'description' => 'Articule acciones con actores institucionales.'],
                ],
                'alerts' => [
                    ['title' => 'Atención sensible', 'detail' => 'Se registró una derivación con prioridad alta esta mañana.'],
                    ['title' => 'Seguimiento pendiente', 'detail' => 'Falta cerrar acuerdo familiar de un caso activo.'],
                ],
            ],
        ];
    }
}

if (! function_exists('integraEduModules')) {
    function integraEduModules(): array
    {
        return [
            'admin' => [
                'usuarios' => [
                    'title' => 'Gestión de usuarios',
                    'subtitle' => 'Controle cuentas institucionales, roles y estado de acceso desde un solo panel.',
                    'summary' => 'Módulo para administración de usuarios del sistema con foco en permisos, estado y trazabilidad.',
                    'stats' => [
                        ['value' => '1,284', 'label' => 'Cuentas activas'],
                        ['value' => '48', 'label' => 'Pendientes de revisión'],
                        ['value' => '12', 'label' => 'Nuevos esta semana'],
                    ],
                    'table' => [
                        'columns' => ['Usuario', 'Rol', 'Estado', 'Último acceso'],
                        'rows' => [
                            ['Rectorado General', 'Administrador', 'Activo', 'Hoy 07:12'],
                            ['Coordinación Académica', 'Administrador', 'Activo', 'Hoy 08:03'],
                            ['Secretaría 01', 'Administrativo', 'Observación', 'Ayer 16:48'],
                            ['Soporte Campus Norte', 'Soporte Técnico', 'Activo', 'Hoy 06:55'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Acciones sugeridas', 'body' => 'Revise usuarios en observación y complete la validación de perfiles nuevos.'],
                        ['title' => 'Control recomendado', 'body' => 'Mantenga actualizados los permisos de coordinación y soporte antes del cierre semanal.'],
                    ],
                ],
                'configuracion' => [
                    'title' => 'Configuración institucional',
                    'subtitle' => 'Ajuste parámetros académicos, periodos, permisos y componentes estratégicos.',
                    'summary' => 'Módulo central para mantener coherencia operativa entre calendarios, niveles y reglas del sistema.',
                    'stats' => [
                        ['value' => '4', 'label' => 'Períodos activos'],
                        ['value' => '27', 'label' => 'Parámetros editables'],
                        ['value' => '3', 'label' => 'Cambios por aprobar'],
                    ],
                    'table' => [
                        'columns' => ['Componente', 'Estado', 'Última edición', 'Responsable'],
                        'rows' => [
                            ['Calendario académico', 'Vigente', '24 Mar 2026', 'Rectorado'],
                            ['Escala de evaluación', 'En revisión', '23 Mar 2026', 'Consejo Académico'],
                            ['Permisos por rol', 'Vigente', '25 Mar 2026', 'Administrador principal'],
                            ['Plantillas de reportes', 'Actualizado', '22 Mar 2026', 'Coordinación TI'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Cambio pendiente', 'body' => 'La escala de evaluación del siguiente período requiere validación final.'],
                        ['title' => 'Recomendación', 'body' => 'Antes de publicar cambios, confirme impacto en reportes y dashboards.'],
                    ],
                ],
                'auditoria' => [
                    'title' => 'Auditoría y trazabilidad',
                    'subtitle' => 'Consulte eventos críticos, movimientos sensibles y accesos relevantes.',
                    'summary' => 'Supervisión de actividad institucional para seguridad, seguimiento y cumplimiento interno.',
                    'stats' => [
                        ['value' => '326', 'label' => 'Eventos hoy'],
                        ['value' => '5', 'label' => 'Alertas críticas'],
                        ['value' => '99.7%', 'label' => 'Integridad de registros'],
                    ],
                    'table' => [
                        'columns' => ['Evento', 'Usuario', 'Nivel', 'Hora'],
                        'rows' => [
                            ['Cambio de permisos', 'Administrador principal', 'Alto', '08:10'],
                            ['Exportación de reportes', 'Secretaría 01', 'Medio', '09:42'],
                            ['Reinicio de credenciales', 'Soporte Campus Norte', 'Medio', '10:16'],
                            ['Bloqueo preventivo', 'Sistema', 'Crítico', '10:33'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Alerta vigente', 'body' => 'Existe un bloqueo preventivo por múltiples intentos fallidos en un perfil administrativo.'],
                        ['title' => 'Acción inmediata', 'body' => 'Valide el origen del evento crítico y confirme si requiere seguimiento.'],
                    ],
                ],
                'estudiantes' => [
                    'title' => 'Administracion de alumnos',
                    'subtitle' => 'Consolide matricula, seguimiento y control operativo de estudiantes por institucion.',
                    'summary' => 'Espacio preparado para administrar estudiantes de la institucion activa desde un solo panel.',
                    'stats' => [
                        ['value' => '0', 'label' => 'Modulo en construccion'],
                        ['value' => '1', 'label' => 'Institucion activa'],
                        ['value' => '100%', 'label' => 'Preparado para integrar'],
                    ],
                    'table' => [
                        'columns' => ['Proceso', 'Estado', 'Cobertura', 'Observacion'],
                        'rows' => [
                            ['Listado institucional', 'Disponible', 'Institucion activa', 'Pendiente conectar CRUD'],
                            ['Ficha estudiantil', 'Planeado', 'Individual', 'Pendiente implementacion'],
                            ['Seguimiento academico', 'Planeado', 'Por curso', 'Pendiente integracion'],
                            ['Estado de matricula', 'Planeado', 'General', 'Pendiente implementacion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Siguiente paso', 'body' => 'Este acceso ya esta visible en el dashboard y puede conectarse al CRUD cuando lo indiquen.'],
                        ['title' => 'Alcance actual', 'body' => 'La navegacion ya respeta la institucion activa del administrador.'],
                    ],
                ],
                'cursos' => [
                    'title' => 'Administracion de cursos',
                    'subtitle' => 'Organice la estructura academica de cursos para la institucion activa.',
                    'summary' => 'Vista base del modulo de cursos lista para conectarse con el mantenimiento institucional.',
                    'stats' => [
                        ['value' => '0', 'label' => 'Modulo en construccion'],
                        ['value' => '1', 'label' => 'Institucion activa'],
                        ['value' => '100%', 'label' => 'Acceso disponible'],
                    ],
                    'table' => [
                        'columns' => ['Componente', 'Estado', 'Cobertura', 'Observacion'],
                        'rows' => [
                            ['Catalogo de cursos', 'Planeado', 'Institucion activa', 'Pendiente implementacion'],
                            ['Carga horaria', 'Planeado', 'Por nivel', 'Pendiente integracion'],
                            ['Relacion con paralelos', 'Planeado', 'Academico', 'Pendiente implementacion'],
                            ['Control de vigencia', 'Planeado', 'Institucional', 'Pendiente definicion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Preparado', 'body' => 'El dashboard ya presenta este acceso en el orden solicitado para el perfil administrador.'],
                        ['title' => 'Continuidad', 'body' => 'Podemos conectar este modulo a datos reales cuando lo necesites.'],
                    ],
                ],
                'paralelos' => [
                    'title' => 'Administracion de paralelos',
                    'subtitle' => 'Defina y supervise paralelos academicos de la institucion activa.',
                    'summary' => 'Vista base para la futura gestion de paralelos dentro de la estructura academica institucional.',
                    'stats' => [
                        ['value' => '0', 'label' => 'Modulo en construccion'],
                        ['value' => '1', 'label' => 'Institucion activa'],
                        ['value' => '100%', 'label' => 'Acceso disponible'],
                    ],
                    'table' => [
                        'columns' => ['Proceso', 'Estado', 'Cobertura', 'Observacion'],
                        'rows' => [
                            ['Listado de paralelos', 'Planeado', 'Institucion activa', 'Pendiente implementacion'],
                            ['Capacidad por paralelo', 'Planeado', 'Por curso', 'Pendiente integracion'],
                            ['Asignacion de tutor', 'Planeado', 'Academico', 'Pendiente implementacion'],
                            ['Estado operativo', 'Planeado', 'Institucional', 'Pendiente definicion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Visibilidad', 'body' => 'El acceso ya forma parte del dashboard institucional del administrador.'],
                        ['title' => 'Proximo desarrollo', 'body' => 'Se puede extender con CRUD filtrado por institucion activa.'],
                    ],
                ],
                'especialidades' => [
                    'title' => 'Administracion de especialidades',
                    'subtitle' => 'Administre la oferta de especialidades academicas de la institucion activa.',
                    'summary' => 'Vista base para la gestion de especialidades con enfoque institucional.',
                    'stats' => [
                        ['value' => '0', 'label' => 'Modulo en construccion'],
                        ['value' => '1', 'label' => 'Institucion activa'],
                        ['value' => '100%', 'label' => 'Acceso disponible'],
                    ],
                    'table' => [
                        'columns' => ['Componente', 'Estado', 'Cobertura', 'Observacion'],
                        'rows' => [
                            ['Catalogo de especialidades', 'Planeado', 'Institucion activa', 'Pendiente implementacion'],
                            ['Relacion por nivel', 'Planeado', 'Academico', 'Pendiente integracion'],
                            ['Estado de vigencia', 'Planeado', 'Institucional', 'Pendiente implementacion'],
                            ['Parametros base', 'Planeado', 'General', 'Pendiente definicion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Acceso listo', 'body' => 'El administrador ya dispone de esta opcion directamente en su dashboard.'],
                        ['title' => 'Evolucion sugerida', 'body' => 'Este modulo puede enlazarse despues con cursos, paralelos y asignaturas.'],
                    ],
                ],
                'asignaturas' => [
                    'title' => 'Administracion de asignaturas',
                    'subtitle' => 'Centralice la gestion de asignaturas de la institucion activa.',
                    'summary' => 'Vista base del modulo de asignaturas preparada para integrarse con la estructura academica.',
                    'stats' => [
                        ['value' => '0', 'label' => 'Modulo en construccion'],
                        ['value' => '1', 'label' => 'Institucion activa'],
                        ['value' => '100%', 'label' => 'Acceso disponible'],
                    ],
                    'table' => [
                        'columns' => ['Proceso', 'Estado', 'Cobertura', 'Observacion'],
                        'rows' => [
                            ['Catalogo institucional', 'Planeado', 'Institucion activa', 'Pendiente implementacion'],
                            ['Asignacion por curso', 'Planeado', 'Academico', 'Pendiente integracion'],
                            ['Relacion con docentes', 'Planeado', 'Operativo', 'Pendiente implementacion'],
                            ['Estado de vigencia', 'Planeado', 'Institucional', 'Pendiente definicion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Orden del dashboard', 'body' => 'La opcion ya aparece dentro del conjunto solicitado para el perfil administrador.'],
                        ['title' => 'Continuidad funcional', 'body' => 'Cuando quieras, seguimos con el CRUD filtrado por institucion activa.'],
                    ],
                ],
            ],
            'inspector' => [
                'novedades' => [
                    'title' => 'Registro de novedades',
                    'subtitle' => 'Documente observaciones disciplinarias, incidencias y acciones inmediatas de la jornada.',
                    'summary' => 'Modulo base para consolidar novedades de convivencia con prioridad, responsable y trazabilidad.',
                    'stats' => [
                        ['value' => '18', 'label' => 'Novedades registradas'],
                        ['value' => '5', 'label' => 'Pendientes de cierre'],
                        ['value' => '2', 'label' => 'Casos criticos hoy'],
                    ],
                    'table' => [
                        'columns' => ['Caso', 'Curso', 'Nivel', 'Ultima accion'],
                        'rows' => [
                            ['Ingreso tardio recurrente', '9no B', 'Medio', 'Llamada al representante'],
                            ['Incidente en recreo', '8vo C', 'Alto', 'Acta en validacion'],
                            ['Conflicto verbal en aula', '10mo A', 'Medio', 'Seguimiento con tutoria'],
                            ['Falta injustificada', '1ro BGU', 'Bajo', 'Registro preventivo'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Atencion inmediata', 'body' => 'El incidente de 8vo C necesita validacion y cierre con coordinacion antes del fin de la jornada.'],
                        ['title' => 'Buena practica', 'body' => 'Registrar responsables y acuerdos en el momento reduce vacios de seguimiento posterior.'],
                    ],
                ],
                'seguimiento' => [
                    'title' => 'Seguimiento de casos',
                    'subtitle' => 'Mantenga continuidad entre observaciones, acuerdos, familias y areas institucionales.',
                    'summary' => 'Modulo base para supervisar casos activos, compromisos pendientes y coordinacion de acciones formativas.',
                    'stats' => [
                        ['value' => '7', 'label' => 'Casos activos'],
                        ['value' => '3', 'label' => 'Reuniones por confirmar'],
                        ['value' => '81%', 'label' => 'Acuerdos cumplidos'],
                    ],
                    'table' => [
                        'columns' => ['Estudiante', 'Tipo de caso', 'Proxima accion', 'Estado'],
                        'rows' => [
                            ['A. Cedeño', 'Convivencia', 'Reunion con familia 16:30', 'Prioritario'],
                            ['M. Paredes', 'Asistencia', 'Verificar reincidencia mañana', 'En seguimiento'],
                            ['J. Mera', 'Conductual', 'Acta de compromiso', 'Pendiente'],
                            ['C. Velez', 'Puntualidad', 'Cierre con tutoria', 'Estable'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Coordinacion activa', 'body' => 'Dos casos requieren articulacion inmediata con familia y tutoria durante el mismo turno.'],
                        ['title' => 'Recomendacion', 'body' => 'Cerrar acuerdos con fecha y responsable visible ayuda a sostener continuidad entre inspeccion y coordinacion.'],
                    ],
                ],
                'asistencia-docentes' => [
                    'title' => 'Asistencia de docentes',
                    'subtitle' => 'Supervise presencia, reemplazos y novedades del equipo docente institucional.',
                    'summary' => 'Panel para revisar cobertura docente y alertas que afecten la jornada academica.',
                    'stats' => [
                        ['value' => '24', 'label' => 'Docentes convocados'],
                        ['value' => '22', 'label' => 'Presentes'],
                        ['value' => '2', 'label' => 'Coberturas activas'],
                    ],
                    'table' => [
                        'columns' => ['Docente', 'Bloque', 'Estado', 'Observacion'],
                        'rows' => [
                            ['L. Andrade', '08:00 - 09:30', 'Presente', 'Sin novedades'],
                            ['R. Suarez', '09:30 - 11:00', 'Reemplazo', 'Cobertura asignada a P. Leon'],
                            ['P. Leon', '11:00 - 12:30', 'Presente', 'Sin novedades'],
                            ['M. Zambrano', '13:15 - 14:45', 'Atraso', 'Ingreso registrado 13:25'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Cobertura pendiente', 'body' => 'Confirme la cobertura del bloque de R. Suarez antes del inicio de la jornada de la tarde.'],
                        ['title' => 'Seguimiento', 'body' => 'Los atrasos reiterados deben reportarse a coordinacion para su tratamiento formal.'],
                    ],
                ],
                'asistencia-estudiantes' => [
                    'title' => 'Asistencia de estudiantes',
                    'subtitle' => 'Consolide presencia, atrasos y ausencias por paralelo con enfoque institucional.',
                    'summary' => 'Vista operativa para validar asistencia estudiantil y detectar novedades recurrentes.',
                    'stats' => [
                        ['value' => '184', 'label' => 'Estudiantes monitoreados'],
                        ['value' => '93%', 'label' => 'Asistencia del dia'],
                        ['value' => '5', 'label' => 'Casos por revisar'],
                    ],
                    'table' => [
                        'columns' => ['Paralelo', 'Jornada', 'Presentes', 'Novedad'],
                        'rows' => [
                            ['10mo A', 'Matutina', '31/33', '2 atrasos'],
                            ['9no B', 'Matutina', '28/30', '1 falta justificada'],
                            ['8vo C', 'Matutina', '34/34', 'Sin novedades'],
                            ['1ro BGU', 'Vespertina', '29/31', '2 faltas por confirmar'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Lectura sugerida', 'body' => 'Cruzar atrasos con reportes de convivencia ayuda a priorizar mejor el seguimiento.'],
                        ['title' => 'Accion siguiente', 'body' => 'Valide hoy las faltas pendientes antes del cierre para mantener consistencia en reportes.'],
                    ],
                ],
            ],
            'docente' => [
                'asistencia' => [
                    'title' => 'Registro de asistencia',
                    'subtitle' => 'Controle presencia, atrasos y novedades por curso con rapidez.',
                    'summary' => 'Panel diario para consolidar asistencia y observaciones del aula.',
                    'stats' => [
                        ['value' => '184', 'label' => 'Estudiantes a cargo'],
                        ['value' => '93%', 'label' => 'Asistencia promedio'],
                        ['value' => '7', 'label' => 'Novedades del día'],
                    ],
                    'table' => [
                        'columns' => ['Curso', 'Hora', 'Presentes', 'Novedades'],
                        'rows' => [
                            ['10mo A', '08:00', '31/33', '2 atrasos'],
                            ['9no B', '09:20', '28/30', '1 falta justificada'],
                            ['8vo C', '11:00', '34/34', 'Sin novedades'],
                            ['1ro BGU', '13:15', '29/31', '2 faltas por confirmar'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Atención prioritaria', 'body' => '1ro BGU presenta dos faltas por confirmar y requiere seguimiento.'],
                        ['title' => 'Siguiente paso', 'body' => 'Cierre la asistencia antes del fin de jornada para liberar reportes.'],
                    ],
                ],
                'asistencia-estudiantes' => [
                    'title' => 'Asistencia de estudiantes',
                    'subtitle' => 'Consolide presencia, atrasos y ausencias por paralelo con enfoque diario.',
                    'summary' => 'Vista operativa para validar asistencia estudiantil y detectar novedades recurrentes.',
                    'stats' => [
                        ['value' => '184', 'label' => 'Estudiantes monitoreados'],
                        ['value' => '93%', 'label' => 'Asistencia del dia'],
                        ['value' => '5', 'label' => 'Casos por revisar'],
                    ],
                    'table' => [
                        'columns' => ['Paralelo', 'Jornada', 'Presentes', 'Novedad'],
                        'rows' => [
                            ['10mo A', 'Matutina', '31/33', '2 atrasos'],
                            ['9no B', 'Matutina', '28/30', '1 falta justificada'],
                            ['8vo C', 'Matutina', '34/34', 'Sin novedades'],
                            ['1ro BGU', 'Vespertina', '29/31', '2 faltas por confirmar'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Lectura sugerida', 'body' => 'Cruzar atrasos con rendimiento ayuda a priorizar mejor el seguimiento tutorial.'],
                        ['title' => 'Accion siguiente', 'body' => 'Valide hoy las faltas pendientes antes del cierre para mantener consistencia en reportes.'],
                    ],
                ],
                'asistencia-docentes' => [
                    'title' => 'Asistencia de docentes',
                    'subtitle' => 'Supervise presencia, reemplazos y novedades del equipo docente.',
                    'summary' => 'Panel para revisar cobertura docente y alertas que afecten la jornada academica.',
                    'stats' => [
                        ['value' => '24', 'label' => 'Docentes convocados'],
                        ['value' => '22', 'label' => 'Presentes'],
                        ['value' => '2', 'label' => 'Coberturas activas'],
                    ],
                    'table' => [
                        'columns' => ['Docente', 'Bloque', 'Estado', 'Observacion'],
                        'rows' => [
                            ['Ana Morales', '1 y 2', 'Presente', 'Sin novedades'],
                            ['Luis Velez', '3 y 4', 'Retraso', 'Ingreso 15 min tarde'],
                            ['Carmen Ruiz', '5 y 6', 'Permiso', 'Cobertura activada'],
                            ['Joel Vega', '7 y 8', 'Presente', 'Apoyo en recuperacion'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Cobertura del dia', 'body' => 'Hay una ausencia con reemplazo activo y conviene verificar continuidad del bloque final.'],
                        ['title' => 'Recomendacion', 'body' => 'Mantener observaciones breves por bloque facilita justificar ajustes ante coordinacion.'],
                    ],
                ],
                'asistencia-administrativos' => [
                    'title' => 'Asistencia de administrativos',
                    'subtitle' => 'Controle ingreso, permanencia y novedades del personal administrativo.',
                    'summary' => 'Espacio de seguimiento para validar asistencia del equipo de soporte interno institucional.',
                    'stats' => [
                        ['value' => '11', 'label' => 'Administrativos activos'],
                        ['value' => '10', 'label' => 'Presentes'],
                        ['value' => '1', 'label' => 'Novedad abierta'],
                    ],
                    'table' => [
                        'columns' => ['Area', 'Responsable', 'Ingreso', 'Estado'],
                        'rows' => [
                            ['Secretaria', 'Martha Solis', '07:35', 'Presente'],
                            ['Colecturia', 'Diego Paz', '07:42', 'Presente'],
                            ['Recepcion', 'Paola Mena', '08:05', 'Retraso'],
                            ['Archivo', 'Rosa Leon', '07:30', 'Presente'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Punto de control', 'body' => 'Recepcion registra retraso y conviene confirmar cobertura del primer tramo de atencion.'],
                        ['title' => 'Cierre recomendado', 'body' => 'Deje observaciones cortas por area para facilitar consolidacion administrativa.'],
                    ],
                ],
                'asistencia-personal-servicio' => [
                    'title' => 'Asistencia del personal de servicio',
                    'subtitle' => 'Monitoree presencia y continuidad operativa del personal de servicio.',
                    'summary' => 'Panel rapido para revisar cobertura de limpieza, apoyo logistico y mantenimiento.',
                    'stats' => [
                        ['value' => '9', 'label' => 'Personal asignado'],
                        ['value' => '8', 'label' => 'Presentes'],
                        ['value' => '1', 'label' => 'Turno por cubrir'],
                    ],
                    'table' => [
                        'columns' => ['Responsable', 'Area', 'Turno', 'Estado'],
                        'rows' => [
                            ['Julia Cedeno', 'Patio central', 'Matutino', 'Presente'],
                            ['Carlos Naranjo', 'Mantenimiento', 'Completo', 'Presente'],
                            ['Miriam Toapanta', 'Aulas bloque B', 'Matutino', 'Permiso'],
                            ['Ruth Zambrano', 'Banos y pasillos', 'Vespertino', 'Confirmado'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Cobertura sensible', 'body' => 'El permiso en bloque B necesita redistribucion para sostener limpieza del turno de la manana.'],
                        ['title' => 'Siguiente paso', 'body' => 'Confirme reemplazo del area pendiente antes del cambio de jornada.'],
                    ],
                ],
                'calificaciones' => [
                    'title' => 'Calificaciones y retroalimentación',
                    'subtitle' => 'Evalúe actividades y mantenga al día la retroalimentación académica.',
                    'summary' => 'Módulo para revisión de tareas, rúbricas y publicación de notas parciales.',
                    'stats' => [
                        ['value' => '18', 'label' => 'Entregas pendientes'],
                        ['value' => '6', 'label' => 'Cursos evaluados'],
                        ['value' => '8.7', 'label' => 'Promedio del bloque'],
                    ],
                    'table' => [
                        'columns' => ['Actividad', 'Curso', 'Estado', 'Fecha límite'],
                        'rows' => [
                            ['Informe de laboratorio', '9no B', 'Por revisar', 'Hoy 18:00'],
                            ['Ensayo argumentativo', '10mo A', 'Retroalimentando', 'Mañana'],
                            ['Quiz de Matemática', '1ro BGU', 'Publicado', '24 Mar'],
                            ['Mapa conceptual', '8vo C', 'Por revisar', 'Hoy 20:00'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Carga del día', 'body' => 'Hay dos actividades que conviene calificar antes de las 20:00.'],
                        ['title' => 'Recomendación pedagógica', 'body' => 'Priorice retroalimentación breve en tareas con mayor impacto en promedio.'],
                    ],
                ],
                'recursos' => [
                    'title' => 'Recursos y publicaciones',
                    'subtitle' => 'Comparta materiales, tareas y contenidos por asignatura.',
                    'summary' => 'Panel para organizar recursos de aula y publicaciones semanales.',
                    'stats' => [
                        ['value' => '24', 'label' => 'Recursos publicados'],
                        ['value' => '5', 'label' => 'Nuevas tareas'],
                        ['value' => '92%', 'label' => 'Acceso del alumnado'],
                    ],
                    'table' => [
                        'columns' => ['Recurso', 'Curso', 'Tipo', 'Estado'],
                        'rows' => [
                            ['Guía de estudio parcial 2', '10mo A', 'PDF', 'Publicado'],
                            ['Video de refuerzo', '9no B', 'Enlace', 'Programado'],
                            ['Taller de ecuaciones', '1ro BGU', 'Tarea', 'Publicado'],
                            ['Lectura crítica', '8vo C', 'Documento', 'Borrador'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Mejora sugerida', 'body' => 'Hay un recurso en borrador listo para publicarse en 8vo C.'],
                        ['title' => 'Seguimiento', 'body' => 'El video de refuerzo fue programado para activarse mañana a primera hora.'],
                    ],
                ],
            ],
            'estudiante' => [
                'tareas' => [
                    'title' => 'Tareas y entregas',
                    'subtitle' => 'Organice prioridades y cumpla fechas clave de forma ordenada.',
                    'summary' => 'Vista personal de actividades pendientes, próximas y completadas.',
                    'stats' => [
                        ['value' => '4', 'label' => 'Pendientes'],
                        ['value' => '2', 'label' => 'Vencen hoy'],
                        ['value' => '91%', 'label' => 'Entregas a tiempo'],
                    ],
                    'table' => [
                        'columns' => ['Actividad', 'Materia', 'Entrega', 'Estado'],
                        'rows' => [
                            ['Proyecto final', 'Historia', 'Hoy 19:00', 'Pendiente'],
                            ['Ejercicios de funciones', 'Matemática', 'Mañana 08:00', 'En progreso'],
                            ['Lectura comentada', 'Lengua', '26 Mar', 'Pendiente'],
                            ['Práctica de laboratorio', 'Ciencias', '24 Mar', 'Entregada'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Prioridad personal', 'body' => 'Concentre esfuerzo en Historia y Matemática antes del final del día.'],
                        ['title' => 'Consejo', 'body' => 'Suba la evidencia del proyecto final con anticipación para evitar contratiempos.'],
                    ],
                ],
                'clases' => [
                    'title' => 'Clases y horario',
                    'subtitle' => 'Revise el calendario del día y entre a sus recursos activos.',
                    'summary' => 'Organización del horario diario con acceso rápido a sesiones y materiales.',
                    'stats' => [
                        ['value' => '6', 'label' => 'Clases hoy'],
                        ['value' => '1', 'label' => 'Laboratorios'],
                        ['value' => '100%', 'label' => 'Material cargado'],
                    ],
                    'table' => [
                        'columns' => ['Hora', 'Asignatura', 'Espacio', 'Estado'],
                        'rows' => [
                            ['07:10', 'Lengua y Literatura', 'Aula 2', 'Completada'],
                            ['08:40', 'Matemática', 'Aula 2', 'Completada'],
                            ['10:40', 'Ciencias', 'Laboratorio', 'En curso'],
                            ['13:20', 'Historia', 'Aula 5', 'Próxima'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Próxima clase', 'body' => 'Historia inicia a las 13:20 en Aula 5 con exposición grupal.'],
                        ['title' => 'Preparación', 'body' => 'Revise el material de Ciencias antes del laboratorio final del bloque.'],
                    ],
                ],
                'progreso' => [
                    'title' => 'Progreso académico',
                    'subtitle' => 'Analice su rendimiento por materia y detecte oportunidades de mejora.',
                    'summary' => 'Seguimiento personal de notas, asistencia y evolución académica.',
                    'stats' => [
                        ['value' => '8.94', 'label' => 'Promedio general'],
                        ['value' => '96%', 'label' => 'Asistencia'],
                        ['value' => '3', 'label' => 'Materias destacadas'],
                    ],
                    'table' => [
                        'columns' => ['Asignatura', 'Promedio', 'Asistencia', 'Tendencia'],
                        'rows' => [
                            ['Matemática', '8.6', '95%', 'Al alza'],
                            ['Lengua', '9.1', '98%', 'Estable'],
                            ['Ciencias', '8.9', '97%', 'Al alza'],
                            ['Historia', '9.2', '94%', 'Excelente'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Fortaleza actual', 'body' => 'Historia y Lengua sostienen el promedio más alto del período.'],
                        ['title' => 'Oportunidad', 'body' => 'Un pequeño refuerzo en Matemática puede elevar el promedio global.'],
                    ],
                ],
            ],
            'padre-tutor' => [
                'progreso' => [
                    'title' => 'Progreso del representado',
                    'subtitle' => 'Siga el rendimiento, la asistencia y las observaciones más relevantes.',
                    'summary' => 'Vista consolidada para familias con foco en desempeño, hábitos y señales de seguimiento.',
                    'stats' => [
                        ['value' => '8.71', 'label' => 'Promedio actual'],
                        ['value' => '96%', 'label' => 'Asistencia acumulada'],
                        ['value' => '2', 'label' => 'Materias por reforzar'],
                    ],
                    'table' => [
                        'columns' => ['Área', 'Promedio', 'Asistencia', 'Observación'],
                        'rows' => [
                            ['Matemática', '8.2', '94%', 'Refuerzo sugerido'],
                            ['Lengua', '8.9', '98%', 'Buen avance'],
                            ['Ciencias', '8.6', '97%', 'Seguimiento regular'],
                            ['Historia', '9.1', '95%', 'Desempeño sólido'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Lectura recomendada', 'body' => 'Matemática necesita acompañamiento breve esta semana para sostener el promedio general.'],
                        ['title' => 'Señal positiva', 'body' => 'La asistencia se mantiene dentro del objetivo institucional esperado.'],
                    ],
                ],
                'avisos' => [
                    'title' => 'Avisos y comunicaciones',
                    'subtitle' => 'Revise mensajes recientes de docentes, tutoría y coordinación.',
                    'summary' => 'Canal de información para mantener a la familia al tanto de novedades académicas e institucionales.',
                    'stats' => [
                        ['value' => '3', 'label' => 'Avisos nuevos'],
                        ['value' => '2', 'label' => 'Mensajes docentes'],
                        ['value' => '1', 'label' => 'Circular institucional'],
                    ],
                    'table' => [
                        'columns' => ['Origen', 'Asunto', 'Fecha', 'Estado'],
                        'rows' => [
                            ['Tutoría', 'Seguimiento de Matemática', 'Hoy 09:10', 'Nuevo'],
                            ['Docente de Ciencias', 'Actividad de refuerzo', 'Hoy 11:25', 'Leído'],
                            ['Institución', 'Cronograma evaluativo', 'Ayer 18:00', 'Nuevo'],
                            ['Coordinación', 'Recordatorio de reunión', 'Ayer 16:40', 'Leído'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Atención prioritaria', 'body' => 'Existe un aviso nuevo sobre el próximo corte evaluativo que conviene revisar hoy.'],
                        ['title' => 'Sugerencia', 'body' => 'Responder la comunicación de tutoría ayudará a coordinar mejor el acompañamiento.'],
                    ],
                ],
                'reuniones' => [
                    'title' => 'Reuniones y seguimiento',
                    'subtitle' => 'Coordine espacios de conversación con docentes y áreas de apoyo.',
                    'summary' => 'Panel para consultar, solicitar y dar seguimiento a reuniones de acompañamiento familiar.',
                    'stats' => [
                        ['value' => '2', 'label' => 'Reuniones próximas'],
                        ['value' => '1', 'label' => 'Solicitud pendiente'],
                        ['value' => '100%', 'label' => 'Confirmaciones al día'],
                    ],
                    'table' => [
                        'columns' => ['Tipo', 'Responsable', 'Fecha', 'Estado'],
                        'rows' => [
                            ['Tutoría académica', 'Docente de Matemática', 'Hoy 17:30', 'Confirmada'],
                            ['Seguimiento familiar', 'Coordinación de curso', 'Mañana 08:00', 'Programada'],
                            ['Orientación', 'DECE', '28 Mar 2026', 'Solicitada'],
                            ['Revisión de avance', 'Representante y tutor', '22 Mar 2026', 'Completada'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Próximo encuentro', 'body' => 'La tutoría de Matemática está lista para revisar estrategias de apoyo en casa.'],
                        ['title' => 'Gestión sugerida', 'body' => 'Conviene cerrar la solicitud con DECE antes de finalizar la semana.'],
                    ],
                ],
            ],
            'administrativo' => [
                'matriculas' => [
                    'title' => 'Matrículas y admisiones',
                    'subtitle' => 'Controle estados, documentos y observaciones de ingreso institucional.',
                    'summary' => 'Vista operativa para revisar procesos de inscripción, cupos y validación documental.',
                    'stats' => [
                        ['value' => '32', 'label' => 'Matrículas por confirmar'],
                        ['value' => '14', 'label' => 'Casos prioritarios'],
                        ['value' => '87%', 'label' => 'Documentación completa'],
                    ],
                    'table' => [
                        'columns' => ['Estudiante', 'Nivel', 'Estado', 'Observación'],
                        'rows' => [
                            ['Camila Torres', '8vo EGB', 'En revisión', 'Falta firma del representante'],
                            ['Mateo Ruiz', '1ro BGU', 'Aprobada', 'Documentos completos'],
                            ['Valentina Mora', 'Inicial 2', 'Pendiente', 'Pago por validar'],
                            ['Joaquín Pérez', '10mo EGB', 'Observación', 'Certificado médico pendiente'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Punto crítico', 'body' => 'Hay dos procesos detenidos por documentos sin firma que afectan el cierre del día.'],
                        ['title' => 'Siguiente movimiento', 'body' => 'Priorice los casos observados antes del corte de las 16:00.'],
                    ],
                ],
                'certificados' => [
                    'title' => 'Certificados y constancias',
                    'subtitle' => 'Emita documentos oficiales con trazabilidad y control de entrega.',
                    'summary' => 'Módulo para gestionar solicitudes de certificados académicos y administrativos.',
                    'stats' => [
                        ['value' => '12', 'label' => 'Solicitudes en cola'],
                        ['value' => '5', 'label' => 'Entregas hoy'],
                        ['value' => '98%', 'label' => 'Tiempo de cumplimiento'],
                    ],
                    'table' => [
                        'columns' => ['Documento', 'Solicitante', 'Entrega', 'Estado'],
                        'rows' => [
                            ['Certificado de matrícula', 'Familia Andrade', 'Hoy 12:30', 'Listo'],
                            ['Constancia de notas', 'Est. Daniela León', 'Hoy 15:00', 'En proceso'],
                            ['Historial académico', 'Secretaría general', 'Mañana', 'Validando'],
                            ['Certificado de conducta', 'Familia Vela', 'Hoy 17:00', 'Pendiente'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Flujo activo', 'body' => 'La constancia de notas requiere validación final antes de imprimirse.'],
                        ['title' => 'Control interno', 'body' => 'Verifique firmas y sellos en documentos que salen hoy.'],
                    ],
                ],
                'expedientes' => [
                    'title' => 'Expedientes institucionales',
                    'subtitle' => 'Mantenga actualizado el archivo documental por estudiante.',
                    'summary' => 'Panel de control para revisar integridad, observaciones y actualización de expedientes.',
                    'stats' => [
                        ['value' => '99%', 'label' => 'Expedientes completos'],
                        ['value' => '2', 'label' => 'Casos observados'],
                        ['value' => '18', 'label' => 'Actualizaciones hoy'],
                    ],
                    'table' => [
                        'columns' => ['Expediente', 'Responsable', 'Última actualización', 'Estado'],
                        'rows' => [
                            ['8vo A - Valeria C.', 'Secretaría 01', 'Hoy 08:40', 'Completo'],
                            ['9no B - Sebastián T.', 'Secretaría 02', 'Hoy 10:15', 'Observación'],
                            ['1ro BGU - Paula N.', 'Archivo central', 'Ayer 16:30', 'Completo'],
                            ['Inicial 2 - Thiago R.', 'Admisiones', 'Hoy 11:05', 'Actualizando'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Caso abierto', 'body' => 'Un expediente aún necesita firma de autorización para quedar cerrado.'],
                        ['title' => 'Buena práctica', 'body' => 'Mantener el archivo del día al cierre evita reprocesos y demoras de atención.'],
                    ],
                ],
            ],
            'asesor-academico' => [
                'informes' => [
                    'title' => 'Informes académicos',
                    'subtitle' => 'Genere análisis por curso, área y período con foco en decisiones pedagógicas.',
                    'summary' => 'Espacio para revisar reportes de desempeño y consolidar recomendaciones técnicas.',
                    'stats' => [
                        ['value' => '12', 'label' => 'Informes por emitir'],
                        ['value' => '6', 'label' => 'Cursos priorizados'],
                        ['value' => '84%', 'label' => 'Cobertura semanal'],
                    ],
                    'table' => [
                        'columns' => ['Informe', 'Curso', 'Corte', 'Estado'],
                        'rows' => [
                            ['Rendimiento por áreas', '9no B', 'Parcial 2', 'En elaboración'],
                            ['Comparativa institucional', '1ro BGU', 'Mensual', 'Listo'],
                            ['Alertas por asistencia', '8vo A', 'Semanal', 'En revisión'],
                            ['Progreso por competencias', '10mo C', 'Parcial 2', 'Pendiente'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Entrega cercana', 'body' => 'El comparativo institucional ya puede compartirse en la mesa técnica de hoy.'],
                        ['title' => 'Pendiente clave', 'body' => '9no B necesita cerrar su informe para respaldar decisiones de refuerzo.'],
                    ],
                ],
                'alertas' => [
                    'title' => 'Alertas de desempeño',
                    'subtitle' => 'Priorice casos académicos según riesgo y tendencia de aprendizaje.',
                    'summary' => 'Monitoreo focalizado para intervenir con oportunidad en cursos y estudiantes críticos.',
                    'stats' => [
                        ['value' => '27', 'label' => 'Casos en seguimiento'],
                        ['value' => '6', 'label' => 'Riesgo alto'],
                        ['value' => '3', 'label' => 'Acciones hoy'],
                    ],
                    'table' => [
                        'columns' => ['Caso', 'Indicador', 'Nivel', 'Acción sugerida'],
                        'rows' => [
                            ['9no B - Matemática', 'Descenso sostenido', 'Alto', 'Refuerzo inmediato'],
                            ['8vo C - Lengua', 'Entrega irregular', 'Medio', 'Monitoreo docente'],
                            ['1ro BGU - Ciencias', 'Asistencia variable', 'Medio', 'Seguimiento familiar'],
                            ['10mo A - Inglés', 'Baja participación', 'Bajo', 'Observación'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Caso prioritario', 'body' => '9no B concentra la señal más alta y debería abrir intervención antes del próximo corte.'],
                        ['title' => 'Siguiente análisis', 'body' => 'Cruzar estas alertas con asistencia puede afinar mejor la decisión pedagógica.'],
                    ],
                ],
                'intervencion' => [
                    'title' => 'Coordinación de intervención',
                    'subtitle' => 'Organice acciones con docentes, coordinación y familias.',
                    'summary' => 'Seguimiento de planes de apoyo académico con responsables, fechas y acuerdos.',
                    'stats' => [
                        ['value' => '9', 'label' => 'Planes activos'],
                        ['value' => '4', 'label' => 'Reuniones esta semana'],
                        ['value' => '78%', 'label' => 'Acciones cumplidas'],
                    ],
                    'table' => [
                        'columns' => ['Plan', 'Responsable', 'Próxima acción', 'Estado'],
                        'rows' => [
                            ['Refuerzo numérico 9no B', 'Docente + Asesoría', 'Hoy 16:00', 'Activo'],
                            ['Acompañamiento lector 8vo C', 'Lengua', 'Mañana 09:00', 'Programado'],
                            ['Seguimiento ciencias 1ro BGU', 'Coordinación', '28 Mar 2026', 'En curso'],
                            ['Tutoría de hábitos 10mo A', 'Tutoría', '22 Mar 2026', 'Completado'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Coordinación activa', 'body' => 'El plan de 9no B ya tiene responsables definidos y necesita validación de avances.'],
                        ['title' => 'Recomendación', 'body' => 'Registrar acuerdos de cada reunión ayuda a sostener continuidad entre áreas.'],
                    ],
                ],
            ],
            'soporte-tecnico' => [
                'tickets' => [
                    'title' => 'Mesa de tickets',
                    'subtitle' => 'Administre incidencias, prioridad y tiempos de respuesta del soporte institucional.',
                    'summary' => 'Panel central para clasificar y atender solicitudes técnicas de usuarios y áreas.',
                    'stats' => [
                        ['value' => '14', 'label' => 'Tickets abiertos'],
                        ['value' => '4', 'label' => 'Críticos'],
                        ['value' => '6 min', 'label' => 'Respuesta media'],
                    ],
                    'table' => [
                        'columns' => ['Ticket', 'Área', 'Prioridad', 'Estado'],
                        'rows' => [
                            ['INC-241', 'Secretaría', 'Crítica', 'En atención'],
                            ['INC-238', 'Laboratorio', 'Alta', 'Asignada'],
                            ['INC-236', 'Docencia', 'Media', 'Pendiente'],
                            ['INC-232', 'Rectorado', 'Baja', 'Resuelta'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Urgencia actual', 'body' => 'La incidencia de impresión en Secretaría sigue siendo la atención crítica del turno.'],
                        ['title' => 'Recomendación operativa', 'body' => 'Cerrar tickets resueltos ayuda a reflejar mejor la carga real del equipo.'],
                    ],
                ],
                'monitoreo' => [
                    'title' => 'Monitoreo de servicios',
                    'subtitle' => 'Supervise disponibilidad, red y estado general de la plataforma.',
                    'summary' => 'Vista rápida del estado técnico de servicios clave para prevenir interrupciones.',
                    'stats' => [
                        ['value' => '99.2%', 'label' => 'Uptime'],
                        ['value' => '7', 'label' => 'Servicios vigilados'],
                        ['value' => '2', 'label' => 'Alertas menores'],
                    ],
                    'table' => [
                        'columns' => ['Servicio', 'Estado', 'Último chequeo', 'Observación'],
                        'rows' => [
                            ['Portal web', 'Estable', 'Hoy 11:20', 'Sin incidentes'],
                            ['Base de datos', 'Estable', 'Hoy 11:20', 'Carga normal'],
                            ['Impresión administrativa', 'Observación', 'Hoy 11:18', 'Cola lenta'],
                            ['Red laboratorio', 'Estable', 'Hoy 11:15', 'Latencia dentro del rango'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Señal técnica', 'body' => 'La impresión administrativa muestra degradación parcial y requiere seguimiento corto.'],
                        ['title' => 'Buena noticia', 'body' => 'Los servicios núcleo permanecen estables durante la jornada.'],
                    ],
                ],
                'mantenimiento' => [
                    'title' => 'Mantenimiento programado',
                    'subtitle' => 'Organice intervenciones preventivas sin afectar la continuidad institucional.',
                    'summary' => 'Calendario operativo para actualizaciones, parches y rutinas de soporte preventivo.',
                    'stats' => [
                        ['value' => '3', 'label' => 'Intervenciones próximas'],
                        ['value' => '1', 'label' => 'Crítica hoy'],
                        ['value' => '100%', 'label' => 'Respaldos verificados'],
                    ],
                    'table' => [
                        'columns' => ['Actividad', 'Ventana', 'Responsable', 'Estado'],
                        'rows' => [
                            ['Parche de seguridad portal', 'Hoy 22:30', 'Infraestructura', 'Programado'],
                            ['Revisión de UPS', '27 Mar 2026', 'Soporte campus', 'Pendiente'],
                            ['Limpieza de equipos lab', '28 Mar 2026', 'Mesa técnica', 'Planificado'],
                            ['Verificación de respaldos', 'Hoy 07:00', 'Administrador TI', 'Completado'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Acción de hoy', 'body' => 'La actualización nocturna requiere aviso preventivo a las áreas administrativas.'],
                        ['title' => 'Control previo', 'body' => 'Confirmar respaldo y ventana de menor uso reduce riesgo operativo.'],
                    ],
                ],
            ],
            'coordinador-curso' => [
                'alertas' => [
                    'title' => 'Alertas del curso',
                    'subtitle' => 'Consolide señales de asistencia, convivencia y rendimiento por paralelo.',
                    'summary' => 'Vista priorizada para detectar casos que requieren atención inmediata dentro del curso.',
                    'stats' => [
                        ['value' => '11', 'label' => 'Alertas activas'],
                        ['value' => '3', 'label' => 'Nuevas hoy'],
                        ['value' => '1', 'label' => 'Caso crítico'],
                    ],
                    'table' => [
                        'columns' => ['Estudiante', 'Tipo', 'Nivel', 'Observación'],
                        'rows' => [
                            ['A. Cedeño', 'Convivencia', 'Alta', 'Tres reportes esta semana'],
                            ['M. Paredes', 'Asistencia', 'Media', 'Dos faltas seguidas'],
                            ['J. Mera', 'Rendimiento', 'Media', 'Descenso en dos áreas'],
                            ['C. Vélez', 'Seguimiento', 'Baja', 'Pendiente llamada familiar'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Prioridad del día', 'body' => 'El caso de convivencia requiere coordinación inmediata con familia y DECE.'],
                        ['title' => 'Enfoque sugerido', 'body' => 'Cruzar alertas de asistencia y rendimiento ayuda a definir mejor la intervención.'],
                    ],
                ],
                'tutorias' => [
                    'title' => 'Tutorías y acompañamiento',
                    'subtitle' => 'Programe acciones de seguimiento con estudiantes y representantes.',
                    'summary' => 'Panel para organizar tutorías, acuerdos y continuidad del acompañamiento por curso.',
                    'stats' => [
                        ['value' => '5', 'label' => 'Tutorías activas'],
                        ['value' => '2', 'label' => 'Reuniones hoy'],
                        ['value' => '83%', 'label' => 'Acuerdos cumplidos'],
                    ],
                    'table' => [
                        'columns' => ['Tutoría', 'Responsable', 'Fecha', 'Estado'],
                        'rows' => [
                            ['Seguimiento académico', 'Tutor del curso', 'Hoy 13:30', 'Confirmada'],
                            ['Reunión con familia', 'Coordinación', 'Hoy 17:00', 'Pendiente'],
                            ['Apoyo conductual', 'DECE', 'Mañana 09:15', 'Programada'],
                            ['Control de compromisos', 'Tutoría', '24 Mar 2026', 'Realizada'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Movimiento clave', 'body' => 'La reunión familiar de hoy debe cerrar compromisos sobre asistencia y hábitos.'],
                        ['title' => 'Siguiente paso', 'body' => 'Registrar acuerdos al finalizar cada tutoría facilita el seguimiento semanal.'],
                    ],
                ],
                'coordinacion' => [
                    'title' => 'Coordinación con docentes',
                    'subtitle' => 'Unifique reportes del equipo docente y tome decisiones conjuntas.',
                    'summary' => 'Espacio de articulación para consolidar novedades y acciones compartidas por el curso.',
                    'stats' => [
                        ['value' => '4', 'label' => 'Docentes con reportes'],
                        ['value' => '7', 'label' => 'Novedades consolidadas'],
                        ['value' => '2', 'label' => 'Acuerdos por validar'],
                    ],
                    'table' => [
                        'columns' => ['Docente', 'Tema', 'Fecha', 'Estado'],
                        'rows' => [
                            ['Matemática', 'Bajo desempeño', 'Hoy 08:10', 'Revisado'],
                            ['Lengua', 'Entrega irregular', 'Hoy 09:25', 'Pendiente'],
                            ['Ciencias', 'Participación baja', 'Ayer 15:40', 'En seguimiento'],
                            ['Tutoría', 'Asistencia y hábitos', 'Ayer 17:00', 'Consolidado'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Coherencia de equipo', 'body' => 'Hay dos acuerdos pendientes de validación para mantener la intervención alineada.'],
                        ['title' => 'Recomendación', 'body' => 'Cerrar el reporte de Lengua hoy evitará duplicidad de acciones mañana.'],
                    ],
                ],
            ],
            'dece' => [
                'casos' => [
                    'title' => 'Casos activos',
                    'subtitle' => 'Administre expedientes socioemocionales con enfoque de prioridad y seguimiento.',
                    'summary' => 'Panel para revisar estado, contexto y próximos movimientos de cada caso acompañado.',
                    'stats' => [
                        ['value' => '19', 'label' => 'Casos activos'],
                        ['value' => '5', 'label' => 'Prioridad alta'],
                        ['value' => '3', 'label' => 'Nuevas derivaciones'],
                    ],
                    'table' => [
                        'columns' => ['Caso', 'Origen', 'Nivel', 'Estado'],
                        'rows' => [
                            ['Caso DE-041', 'Tutoría', 'Alto', 'Entrevista pendiente'],
                            ['Caso DE-038', 'Familia', 'Medio', 'Seguimiento activo'],
                            ['Caso DE-034', 'Coordinación', 'Alto', 'Plan en curso'],
                            ['Caso DE-029', 'Docencia', 'Bajo', 'Observación'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Atención inmediata', 'body' => 'El caso DE-041 requiere apertura de entrevista antes de terminar la jornada.'],
                        ['title' => 'Criterio útil', 'body' => 'Mantener clasificación por prioridad ayuda a cuidar tiempos de respuesta y sensibilidad.'],
                    ],
                ],
                'intervenciones' => [
                    'title' => 'Intervenciones registradas',
                    'subtitle' => 'Documente entrevistas, acuerdos y acciones de acompañamiento.',
                    'summary' => 'Historial operativo para garantizar continuidad y trazabilidad en cada proceso.',
                    'stats' => [
                        ['value' => '7', 'label' => 'Intervenciones hoy'],
                        ['value' => '4', 'label' => 'Acuerdos abiertos'],
                        ['value' => '92%', 'label' => 'Registros completos'],
                    ],
                    'table' => [
                        'columns' => ['Intervención', 'Actor principal', 'Fecha', 'Resultado'],
                        'rows' => [
                            ['Entrevista individual', 'Estudiante', 'Hoy 08:30', 'Seguimiento'],
                            ['Reunión interdisciplinaria', 'Familia y coordinación', 'Hoy 11:00', 'Acuerdos'],
                            ['Orientación familiar', 'Representante', 'Ayer 16:20', 'Pendiente cierre'],
                            ['Derivación interna', 'Tutoría', 'Ayer 10:15', 'Aceptada'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Registro clave', 'body' => 'La reunión interdisciplinaria de hoy necesita cargar acuerdos antes del cierre.'],
                        ['title' => 'Buenas prácticas', 'body' => 'Completar resultados el mismo día fortalece continuidad y precisión del seguimiento.'],
                    ],
                ],
                'derivacion' => [
                    'title' => 'Red de derivación',
                    'subtitle' => 'Coordine acciones con familia, docentes y áreas institucionales.',
                    'summary' => 'Mapa operativo de articulación para sostener el acompañamiento entre distintos actores.',
                    'stats' => [
                        ['value' => '3', 'label' => 'Derivaciones nuevas'],
                        ['value' => '2', 'label' => 'Internas'],
                        ['value' => '1', 'label' => 'Externa sugerida'],
                    ],
                    'table' => [
                        'columns' => ['Derivación', 'Destino', 'Fecha', 'Estado'],
                        'rows' => [
                            ['Académica', 'Coordinación de curso', 'Hoy 10:40', 'Activa'],
                            ['Familiar', 'Representante', 'Hoy 12:10', 'Pendiente contacto'],
                            ['Clínica externa', 'Red de apoyo', '27 Mar 2026', 'Por validar'],
                            ['Conductual', 'Tutoría', 'Ayer 14:35', 'En seguimiento'],
                        ],
                    ],
                    'side' => [
                        ['title' => 'Articulación pendiente', 'body' => 'La derivación familiar aún no confirma contacto y conviene insistir hoy.'],
                        ['title' => 'Cuidado institucional', 'body' => 'Toda derivación externa debe quedar respaldada por observación y acuerdo interno.'],
                    ],
                ],
            ],
        ];
    }
}

if (! function_exists('integraEduRoleAliases')) {
    function integraEduRoleAliases(string $roleKey): array
    {
        return match ($roleKey) {
            'superusuario' => ['SUPER', 'SUPERUSUARIO'],
            'admin' => ['ADMIN', 'ADMINISTRADOR', 'ADM'],
            'directivo' => ['DIRECTIVO', 'DIRECTOR', 'RECTOR', 'GERENTE'],
            'inspector' => ['INSPECTOR', 'INSPECTORGENERAL', 'INSP'],
            'docente' => ['DOC', 'DOCENTE'],
            'estudiante' => ['EST', 'ESTUDIANTE', 'ALUMNO'],
            'padre-tutor' => ['PADRE', 'PADREOTUTOR', 'TUTOR', 'REPRESENTANTE'],
            'administrativo' => ['ADMINISTRATIVO', 'PERSONALADMINISTRATIVO'],
            'asesor-academico' => ['ASESORACADEMICO', 'ASESOR'],
            'soporte-tecnico' => ['SOPORTETECNICO', 'SOPORTE', 'TI'],
            'coordinador-curso' => ['COORDINADORCURSO', 'COORDINADOR'],
            'dece' => ['DECE'],
            default => [],
        };
    }
}

if (! function_exists('integraEduNormalizeRoleValue')) {
    function integraEduNormalizeRoleValue(?string $value): string
    {
        $value = strtoupper(trim((string) $value));

        return preg_replace('/[^A-Z0-9]/', '', $value) ?? '';
    }
}

if (! function_exists('integraEduUsuarioTieneRol')) {
    function integraEduUsuarioTieneRol(Usuario $usuario, string $roleKey): bool
    {
        $aliases = integraEduRoleAliases($roleKey);

        if ($aliases === []) {
            return false;
        }

        $normalizedAliases = array_map('integraEduNormalizeRoleValue', $aliases);

        foreach ($usuario->roles as $rol) {
            foreach (array_filter([$rol->codigo ?? null, $rol->nombre ?? null]) as $candidate) {
                if (in_array(integraEduNormalizeRoleValue($candidate), $normalizedAliases, true)) {
                    return true;
                }
            }
        }

        return false;
    }
}

if (! function_exists('integraEduPasswordMatches')) {
    function integraEduPasswordMatches(string $plainPassword, ?string $storedPassword): bool
    {
        if ($storedPassword === null || $storedPassword === '') {
            return false;
        }

        if (hash_equals($storedPassword, $plainPassword)) {
            return true;
        }

        try {
            return Hash::check($plainPassword, $storedPassword);
        } catch (Throwable) {
            return false;
        }
    }
}

if (! function_exists('integraEduSessionUser')) {
    function integraEduSessionUser(Request $request): ?array
    {
        $user = $request->session()->get('auth_user');

        if (is_array($user) && ! empty($user['id'])) {
            $usuario = Usuario::find($user['id']);
            if (! $usuario || strtoupper((string) $usuario->estado) === 'INACTIVO') {
                $request->session()->invalidate();
                $request->session()->regenerateToken();

                return null;
            }
        }

        return is_array($user) ? $user : null;
    }
}

if (! function_exists('integraEduEnsureRoleAccess')) {
    function integraEduEnsureRoleAccess(Request $request, string|array $allowedRoles)
    {
        $allowedRoles = array_values((array) $allowedRoles);
        $sessionUser = integraEduSessionUser($request);

        if (! $sessionUser) {
            $targetRole = $allowedRoles[0] ?? null;

            return $targetRole && isset(integraEduRoles()[$targetRole])
                ? redirect()->route('roles.access', $targetRole)->with('error', 'Debe iniciar sesión para continuar.')
                : redirect()->route('auth.form')->with('error', 'Debe iniciar sesión para continuar.');
        }

        if (! in_array($sessionUser['role'] ?? null, $allowedRoles, true)) {
            $currentRole = $sessionUser['role'] ?? null;

            return $currentRole && isset(integraEduRoles()[$currentRole])
                ? redirect()->route('roles.dashboard', $currentRole)->with('error', 'Su sesión no tiene permiso para acceder a esta sección.')
                : redirect()->route('auth.form')->with('error', 'Su sesión no tiene permiso para acceder a esta sección.');
        }

        return null;
    }
}

Route::get('/', function () {
    return view('auth');
});

// Página de login personalizado (alias)
Route::get('/login', function () {
    return view('auth');
})->name('auth.form');

Route::post('/login', function (Request $request) {
    $request->validate(['username' => 'required|string', 'password' => 'required|string', 'role' => 'required|string']);

    // Lógica de ejemplo simple para demostración
    $username = $request->input('username');
    $password = $request->input('password');
    $role = $request->input('role');
    $roles = integraEduRoles();

    $fallbackRoute = isset($roles[$role])
        ? redirect()->route('roles.access', $role)
        : redirect()->route('auth.form');

    $usuario = Usuario::query()
        ->with(['persona', 'roles'])
        ->where(function ($query) use ($username) {
            $query
                ->where('username', $username)
                ->orWhere('email', $username);
        })
        ->first();

    if (! $usuario || ! integraEduPasswordMatches($password, $usuario->password_hash) || ! integraEduUsuarioTieneRol($usuario, $role)) {
        return $fallbackRoute
            ->withInput($request->only('username', 'role'))
            ->with('error', 'Credenciales incorrectas o rol no coincide.');
    }

    // Simular sesión
    if (strtoupper((string) $usuario->estado) === 'INACTIVO') {
        return $fallbackRoute
            ->withInput($request->only('username', 'role'))
            ->with('error', 'La cuenta seleccionada se encuentra inactiva.');
    }

    $usuario->forceFill([
        'ultimo_acceso' => now(),
    ])->save();

    $request->session()->put('auth_user', [
        'id' => $usuario->id,
        'username' => $usuario->username,
        'email' => $usuario->email,
        'role' => $role,
        'institucion_id' => $usuario->institucion_id,
        'display_name' => $usuario->nombre_completo,
    ]);

    return redirect()
        ->route('roles.dashboard', $role)
        ->with('status', 'Ingreso correcto como '.$roles[$role]['name'].'.');
})->name('auth.post');
Route::post('/logout', function (Request $request) {
    $request->session()->forget('auth_user');
    $request->session()->invalidate();
    $request->session()->regenerateToken();

    return redirect()
        ->route('auth.form')
        ->with('status', 'Sesion cerrada correctamente.');
})->name('auth.logout');

Route::prefix('/dashboard/admin/usuarios')->middleware('role.session:admin,superusuario')->name('admin.usuarios.')->group(function () {
    Route::get('/', [UsuarioController::class, 'index'])->name('index');
    Route::post('/', [UsuarioController::class, 'store'])->name('store');
    Route::put('/{usuario}', [UsuarioController::class, 'update'])->name('update');
    Route::delete('/{usuario}', [UsuarioController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/superusuario/instituciones')->middleware('role.session:superusuario')->name('superusuario.instituciones.')->group(function () {
    Route::get('/', [InstitucionController::class, 'index'])->name('index');
    Route::post('/', [InstitucionController::class, 'store'])->name('store');
    Route::put('/{institucion}', [InstitucionController::class, 'update'])->name('update');
    Route::delete('/{institucion}', [InstitucionController::class, 'destroy'])->name('destroy');
});

Route::get('/dashboard/superusuario/usuarios', [SuperusuarioUsuarioController::class, 'index'])
    ->middleware('role.session:superusuario')
    ->name('superusuario.usuarios.index');

Route::middleware('role.session:superusuario')->name('superusuario.usuarios.')->group(function () {
    Route::put('/dashboard/superusuario/usuarios/{usuario}', [SuperusuarioUsuarioController::class, 'update'])->name('update');
    Route::patch('/dashboard/superusuario/usuarios/{usuario}/bloquear', [SuperusuarioUsuarioController::class, 'bloquear'])->name('bloquear');
    Route::patch('/dashboard/superusuario/usuarios/{usuario}/desbloquear', [SuperusuarioUsuarioController::class, 'desbloquear'])->name('desbloquear');
});

Route::get('/dashboard/superusuario/roles', [RolController::class, 'index'])
    ->middleware('role.session:superusuario')
    ->name('superusuario.roles.index');

Route::middleware('role.session:superusuario')->name('superusuario.roles.')->group(function () {
    Route::post('/dashboard/superusuario/roles', [RolController::class, 'store'])->name('store');
    Route::put('/dashboard/superusuario/roles/{rol}', [RolController::class, 'update'])->name('update');
    Route::delete('/dashboard/superusuario/roles/{rol}', [RolController::class, 'destroy'])->name('destroy');
});

Route::get('/dashboard/superusuario/zonas', [ZonaController::class, 'index'])
    ->middleware('role.session:superusuario')
    ->name('superusuario.zonas.index');

Route::put('/dashboard/superusuario/zonas/{zona}', [ZonaController::class, 'update'])
    ->middleware('role.session:superusuario')
    ->name('superusuario.zonas.update');

Route::middleware('role.session:superusuario')->name('superusuario.distritos.')->group(function () {
    Route::get('/dashboard/superusuario/distritos', [DistritoController::class, 'index'])->name('index');
    Route::get('/dashboard/superusuario/zonas/{zona}/distritos/reporte', [DistritoController::class, 'pdf'])->name('pdf');
    Route::put('/dashboard/superusuario/zonas/{zona}/distritos/{distrito}', [DistritoController::class, 'update'])->scopeBindings()->name('update');
});

Route::prefix('/dashboard/admin/docentes')->middleware('role.session:admin')->name('admin.docentes.')->group(function () {
    Route::get('/', [DocenteController::class, 'index'])->name('index');
    Route::post('/', [DocenteController::class, 'store'])->name('store');
    Route::put('/{usuario}', [DocenteController::class, 'update'])->name('update');
    Route::delete('/{usuario}', [DocenteController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/estudiantes')->middleware('role.session:admin')->name('admin.estudiantes.')->group(function () {
    Route::get('/', [EstudianteController::class, 'index'])->name('index');
    Route::post('/', [EstudianteController::class, 'store'])->name('store');
    Route::put('/{usuario}', [EstudianteController::class, 'update'])->name('update');
    Route::delete('/{usuario}', [EstudianteController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/cursos')->middleware('role.session:admin')->name('admin.cursos.')->group(function () {
    Route::get('/', [CursoController::class, 'index'])->name('index');
    Route::post('/', [CursoController::class, 'store'])->name('store');
    Route::put('/{curso}', [CursoController::class, 'update'])->name('update');
    Route::delete('/{curso}', [CursoController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/paralelos')->middleware('role.session:admin')->name('admin.paralelos.')->group(function () {
    Route::get('/', [ParaleloController::class, 'index'])->name('index');
    Route::post('/', [ParaleloController::class, 'store'])->name('store');
    Route::put('/{paralelo}', [ParaleloController::class, 'update'])->name('update');
    Route::delete('/{paralelo}', [ParaleloController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/especialidades')->middleware('role.session:admin')->name('admin.especialidades.')->group(function () {
    Route::get('/', [EspecialidadController::class, 'index'])->name('index');
    Route::post('/', [EspecialidadController::class, 'store'])->name('store');
    Route::put('/{especialidad}', [EspecialidadController::class, 'update'])->name('update');
    Route::delete('/{especialidad}', [EspecialidadController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/asignaturas')->middleware('role.session:admin')->name('admin.asignaturas.')->group(function () {
    Route::get('/', [AsignaturaController::class, 'index'])->name('index');
    Route::get('/pdf', [AsignaturaController::class, 'pdf'])->name('pdf');
    Route::post('/', [AsignaturaController::class, 'store'])->name('store');
    Route::put('/{asignatura}', [AsignaturaController::class, 'update'])->name('update');
    Route::delete('/{asignatura}', [AsignaturaController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/periodos-academicos')->middleware('role.session:admin')->name('admin.periodos.')->group(function () {
    Route::get('/', [PeriodoAcademicoController::class, 'index'])->name('index');
    Route::post('/', [PeriodoAcademicoController::class, 'store'])->name('store');
    Route::put('/{periodo}', [PeriodoAcademicoController::class, 'update'])->name('update');
    Route::delete('/{periodo}', [PeriodoAcademicoController::class, 'destroy'])->name('destroy');
});

Route::prefix('/dashboard/admin/datos-institucionales')->middleware('role.session:admin')->name('admin.institucion.')->group(function () {
    Route::get('/', [InstitucionDatosController::class, 'index'])->name('index');
    Route::put('/{institucion}', [InstitucionDatosController::class, 'update'])->name('update');
});

Route::get('/acceso/{role}', function (string $role) {
    $roles = integraEduRoles();

    abort_unless(isset($roles[$role]), 404);

    return view('role-access', [
        'roleKey' => $role,
        'role' => $roles[$role],
    ]);
})->name('roles.access');

Route::get('/dashboard/{role}', function (string $role) {
    $roles = integraEduRoles();
    $dashboards = integraEduDashboards();

    abort_unless(isset($roles[$role]) && isset($dashboards[$role]), 404);

    if ($redirect = integraEduEnsureRoleAccess(request(), $role)) {
        return $redirect;
    }

    if ($role === 'superusuario') {
        return view('dashboard-role', [
            'roleKey' => $role,
            'role' => $roles[$role],
            'dashboard' => $dashboards[$role],
            'dashboardSection' => 'resumen',
            'resumen' => app(\App\Services\Superusuario\ResumenEstadistico::class)->datos(),
        ]);
    }

    if ($role === 'admin') {
        $sessionUser = request()->session()->get('auth_user', []);
        $institucionActiva = null;
        $institucionId = $sessionUser['institucion_id'] ?? null;

        if ($institucionId) {
            $institucionActiva = Institucion::find($institucionId);
        } elseif ($usuarioId = $sessionUser['id'] ?? null) {
            $usuario = Usuario::find($usuarioId);

            if ($usuario?->institucion_id) {
                $institucionActiva = Institucion::find($usuario->institucion_id);
            }
        }

        if ($institucionActiva?->nombre) {
            $dashboards['admin']['greeting'] = $institucionActiva->nombre;
        }

        $dashboards['admin']['actions'] = array_map(function (array $action) {
            if (($action['slug'] ?? null) !== 'configuracion') {
                return $action;
            }

            $action['label'] = 'Datos institucionales';
            $action['description'] = 'Editar la informacion principal de la institucion asignada.';
            $action['slug'] = 'datos-institucionales';

            return $action;
        }, $dashboards['admin']['actions']);
    }

    if ($role === 'admin') {
        return view('admin.dashboard', [
            'roleKey' => $role,
            'role' => $roles[$role],
            'dashboard' => $dashboards[$role],
        ]);
    }

    if ($role === 'inspector') {
        return view('inspector.dashboard', [
            'roleKey' => $role,
            'role' => $roles[$role],
            'dashboard' => $dashboards[$role],
        ]);
    }

    return view('dashboard-role', [
        'roleKey' => $role,
        'role' => $roles[$role],
        'dashboard' => $dashboards[$role],
    ]);
})->name('roles.dashboard');

Route::get('/dashboard/{role}/modulo/{module}', function (string $role, string $module) {
    $roles = integraEduRoles();
    $dashboards = integraEduDashboards();
    $modules = integraEduModules();

    abort_unless(
        isset($roles[$role]) &&
        isset($dashboards[$role]) &&
        isset($modules[$role]) &&
        isset($modules[$role][$module]),
        404
    );

    if ($redirect = integraEduEnsureRoleAccess(request(), $role)) {
        return $redirect;
    }

    return view('dashboard-module', [
        'roleKey' => $role,
        'role' => $roles[$role],
        'dashboard' => $dashboards[$role],
        'moduleKey' => $module,
        'module' => $modules[$role][$module],
    ]);
})->name('roles.module');

Route::prefix('/dashboard/inspector/distribucion-horas')->middleware('role.session:inspector')->name('inspector.distribucion-horas.')->group(function () {
    Route::get('/', [DistribucionHorasController::class, 'index'])->name('index');
});

Route::prefix('/dashboard/inspector/horario-docente')->middleware('role.session:inspector')->name('inspector.horario-docente.')->group(function () {
    Route::get('/', [HorarioDocenteController::class, 'index'])->name('index');
    Route::get('/{usuario}', [HorarioDocenteController::class, 'show'])->name('show');
});

Route::prefix('/dashboard/inspector/novedades')->middleware('role.session:inspector')->name('inspector.novedades.')->group(function () {
    Route::get('/', [NovedadController::class, 'index'])->name('index');
});
