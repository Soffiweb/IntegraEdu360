# Soffiweb Design System

**Versión:** 1.0  
**Descripción:** Sistema de diseño para los productos SaaS de Soffiweb — facturación, contabilidad, citas médicas, nóminas y bodega.

---

## Qué es este sistema

Soffiweb es una plataforma SaaS de gestión empresarial para PyMEs latinoamericanas. Su identidad visual nace directamente del logo: un cuerpo en **azul navy** con gradiente hacia azul claro, cruzado por un trazo en **rosa Soffi**. Estos dos colores son la base de todo el sistema.

El sistema tiene dos temas completos: **light** y **dark**. Ambos comparten las mismas rampas de color — solo cambia qué stop de cada rampa se asigna a cada token semántico.

---

## Principios de diseño

- **Densidad controlada** — los formularios de facturación, nóminas y citas manejan muchos campos; el diseño no puede ser demasiado aireado.
- **Mono para números** — `DM Mono` en todos los montos, códigos e IDs para alineación y lectura rápida.
- **Primario = acción** — el color primario nunca se usa como decoración. Solo en CTAs, estados activos y elementos de marca.
- **Dark mode de primera clase** — cada token tiene su variante oscura definida explícitamente, no derivada.

---

## Archivos del sistema

| Archivo | Contenido | Cuándo leerlo |
|---|---|---|
| `colors.md` | Rampas Navy/Rosa/Steel, tokens de superficie, colores semánticos, reglas de uso | Cambiar colores, ajustar temas, revisar contraste |
| `typography.md` | Familias tipográficas, escala de 12 tokens, reglas de peso y uso | Cambiar fuente, ajustar jerarquía, elegir token para un texto |
| `spacing.md` | Tokens de espaciado, bordes redondeados, sombras, alturas de componentes | Padding, gaps, radios, niveles de elevación |
| `components.md` | Botones, inputs, cards, tabla, panel de totales, sidebar, topbar, chips | Crear o modificar cualquier componente de UI |
| `layout.md` | Grid de contenido, breakpoints, grids de formulario, estructura de página | Crear página nueva, definir responsive, organizar secciones |
| `modules.md` | Diferencias visuales entre los 5 módulos del sistema | Trabajar en un módulo específico (facturación, citas, etc.) |

---

## Regla de lectura selectiva

Solo leer los archivos relevantes para la tarea en curso:

- `"cambia el color del botón"` → solo `colors.md`
- `"ajusta el espaciado del card-header"` → solo `spacing.md`
- `"corrige la fuente del título"` → solo `typography.md`
- `"crea un formulario nuevo"` → `components.md` + `spacing.md`
- `"nueva página completa"` → todos los archivos
- `"agrego una sección al módulo de citas"` → `modules.md` + `components.md`

---

## Lo que NO está en estos archivos

Los siguientes elementos son decisiones de implementación y pertenecen al `CLAUDE.md`:

- Clases de framework (`btn btn-primary`, `bg-navy-700`)
- Versiones de librerías (Tailwind, DaisyUI, Vue, Laravel)
- Convenciones de nombrado de archivos o componentes
- Lógica de negocio de los módulos

El sistema de diseño es **agnóstico a tecnología**. Si el stack cambia, estos archivos no cambian.
