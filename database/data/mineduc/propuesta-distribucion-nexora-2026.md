# Propuesta institucional NEXORA · 2026–2027

Distribución solicitada por el usuario para la institución 16216 y el período 1. Se conserva el catálogo ministerial existente; las horas que ese catálogo dejaba a criterio institucional se completan como **propuesta institucional**, sin presentarlas como cargas individuales impuestas por el Ministerio. No se sustituyen asignaciones docentes previas.

## Distribución de los bloques pendientes

Períodos semanales por paralelo:

| Grados | Lengua | Matemática | Estudios Sociales | Ciencias Naturales | ECA | Educación Física |
| --- | ---: | ---: | ---: | ---: | ---: | ---: |
| 1.º EGB | Currículo integrador: 25 | — | — | — | 2 | 3 |
| 2.º–4.º EGB | 8 | 6 | 3 | 3 | 2 | 3 |
| 5.º–7.º EGB | 6 | 6 | 4 | 4 | 2 | 3 |

En 2.º–7.º se conservan Inglés (3), Cívica y Acompañamiento Integral en el Aula (1) y Animación a la Lectura (1). Total: 30 períodos por paralelo. EGB Superior conserva la carga existente de 30.

Tercero de Ciencias completa 10 períodos de optativas: Números Complejos y Métodos de Demostración Matemática (3), Investigación en Ciencia y Tecnología (2), Redacción Creativa (3) y Problemas del Mundo Contemporáneo (2). Total de Ciencias: 35 por paralelo.

Gestión Financiera y Contable completa 19 períodos de tronco común en cada curso: Lengua (3), Matemática (3), Educación Física (2), Inglés (2), Historia (1), Educación para la Ciudadanía (1), Filosofía (1), Biología (1), Física (1), Química (1), ECA (1), Cívica y Acompañamiento Integral en el Aula (1) y Emprendimiento y Gestión (1). Se conservan los 21 períodos técnicos ya registrados: total 40. Se utiliza la oferta técnica que ya consta en la base de datos; esta operación no determina ni certifica la transición de cohortes entre planes.

## Docentes y comisiones

Se completa cada combinación asignatura/paralelo una sola vez y se verifica contra la carga curricular. El máximo de clase es 25 períodos por docente. Se utiliza primero la capacidad disponible de los docentes activos y se crean los faltantes con nombres `Docente NUEVO NN`, sin inventar cédulas ni títulos profesionales. Los nuevos registros tienen contraseñas aleatorias no publicadas.

Se propone una comisión por docente, distribuida de forma equilibrada entre seis equipos: técnico-pedagógico, convivencia y cultura de paz, gestión de riesgos y seguridad escolar, salud y bienestar, cultura y participación artística, y deportes y recreación. Una comisión se registra con cero períodos de clase; no significa que sus tareas carezcan de dedicación, sino que su gestión se organiza fuera del cómputo pedagógico. Se conservan comisiones existentes y se valida el máximo de dos por docente y período solicitado por el usuario.

El reparto automático respeta capacidad y currículo; no certifica la idoneidad de especialidad de cada docente, pues esta operación no dispone de una matriz validada de títulos y habilitaciones. Los docentes nuevos representan necesidades de personal de la planificación.

## Reproducción

```sh
# Simular, revirtiendo todos los cambios de la transacción
php artisan docentes:completar-distribucion 16216 1 --propuesta-institucional

# Guardar, sin duplicar cargas ni comisiones en ejecuciones posteriores
php artisan docentes:completar-distribucion 16216 1 --propuesta-institucional --aplicar
```

Ver fuentes del catálogo en [asignaturas.md](asignaturas.md). Referencias: [plan 2023](https://educacion.gob.ec/wp-content/uploads/downloads/2023/03/MINEDUC-MINEDUC-2023-00008-A.pdf), [reforma técnica 2025](https://educacion.gob.ec/wp-content/uploads/downloads/2025/10/MINEDEC-MINEDEC-2025-00051-A.pdf), [currículo oficial de Investigación en Ciencia y Tecnología](https://educacion.gob.ec/wp-content/uploads/downloads/2016/09/Optativa-Investigacion-en-Ciencia-y-Tecnologia.pdf) y [Problemas del Mundo Contemporáneo](https://www.educacion.gob.ec/wp-content/uploads/downloads/2013/10/LINEAMIENTOS_CURRICULARES_PROBLEMAS_DEL_MUNDO_CONTEMPORANEO_151013.pdf). El máximo de dos comisiones se aplica como requisito del usuario; no se atribuye aquí a una norma ecuatoriana concreta sin verificación.
