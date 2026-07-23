---
name: soffiweb-pdf
description: "Use for any PDF report generation in SoffiWeb projects. Triggers when creating or modifying blade templates rendered with dompdf (barryvdh/laravel-dompdf): simple listings, grouped reports, financial statements, actas, or any institutional printable document. Enforces larger print-safe margins, standard header (logo / title+company+address / date+time), footer branding, direct CSS values, and controller-driven pagination via canvas->page_text()."
license: MIT
metadata:
    author: soffiweb
    version: "1.0"
---

# Soffiweb PDF

## Cuándo usar

Siempre que se genere un reporte PDF en proyectos SoffiWeb: listados, agrupados, reportes financieros/contables, actas, cualquier documento institucional imprimible.

---

## PASO 1 — Leer el prompt antes de generar

Antes de escribir cualquier blade, identificar estas variables en el prompt del usuario. Si no se mencionan, usar el valor por defecto indicado.

| Variable en el prompt | Valores posibles                  | Default si no se menciona |
| --------------------- | --------------------------------- | ------------------------- |
| **orientación**       | `vertical` / `horizontal`         | `vertical` (portrait)     |
| **firmas**            | `sí` / `no` / número (2, 3, 4)    | no                        |
| **tipo de firma**     | `conforme` / `sin etiqueta`       | `conforme`                |
| **totales**           | `sí` / `no`                       | no                        |
| **narrativo**         | `sí` / `no`                       | no                        |
| **agrupado**          | `sí` / `no` + campo de agrupación | no                        |
| **jerárquico**        | `sí` / `no`                       | no                        |
| **firma por fila**    | `sí` / `no`                       | no                        |

### Ejemplos de cómo el usuario lo indica en el prompt

```
"genera el pdf en horizontal"                     → orientación: horizontal
"necesito firmas de 2 personas"                   → firmas: sí, cantidad: 2
"que tenga el total al final"                     → totales: sí
"es un acta, necesita párrafo introductorio"      → narrativo: sí
"agrupado por departamento"                       → agrupado: sí, campo: departamento
"cada fila con su firma"                          → firma por fila: sí
"es un estado de resultados"                      → jerárquico: sí, orientación: horizontal
```

Con esas variables definidas, incluir **solo** los componentes que correspondan (sección Componentes reutilizables más abajo). No agregar componentes que el usuario no pidió.

## Stack

```bash
composer require barryvdh/laravel-dompdf
```

`config/dompdf.php`: `isHtml5ParserEnabled: true`, `defaultPaperSize: a4`, `defaultFont: DejaVu Sans`.

---

## Fuente y CSS vars en dompdf

- **Fuente obligatoria:** `DejaVu Sans, sans-serif` — nunca Inter, nunca Google Fonts.
- **Regla nueva obligatoria:** dompdf **no reconoce de forma confiable** las variables CSS, así que en los PDF nuevos se deben usar **valores directos** en las propiedades CSS.
- **No usar `var(...)` en estilos PDF nuevos.**
- **No depender de parciales compartidos de variables para render PDF.**
- **No funcionan en dompdf:** `oklch()`, `color-mix()`, `--brd`, `--sh-*` — nunca usarlas en blades PDF.
- **Compatibilidad real:** usar hex/rgb directos en cada propiedad. No mezclar valor directo + variable.
- **Paginación:** no usar `<script type="text/php">` dentro del blade como mecanismo principal. En este proyecto la paginación se agrega desde el controller con `render() + getCanvas() + page_text()`.
- **Regla fija del proyecto:** la paginación va abajo a la derecha. Si existe código para el usuario del reporte abajo a la izquierda, debe quedar comentado.

### Regla de colores en PDF nuevo

Usar valores directos del sistema Soffiweb:

```css
color: #303237;
background: #28449a;
background: #edf1fb;
color: #6b7280;
color: #fdfdff;
```

### Color de `table-header`

- Para reportes PDF SoffiWeb, el encabezado de tabla debe usar un color del sistema Soffiweb.
- Regla por defecto en listados simples: usar **`primary`** como fondo del `<th>` y `btn-text` como color del texto.
- `secondary` solo se usa si el reporte necesita una apariencia más suave o una jerarquía secundaria.
- No inventar nuevos hex para encabezados de tabla.
- Si se usa fondo sólido en `<th>`, mantener borde inferior visual con identidad Soffiweb.

