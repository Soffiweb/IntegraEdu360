# Reportes PDF

Para todos los reportes PDF del proyecto, utilizar la cabecera compartida `<x-pdf.header>` y los estilos `components.pdf.header-styles`:

- Logotipo institucional `logo1` a la izquierda: 20% de la cabecera.
- Centro: 60%, con nombre de institución, eslogan y nombre del reporte, en ese orden.
- Logotipo institucional `logo2` a la derecha: 20% de la cabecera.
- Mostrar solo los logos configurados; no sustituirlos por los de otra institución.
- Incluir columna **Ord** y un resumen estadístico al final, calculado sobre los datos filtrados del reporte.
- Reporte de paralelos: agrupar por especialidad, con subtotales de paralelos, cursos y capacidad. Matriculados permanece sin dato hasta que exista un registro real de matrículas.

Esta distribución solicitada por el usuario prevalece sobre la cabecera por defecto de la skill soffiweb-pdf. Mantener DejaVu Sans, CSS con valores directos y paginación desde el controlador.
