# Layout y Estructura — Soffiweb Design System

> Leer este archivo cuando: crees una página nueva, definas el comportamiento responsive, organices secciones de un formulario, o necesites saber los breakpoints del sistema.

---

## Estructura de página estándar

Todos los módulos de Soffiweb siguen esta estructura base:

```
┌─────────────────────────────────────────────┐
│  Topbar (60px fijo)                         │
├──────────┬──────────────────────────────────┤
│          │  Content area (scroll)           │
│ Sidebar  │  ┌─ page-header ───────────────┐ │
│ (220px)  │  │  título + acciones          │ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ required-note (si aplica) ─┐ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ card 1 ────────────────────┐ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ card 2 ────────────────────┐ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ card 3 ────────────────────┐ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ totals + notas ────────────┐ │
│          │  └─────────────────────────────┘ │
│          │  ┌─ action-footer ─────────────┐ │
│          │  │  Cancelar / Borrador / CTA  │ │
│          │  └─────────────────────────────┘ │
└──────────┴──────────────────────────────────┘
```

### Dimensiones de layout

```
topbar height:     60px — fijo en la parte superior
sidebar width:     220px expandido / 56px colapsado
content max-width: 1200px — centrado en el área main
content padding:   --sp-xl (32px) en desktop
                   --sp-lg (24px) en tablet
main:              flex-col, ocupa el resto del viewport
```

---

## Breakpoints

| Nombre | Ancho | Comportamiento |
|---|---|---|
| mobile | < 640px | Sidebar colapsa a barra inferior o hamburger; grids de formulario a 1 columna; tablas con scroll horizontal |
| tablet | 640px – 1024px | Sidebar colapsado (56px con solo íconos); grids de formulario a 2 columnas; topbar completo |
| desktop | > 1024px | Sidebar expandido (220px con labels); grids completos 3–4 columnas; layout de 2 columnas para totales |

---

## Grids de formulario

Los formularios usan CSS Grid con las siguientes variantes:

```css
.form-grid-1 { grid-template-columns: 1fr; }
.form-grid-2 { grid-template-columns: 1fr 1fr; }
.form-grid-3 { grid-template-columns: 1fr 1fr 1fr; }
.form-grid-4 { grid-template-columns: 1fr 1fr 1fr 1fr; }

/* Gap siempre --sp-lg (24px) entre campos */
.form-grid   { gap: 24px; }

/* Campo que ocupa todo el ancho del grid */
.form-group.full { grid-column: 1 / -1; }
```

**En tablet (< 1024px):** form-grid-3 y form-grid-4 colapsan a 2 columnas.  
**En mobile (< 640px):** todos los grids colapsan a 1 columna.

---

## Stack de cards

Las secciones de un formulario se apilan verticalmente con gap `--sp-lg` (24px):

```
.stack {
  display: flex;
  flex-direction: column;
  gap: --sp-lg;
}
```

Cada card representa un paso lógico del flujo. Ejemplo en facturación:
1. Card "Datos del cliente" (paso 1 de 3)
2. Card "Agregar productos" (paso 2 de 3)
3. Card "Detalle de la factura" (paso 3 de 3)
4. Row: notas + panel de totales

---

## Page header

Encabezado de cada página con el título y las acciones principales.

```
display:          flex space-between align-start
margin-bottom:    --sp-xl (32px)

.page-title:      display-xl (28px/700), letter-spacing -0.4px
                  color navy-800 (light) / base-content (dark)
.page-subtitle:   body-md (13px/400), color muted
                  margin-top 4px

.page-header-actions: flex, gap --sp-sm, align-center
  orden típico: btn-ghost "Volver" | btn-ghost "Exportar" | btn-primary "Guardar"
```

---

## Bottom row — totales y notas

La fila inferior de un formulario de datos usa un grid de 2 columnas:

```
display:               grid
grid-template-columns: 1fr 320px
gap:                   --sp-lg
align-items:           start

columna izquierda:   card con textarea de notas
columna derecha:     panel de totales (320px fijo)
```

**En tablet/mobile:** el panel de totales se apila debajo del textarea, ambos a full width.

---

## Action footer

Botones de acción al final del formulario.

```
display:         flex
justify-content: flex-end
gap:             --sp-sm
padding-bottom:  --sp-xxl (48px)

orden de botones (izquierda a derecha):
  btn-ghost "Cancelar"
  btn-ghost "Guardar borrador"
  btn-primary "Emitir factura" (o la acción principal del módulo)
```

**Principio:** Las acciones destructivas (cancelar) van a la izquierda; la acción principal va siempre a la derecha.

---

## Tabla de datos — layout de página de lista

Páginas de listado (lista de facturas, lista de clientes, etc.) usan esta estructura:

```
<page-header>    título + botón "Nueva [entidad]" (btn-accent)
<filters-bar>    barra de filtros y búsqueda (opcional)
<card>
  <card-header>  título de la tabla + contador + acciones masivas
  <table-wrap>   tabla con thead navy + tbody
</card>
<pagination>     paginación inferior
```

---

## Responsive — collapso de sidebar

```
desktop (> 1024px):
  sidebar width: 220px, labels visibles, nav-group-labels visibles

tablet (640–1024px):
  sidebar width: 56px
  labels ocultos, nav-group-labels ocultos
  solo íconos centrados en el nav-item

mobile (< 640px):
  sidebar se oculta completamente
  topbar muestra hamburger que abre sidebar como drawer
  o navegación se mueve a barra inferior
```