---

## Orientación del papel

La orientación la indica el usuario en el prompt (ver PASO 1). Aplicarla en el controller y en `@page`:

| Prompt dice          | `setPaper`          | `@page` margins       |
| -------------------- | ------------------- | --------------------- |
| `vertical` (default) | `'a4', 'portrait'`  | `78px 32px 54px 32px` |
| `horizontal`         | `'a4', 'landscape'` | `72px 28px 48px 28px` |

---

## CSS estándar — pegar en `<style>` de cada blade

```css
@page {
    margin: 92px 32px 54px 32px;
}
/* landscape: @page { margin: 92px 28px 48px 28px; } */

body {
    font-family:
        DejaVu Sans,
        sans-serif;
    font-size: 10px;
    color: #303237;
    margin: 0;
}

/* HEADER FIJO
   Geometría: top:-70px + height:66px caben dentro del margin-top:92px
   Columnas: logo 20% | centro 56% | meta 24%
   El centro muestra: nombre del reporte (bold, primary) + empresa debajo (muted).
   La razón social puede ocupar hasta 2 líneas; la dirección se mantiene en 1.
   Si se usa 2 líneas para empresa, dejar aire vertical extra real en header y page margin-top.
*/
.header {
    position: fixed;
    top: -70px;
    left: 0;
    right: 0;
    height: 66px;
    border-bottom: 1px solid #28449a;
    padding: 0 4px 10px;
}
.header-table {
    width: 100%;
    border-collapse: collapse;
}
.header-logo {
    width: 20%;
    vertical-align: middle;
    padding-right: 10px;
}
.header-logo img {
    max-width: 104px;
    max-height: 50px;
}

.header-center {
    width: 56%;
    text-align: center;
    vertical-align: middle;
    padding: 2px 0;
}
.header-center .pdf-title {
    margin: 0 0 3px;
    font-size: 13px;
    font-weight: bold;
    color: #28449a;
    text-transform: uppercase;
    letter-spacing: 0.4px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}
.header-center .pdf-empresa {
    margin: 0 0 3px;
    color: #303237;
    font-size: 10px;
    font-weight: bold;
    line-height: 1.1;
    overflow: hidden;
    white-space: normal;
    word-break: break-word;
    max-width: 100%;
    max-height: 26px;
}
.header-center .pdf-direccion {
    margin: 0;
    color: #6b7280;
    font-size: 8.5px;
    overflow: hidden;
    white-space: nowrap;
    text-overflow: ellipsis;
    max-width: 100%;
}

.header-meta {
    width: 24%;
    text-align: right;
    vertical-align: middle;
    font-size: 8px;
    color: #6b7280;
    padding-left: 10px;
}
.header-meta .meta-line {
    margin-bottom: 1px;
}
.header-meta .meta-line strong {
    color: #303237;
}

/* Para evitar que razonsocial o direccion rompan el layout del header,
   limitar el texto desde Blade.
   Recomendado:
   - empresa: Str::limit(..., 110) y permitir wrap hasta 2 líneas
   - direccion: Str::limit(..., 82)
*/

/* FOOTER FIJO
   Tres zonas: izquierda vacía | centro SOFFICAJA · SOFFIWEB | derecha reservada para paginación.
   La paginación se agrega desde el controller con canvas->page_text().
   Si existe impresión de usuario en el pie izquierdo, dejarla comentada.
*/
.footer {
    position: fixed;
    bottom: -20px;
    left: 0;
    right: 0;
    height: 18px;
    border-top: 1px solid #cbd5e1;
    color: #6b7280;
    font-size: 7px;
}
.footer-table {
    width: 100%;
    border-collapse: collapse;
    height: 18px;
}
.footer-left {
    width: 20%;
    vertical-align: middle;
}
.footer-center {
    width: 60%;
    text-align: center;
    vertical-align: middle;
    letter-spacing: 0.3px;
    text-transform: uppercase;
    font-size: 7px;
}
.footer-right {
    width: 20%;
    text-align: right;
    vertical-align: middle;
}

/* TÍTULO-BAND — eliminado: el header ya muestra título, empresa y dirección.
   No usar en blades nuevos. Solo conservar si un reporte legacy lo requiere. */

/* TABLA */
.report-table {
    width: 100%;
    border-collapse: collapse;
    border: 1px solid #94a3b8;
    margin-bottom: 4px;
    margin-top: 6px;
}
.report-table thead {
    display: table-header-group;
}
.report-table th,
.report-table td {
    border: 1px solid #94a3b8;
    padding: 2px 3px;
    vertical-align: top;
    word-wrap: break-word;
    line-height: 1.05;
}
.report-table th {
    background: #28449a;
    color: #fdfdff;
    font-size: 8px;
    text-transform: uppercase;
    letter-spacing: 0.3px;
    text-align: center;
    border-bottom: 2px solid #28449a;
}
.category-heading {
    background: #edf1fb !important;
    color: #28449a !important;
    text-align: left !important;
    font-size: 8px !important;
    border-left: 3px solid #28449a !important;
    padding: 4px 6px !important;
}
.report-table tbody tr:nth-child(even) {
    background: #fdfdff;
}

/* FILAS JERÁRQUICAS (reportes financieros/contables) */
.row-group {
    background: #e8eef8 !important;
    color: #28449a !important;
    font-weight: bold;
    font-size: 9px;
}
.row-subgroup {
    background: #edf1fb !important;
    color: #28449a !important;
    font-style: italic;
    padding-left: 8px !important;
}
.row-account {
    padding-left: 14px !important;
    font-size: 9px;
}
.row-total {
    background: #edf1fb !important;
    font-weight: bold;
    border-top: 2px solid #28449a !important;
}

/* TOTALES AL PIE DE TABLA */
.table-total-row td {
    background: #e8eef8 !important;
    color: #28449a !important;
    font-weight: bold;
    font-size: 9px;
    border-top: 2px solid #28449a !important;
    text-align: right;
}
.table-total-row td.label {
    text-align: left;
    text-transform: uppercase;
    letter-spacing: 0.3px;
}

/* BLOQUE DE TEXTO NARRATIVO (para actas con párrafo introductorio) */
.narrative {
    font-size: 9px;
    color: #303237;
    line-height: 1.5;
    margin-bottom: 6px;
    text-align: justify;
}

/* FIRMAS AL PIE */
.signatures-block {
    margin-top: 24px;
    page-break-inside: avoid;
}
.signatures-table {
    width: 100%;
    border-collapse: collapse;
}
.sig-cell {
    width: 50%;
    text-align: center;
    padding: 0 20px;
    vertical-align: bottom;
}
.sig-line {
    border-top: 1px solid #303237;
    margin: 0 auto 4px;
    width: 80%;
}
.sig-name {
    font-size: 9px;
    font-weight: bold;
    color: #303237;
    text-transform: uppercase;
}
.sig-role {
    font-size: 8px;
    color: #6b7280;
    margin-top: 1px;
}
.sig-label {
    font-size: 8px;
    color: #6b7280;
    margin-bottom: 18px;
}
/* Para 3 o 4 firmas: usar width:33% o width:25% en sig-cell */

/* UTILIDADES */
.text-center {
    text-align: center;
}
.text-right {
    text-align: right;
}
.text-top-center {
    text-align: center;
    vertical-align: top;
}
.nowrap {
    white-space: nowrap;
}
.font-small {
    font-size: 8px;
    line-height: 1.05;
}
.col-firma {
    min-width: 60px;
    height: 18px;
}
.num {
    text-align: right;
    font-variant-numeric: tabular-nums;
}
.summary {
    margin-top: 4px;
    font-size: 8px;
    color: #303237;
}
```

