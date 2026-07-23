---
version: 1.0
name: Soffiweb
description: Sistema de diseño para los productos SaaS de Soffiweb — facturación, contabilidad, citas médicas, nóminas y bodega. Diseño profesional orientado a productividad, con doble tema (light/dark) basado en un navy profundo extraído del logo y un rosa Soffi como acento de identidad. Tipografía en dos familias — Plus Jakarta Sans para UI y DM Mono para datos numéricos.
---

## Descripción general

Soffiweb es una plataforma SaaS de gestión empresarial para pequeñas y medianas empresas latinoamericanas. Su identidad visual nace directamente del logo: un cuerpo en **azul navy** con gradiente hacia azul claro, cruzado por un trazo en **rosa Soffi**. Estos dos colores — navy y rosa — son la base de todo el sistema.

El sistema tiene dos temas completos: **light** (fondo casi blanco con tinte azul muy suave) y **dark** (fondo navy profundo). Ambos comparten la misma paleta de rampas; solo cambian qué stop de cada rampa se asigna a cada token semántico.

**Principios de diseño:**
- Densidad de información controlada — los formularios de facturación, nóminas y citas médicas manejan muchos campos; el diseño no puede ser aireado en exceso.
- Los números y códigos siempre en fuente monoespaciada (`DM Mono`) para alineación y lectura rápida.
- El color primario nunca se usa como decoración — solo en acciones, estados activos y elementos de marca.
- Dark mode es ciudadano de primera clase, no un afterthought — cada token tiene su variante oscura definida explícitamente.

---

## Colores

### Logo — colores fuente

Todos los tokens de la paleta derivan de estos cinco colores extraídos directamente del logo:

| Rol en el logo | Hex | Descripción |
|---|---|---|
| Azul marino oscuro | `#1e3478` | Cuerpo exterior del logo |
| Azul medio | `#3d5fc4` | Cuerpo interior, gradiente alto |
| Azul claro | `#8fb3d9` | Gradiente medio del logo |
| Azul hielo | `#d4e8f5` | Gradiente bajo, casi blanco |
| Rosa Soffi | `#c4527a` | Trazo de identidad del logo |

---

### Rampa Navy (Primary)

Color de acción principal. Botones CTA, enlaces interactivos, estados activos, cabeceras de tabla, sidebar.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#eef1fb` | Fondos de hover, card-icon-navy light |
| 100 | `#ccd4f1` | Bordes suaves en light, texto muted sobre navy |
| 200 | `#9aaae3` | Texto primary en dark mode, íconos sobre oscuro |
| 300 | `#6880d4` | **`--color-primary` en dark mode**, botones en oscuro |
| 400 | `#3d5fc4` | Azul medio — decorativo, nunca CTA |
| 500 | `#2a4aaa` | Intermedio |
| 600 | `#1e3890` | Borde de focus ring en light |
| 700 | `#152b74` | **`--color-primary` en light mode**, botones en claro |
| 800 | `#0e1f55` | Cabeceras de tabla, totals-final, sidebar activo |
| 900 | `#070f2e` | Fondo sidebar en light, tonos más profundos |

**Regla de uso:** El navy-700 es el único tono que actúa como CTA en light mode. El navy-300 lo reemplaza en dark mode. Nunca usar navy-400 o navy-500 como botón — su contraste es insuficiente en ambos modos.

---

### Rampa Rosa Soffi (Accent)

Acento de identidad de marca. Botones de acción secundaria, badges de estado, elementos de énfasis, trazo del logo.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#fdf0f4` | Fondo de nota de campos requeridos en light |
| 100 | `#f7d0de` | Borde de nota requerida en light |
| 200 | `#edaac2` | Decorativo suave |
| 300 | `#e07fa4` | Accent en dark mode sobre fondos oscuros |
| 400 | `#d05c88` | **`--color-accent` en dark mode** |
| 500 | `#c4527a` | **`--color-accent` en light mode** — extraído exacto del logo |
| 600 | `#a03560` | Hover del accent en light |
| 700 | `#7d1f46` | Texto rosa sobre fondos muy claros (AAA) |
| 800 | `#590e30` | Decorativo oscuro |
| 900 | `#36001b` | Fondos de nota requerida en dark |

**Regla de uso:** El rosa-500 es el acento de identidad, no un color de error. Nunca usarlo para mensajes de validación negativa — eso es responsabilidad del token `--color-error`. Se usa en: botón "Nueva factura", "Agregar detalle", campo requerido (*), íconos de identidad.

