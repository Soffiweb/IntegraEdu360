# Tipografía — Soffiweb Design System

> Leer este archivo cuando: cambies fuente, ajustes jerarquía de títulos, elijas qué token usar para un texto, o definas el estilo de números y montos.

---

## Familias tipográficas

| Rol | Fuente | Fallback | Carga |
|---|---|---|---|
| UI / Body | `Plus Jakarta Sans` | `system-ui, sans-serif` | Google Fonts — weights 400, 500, 600, 700 |
| Mono / Datos | `DM Mono` | `monospace` | Google Fonts — weights 400, 500 |

```html
<!-- Importar en el <head> de cada página -->
<link rel="preconnect" href="https://fonts.googleapis.com"/>
<link rel="preconnect" href="https://fonts.gstatic.com" crossorigin/>
<link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet"/>
```

---

## Principio de separación

**Plus Jakarta Sans** — todo texto de interfaz sin excepción: labels, botones, títulos, descripciones, breadcrumbs, navegación, mensajes de error.

**DM Mono** — solo cuando el contenido es numérico, técnico o requiere alineación de columna:
- Montos y precios: `$12,450.00`
- Códigos de factura: `001-001-00000XXXX`
- IDs de registro, números de secuencia
- Celdas numéricas de tablas (align-right)
- Total a cobrar en panel de resumen

Incluso "Paso 1 de 3" o "4 productos" usan Plus Jakarta Sans — son texto de interfaz, no datos.

---

## Escala tipográfica

### Display — títulos de página y sección

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `display-xl` | 28px | 700 | -0.5px | 1.2 | H1 de página — "Nueva Factura", "Dashboard" |
| `display-lg` | 22px | 700 | -0.4px | 1.2 | H2 de sección grande, títulos de módulo |
| `display-md` | 18px | 700 | -0.3px | 1.3 | H3 de subsección, título de card principal |

### Títulos de componente

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `title-lg` | 16px | 700 | 0 | 1.4 | Título de card-header |
| `title-md` | 14px | 600 | 0 | 1.4 | Label de campo, nombre de columna en tabla |

### Cuerpo

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `body-lg` | 16px | 400 | 0 | 1.6 | Texto de ayuda, descripciones largas, tooltips |
| `body-md` | 13px | 400 | 0 | 1.5 | Contenido de tabla, texto de UI general, meta |

### Etiquetas y captions

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `label` | 12px | 600 | 0.02em | 1.4 | Etiquetas de formulario (encima del input), subtítulos de card |
| `caption` | 11px | 500 | 0.06em + uppercase | 1.3 | Encabezados de sección en MAYÚSCULAS, chips de paso |

### Mono — datos numéricos (DM Mono)

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `code-lg` | 16px | 500 | 0.02em | 1.4 | Montos destacados, precio unitario visible |
| `code-md` | 13px | 400 | 0.02em | 1.5 | Celdas de tabla numéricas, subtotales de fila |
| `amount-display` | 28px | 700 | -0.5px | 1.2 | Total a cobrar, monto principal del panel de resumen |

---

## Pesos usados

Solo cuatro pesos en todo el sistema:

| Peso | Valor | Uso |
|---|---|---|
| Regular | 400 | Texto de cuerpo, contenido de tabla, placeholders |
| Medium | 500 | Labels de nav-item, captions, mono destacado |
| Semibold | 600 | Labels de formulario, title-md, botones |
| Bold | 700 | Títulos display, card-header, total-final |

Nunca usar pesos fuera de estos cuatro. No hay 300 (light) ni 800 (extrabold) en el sistema.

---

## CSS variables de referencia

```css
:root {
  --font-body: 'Plus Jakarta Sans', system-ui, sans-serif;
  --font-mono: 'DM Mono', monospace;

  /* Display */
  --text-display-xl: 700 28px/1.2 var(--font-body);
  --text-display-lg: 700 22px/1.2 var(--font-body);
  --text-display-md: 700 18px/1.3 var(--font-body);

  /* Títulos */
  --text-title-lg: 700 16px/1.4 var(--font-body);
  --text-title-md: 600 14px/1.4 var(--font-body);

  /* Cuerpo */
  --text-body-lg: 400 16px/1.6 var(--font-body);
  --text-body-md: 400 13px/1.5 var(--font-body);

  /* Etiquetas */
  --text-label: 600 12px/1.4 var(--font-body);
  --text-caption: 500 11px/1.3 var(--font-body);  /* + uppercase + tracking 0.06em */

  /* Mono */
  --text-code-lg: 500 16px/1.4 var(--font-mono);
  --text-code-md: 400 13px/1.5 var(--font-mono);
  --text-amount-display: 700 28px/1.2 var(--font-mono);
}
```

---

## Reglas de aplicación

1. **Tamaño mínimo interactivo:** 11px. Cualquier texto por debajo es puramente decorativo.
2. **Letter-spacing negativo** solo en display (títulos grandes) para compensar el espaciado óptico de fuentes grandes.
3. **Letter-spacing positivo** en captions y labels para mejorar legibilidad en tamaños pequeños.
4. **Line-height 1.5–1.6** en cuerpo para lectura cómoda en formularios largos.
5. **Line-height 1.2** en display para que los títulos grandes no floten demasiado.

---

## Guía rápida de decisión

```
¿Es un título de página o sección grande?  → display-xl / display-lg / display-md
¿Es el título de una card o panel?         → title-lg
¿Es la etiqueta encima de un input?        → label (12px/600)
¿Es texto de ayuda o descripción?          → body-lg (16px/400)
¿Es texto dentro de una tabla?             → body-md (13px/400)
¿Es un encabezado en MAYÚSCULAS?          → caption (11px/500/uppercase)
¿Es un monto, precio o código?             → code-md o code-lg (DM Mono)
¿Es el total principal de una factura?     → amount-display (28px/700 DM Mono)
```