---

## Estructura base de cada blade PDF

```blade
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <title>{{ $tituloReporte ?? 'Reporte' }}</title>
    <style>/* CSS estándar aquí con valores directos */</style>
</head>
<body>
@php
    $logoPath = null;
    if ($empresa?->logo) {
        $path = storage_path('app/public/img/' . basename($empresa->logo));
        if (file_exists($path)) $logoPath = $path;
    }
@endphp

{{--
    HEADER — estructura fija. No modificar el layout de columnas.
    Solo cambiar los valores de las variables PHP que se le pasan desde el controller.
    · Izquierda : logo de la empresa (ruta absoluta storage_path)
    · Centro    : nombre del reporte (arriba) + razón social de la empresa (abajo)
    · Derecha   : usuario que generó + fecha + hora
--}}
<div class="header">
    <table class="header-table"><tr>
        <td class="header-logo">
            @if($logoPath)<img src="{{ $logoPath }}" alt="Logo">@endif
        </td>
        <td class="header-center">
            <div class="pdf-title">{{ $tituloReporte ?? 'Reporte' }}</div>
            <div class="pdf-empresa">{{ $empresa?->razonsocial ?? '' }}</div>
            <div class="pdf-direccion">{{ $empresa?->direccionmatriz ?? '' }}</div>
        </td>
        <td class="header-meta">
            {{-- <div class="meta-line"><strong>Usuario:</strong> {{ $usuarioReporte ?? auth()->user()?->name ?? '—' }}</div> --}}
            <div class="meta-line"><strong>Fecha:</strong> {{ now()->format('d/m/Y') }}</div>
            <div class="meta-line"><strong>Hora:</strong>  {{ now()->format('H:i') }}</div>
        </td>
    </tr></table>
</div>

{{--
    FOOTER — estructura fija. No modificar.
    · Izquierda : vacío (reservado)
    · Centro    : SOFFICAJA · SOFFIWEB  (separador ·)
    · Derecha   : vacío en el blade; la paginación se agrega desde el controller
--}}
<div class="footer">
    <table class="footer-table"><tr>
        <td class="footer-left"></td>
        <td class="footer-center">SOFFICAJA &nbsp;·&nbsp; SOFFIWEB</td>
        <td class="footer-right"></td>
    </tr></table>
</div>

{{-- COMPONENTES AQUÍ según variante --}}
{{-- El header ya muestra: título del reporte / empresa / dirección.
     NO agregar title-band con esos mismos datos. --}}

</body>
</html>
```

