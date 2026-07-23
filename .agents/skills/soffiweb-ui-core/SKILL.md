---
name: soffiweb-ui-core
description: "Skill base del sistema de diseño Soffiweb. Contiene tokens, topbar tenant/periodo, menu de usuario, sidebar accesible/responsive, componentes visuales y JS compartido. NO contiene codigo especifico de Bootstrap ni Tailwind. Invocar junto a soffiweb-ui-bootstrap en Laravel + Bootstrap o soffiweb-ui-modern en frameworks modernos. Trigger: cualquier tarea de UI/diseno que requiera componentes visuales o tokens Soffiweb."
license: MIT
metadata:
  author: soffiweb
  version: "1.1"
---

# Soffiweb UI — Core (tokens y componentes)

## Regla maestra

Toda interfaz usa ÚNICAMENTE los tokens definidos aquí. Sin colores hex inventados. Sin fuentes distintas a Inter. Sin gradientes decorativos. Consistencia siempre — sin importar el framework.

No renderizar métricas, totales o cards-resumen por defecto. Solo incluirlas si el usuario las pide explícitamente o si el requerimiento funcional las exige.

---

## Quick Reference: Paleta de colores

| Token            | Modo claro                      | Modo oscuro                       |
|------------------|---------------------------------|-----------------------------------|
| **bg**           | `#fdfdff`                       | `#2a3040`                         |
| **surface**      | `#edf1fb`                       | `#222736`                         |
| **primary**      | `#28449a`                       | `#7da4e0`                         |
| **secondary**    | `#81a2cf`                       | `#9dbde8`                         |
| **accent**       | `#fa75ad`                       | `#fb8dc0`                         |
| **neutral**      | `#303237`                       | `#e8ecf4`                         |
| **neutral-2**    | `#6b7280`                       | `#9ca3af`                         |
| **success**      | `oklch(69% 0.17 162.48)`        | `oklch(76% 0.177 163.223)`        |
| **warning**      | `oklch(76% 0.188 70.08)`        | `oklch(82% 0.189 84.429)`         |
| **error**        | `#E11D48`                       | `oklch(71% 0.194 13.428)`         |
| **info**         | `oklch(68% 0.169 237.323)`      | `oklch(74% 0.16 232.661)`         |
| **btn-text**     | `#fdfdff`                       | `#1a1f2e`                         |
| **sidebar-bg**   | `#1e2330`                       | `#111520`                         |
| **th-bg**        | `#e8eef8`                       | `#1a2340`                         |
| **th-color**     | `#28449a`                       | `#7da4e0`                         |

---

## Tipografía

- **Font**: Inter 400/500/700 desde Google Fonts
- **Escala**: xs=11px · sm=12px · base=13px · md=14px · lg=16px
- **Iconos**: Font Awesome 6 (`fa-solid`, `fa-regular`) — NUNCA emoji
- **Datos**: `font-variant-numeric: tabular-nums` en números

---

## Tokens de escala

| Token       | Valor                            |
|-------------|----------------------------------|
| radius-sm   | `.25rem`                         |
| radius-md   | `.5rem`                          |
| radius-lg   | `1rem`                           |
| radius-pill | `25px`                           |
| shadow-sm   | `0 1px 2px rgba(0,0,0,.05)`     |
| shadow-md   | `0 4px 6px rgba(0,0,0,.10)`     |
| shadow-lg   | `0 10px 15px rgba(0,0,0,.15)`   |

---

## Reglas de zona (inviolables)

| Zona                | Regla                                                            |
|---------------------|------------------------------------------------------------------|
| Sidebar bg          | Siempre `#1e2330` — neutro oscuro — NUNCA color de marca        |
| Sidebar link activo | Borde izquierdo `secondary`, fondo rgba blanco                   |
| Sidebar submenus    | Encabezado expandible con botón, iconos y grupo visual contenido |
| Topbar              | Fondo `surface`/`bg` — sin color de marca — borde 1px fino      |
| Marca topbar        | Lado izquierdo con logo de marca; no texto plano si existe logo |
| Contexto topbar     | Aplicación + empresa grande + período activo; NUNCA badge        |
| Cuenta topbar       | Avatar/nombre abre menú con rol y cerrar sesión                  |
| Responsive          | Sidebar tipo drawer en móvil con backdrop y cierre por `Escape` |
| Header tabla        | Fondo `#e8eef8`, texto `primary`, borde inferior 2px primary     |
| Botones             | Texto siempre `btn-text` — NUNCA `#fff` hardcodeado             |
| Colores vivos       | Solo en componentes internos: badges, botones, métricas, progress|
| Gradientes          | Prohibidos — excepto banner de perfil                            |

---

## Prohibido (siempre, en cualquier framework)

| ❌ Nunca                          | ✅ Siempre                              |
|-----------------------------------|-----------------------------------------|
| `color:#28449a` hardcodeado       | `color:var(--p)`                        |
| `color:#fff` en botones           | `color:var(--btn-text)`                 |
| Emoji como iconos (`😀 ✌️ 🔥`)    | Font Awesome 6: `<i class="fa-solid fa-...">` |
| Sidebar con color de marca        | Sidebar siempre `#1e2330`               |
| Texto plano como marca topbar     | Logo de marca en el bloque izquierdo    |
| Empresa/período dentro de badge   | Texto visible jerarquizado en topbar    |
| `div onclick` para submenús       | `<button>` con `aria-expanded`          |
| Header tabla gris plano           | `var(--th-bg)` + `var(--th-color)`      |
| Gradientes decorativos            | Fondos planos — gradiente solo en banner |
| Múltiples fuentes                 | Solo Inter 400/500/700                  |

---

## Estructura de archivos en esta skill

- **`tokens.md`** → Paleta completa, tipografía, CSS variables en :root y dark mode
- **`components.md`** → Todas las clases CSS (botones, tablas, modales, cards, badges, etc.)
- **`layout.md`** → Estructura base con topbar tenant, menú de cuenta y sidebar accesible/responsive
- **`js.md`** → JS compartido (tema, sidebar/submenus, menú de usuario y select buscador)

---

## Dependencias

Esta skill define el **sistema de diseño puro**. Siempre úsala junto a:

- **`soffiweb-ui-bootstrap`** si usas Laravel + Bootstrap 4 + Blade
- **`soffiweb-ui-modern`** si usas un framework moderno (React, Vue, Svelte, etc.)

**Nunca uses soffiweb-ui-core sola** — necesita la integración específica del framework.
