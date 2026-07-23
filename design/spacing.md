# Espaciado, Radios y Sombras — Soffiweb Design System

> Leer este archivo cuando: ajustes padding o gaps de un componente, elijas el radio de borde correcto, determines el nivel de sombra, o definas la altura de un elemento interactivo.

---

## Sistema de espaciado

Base: **4px**. Cada token es un múltiplo entero de 4px (con excepción de `--sp-xxs` = 2px para micro-gaps).

| Token CSS | Valor | Uso principal |
|---|---|---|
| `--sp-xxs` | 2px | Micro-gaps: espacio entre ícono y texto dentro de un badge inline |
| `--sp-xs` | 4px | Gap entre elementos inline — ícono + label en botones |
| `--sp-sm` | 8px | Gap entre campos de formulario en fila, separación de nav-items |
| `--sp-md` | 12px | Padding interno de chips, badges, filas de totales |
| `--sp-base` | 16px | Padding horizontal de inputs, gap entre cards en grid |
| `--sp-lg` | 24px | Padding de card-header, gap entre secciones del formulario |
| `--sp-xl` | 32px | Padding de card-body, padding horizontal del área de contenido |
| `--sp-xxl` | 48px | Padding bottom de páginas, separación entre bloques mayores |
| `--sp-section` | 64px | Separación entre secciones de una landing o página editorial |

**Regla de consistencia:** Los componentes del mismo nivel de jerarquía usan el mismo token. Un card-header usa siempre --sp-lg (24px) — nunca 20px, nunca 28px. La consistencia del espaciado es lo que hace que la UI se vea diseñada y no improvisada.

### Guía rápida de espaciado

```
Dentro de un badge o chip              → --sp-md (12px) padding H, --sp-xs (4px) padding V
Gap ícono + label en botón             → --sp-xs (4px)
Gap entre campos de un form-row        → --sp-sm (8px)
Padding de un input                    → 9px 12px
Padding de card-header                 → --sp-lg --sp-xl (24px 32px)
Padding de card-body                   → --sp-xl (32px)
Gap entre cards en grid                → --sp-base (16px)
Gap entre secciones del form           → --sp-lg (24px)
Padding del content area               → --sp-xl (32px)
Padding bottom de página               → --sp-xxl (48px)
```

---

## Bordes redondeados

| Token CSS | Valor | Uso |
|---|---|---|
| `--r-xs` | 2px | Barras de progreso, separadores con relleno |
| `--r-sm` | 4px | Badges compactos, íconos de estado pequeños |
| `--r-md` | 6px | Card-icon (cuadrado 32px con ícono), elementos de lista |
| `--r-lg` | 10px | Inputs de formulario, botones estándar |
| `--r-xl` | 14px | Cards principales, paneles, modales internos |
| `--r-2xl` | 20px | Modales completos, overlays, drawers |
| `--r-full` | 9999px | Pills, chips, badges redondos, avatares, toggles |

### Guía rápida de radios

```
Badge / chip / avatar / toggle  → --r-full (9999px)
Input / select / button         → --r-lg (10px)
Card-icon (cuadrado 32px)       → --r-md (6px)
Card / panel                    → --r-xl (14px)
Modal completo                  → --r-2xl (20px)
Barra de progreso               → --r-xs (2px)
```

---

## Elevación y sombras

Soffiweb usa sombras teñidas en navy en light mode.

| Token CSS | Valor | Uso |
|---|---|---|
| `--shadow-none` | sin sombra, solo borde 0.5px | Body, banners, elementos estáticos |
| `--shadow-xs` | `0 1px 2px rgba(21,43,116,0.06)` | Topbar |
| `--shadow-sm` | `0 1px 3px rgba(21,43,116,0.08), 0 1px 2px rgba(21,43,116,0.06)` | Cards, paneles |
| `--shadow-md` | `0 4px 6px rgba(21,43,116,0.07), 0 2px 4px rgba(21,43,116,0.06)` | Dropdowns, tooltips |
| `--shadow-lg` | `0 10px 15px rgba(21,43,116,0.08), 0 4px 6px rgba(21,43,116,0.05)` | Modales, drawers |
| `--shadow-focus` | `0 0 0 3px rgba(61,95,196,0.18)` | Focus ring en inputs y botones |

**Dark mode:** Usar `rgba(0,0,0,0.4)` en lugar de sombras navy. La jerarquía visual en dark la dan las capas de base-100/200/300, no las sombras.

---

## Alturas de componentes interactivos

| Token CSS | Valor | Uso |
|---|---|---|
| `--h-xs` | 24px | Chips, badges pequeños |
| `--h-sm` | 32px | Botones compactos (icon-only, btn-sm) |
| `--h-md` | 36px | Inputs de formulario estándar, botones normales |
| `--h-lg` | 48px | Botón CTA principal (Emitir factura, Guardar) |
| `--h-topbar` | 60px | Barra de navegación superior |
| `--w-sidebar` | 220px expandido / 56px colapsado | Sidebar lateral |

---

## Animaciones

| Tipo | Duración | Easing | Uso |
|---|---|---|---|
| Entrada de card | 250ms | ease | fadeInUp al cargar página |
| Stagger de cards | +50ms por card | ease | Delay incremental en carga |
| Hover de botón | 150ms | ease | translateY(-1px) + sombra |
| Focus de input | 150ms | ease | border-color + box-shadow |
| Transición de nav | 150ms | ease | color + background |

**Regla:** Con `prefers-reduced-motion`, eliminar fadeInUp y translateY. Mantener siempre el focus ring.
