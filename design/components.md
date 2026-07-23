# Componentes — Soffiweb Design System

> Leer este archivo cuando: crees o modifiques botones, inputs, cards, tablas, el sidebar, el topbar, chips, el panel de totales, o cualquier elemento de UI reutilizable.

---

## Botones

### button-primary
CTA principal — guardar, emitir, confirmar, continuar.

```
background:   --color-primary  (navy-700 light / navy-300 dark)
color:        white
font:         title-md (14px/600) para tamaño normal
              body-lg (16px/500) para tamaño grande
radius:       --r-lg (10px)
height:       --h-lg (48px) para CTA de página
              --h-md (36px) para acciones en formulario
padding:      0 16px
shadow:       0 2px 4px rgba(21,43,116,0.25)       — light
              0 2px 8px rgba(104,128,212,0.3)       — dark
hover:        navy-800 + translateY(-1px) + sombra  — light
              navy-200 + translateY(-1px)            — dark
active:       translateY(0)
```

### button-accent
Acción de creación — nueva factura, agregar producto, importar.

```
background:   --color-accent  (rosa-500 light / rosa-400 dark)
color:        white
radius:       --r-lg (10px)
height:       --h-md (36px) o --h-lg (48px)
shadow:       0 2px 4px rgba(196,82,122,0.25)
hover:        rosa-600 + translateY(-1px)  — light
              rosa-300 + translateY(-1px)  — dark
```

### button-ghost
Acciones terciarias — cancelar, volver, exportar PDF.

```
background:   white          — light
              base-300       — dark
color:        base-content
border:       1px solid --border
radius:       --r-lg (10px)
height:       --h-md (36px)
hover:        base-200 / border navy-200  — light
              #2e3d58 / border navy-300   — dark
```

### button-icon
Botón de solo ícono — notificaciones, acciones de fila en tabla.

```
background:   white / base-300
border:       1px solid --border
radius:       --r-lg (10px)
size:         34×34px
ícono:        16×16px SVG outline
```

---

## Inputs y formularios

### text-input / select

```
background:   white         — light
              base-200      — dark
border:       1px solid --border
radius:       --r-lg (10px)
padding:      9px 12px
height:       --h-md (36px)
font:         body-md (Plus Jakarta Sans, 13px/400)
color:        base-content
placeholder:  color muted

:focus
  border-color:  navy-400 (light) / navy-300 (dark)
  box-shadow:    --shadow-focus

:hover sin focus
  border-color:  navy-200 (light) / navy-400 (dark)

:disabled
  background:    base-200 (light) / #111926 (dark)
  color:         muted
  border-style:  dashed
  cursor:        not-allowed
```

### input con prefijo de moneda
Para campos de precio, monto, descuento, subsidio.

```
wrapper:       position relative
.input-prefix: posición absoluta, left 10px, centrado vertical
               font label (12px/600), color muted
input:         padding-left 28px
               font code-md (DM Mono 13px/400)
               text-align right
```

### form-label
Etiqueta encima de cada campo.

```
font:          label (Plus Jakarta Sans, 12px/600)
color:         navy-700 (light) / navy-200 (dark)
margin-bottom: 5–6px

asterisco *:   color --color-accent (rosa-500/rosa-300)
               NUNCA usar color de error para el asterisco
```

### form-hint
Texto de ayuda debajo del campo.

```
font:   body-md (13px/400)
color:  muted
```

### form-group / form-grid
Contenedores de campos en formularios.

```
.form-group:     flex column, gap 6px
.form-grid-1:    grid, 1fr
.form-grid-2:    grid, 1fr 1fr
.form-grid-3:    grid, 1fr 1fr 1fr
.form-grid-4:    grid, 1fr 1fr 1fr 1fr
.gap en grids:   --sp-lg (24px)
.form-group.full: grid-column 1 / -1
```

### nota de campo requerido
Banner superior de formularios.

```
background:   rosa-50 / border rosa-100    — light
              #1e0e16 / border #3d1a28     — dark
padding:      6px 12px
radius:       --r-lg (10px)
font:         body-md (13px) muted
dot:          6px círculo rosa-500 (light) / rosa-400 (dark)
margin-bottom: --sp-lg
```

### área de agregar ítem
Panel dashed para añadir productos a una factura.

```
background:   base-200
border:       1px dashed --border
radius:       --r-xl (14px)
padding:      --sp-lg --sp-xl

grid interior:
  columns: 2fr 80px 80px 120px 80px 80px 80px auto
  gap:     --sp-sm
  align:   end

.detail-col-label:
  font:   11px/600
  color:  navy-600 (light) / navy-200 (dark)
  mb:     5px
```

---

## Cards

### card — contenedor principal

```
background:   base-100
border:       1px solid --border
radius:       --r-xl (14px)
shadow:       --shadow-sm (light) / 0 1px 3px rgba(0,0,0,0.4) (dark)
animation:    fadeInUp 250ms ease (con stagger de 50ms por card)
```

### card-header

```
padding:        --sp-lg --sp-xl  (24px 32px)
border-bottom:  1px solid base-300
display:        flex, space-between, align-center
```

### card-icon
Cuadrado de 32px con ícono del módulo, en la izquierda del card-header.

