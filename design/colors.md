# Colores — Soffiweb Design System

> Leer este archivo cuando: cambies colores, ajustes el tema light/dark, revises contraste, o definas tokens semánticos.

---

## Origen — colores extraídos del logo

Todos los tokens de la paleta derivan de estos cinco colores del logo:

| Rol en el logo | Hex | Descripción |
|---|---|---|
| Azul marino oscuro | `#1e3478` | Cuerpo exterior del logo |
| Azul medio | `#3d5fc4` | Cuerpo interior, gradiente alto |
| Azul claro | `#8fb3d9` | Gradiente medio del logo |
| Azul hielo | `#d4e8f5` | Gradiente bajo, casi blanco |
| Rosa Soffi | `#c4527a` | Trazo de identidad del logo |

---

## Rampa Navy — Primary

Color de acción principal. Botones CTA, enlaces interactivos, estados activos, cabeceras de tabla, sidebar.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#eef1fb` | Fondos de hover, card-icon-navy light, tint suave |
| 100 | `#ccd4f1` | Bordes suaves en light, texto muted sobre navy |
| 200 | `#9aaae3` | Texto primary en dark mode, íconos sobre oscuro |
| 300 | `#6880d4` | **`--color-primary` en dark mode**, botones en oscuro |
| 400 | `#3d5fc4` | Azul medio — decorativo, nunca CTA |
| 500 | `#2a4aaa` | Intermedio |
| 600 | `#1e3890` | Borde de focus ring en light |
| 700 | `#152b74` | **`--color-primary` en light mode**, botones en claro |
| 800 | `#0e1f55` | Cabeceras de tabla, totals-final, sidebar activo |
| 900 | `#070f2e` | Fondo sidebar en light, tonos más profundos |

**Reglas:**
- Navy-700 es el único tono que actúa como CTA en light. Navy-300 lo reemplaza en dark.
- Nunca usar navy-400 o navy-500 como botón — su contraste es insuficiente en ambos modos.
- Contraste navy-700 sobre blanco: **8.4:1 (AAA)**.
- Contraste navy-300 sobre base-100 dark: **4.9:1 (AA)**.

---

## Rampa Rosa Soffi — Accent

Acento de identidad de marca. Botones de acción secundaria, badges de énfasis, campo requerido (*), trazo del logo.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#fdf0f4` | Fondo de nota de campos requeridos en light |
| 100 | `#f7d0de` | Borde de nota requerida en light |
| 200 | `#edaac2` | Decorativo suave |
| 300 | `#e07fa4` | Accent hover en dark mode |
| 400 | `#d05c88` | **`--color-accent` en dark mode** |
| 500 | `#c4527a` | **`--color-accent` en light mode** — extraído exacto del logo |
| 600 | `#a03560` | Hover del accent en light |
| 700 | `#7d1f46` | Texto rosa sobre fondos muy claros (AAA) |
| 800 | `#590e30` | Decorativo oscuro |
| 900 | `#36001b` | Fondos de nota requerida en dark |

**Reglas:**
- Rosa-500 es el acento de identidad, **no un color de error**. Nunca usarlo para mensajes de validación negativa.
- Usos válidos: botón "Nueva factura", "Agregar detalle", asterisco (*) de campo requerido, íconos de módulo de Citas médicas.
- Contraste texto blanco sobre rosa-500: **4.6:1 (AA)**.

---

## Rampa Steel — Secondary / Decorativo

Azul medio extraído del gradiente del logo. **Solo decorativo** — nunca como elemento interactivo.

| Stop | Hex | Uso típico |
|---|---|---|
| 50 | `#f0f4fb` | Fondos de card-icon-steel en light |
| 100 | `#d8e4f4` | Bordes suaves decorativos |
| 200 | `#b8cee9` | Separadores en dark |
| 300 | `#8fb3d9` | Subtítulos en sidebar, texto de módulo activo |
| 400 | `#6a97c5` | Íconos decorativos |
| 500 | `#4a7cc7` | Acento steel intenso — solo ilustrativo |