---

## Componentes reutilizables

### A — Listado simple

```blade
<table class="report-table">
    <thead>
        <tr>
            <th style="width:5%">#</th>
            <th style="width:14%">Identificación</th>
            <th style="width:29%">Nombres</th>
            <th style="width:12%">Celular</th>
            <th style="width:40%">Dirección</th>
        </tr>
    </thead>
    <tbody>
        @foreach($registros as $i => $r)
        <tr>
            <td class="text-top-center">{{ $i+1 }}</td>
            <td>{{ $r->identificacion }}</td>
            <td>{{ $r->nombre }}</td>
            <td class="nowrap">{{ $r->celular }}</td>
            <td class="font-small">{{ $r->direccion }}</td>
        </tr>
        @endforeach
    </tbody>
</table>
<div class="summary">{{ $registros->count() }} registros</div>
```

### B — Agrupado por categoría

```blade
@php $agrupado = $registros->groupBy(fn($r) => $r->categoria ?? 'Sin categoría'); @endphp
@foreach($agrupado as $grupo => $items)
<table class="report-table">
    <thead>
        <tr><th colspan="N" class="category-heading">{{ $grupo }} ({{ $items->count() }})</th></tr>
        <tr><!-- columnas --></tr>
    </thead>
    <tbody>
        @foreach($items as $k => $r)<tr><!-- celdas --></tr>@endforeach
    </tbody>
</table>
@endforeach
<div class="summary">Total: {{ $registros->count() }} registros</div>
```

### C — Columna firma en tabla (registro por registro)

Agregar como última columna. Usar cuando cada fila necesita su propia firma (listas de asistencia, recepción de materiales, etc.).

```blade
<th style="width:22%">Firma</th>
{{-- en cada <tr> --}}
<td class="col-firma"></td>
```

### D — Total al pie de tabla

Usar cuando la tabla tiene columnas numéricas y se necesita sumar. Va dentro de `<tbody>` como última fila, o en `<tfoot>`.

```blade
<tfoot>
    <tr class="table-total-row">
        <td colspan="N" class="label">Total</td>
        <td class="num">{{ number_format($registros->sum('valor'), 2) }}</td>
    </tr>
</tfoot>
```

Para múltiples columnas con total:

```blade
<tr class="table-total-row">
    <td colspan="3" class="label">Total General</td>
    <td class="num">{{ number_format($totalCantidad) }}</td>
    <td class="num">{{ number_format($totalUnitario, 2) }}</td>
    <td class="num">{{ number_format($totalSubtotal, 2) }}</td>
</tr>
```

### E — Párrafo narrativo

Usar en actas o documentos legales que tienen un texto introductorio antes de la tabla.

