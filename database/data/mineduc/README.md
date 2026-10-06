# Catálogo de distritos educativos

Fuente: [Acuerdo MINEDUC-MINEDUC-2025-00012-A](https://educacion.gob.ec/wp-content/uploads/downloads/2025/03/MINEDUC-MINEDUC-2025-00012-A.pdf), firmado el 18 de marzo de 2025, artículo 1, páginas 6–8. Consultado el 4 de octubre de 2026.

El CSV conserva las 140 nomenclaturas oficiales y sus códigos de zona/distrito. Únicamente se unen los saltos de línea del PDF. Provincia se obtiene de los primeros dos dígitos del código distrital (codificación provincial). No contiene cifras de estudiantes, instituciones ni circuitos.

Se incluye el PDF original y su SHA-256 en `fuente.json` para comprobar la extracción. Totales por zona: 16, 8, 19, 15, 25, 17, 19, 12 y 9.

Importación después de migrar: `php artisan db:seed --class=DistritoSeeder`. Requiere zonas con códigos 1–9. El seeder es transaccional e inserta únicamente códigos inexistentes, para conservar las modificaciones locales. No elimina registros ni sobrescribe ediciones. También se ejecuta con `DatabaseSeeder`, después de `ZonaSeeder`.

## Instituciones educativas

Registro AMIE oficial de inicio 2025–2026: 16.215 instituciones. Fuente: [Base de datos del Ministerio](https://educacion.gob.ec/base-de-datos/), archivo histórico publicado en abril de 2026 (URL y SHA-256 en `fuente-instituciones.json`). Se usa el último período de inicio del archivo, sin mezclar años ni sumar matrículas históricas.

El distrito se obtiene por código AMIE del [visualizador oficial Escuelas Transparentes](https://educacion.gob.ec/visualizador-de-transparencia/), cuyo modelo público fue actualizado el 17 de septiembre de 2026. Se conservan sus respuestas comprimidas en `cruce-instituciones-*.json.gz`. El cruce principal usa COD_AMIE/COD_AD_DISTRITO/NOM_ZONA; las tablas públicas Pensiones_matr y zonas_unidas resuelven dos casos adicionales. Cada asignación se verifica contra el catálogo de 140 distritos y su zona. Se normalizan los códigos AMIE a mayúsculas.

Resultado: 16.182 instituciones con distrito y 33 pendientes (ausentes del cruce o con código distrital 00000). `instituciones-pendientes.csv` identifica estos casos para revisión manual; no se asignan por aproximación geográfica. `instituciones-cambios-zona.csv` registra 30 diferencias entre la zona estadística y la zona del directorio vigente; se utiliza la zona del distrito verificado. La ubicación del directorio se utiliza cuando existe una asignación válida.

```sh
php artisan migrate --force
php artisan db:seed --class=InstitucionCatalogosSeeder --force
php artisan minedec:importar-instituciones --dry-run
php artisan minedec:importar-instituciones
```

La importación valida el archivo completo antes de escribir y ejecuta las inserciones/actualizaciones dentro de una transacción. Repetirla no duplica instituciones ni altera filas idénticas. Conserva los identificadores existentes, estado, dirección y contactos locales; si la fuente no identifica distrito, conserva una asignación local previa. Los registros anteriores sin AMIE se conservan.

Para reproducir el CSV, descargar el XLSX indicado en la metadata y ejecutar:

```sh
python3 scripts/minedec/extract_instituciones.py /ruta/registro.xlsx database/data/mineduc/cruce-instituciones-distritos-2026.json.gz --complemento=database/data/mineduc/cruce-instituciones-complemento-2026.json.gz
```