```
size:     32×32px
radius:   --r-md (6px)
ícono:    16×16px SVG stroke-width 1.8

variante navy   bg #eef1fb / color navy-600    — light
                bg #0e1f55 / color navy-300    — dark
variante rosa   bg #fdf0f4 / color rosa-600    — light
                bg #2a0d1a / color rosa-300    — dark
variante steel  bg #f0f4fb / color steel-500   — light
                bg #0d1e35 / color steel-300   — dark
```

### card-title / card-desc

```
card-title:  title-lg (16px/700), color base-content (light) / base-content (dark)
card-desc:   body-md (13px/400), color muted
margin-top:  1px entre título y descripción
```

### card-body

```
padding: --sp-xl (32px)
```

---

## Tabla de datos

### thead

```
background:    navy-800 (#0e1f55)   — light
               #0a1020              — dark
font:          caption (11px/500/uppercase/tracking 0.04em)
color:         rgba(255,255,255,0.75)  — light
               navy-200 (#9aaae3)      — dark
padding th:    11px 14px
radius:        --r-xl en esquinas superiores
```

### tbody

```
fila:          border-bottom 1px solid base-300
fila hover:    background navy-50 (light) / #1a2440 (dark)
padding td:    11px 14px
font texto:    body-md (13px/400), color base-content
font números:  code-md (DM Mono 13px/400), text-align right
```

### empty state (tabla sin datos)

```
ícono:      40×40px círculo base-200/base-300, SVG 18px muted
título:     title-md (14px/600), navy-700 (light) / navy-200 (dark)
subtítulo:  body-md (13px/400), color muted
padding:    --sp-xxl vertical, centrado
```

---

## Panel de totales

Componente de facturación, nóminas y cuentas por cobrar.

```
base:          card con overflow hidden

.totals-header
  background:    navy-50 (light) / #0a1020 (dark)
  padding:       --sp-md --sp-lg
  font:          caption (11px/700/uppercase)
  color:         navy-700 (light) / navy-200 (dark)
  border-bottom: 1px solid --border

.totals-row
  padding:       10px --sp-lg
  display:       flex space-between align-center
  label:         body-md (13px), color muted
  valor:         code-md (DM Mono 13px/500), color base-content
  border-bottom: 1px solid base-300

.totals-row.total-final
  background:    navy-800                                    — light
                 linear-gradient(135deg, #0e1f55, #1a0a2e)  — dark
  border-top:    1px solid navy-600  (solo en dark)
  label:         13px/600 rgba(255,255,255,0.7) (light) / navy-200 (dark)
  valor:         amount-display (DM Mono 28px/700/white)
```

---

## Sidebar de navegación

```
width:       220px expandido / 56px colapsado
background:  navy-900 (#070f2e) — light
             #0d1424            — dark
padding:     --sp-lg 0

.sidebar-logo
  padding:      0 --sp-lg --sp-xl
  border-bottom: 1px solid rgba(255,255,255,0.07)

.sidebar-logo-mark
  size:     32×32px
  radius:   --r-md (6px)
  bg:       linear-gradient(135deg, navy-400, rosa-500)
  color:    white / 700 / 16px

.nav-group-label
  font:    10px/600/uppercase/tracking 0.08em
  color:   rgba(255,255,255,0.3)
  padding: --sp-sm --sp-lg --sp-xs

.nav-item
  padding:     9px --sp-lg
  font:        body-md (13px/500)
  color:       rgba(255,255,255,0.55)
  border-left: 3px solid transparent
  gap:         --sp-sm
  ícono:       15×15px SVG outline
  hover:       bg rgba(255,255,255,0.05) / color rgba(255,255,255,0.85)

.nav-item.active
  bg:           rgba(104,128,212,0.15)
  color:        white
  border-left:  3px solid navy-300 (#6880d4)

.nav-badge
  bg:      rosa-500
  color:   white
  font:    10px/700
  radius:  --r-full
  padding: 1px 6px
  margin:  auto (empujado a la derecha)

.user-block (fondo del sidebar)
  bg:      rgba(104,128,212,0.12)
  radius:  --r-xl (14px)
  padding: 12px
```

---

## Topbar

```
height:        60px
background:    base-100
border-bottom: 1px solid --border
shadow:        --shadow-xs
padding:       0 --sp-xl
display:       flex space-between align-center

.breadcrumb
  font:    body-md (13px)
  links:   color muted — hover navy-700 (light) / navy-300 (dark)
  sep:     color --border
  current: color base-content / weight 600

.avatar-btn
  size:      34×34px
  radius:    --r-full
  bg:        linear-gradient(135deg, navy-400, rosa-500)
  border:    2px solid navy-100 (light) / navy-700 (dark)
  color:     white / 700 / 13px
```

---

## Chips y badges

```
padding:  3px 8px
radius:   --r-full (9999px)
font:     11px/600 (sin uppercase — eso es para caption)
gap:      4px entre ícono y texto

chip-info:    bg info-bg    / color info-text
chip-success: bg success-bg / color success-text
chip-warning: bg warning-bg / color warning-text
chip-error:   bg error-bg   / color error-text
```

---

## Iconografía

Estilo único: **outline, stroke-width 1.8, caps y joins redondeados**.  
Librería recomendada: Lucide Icons o Heroicons (variante outline).

```
nav-item:       15×15px
card-icon:      16×16px
botones:        14×14px
empty-state:    18–24px decorativo
topbar:         16×16px
```

Nunca mezclar íconos filled con outline en el mismo contexto. El sistema es 100% outline.
