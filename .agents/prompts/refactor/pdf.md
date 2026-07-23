Quiero que me ayudes a refactorizar los reportes del modulo [NOMBRE_MODULO].

Alcance:
- Solo reportes PDF / blades PDF / salida visual del reporte.
- Si hace falta, ajusta unicamente la data minima del controller para alimentar bien el PDF.
- No hagas refactor backend general todavia.

Instrucciones:
- Lee y aplica mi skill `soffiweb-pdf`.
- Usa el estandar PDF Soffiweb del proyecto: header institucional, footer institucional, margenes correctos, colores directos, tipografia `DejaVu Sans`, paginacion desde controller.
- No uses variables CSS en PDF.
- Usa como modelo base de cabecera institucional el formato horizontal validado del reporte de prestamos:
  - estructura con tabla, no `flex`, para compatibilidad con `dompdf`
  - bloque izquierdo `25%` para el logo
  - bloque derecho `75%` para informacion alineada horizontalmente a la derecha del logo
  - orden del bloque derecho:
    1. nombre del reporte en mayusculas
    2. nombre o razon social de la empresa
    3. direccion de la empresa
    4. filtros aplicados al reporte
  - si no existe filtro especifico, mostrar el filtro general por defecto del reporte
- Toma como referencia un PDF ya refactorizado del proyecto.
- Si el reporte necesita horizontal/vertical, respetalo.
- Si el reporte necesita totales, incluyelos.
- Manten todo limpio y listo para produccion.

Flujo:
1. Lee la skill.
2. Revisa controller + blades PDF actuales.
3. Revisa un PDF Soffiweb de referencia.
4. Refactoriza el/los reportes.
5. Verifica sintaxis y consistencia final.

Archivos:
- Controller: [RUTA_CONTROLLER]
- PDF listado: [RUTA_PDF_1]
- PDF detalle: [RUTA_PDF_2]
- Referencia PDF existente: [RUTA_REFERENCIA]