**Regla:** Nunca usar steel como CTA ni como estado activo. Un usuario podría confundirlo con el primary navy y perder la jerarquía de acciones.

---

## Superficies — Light Mode

| Token CSS | Hex | Descripción |
|---|---|---|
| `--base-100` | `#fdfdff` | Canvas principal — blanco con tinte azul muy suave |
| `--base-200` | `#f7f9fe` | Fondo de página (body background) |
| `--base-300` | `#eef1f8` | Separadores internos de cards, hover de filas |
| `--base-content` | `#303237` | Texto principal sobre fondos claros |
| `--muted` | `#64748b` | Texto secundario, placeholders, hints |
| `--border` | `#dde3f0` | Borde estándar de inputs, cards, tablas |

---

## Superficies — Dark Mode

| Token CSS | Hex | Descripción |
|---|---|---|
| `--base-100` | `#1e2535` | Superficie de cards y formularios |
| `--base-200` | `#151c2c` | Fondo de página (body background) |
| `--base-300` | `#252f42` | Separadores internos, hover de filas |
| `--base-content` | `#e2e8f4` | Texto principal sobre fondos oscuros |
| `--muted` | `#7a8aaa` | Texto secundario, placeholders |
| `--border` | `#2e3d58` | Borde estándar |
| sidebar-bg | `#0d1424` | Fondo del sidebar — más oscuro que el body para jerarquía |

**Nota de jerarquía dark:** El sidebar (`#0d1424`) es más oscuro que el body (`#151c2c`), que es más oscuro que las cards (`#1e2535`). Esta profundidad de 3 capas reemplaza las sombras, que no se perciben bien sobre fondos oscuros.

---

## Colores semánticos

### Light Mode

| Token | Background | Texto | Uso |
|---|---|---|---|
| `--color-success` | `#eaf3de` | `#3b6d11` | Facturas pagadas, verificaciones OK, stock disponible |
| `--color-warning` | `#faeeda` | `#854f0b` | Alertas, vencimientos próximos, stock bajo |
| `--color-error` | `#fcebeb` | `#a32d2d` | Errores de validación, acceso denegado, campos inválidos |
| `--color-info` | `#eef1fb` | `#152b74` | Información neutral, chips de paso, tooltips |

### Dark Mode

| Token | Background | Texto | Uso |
|---|---|---|---|
| `--color-success` | `#0d2410` | `#86efac` | Mismo rol |
| `--color-warning` | `#2a1f05` | `#fcd34d` | Mismo rol |
| `--color-error` | `#2a0a0a` | `#fca5a5` | Mismo rol |
| `--color-info` | `#0e1f55` | `#93c5fd` | Mismo rol |

---

## Tokens de componentes — resumen rápido

```css
/* Light mode */
--color-primary:       #152b74;  /* navy-700 */
--color-primary-hover: #0e1f55;  /* navy-800 */
--color-primary-light: #eef1fb;  /* navy-50  */
--color-accent:        #c4527a;  /* rosa-500 */
--color-accent-hover:  #a03560;  /* rosa-600 */

/* Dark mode */
--color-primary:       #6880d4;  /* navy-300 */
--color-primary-hover: #9aaae3;  /* navy-200 */
--color-primary-light: #0e1f55;  /* navy-800 */
--color-accent:        #d05c88;  /* rosa-400 */
--color-accent-hover:  #e07fa4;  /* rosa-300 */
```

---

## Reglas globales

1. **Error ≠ Accent** — El error usa rojo (`#E11D48`), completamente distinto del rosa-500. No hay ambigüedad posible.
2. **Steel nunca es primary** — El steel es decorativo. El navy es el único color de acción.
3. **Sombras en dark** — En dark mode, reemplazar sombras navy por `rgba(0,0,0,0.4)` porque los fondos oscuros absorben el tinte navy y la sombra no se percibe.
4. **Contraste mínimo** — Todo texto interactivo debe cumplir WCAG AA (4.5:1). Los CTAs primarios cumplen AAA (7:1+).