```blade
<div class="narrative">
    {{ $textoNarrativo }}
    {{-- o texto fijo con variables: --}}
    En la ciudad de {{ $ciudad }}, a los {{ now()->format('d') }} días del mes de
    {{ now()->translatedFormat('F') }} del {{ now()->format('Y') }}, en las oficinas de
    {{ $empresa?->razonsocial }}, ubicada en {{ $empresa?->direccionmatriz }},
    se constituyen...
</div>
```

### F — Firmas al pie del documento

Usar al final del blade, fuera de cualquier tabla. `page-break-inside: avoid` mantiene las firmas juntas.

**2 firmantes (más común):**

```blade
<div class="signatures-block">
    <table class="signatures-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-label">RECIBÍ CONFORME</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $firmante1->nombre }}</div>
                <div class="sig-role">{{ $firmante1->cargo }}</div>
            </td>
            <td class="sig-cell">
                <div class="sig-label">ENTREGUÉ CONFORME</div>
                <div class="sig-line"></div>
                <div class="sig-name">{{ $firmante2->nombre }}</div>
                <div class="sig-role">{{ $firmante2->cargo }}</div>
            </td>
        </tr>
    </table>
</div>
```

**3 firmantes:** agregar `<td class="sig-cell" style="width:33%">` y cambiar las otras dos a `width:33%` también.

**4 firmantes:** `width:25%` en los 4 `sig-cell`. Si los nombres son largos, usar 2 filas de 2 celdas.

**Firmas con usuario del sistema** (reportes contables/financieros — sin etiqueta conforme):

```blade
<div class="signatures-block">
    <table class="signatures-table">
        <tr>
            <td class="sig-cell">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $firmante1->nombre }}</div>
                <div class="sig-role">{{ $firmante1->cargo }}</div>
            </td>
            <td class="sig-cell">
                <div class="sig-line"></div>
                <div class="sig-name">{{ $firmante2->nombre }}</div>
                <div class="sig-role">{{ $firmante2->cargo }}</div>
            </td>
        </tr>
    </table>
</div>
```

### G — Tabla jerárquica (reportes financieros/contables)

Para estados de resultados, balances, presupuestos — donde hay grupos, subgrupos y cuentas con indentación visual.

```blade
<table class="report-table">
    <thead>
        <tr>
            <th style="width:8%">Código</th>
            <th style="width:42%">Descripción</th>
            <th style="width:25%">Período 1</th>
            <th style="width:25%">Período 2</th>
        </tr>
    </thead>
    <tbody>
        @foreach($filas as $fila)
            @if($fila->nivel === 'grupo')
            <tr>
                <td colspan="2" class="row-group">{{ $fila->descripcion }}</td>
                <td class="num row-group">{{ number_format($fila->periodo1, 2) }}</td>
                <td class="num row-group">{{ number_format($fila->periodo2, 2) }}</td>
            </tr>
            @elseif($fila->nivel === 'subgrupo')
            <tr>
                <td class="row-subgroup">{{ $fila->codigo }}</td>
                <td class="row-subgroup">{{ $fila->descripcion }}</td>
                <td class="num row-subgroup">{{ number_format($fila->periodo1, 2) }}</td>
                <td class="num row-subgroup">{{ number_format($fila->periodo2, 2) }}</td>
            </tr>
            @else
            <tr>
                <td class="row-account">{{ $fila->codigo }}</td>
                <td class="row-account">{{ $fila->descripcion }}</td>
                <td class="num">{{ number_format($fila->periodo1, 2) }}</td>
                <td class="num">{{ number_format($fila->periodo2, 2) }}</td>
            </tr>
            @endif
        @endforeach
        <tr class="table-total-row">
            <td colspan="2" class="label">Resultado del Ejercicio</td>
            <td class="num">{{ number_format($totalPeriodo1, 2) }}</td>
            <td class="num">{{ number_format($totalPeriodo2, 2) }}</td>
        </tr>
    </tbody>
</table>
```

---

## Componentes que se incluyen según el prompt

Incluir **únicamente** los componentes que el usuario indicó en el prompt. Referencia rápida:

| El usuario pidió en el prompt   | Incluir componente        |
| ------------------------------- | ------------------------- |
| (siempre)                       | A — listado simple (base) |
| `agrupado`                      | B en lugar de A           |
| `firma por fila`                | C dentro de A o B         |
| `totales`                       | D al pie de tabla         |
| `narrativo`                     | E antes de tabla          |
| `firmas` (al pie del documento) | F al final del body       |
| `jerárquico`                    | G en lugar de A           |

