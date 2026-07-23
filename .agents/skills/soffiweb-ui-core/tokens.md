# Soffiweb UI — Tokens

## Tipografía e iconos

```html
<link
    href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap"
    rel="stylesheet"
/>
<link
    href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css"
    rel="stylesheet"
/>
```

- `font-family: 'Inter', sans-serif` en body — siempre
- Pesos: 400 regular · 500 medium · 700 bold
- Datos numéricos: `font-variant-numeric: tabular-nums`
- Escala base: **16px** — pensado para usuarios de 30–60 años, prioriza legibilidad
- Escala de texto: sm=13px · base=14px · md=15px · lg=16px · xl=17px · 2xl=19px · 3xl=28px
- **Iconos: Font Awesome 6 — `fa-solid`, `fa-regular`. NUNCA emoji como iconos.**

---

## CSS Variables — Pegar en `<style>` o CSS global

```css
:root {
    --p: #28449a;
    --pc: #ffffff;
    --s: #4361ee;
    --sc: #ffffff;
    --a: #fa75ad;
    --ac: #2b2230;
    --n: #303237;
    --nc: #fdfdff;
    --b1: #fdfdff;
    --b2: #edf1fb;
    --bc: #303237;
    --bc2: #6b7280;
    --su: oklch(69% 0.17 162.48);
    --suc: #fdfdff;
    --wa: oklch(76% 0.188 70.08);
    --wac: #fdfdff;
    --er: #e11d48;
    --erc: #fdfdff;
    --in: oklch(68% 0.169 237.323);
    --inc: #fdfdff;
    --r: 0.65rem;
    --rl: 1.1rem;
    --brd: 1px solid color-mix(in srgb, var(--s) 28%, transparent);
    --brd-s: 1px solid color-mix(in srgb, var(--s) 50%, transparent);
    --sh-sm: 0 1px 3px rgba(0, 0, 0, 0.06);
    --sh-md: 0 4px 12px rgba(0, 0, 0, 0.1);
    --sh-lg: 0 10px 30px rgba(0, 0, 0, 0.14);
    --sb: #1e2330;
    --th-bg: #e8eef8;
    --th-color: #28449a;
    --btn-text: #fdfdff;
}
[data-theme="dark"] {
    --b1: #2a3040;
    --b2: #222736;
    --bc: #e8ecf4;
    --bc2: #9ca3af;
    --p: #7da4e0;
    --s: #9dbde8;
    --a: #fb8dc0;
    --su: oklch(76% 0.177 163.223);
    --wa: oklch(82% 0.189 84.429);
    --er: oklch(71% 0.194 13.428);
    --in: oklch(74% 0.16 232.661);
    --brd: 1px solid rgba(255, 255, 255, 0.09);
    --sb: #111520;
    --th-bg: #1a2340;
    --th-color: #7da4e0;
    --btn-text: #1a1f2e;
}
```

### Referencia de variables

| Variable        | Light Mode        | Dark Mode         | Significado                         |
| --------------- | ----------------- | ----------------- | ----------------------------------- |
| `--p`           | `#28449a`         | `#7da4e0`         | Primary color                       |
| `--pc`          | `#ffffff`         | —                 | Primary text color                  |
| `--s`           | `#4361ee`         | `#9dbde8`         | Secondary color                     |
| `--sc`          | `#ffffff`         | —                 | Secondary text color                |
| `--a`           | `#fa75ad`         | `#fb8dc0`         | Accent color                        |
| `--ac`          | `#2b2230`         | —                 | Accent text color                   |
| `--n`           | `#303237`         | —                 | Neutral                             |
| `--nc`          | `#fdfdff`         | —                 | Neutral text color                  |
| `--bc`          | `#303237`         | `#e8ecf4`         | Base text color                     |
| `--bc2`         | `#6b7280`         | `#9ca3af`         | Muted text                          |
| `--b1`          | `#fdfdff`         | `#2a3040`         | Primary background                  |
| `--b2`          | `#edf1fb`         | `#222736`         | Secondary background / surface      |
| `--su`          | oklch(69% 0.17 162.48) | oklch(76% 0.177 163.223) | Success color          |
| `--suc`         | `#fdfdff`         | —                 | Success text color                  |
| `--wa`          | oklch(76% 0.188 70.08) | oklch(82% 0.189 84.429) | Warning color         |
| `--wac`         | `#fdfdff`         | —                 | Warning text color                  |
| `--er`          | `#e11d48`         | oklch(71% 0.194 13.428) | Error color             |
| `--erc`         | `#fdfdff`         | —                 | Error text color                    |
| `--in`          | oklch(68% 0.169 237.323) | oklch(74% 0.16 232.661) | Info color          |
| `--inc`         | `#fdfdff`         | —                 | Info text color                     |
| `--sb`          | `#1e2330`         | `#111520`         | Sidebar background (siempre oscuro) |
| `--th-bg`       | `#e8eef8`         | `#1a2340`         | Table header background             |
| `--th-color`    | `#28449a`         | `#7da4e0`         | Table header text                   |
| `--btn-text`    | `#fdfdff`         | `#1a1f2e`         | Button text color                   |
| `--r`           | `.65rem`          | —                 | Border radius (más redondeado)      |
| `--rl`          | `1.1rem`          | —                 | Border radius large                 |
| `--brd`         | 1px secondary 28% | 1px white 9%      | Border global                       |
| `--sh-sm/md/lg` | —                 | —                 | Sombras                             |

---

## Reset CSS mínimo

```css
*,
*::before,
*::after {
    box-sizing: border-box;
    margin: 0;
    padding: 0;
}
html {
    font-family: "Inter", sans-serif;
    font-size: 16px;
    line-height: 1.6;
    background: var(--b1);
    color: var(--bc);
    transition:
        background 0.25s,
        color 0.25s;
}
body {
    font-family: "Inter", sans-serif !important;
}
```
