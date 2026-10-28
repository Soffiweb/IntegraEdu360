# Asignaturas por grado y oferta

Fuentes oficiales consultadas el 12 de octubre de 2026:

- Plan EGB y Ciencias: https://educacion.gob.ec/wp-content/uploads/downloads/2023/03/MINEDUC-MINEDUC-2023-00008-A.pdf
- Reforma que sustituye Acompañamiento por Cívica y Acompañamiento Integral en el Aula: https://educacion.gob.ec/wp-content/uploads/downloads/2024/08/MINEDUC-MINEDUC-2024-00060-A.pdf
- Reforma técnica de 2025: https://educacion.gob.ec/wp-content/plugins/download-monitor/download.php?force=1&id=23072
- Módulos Gestión Financiera y Contable: https://educacion.gob.ec/wp-content/uploads/downloads/2025/10/curriculo-FIP-gestion-financiera-contable.pdf

`AsignaturaSeeder` carga únicamente cursos existentes de EGB sin especialidad, Ciencias (BGU) y Gestión Financiera y Contable (BT-GFC). Acepta `institucionId` opcional. Cada registro representa una asignatura de un grado/oferta para admitir cargas diferentes sin modificar el esquema. Códigos internos; no son códigos ministeriales. Conserva registros y asociaciones existentes; repetir la carga no duplica registros ni sobrescribe decisiones institucionales.

Las horas son períodos pedagógicos semanales. No se inventa una distribución donde la norma establece bloques compartidos: Preparatoria ECA/EF comparten 5; Elemental/Media Lengua/Matemática/Estudios Sociales/Ciencias Naturales comparten 20 y ECA/EF comparten 5. Sus cargas individuales quedan nulas hasta configurar la distribución institucional.

Ciencias tercero tiene 10 períodos adicionales de optativas: la institución debe elegir las asignaturas; no se crean disciplinas arbitrarias ni una asignatura ficticia llamada Optativas.

La reforma técnica establece 19 períodos de tronco común y 21 de formación técnica por curso, con distribución institucional del tronco común entre asignaturas y cursos. Se registra el catálogo común en los tres cursos con horas nulas para su planificación institucional. Los módulos técnicos sí tienen grado y carga individual publicados (21 por curso). No sumar las asignaturas comunes como si todas debieran impartirse simultáneamente en los tres cursos.

La implementación técnica es progresiva, desde primero en Sierra-Amazonía 2025–2026. Este catálogo representa los tres años de la nueva figura profesional, no convierte automáticamente cohortes del plan anterior.

Preparatoria usa Currículo Integrador por Ámbitos de Aprendizaje, ECA y Educación Física; sus ámbitos no se presentan como asignaturas independientes. Las inserciones curriculares transversales (educación financiera, sostenibilidad, seguridad vial y socioemocional) tampoco se convierten en materias independientes.