---

## Controller

```php
use Barryvdh\DomPDF\Facade\Pdf;

public function exportarPdf()
{
    $data = [
        'empresa'          => Empresa::first(),
        'registros'        => Modelo::orderBy('nombre')->get(),
        'tituloReporte'  => 'Listado de ...',   // aparece en header y title del PDF
        // 'usuarioReporte' => auth()->user()?->name ?? '—',
        // Variables adicionales según componentes usados:
        // 'firmante1'     => (object)['nombre' => '...', 'cargo' => '...'],
        // 'firmante2'     => (object)['nombre' => '...', 'cargo' => '...'],
        // 'totalSubtotal' => $registros->sum('subtotal'),
    ];

    $pdf = Pdf::loadView('reportes.nombre-reporte', $data);
    $pdf->setPaper('a4', 'portrait'); // 'landscape' según tabla
    $pdf->render();

    $domPdf = $pdf->getDomPDF();
    $canvas = $domPdf->getCanvas();
    $font = $domPdf->getFontMetrics()->getFont('DejaVu Sans', 'sans-serif', 'normal');

    // $canvas->page_text(28, 814, 'Usuario: ' . ($data['usuarioReporte'] ?? 'Sistema'), $font, 6, [0.39, 0.45, 0.51]);
    $canvas->page_text(510, 814, 'Pagina {PAGE_NUM} de {PAGE_COUNT}', $font, 6, [0.39, 0.45, 0.51]);

    return $pdf->stream('nombre-' . now()->format('Ymd') . '.pdf');
}
```

## Regla de paginación

- En Soffiweb, la paginación se agrega desde el controller después de `$pdf->render()`.
- Patrón obligatorio:
    1. `\PDF::loadView(...)`
    2. `setPaper(...)`
    3. `render()`
    4. `getDomPDF()->getCanvas()`
    5. `getFontMetrics()->getFont(...)`
    6. `page_text(...)`
    7. `download()` o `stream()`
- No confiar en `<script type="text/php">` dentro del blade.
- El `footer-left` del blade queda vacío.
- Si se prepara impresión de usuario por canvas, dejarla comentada.
- El `footer-right` del blade queda vacío; la numeración se dibuja por canvas.
- Para A4 portrait con el header/márgenes actuales, usar `510, 814` con tamaño `6`.
- Para A4 landscape con el header/márgenes actuales, usar `740, 566` con tamaño `6`.

---

## Reglas inamovibles

1. `DejaVu Sans` — nunca Inter ni Google Fonts.
2. Usar valores directos en el mismo blade PDF — nunca `var(...)`, nunca `_pdf-vars`, nunca `oklch()`, `color-mix()`, `--brd`, `--sh-*`.
3. Logo con `storage_path()` absoluto — nunca `asset()` ni `Storage::url()`.
4. `display: table-header-group` en `thead` — repite cabecera en cada página.
5. Anchos explícitos en `<th>` sumando 100% — dompdf no los infiere.
6. `word-wrap: break-word` en `th` y `td` — previene desbordamiento.
7. Márgenes en `@page`, no en `body`.
8. Múltiples tablas (agrupado) → cada una con `margin-bottom: 4px`.
9. Columnas numéricas → clase `.num` siempre (`text-align: right` + `tabular-nums`).
10. Firmas → siempre `page-break-inside: avoid` en `.signatures-block`.

## Checklist de entrega

- [ ] Fuente DejaVu Sans en body
- [ ] Header con logo (`storage_path`), razón social, fecha/hora
- [ ] Footer con `SOFFICAJA · SOFFIWEB`
- [ ] `footer-right` vacío en el blade
- [ ] `thead` con `display: table-header-group`
- [ ] Anchos de columna suman 100%
- [ ] Sin `oklch`/`color-mix`/`--brd`/`--sh-*` en el CSS
- [ ] Sin `var(...)` ni parciales de variables en el blade PDF
- [ ] Columnas numéricas con clase `.num`
- [ ] Si hay total: `table-total-row` con `tfoot` o última fila de `tbody`
- [ ] Si hay firmas: `signatures-block` fuera de tablas, al final del `<body>`
- [ ] `setPaper('a4', 'portrait'|'landscape')` según columnas
- [ ] Paginación agregada desde controller con `render() + getCanvas() + page_text()`