---

### Rampa Steel (Secondary / Decorativo)

Azul medio extraído del gradiente del logo. **Solo decorativo** — nunca como elemento interactivo para no confundirse con el primary.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#f0f4fb` | Fondos de card-icon-steel en light |
| 100 | `#d8e4f4` | Bordes suaves decorativos |
| 200 | `#b8cee9` | Separadores en dark |
| 300 | `#8fb3d9` | Subtítulos en sidebar, texto de módulo activo |
| 400 | `#6a97c5` | Íconos decorativos |
| 500 | `#4a7cc7` | Acento steel intenso — solo ilustrativo |

---

### Superficies — Light Mode

| Token | Hex | Descripción |
|---|---|---|
| `--base-100` | `#fdfdff` | Canvas principal de página — blanco con tinte azul muy suave |
| `--base-200` | `#f7f9fe` | Fondo de página (body background) |
| `--base-300` | `#eef1f8` | Separadores internos de cards, hover de filas |
| `--base-content` | `#303237` | Texto principal sobre fondos claros |
| `--muted` | `#64748b` | Texto secundario, placeholders, hints |
| `--border` | `#dde3f0` | Borde estándar de inputs, cards, tablas |

---

### Superficies — Dark Mode

| Token | Hex | Descripción |
|---|---|---|
| `--base-100` | `#1e2535` | Superficie de cards y formularios |
| `--base-200` | `#151c2c` | Fondo de página (body background) |
| `--base-300` | `#252f42` | Separadores internos, hover de filas |
| `--base-content` | `#e2e8f4` | Texto principal sobre fondos oscuros |
| `--muted` | `#7a8aaa` | Texto secundario, placeholders |
| `--border` | `#2e3d58` | Borde estándar |
| sidebar bg | `#0d1424` | Fondo del sidebar — más oscuro que el body para jerarquía |

---

### Colores semánticos

#### Light Mode
| Token | Bg | Texto | Uso |
|---|---|---|---|
| success | `#eaf3de` | `#3b6d11` | Facturas pagadas, verificaciones OK |
| warning | `#faeeda` | `#854f0b` | Alertas, vencimientos próximos |
| error | `#fcebeb` | `#a32d2d` | Errores de validación, acceso denegado |
| info | `#eef1fb` | `#152b74` | Información neutral, chips de paso |

#### Dark Mode
| Token | Bg | Texto | Uso |
|---|---|---|---|
| success | `#0d2410` | `#86efac` | Mismo rol, fondos y textos invertidos |
| warning | `#2a1f05` | `#fcd34d` | Mismo rol |
| error | `#2a0a0a` | `#fca5a5` | Mismo rol |
| info | `#0e1f55` | `#93c5fd` | Mismo rol |

**Regla global de color:**
- El error usa rojo (`#E11D48` / oklch equivalente) — completamente distinto del rosa-500 de accent. No hay ambigüedad posible.
- El navy se usa para `--color-primary` porque es el color de acción. El steel nunca actúa como primary.
- En dark mode, los botones primarios pasan de navy-700 a navy-300 para mantener contraste WCAG AA (4.7:1 mínimo).

---

## Tipografía

### Familias

| Familia | Fuente | Fallback | Rol |
|---|---|---|---|
| Body / UI | `Plus Jakarta Sans` | `system-ui, sans-serif` | Todo texto de interfaz — labels, botones, títulos, cuerpo |
| Mono | `DM Mono` | `monospace` | Números, códigos de factura, montos, IDs, campos de código |

**Principio de separación:** La fuente monoespaciada solo aparece cuando el contenido es numérico, técnico o requiere alineación de columnas. Todo lo demás — incluso números como "Paso 1 de 3" — usa Plus Jakarta Sans.

---

### Escala tipográfica

| Token | Tamaño | Peso | Letter-spacing | Line-height | Uso |
|---|---|---|---|---|---|
| `display-xl` | 28px | 700 | -0.5px | 1.2 | Títulos de página principal (h1) |
| `display-lg` | 22px | 700 | -0.4px | 1.2 | Títulos de sección grande |
| `display-md` | 18px | 700 | -0.3px | 1.3 | Títulos de card / sección |
| `title-lg` | 16px | 700 | 0 | 1.4 | Títulos de card-header |
| `title-md` | 14px | 600 | 0 | 1.4 | Labels de campo, nombres de columna |
| `body-lg` | 16px | 400 | 0 | 1.6 | Texto de ayuda, descripciones largas |
| `body-md` | 13px | 400 | 0 | 1.5 | Contenido de tabla, texto de UI, meta |
| `label` | 12px | 600 | 0.02em | 1.4 | Etiquetas de formulario, subtítulos de card |
| `caption` | 11px | 500 | 0.06em + uppercase | 1.3 | Encabezados de sección (uppercase), chips de paso |
| `code-lg` | 16px | 500 | 0.02em | 1.4 | Totales destacados, montos principales (DM Mono) |
| `code-md` | 13px | 400 | 0.02em | 1.5 | Celdas de tabla numéricas, subtotales (DM Mono) |
| `amount-display` | 28px | 700 | -0.5px | 1.2 | Total a cobrar, monto destacado en dashboard (DM Mono) |

**Pesos usados:** 400 (regular), 500 (medium), 600 (semibold), 700 (bold). Nunca usar pesos distintos a estos cuatro — el sistema está calibrado para ellos.

**Nota de accesibilidad:** El tamaño mínimo para texto interactivo es 11px. Cualquier texto por debajo de ese tamaño es puramente decorativo y no debe contener información crítica.

---

## Espaciado

Sistema basado en múltiplos de 4px. La unidad base es 4px; cada token es un múltiplo entero.

| Token | Valor | Uso principal |
|---|---|---|
| `--sp-xxs` | 2px | Micro-gaps: espacio entre ícono y texto dentro de un badge inline |
| `--sp-xs` | 4px | Gap entre elementos inline — ícono + label en botones |
| `--sp-sm` | 8px | Gap entre campos de formulario en fila, separación de nav items |
| `--sp-md` | 12px | Padding interno de chips, badges, filas de totales |
| `--sp-base` | 16px | Padding horizontal de inputs, gap entre cards en grid |
| `--sp-lg` | 24px | Padding de card-header, gap entre secciones del formulario |
| `--sp-xl` | 32px | Padding de card-body, padding horizontal del área de contenido |
| `--sp-xxl` | 48px | Padding bottom de páginas, separación entre bloques mayores |
| `--sp-section` | 64px | Separación entre secciones de una landing o página editorial |

**Regla de composición:** Los componentes del mismo nivel de jerarquía usan el mismo token de espaciado. Un card-header usa siempre `--sp-lg` (24px) de padding — nunca 20px, nunca 28px. La consistencia del espaciado es lo que hace que la UI se vea "diseñada" y no improvisada.

---

## Bordes redondeados

| Token | Valor | Uso |
|---|---|---|
| `--r-xs` | 2px | Elementos casi rectos — barras de progreso, separadores con relleno |
| `--r-sm` | 4px | Badges compactos, íconos de estado pequeños |
| `--r-md` | 6px | Card-icon (cuadrado de 32px con ícono), elementos de lista |
| `--r-lg` | 10px | Inputs de formulario, botones estándar |
| `--r-xl` | 14px | Cards principales, paneles, modales internos |
| `--r-2xl` | 20px | Modales completos, overlays, drawers |
| `--r-full` | 9999px | Pills, chips, badges redondos, avatares, toggles |

**Nota de personalidad:** El sistema usa bordes moderadamente redondeados — no agresivamente suaves (como apps de consumo) ni rectos (como herramientas enterprise antiguas). El radio de 10px en inputs y 14px en cards da una sensación profesional pero amigable, adecuada para usuarios que pasan horas en la herramienta.

---

## Elevación / Sombras

Soffiweb usa un sistema de sombras teñidas en navy (no en negro puro) para reforzar la coherencia de la paleta.

| Token | Valor CSS | Uso |
|---|---|---|
| `--shadow-none` | solo borde 0.5px | Body, banners, elementos estáticos sin elevación |
| `--shadow-xs` | `0 1px 2px rgba(21,43,116,0.06)` | Topbar, separaciones sutiles |
| `--shadow-sm` | `0 1px 3px rgba(21,43,116,0.08), 0 1px 2px rgba(21,43,116,0.06)` | Cards, paneles de contenido |
| `--shadow-md` | `0 4px 6px rgba(21,43,116,0.07), 0 2px 4px rgba(21,43,116,0.06)` | Dropdowns, menús flotantes, tooltips |
| `--shadow-lg` | `0 10px 15px rgba(21,43,116,0.08), 0 4px 6px rgba(21,43,116,0.05)` | Modales, drawers, popovers |
| `--shadow-focus` | `0 0 0 3px rgba(61,95,196,0.18)` | Ring de foco en inputs y botones (accesibilidad) |

**Dark mode:** Las sombras en dark mode usan opacidad de negro puro (no navy) porque los fondos ya son oscuros y las sombras teñidas no se perciben. Reemplazar con `rgba(0,0,0,0.4)` sobre fondos oscuros.

---

## Componentes

### Botones

#### button-primary
El CTA principal — guardar, emitir, confirmar.

```
background:   --color-primary (navy-700 en light / navy-300 en dark)
color:        white
font:         title-md (14px / 600) o body-lg (16px / 500) según tamaño
radius:       --r-lg (10px)
height:       --h-lg (48px) para CTA / --h-md (36px) para acciones secundarias
padding:      0 16px
shadow:       0 2px 4px rgba(21,43,116,0.25) en light / 0 2px 8px rgba(104,128,212,0.3) en dark
hover:        navy-800 en light / navy-200 en dark + translateY(-1px)
active:       translateY(0)
```

#### button-accent
Acción creativa o de incorporación — nueva factura, agregar, importar.

```
background:   --color-accent (rosa-500 en light / rosa-400 en dark)
color:        white
radius:       --r-lg (10px)
height:       --h-md (36px) o --h-lg (48px)
shadow:       0 2px 4px rgba(196,82,122,0.25) en light
hover:        rosa-600 en light / rosa-300 en dark + translateY(-1px)
```

#### button-ghost
Acciones terciarias — cancelar, volver, exportar.

```
background:   white en light / base-300 en dark
color:        base-content
border:       1px solid --border
radius:       --r-lg (10px)
height:       --h-md (36px)
hover:        base-200 en light / #2e3d58 en dark, border-color navy-200
```

#### button-icon
Botón de solo ícono — notificaciones, acciones de fila en tabla.

```
background:   white en light / base-300 en dark
border:       1px solid --border
radius:       --r-lg (10px)
size:         34×34px
```

---

### Inputs y formularios

#### text-input / select

```
background:   white en light / base-200 en dark
border:       1px solid --border
radius:       --r-lg (10px)
padding:      9px 12px
height:       --h-md (36px) — compacto para densidad de datos
font:         body-md (13px / 400)
color:        base-content
placeholder:  muted

:focus →
  border-color: navy-400 en light / navy-300 en dark
  box-shadow:   --shadow-focus

:hover (sin focus) →
  border-color: navy-200 en light / navy-400 en dark

:disabled →
  background: base-200 en light / #111926 en dark
  color: muted
  border-style: dashed
  cursor: not-allowed
```

#### input con prefijo (moneda)
Inputs de precio, monto, descuento llevan un prefijo `$` alineado a la izquierda dentro del campo:

```
.input-prefix: posición absoluta, left 10px, font label (12px/600), color muted
.form-control con prefijo: padding-left 28px
```

#### form-label

```
font:   label (12px / 600)
color:  navy-700 en light / navy-200 en dark
margin-bottom: --sp-xs (4px o 6px)
```

El asterisco de campo requerido usa `--color-accent` (rosa-500 / rosa-300) — nunca el color de error.

---

### Cards

#### card (contenedor principal)

```
background:   base-100
border:       1px solid --border
radius:       --r-xl (14px)
shadow:       --shadow-sm en light / 0 1px 3px rgba(0,0,0,0.4) en dark
```

#### card-header

```
padding:        --sp-lg --sp-xl (24px 32px)
border-bottom:  1px solid base-300
display:        flex, space-between, align center
```

#### card-icon (cuadrado de 32px con ícono del módulo)

```
size:     32×32px
radius:   --r-md (6px)
ícono:    16×16px SVG, stroke-width 1.8

variante navy:  bg #eef1fb / color navy-600  (light)
               bg #0e1f55 / color navy-300  (dark)
variante rosa:  bg #fdf0f4 / color rosa-600  (light)
               bg #2a0d1a / color rosa-300  (dark)
variante steel: bg #f0f4fb / color steel-500 (light)
               bg #0d1e35 / color steel-300  (dark)
```

#### card-body

```
padding: --sp-xl (32px)
```

---

### Tabla de datos

#### thead

```
background:   navy-800 (#0e1f55) en light / #0a1020 en dark
font:         caption (11px / 500 / uppercase / tracking 0.04em)
color:        rgba(255,255,255,0.75) en light / navy-200 en dark
padding th:   11px 14px
```

#### tbody

```
border-bottom: 1px solid base-300 por fila
hover row:     navy-50 en light / #1a2440 en dark
padding td:    11px 14px
font:          body-md (13px)
números:       code-md (DM Mono, 13px, align-right)
```

#### empty state (tabla vacía)

```
ícono:    40×40px círculo base-200/base-300, SVG 18px muted
título:   title-md, navy-700 en light / navy-200 en dark
subtítulo: body-md muted
padding:  --sp-xxl vertical
```

---

### Panel de totales

Componente específico de facturación, nóminas y cuentas por cobrar.

```
card base con overflow hidden

.totals-header:
  background:   navy-50 (light) / #0a1020 (dark)
  padding:      --sp-md --sp-lg
  font:         caption (11px / 700 / uppercase)
  color:        navy-700 (light) / navy-200 (dark)
  border-bottom: 1px solid --border

.totals-row:
  padding:      10px --sp-lg
  display:      flex space-between
  font label:   body-md muted
  font value:   code-md (DM Mono 13px / 500)
  border-bottom: 1px solid base-300

.totals-row.total-final:
  background:   navy-800 (light) / linear-gradient(135deg, #0e1f55, #1a0a2e) (dark)
  label font:   body-md / 600 / rgba(255,255,255,0.7) (light) / navy-200 (dark)
  value font:   amount-display (DM Mono 28px / 700 / white)
```

---

### Sidebar de navegación

```
width:       220px expandido / 56px colapsado
background:  navy-900 (#070f2e) en light / #0d1424 en dark
padding:     --sp-lg 0

.sidebar-logo:
  padding:      0 --sp-lg --sp-xl
  border-bottom: 1px solid rgba(255,255,255,0.07)

.sidebar-logo-mark:
  size:     32×32px
  radius:   --r-md (6px)
  bg:       linear-gradient(135deg, navy-400, rosa-500)
  color:    white / 700 / 16px

.nav-group-label:
  font:   10px / 600 / uppercase / tracking 0.08em
  color:  rgba(255,255,255,0.3)
  padding: --sp-sm --sp-lg --sp-xs

.nav-item:
  padding:       9px --sp-lg
  font:          body-md (13px / 500)
  color:         rgba(255,255,255,0.55)
  border-left:   3px solid transparent
  gap:           --sp-sm
  icon:          15×15px
  hover →        bg rgba(255,255,255,0.05), color rgba(255,255,255,0.85)

.nav-item.active:
  bg:            rgba(104,128,212,0.15)
  color:         white
  border-left:   3px solid navy-300 (#6880d4)

.nav-badge:
  bg:      rosa-500
  color:   white
  font:    10px / 700
  radius:  --r-full
  padding: 1px 6px
  margin:  auto left (push to right)
```

---

### Topbar

```
height:       60px
background:   base-100
border-bottom: 1px solid --border
shadow:       --shadow-xs
padding:      0 --sp-xl
display:      flex space-between align-center

.breadcrumb:
  font:   body-md (13px)
  links:  color muted → hover navy-300/navy-700
  sep:    color --border
  current: base-content / 600
```

---

### Chips y badges

#### chip (inline en card-header, tablas)

```
padding:  3px 8px
radius:   --r-full
font:     caption sin uppercase (11px / 600)
gap:      4px (ícono + texto)

chip-info:    bg info-bg / color info-text
chip-success: bg success-bg / color success-text
chip-warning: bg warning-bg / color warning-text
chip-error:   bg error-bg / color error-text
```

---

### Nota de campo requerido

Banner superior de formularios que indica campos obligatorios.

```
background:   rosa-50 / border rosa-100          (light)
background:   #1e0e16 / border #3d1a28          (dark)
padding:      6px 12px
radius:       --r-lg (10px)
font:         body-md (13px) muted
dot:          6px círculo rosa-500 (light) / rosa-400 (dark)
asterisco *:  color rosa-500 / rosa-300
```

---

### Área de agregar ítem (formulario de detalle)

Panel dashed que contiene la fila de campos para agregar productos a una factura.

```
background:   base-200
border:       1px dashed --border
radius:       --r-xl (14px)
padding:      --sp-lg --sp-xl

grid de campos:
  template-columns: 2fr 80px 80px 120px 80px 80px 80px auto
  gap: --sp-sm
  align: end

.detail-col-label:
  font:   11px / 600
  color:  navy-600 (light) / navy-200 (dark)
  mb:     5px
```

---

## Alturas de componentes interactivos

| Token | Valor | Uso |
|---|---|---|
| `--h-xs` | 24px | Chips, badges pequeños |
| `--h-sm` | 32px | Botones compactos, icon-only |
| `--h-md` | 36px | Inputs de formulario, botones estándar |
| `--h-lg` | 48px | Botón CTA principal (Emitir factura, Guardar) |
| `--h-topbar` | 60px | Barra de navegación superior |
| `--w-sidebar` | 220px expandido / 56px colapsado | Sidebar lateral |

---

## Layout y estructura

### Grid de contenido

```
max-width:    1200px (páginas de formulario y dashboard)
padding:      --sp-xl (32px) en escritorio, --sp-lg (24px) en tablet
layout:       sidebar fijo + main scrollable
```

### Breakpoints

| Nombre | Ancho | Cambios clave |
|---|---|---|
| mobile | < 640px | Sidebar colapsa a barra inferior; grids de formulario a 1 columna |
| tablet | 640px – 1024px | Sidebar colapsado (56px); grids de formulario a 2 columnas |
| desktop | > 1024px | Sidebar expandido (220px); grids completos |

### Grids de formulario

```
.form-grid-1: 1fr                           (campo full width)
.form-grid-2: 1fr 1fr
.form-grid-3: 1fr 1fr 1fr
.form-grid-4: 1fr 1fr 1fr 1fr
.gap:         --sp-lg (24px) entre campos
```

### Estructura de página estándar (módulos SaaS)

```
<topbar 60px>
<layout flex>
  <sidebar 220px>
  <main flex-col>
    <content padding xl>
      <page-header>           ← título + acciones
      <required-note>         ← si aplica
      <stack de cards>        ← secciones del formulario/lista
      <totals + notas>        ← bottom row
      <action-footer>         ← Cancelar / Borrador / CTA principal
    </content>
  </main>
</layout>
```

---

## Iconografía

Estilo de ícono: **outline, stroke-width 1.8, esquinas redondeadas**. Se recomienda la librería Lucide Icons o Heroicons en variante outline.

- Tamaño estándar en nav-item: 15×15px
- Tamaño estándar en card-icon: 16×16px
- Tamaño en botones con ícono: 14×14px
- Tamaño decorativo / empty-state: 18×24px

Nunca usar íconos filled junto a íconos outline en el mismo contexto. El sistema es 100% outline.

---

## Animaciones

Principio: las animaciones deben sentirse funcionales, no decorativas. El usuario de un sistema de facturación necesita velocidad, no efectos.

| Tipo | Duración | Easing | Uso |
|---|---|---|---|
| Entrada de card | 250ms | ease | fadeInUp al cargar página |
| Hover de botón | 150ms | ease | translateY(-1px) + shadow |
| Focus de input | 150ms | ease | border-color + box-shadow |
| Transición de nav | 150ms | ease | color + background |
| Stagger de cards | +50ms por card | ease | delay incremental en carga |

**Regla:** `prefers-reduced-motion` debe eliminar fadeInUp y translateY. El focus ring y las transiciones de color son funcionales y se mantienen.

---

## Módulos del sistema

Soffiweb tiene 5 módulos. Todos comparten este sistema de diseño base. Las diferencias por módulo son únicamente de contenido, no de estilos.

| Módulo | Color de card-icon | Componentes característicos |
|---|---|---|
| Facturación | navy | Tabla de detalle, panel de totales con IVA |
| Contabilidad | steel | Tabla de cuentas, gráficos de balance |
| Citas médicas | rosa | Calendario, tarjetas de paciente |
| Nóminas | navy | Tabla de empleados, cálculo de aportes |
| Bodega | steel | Tabla de inventario, control de stock |

Los card-icon de cada módulo usan la variante de color de la tabla anterior para dar identidad visual rápida al módulo activo, sin romper la coherencia del sistema global.

---

## Lo que NO está en este archivo

Los siguientes elementos son decisiones de implementación y pertenecen al **CLAUDE.md** o a los archivos de configuración del proyecto:

- Nombres de clases CSS específicas de un framework (ej. `btn btn-primary` de DaisyUI, `bg-navy-700` de Tailwind)
- Versiones de librerías o frameworks (Tailwind, DaisyUI, Bootstrap, etc.)
- Componentes de framework (ej. `<Button variant="primary">` de shadcn)
- Rutas de archivos, convenciones de nombrado de componentes
- Lógica de negocio de los módulos

El DESIGN.md es agnóstico a tecnología. Si el stack cambia de DaisyUI a Tailwind puro, este archivo no cambia.
