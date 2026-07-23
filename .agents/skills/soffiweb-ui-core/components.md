# Soffiweb UI — Components (CSS)

Regla de uso: no introducir bloques `.sw-metric` o `.sw-metrics-grid` por defecto en pantallas CRUD/listado. Usarlos solo si el usuario pide métricas/totales.

```css
/* ── Layout ── */
.sw-app {
    display: flex;
    flex-direction: column;
    min-height: 100vh;
}
.sw-body {
    display: flex;
    flex: 1;
    overflow: hidden;
}
.sw-main {
    flex: 1;
    overflow-y: auto;
    padding: 24px 28px;
    background: var(--b1);
}

/* ── Page Header ── */
.sw-page-header {
    margin-bottom: 24px;
}
.sw-page-title {
    color: var(--bc);
    font-size: 28px;
    font-weight: 700;
    line-height: 1.15;
    margin-bottom: 6px;
}
.sw-page-subtitle {
    color: var(--bc2);
    font-size: 15px;
    line-height: 1.5;
}

/* ── Topbar ── */
.sw-topbar {
    height: 68px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    padding: 0 20px;
    background: var(--b2);
    border-bottom: var(--brd);
    position: sticky;
    top: 0;
    z-index: 40;
    transition: background 0.25s;
    box-shadow: var(--sh-sm);
}
.sw-topbar-brand,
.sw-topbar-actions {
    display: flex;
    align-items: center;
    gap: 14px;
    min-width: 0;
}
.sw-topbar-actions {
    gap: 10px;
}

/* Logo de marca (imagen) */
.sw-topbar-logo {
    height: 36px;
    width: auto;
    object-fit: contain;
    flex-shrink: 0;
}

/* Bloque empresa + período */
.sw-topbar-tenant {
    display: flex;
    flex-direction: column;
    gap: 1px;
    min-width: 0;
}
.sw-topbar-company {
    display: block;
    max-width: min(42vw, 480px);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--bc);
    font-size: 16px;
    font-weight: 700;
    line-height: 1.2;
}
.sw-topbar-period {
    display: block;
    max-width: min(42vw, 480px);
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    color: var(--bc2);
    font-size: 13px;
    font-weight: 500;
    line-height: 1.25;
}
.sw-topbar-period i {
    color: var(--p);
    font-size: 12px;
    margin-right: 4px;
}

/* Menú de usuario */
.sw-user-menu {
    position: relative;
}
.sw-user-trigger {
    display: flex;
    align-items: center;
    gap: 9px;
    height: 44px;
    max-width: 290px;
    padding: 4px 10px 4px 6px;
    background: transparent;
    border: 1px solid transparent;
    border-radius: var(--r);
    color: var(--bc);
    cursor: pointer;
    transition:
        background 0.15s,
        border-color 0.15s;
}
.sw-user-trigger:hover,
.sw-user-trigger[aria-expanded="true"] {
    background: var(--b1);
    border-color: color-mix(in srgb, var(--s) 34%, transparent);
}
.sw-user-trigger:focus-visible,
.sw-user-action:focus-visible {
    outline: 2px solid var(--p);
    outline-offset: 1px;
}
.sw-user-avatar {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    flex-shrink: 0;
    width: 34px;
    height: 34px;
    border-radius: 50%;
    background: color-mix(in srgb, var(--p) 12%, var(--b1));
    color: var(--p);
    font-size: 15px;
}
.sw-user-avatar-img {
    width: 100%;
    height: 100%;
    border-radius: 50%;
    object-fit: cover;
}
.sw-user-avatar-fallback {
    display: none;
}
.sw-user-avatar-photo.is-fallback .sw-user-avatar-img {
    display: none;
}
.sw-user-avatar-photo.is-fallback .sw-user-avatar-fallback {
    display: inline-block;
}
.sw-user-name {
    max-width: 190px;
    overflow: hidden;
    text-overflow: ellipsis;
    white-space: nowrap;
    font-size: 14px;
    font-weight: 500;
}
.sw-user-arrow {
    color: var(--bc2);
    font-size: 12px;
    transition: transform 0.15s;
}
.sw-user-trigger[aria-expanded="true"] .sw-user-arrow {
    transform: rotate(180deg);
}
.sw-user-dropdown {
    display: none;
    position: absolute;
    right: 0;
    top: calc(100% + 8px);
    z-index: 80;
    min-width: 220px;
    padding: 8px;
    background: var(--b1);
    border: var(--brd);
    border-radius: var(--r);
    box-shadow: var(--sh-md);
}
.sw-user-dropdown.open {
    display: block;
}
.sw-user-role {
    display: flex;
    flex-direction: column;
    gap: 2px;
    margin-bottom: 6px;
    padding: 6px 8px 10px;
    border-bottom: var(--brd);
}
.sw-user-role-label {
    color: var(--bc2);
    font-size: 11px;
    font-weight: 700;
    letter-spacing: 0.07em;
    text-transform: uppercase;
}
.sw-user-role-value {
    color: var(--bc);
    font-size: 14px;
    font-weight: 600;
}
.sw-user-dropdown form {
    margin: 0;
}
.sw-user-action {
    display: flex;
    align-items: center;
    gap: 9px;
    width: 100%;
    padding: 10px 8px;
    background: transparent;
    border: 0;
    border-radius: var(--r);
    color: var(--er);
    font-family: inherit;
    font-size: 14px;
    font-weight: 500;
    text-align: left;
    cursor: pointer;
}
.sw-user-action:hover {
    background: color-mix(in srgb, var(--er) 8%, transparent);
}
.sw-btn.sw-sidebar-toggle,
.sw-sidebar-backdrop {
    display: none;
}

/* ── Sidebar ── */
.sw-sidebar {
    width: 245px;
    background: var(--sb);
    display: flex;
    flex-direction: column;
    padding: 16px 12px;
    flex-shrink: 0;
    overflow-y: auto;
    transition: background 0.25s;
}
.sw-sb-brand {
    padding: 4px 0.75rem 14px;
    border-bottom: 1px solid rgba(255, 255, 255, 0.07);
    margin-bottom: 8px;
}
.sw-sb-brand-name {
    font-size: 16px;
    font-weight: 700;
    color: #fff;
    text-transform: uppercase;
    letter-spacing: 0.02em;
}
.sw-sb-brand-sub {
    font-size: 12px;
    color: rgba(255, 255, 255, 0.42);
    margin-top: 2px;
}
.sw-sb-group {
    font-size: 11px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.1em;
    color: rgba(255, 255, 255, 0.35);
    padding: 0 0.75rem;
    margin: 18px 0 6px;
    display: flex;
    align-items: center;
    gap: 8px;
}
.sw-sb-group::after {
    content: "";
    flex: 1;
    height: 1px;
    background: rgba(255, 255, 255, 0.07);
}
.sw-sb-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.65rem 0.85rem;
    border-radius: var(--r);
    color: rgba(255, 255, 255, 0.82);
    font-size: 14px;
    font-weight: 700;
    letter-spacing: 0.03em;
    text-transform: uppercase;
    text-decoration: none;
    cursor: pointer;
    transition:
        background 0.15s,
        color 0.15s,
        border-radius 0.15s;
}
.sw-sb-link:hover {
    background: rgba(255, 255, 255, 0.1);
    color: #fff;
}
.sw-sb-link:focus-visible,
.sw-sb-sub-link:focus-visible {
    outline: 2px solid var(--s);
    outline-offset: -2px;
    color: #fff;
}
.sw-sb-link.active {
    background: rgba(255, 255, 255, 0.14);
    color: #fff;
    border-left: 3px solid var(--s);
    padding-left: calc(0.85rem - 3px);
}
.sw-sb-link i {
    width: 18px;
    text-align: center;
    font-size: 14px;
}
.sw-sb-link .sw-arrow {
    margin-left: auto;
    font-size: 12px;
    transition: transform 0.2s;
}
.sw-sb-link.open .sw-arrow {
    transform: rotate(90deg);
}
.sw-sidebar > .sw-sb-link {
    margin-bottom: 0.15rem;
}
.sw-sb-toggle,
.sw-sb-action {
    width: 100%;
    border: 0;
    font-family: inherit;
    line-height: inherit;
    text-align: left;
    background: transparent;
}
.sw-sidebar > .sw-sb-link.open {
    margin-bottom: 0;
    padding-left: calc(0.85rem - 3px);
    border-left: 3px solid var(--s);
    border-radius: var(--r) var(--r) 0 0;
    background: rgba(255, 255, 255, 0.12);
    color: #fff;
}
.sw-sb-sub {
    overflow: hidden;
    max-height: 0;
    margin: 0;
    padding: 0 0.5rem 0 0.7rem;
    border-left: 3px solid transparent;
    border-radius: 0 0 0.75rem 0.75rem;
    background: transparent;
    transition: max-height 0.25s ease;
}
.sw-sb-sub.open {
    max-height: 480px;
    margin: 0 0 0.55rem;
    padding: 0.5rem 0.5rem 0.5rem 0.7rem;
    border-left-color: var(--s);
    background: rgba(0, 0, 0, 0.14);
}
.sw-sb-sub-link {
    display: flex;
    align-items: center;
    gap: 10px;
    padding: 0.62rem 0.7rem;
    border-radius: 0.45rem;
    color: rgba(255, 255, 255, 0.78);
    font-size: 13px;
    font-weight: 600;
    text-decoration: none;
    transition:
        background 0.15s,
        color 0.15s;
}
.sw-sb-sub-link i {
    width: 15px;
    text-align: center;
    font-size: 13px;
    opacity: 0.75;
}
.sw-sb-sub-link:hover {
    color: #fff;
    background: rgba(255, 255, 255, 0.08);
}
.sw-sb-sub-link.active {
    color: #fff;
    font-weight: 700;
    background: rgba(255, 255, 255, 0.12);
}
.sw-sb-form {
    margin: 0;
}
.sw-sb-action:hover {
    background: color-mix(in srgb, var(--er) 18%, transparent);
}

/* Responsive sidebar (drawer móvil) */
@media (max-width: 767.98px) {
    body.sw-sidebar-open {
        overflow: hidden;
    }
    .sw-topbar {
        padding: 0 14px;
        gap: 10px;
    }
    .sw-topbar-brand {
        gap: 10px;
        overflow: hidden;
    }
    .sw-topbar-company {
        font-size: 14px;
        max-width: 120px;
    }
    .sw-topbar-period {
        font-size: 12px;
        max-width: 120px;
    }
    .sw-btn.sw-sidebar-toggle {
        display: inline-flex;
    }
    .sw-topbar-actions > .sw-btn,
    .sw-user-name,
    .sw-user-arrow {
        display: none;
    }
    .sw-user-trigger {
        padding-right: 6px;
    }
    .sw-sidebar {
        position: fixed;
        top: 68px;
        bottom: 0;
        left: 0;
        z-index: 60;
        visibility: hidden;
        transform: translateX(-100%);
        transition:
            background 0.25s,
            transform 0.2s ease,
            visibility 0.2s ease;
    }
    body.sw-sidebar-open .sw-sidebar {
        visibility: visible;
        transform: translateX(0);
    }
    .sw-sidebar-backdrop {
        position: fixed;
        inset: 68px 0 0;
        z-index: 50;
        display: block;
        border: 0;
        background: rgba(0, 0, 0, 0.38);
        opacity: 0;
        pointer-events: none;
        transition: opacity 0.2s ease;
    }
    body.sw-sidebar-open .sw-sidebar-backdrop {
        opacity: 1;
        pointer-events: auto;
    }
    .sw-main {
        padding: 16px 14px;
    }
}

/* ── Botones — tamaño accesible ── */
.sw-btn {
    display: inline-flex;
    align-items: center;
    gap: 7px;
    padding: 0.6rem 1.25rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    font-weight: 500;
    border: none;
    border-radius: var(--rl);
    cursor: pointer;
    transition:
        opacity 0.15s,
        box-shadow 0.15s;
    color: var(--btn-text);
    line-height: 1.4;
}
.sw-btn:hover {
    opacity: 0.86;
    box-shadow: var(--sh-md);
}
.sw-btn:focus {
    outline: 2px solid var(--a);
    outline-offset: 2px;
}
.sw-btn:disabled {
    opacity: 0.38;
    cursor: not-allowed;
    box-shadow: none;
}
.sw-btn-primary {
    background: var(--p);
}
.sw-btn-secondary {
    background: var(--s);
}
.sw-btn-accent {
    background: var(--a);
}
.sw-btn-error {
    background: var(--er);
}
.sw-btn-success {
    background: var(--su);
}
.sw-btn-ghost {
    background: transparent;
    color: var(--bc);
    border: var(--brd);
}
.sw-btn-ghost:hover {
    background: var(--b2);
    opacity: 1;
    box-shadow: none;
}
.sw-btn-outline {
    background: transparent;
    color: var(--p);
    border: 1.5px solid var(--p);
}
.sw-btn-outline:hover {
    background: color-mix(in srgb, var(--p) 6%, transparent);
    opacity: 1;
}
/* Tamaños */
.sw-btn-sm {
    padding: 0.45rem 1rem;
    font-size: 14px;
    border-radius: var(--r);
}
.sw-btn-lg {
    padding: 0.75rem 1.6rem;
    font-size: 17px;
}
/* Botón icono tabla */
.sw-bico {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 34px;
    height: 34px;
    border: none;
    border-radius: var(--r);
    cursor: pointer;
    font-size: 14px;
    transition: opacity 0.15s;
    color: #fff;
}
.sw-bico:hover {
    opacity: 0.8;
}
.sw-bico-edit {
    background: var(--s);
}
.sw-bico-copy {
    background: var(--p);
}
.sw-bico-delete {
    background: var(--er);
}
.sw-bico-transfer {
    background: var(--wa);
    color: var(--b1);
}

/* ── Badges ── */
.sw-badge {
    display: inline-flex;
    align-items: center;
    gap: 5px;
    padding: 0.28rem 0.8rem;
    border-radius: 25px;
    font-size: 13px;
    font-weight: 600;
    line-height: 1;
}
.sw-badge-primary {
    background: color-mix(in srgb, var(--p) 12%, transparent);
    color: var(--p);
}
.sw-badge-secondary {
    background: color-mix(in srgb, var(--s) 18%, transparent);
    color: color-mix(in srgb, var(--s) 75%, #000);
}
.sw-badge-accent {
    background: color-mix(in srgb, var(--a) 15%, transparent);
    color: color-mix(in srgb, var(--a) 78%, #000);
}
.sw-badge-success {
    background: color-mix(in srgb, var(--su) 14%, transparent);
    color: color-mix(in srgb, var(--su) 62%, #000);
}
.sw-badge-warning {
    background: color-mix(in srgb, var(--wa) 16%, transparent);
    color: color-mix(in srgb, var(--wa) 52%, #000);
}
.sw-badge-error {
    background: color-mix(in srgb, var(--er) 11%, transparent);
    color: var(--er);
}
.sw-badge-info {
    background: color-mix(in srgb, var(--in) 11%, transparent);
    color: color-mix(in srgb, var(--in) 68%, #000);
}
[data-theme="dark"] .sw-badge-success {
    color: var(--su);
}
[data-theme="dark"] .sw-badge-warning {
    color: var(--wa);
}
[data-theme="dark"] .sw-badge-accent {
    color: var(--a);
}
[data-theme="dark"] .sw-badge-secondary {
    color: var(--s);
}
[data-theme="dark"] .sw-badge-info {
    color: var(--in);
}

/* ── Alertas ── */
.sw-alert {
    display: flex;
    align-items: flex-start;
    gap: 11px;
    padding: 0.9rem 1.1rem;
    border-radius: var(--r);
    font-size: 15px;
    line-height: 1.55;
}
.sw-alert-info {
    background: color-mix(in srgb, var(--in) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--in) 22%, transparent);
    color: color-mix(in srgb, var(--in) 68%, #000);
}
.sw-alert-success {
    background: color-mix(in srgb, var(--su) 10%, transparent);
    border: 1px solid color-mix(in srgb, var(--su) 22%, transparent);
    color: color-mix(in srgb, var(--su) 62%, #000);
}
.sw-alert-warning {
    background: color-mix(in srgb, var(--wa) 12%, transparent);
    border: 1px solid color-mix(in srgb, var(--wa) 26%, transparent);
    color: color-mix(in srgb, var(--wa) 50%, #000);
}
.sw-alert-error {
    background: color-mix(in srgb, var(--er) 8%, transparent);
    border: 1px solid color-mix(in srgb, var(--er) 18%, transparent);
    color: var(--er);
}
[data-theme="dark"] .sw-alert-info {
    color: var(--in);
}
[data-theme="dark"] .sw-alert-success {
    color: var(--su);
}
[data-theme="dark"] .sw-alert-warning {
    color: var(--wa);
}

/* ── Inputs — tamaño accesible ── */
.sw-field {
    display: flex;
    flex-direction: column;
    gap: 5px;
}
.sw-label {
    font-size: 14px;
    font-weight: 500;
    color: var(--bc2);
}
.sw-input {
    width: 100%;
    padding: 0.65rem 1rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    color: var(--bc);
    background: var(--b1);
    border: 1.5px solid color-mix(in srgb, var(--s) 42%, transparent);
    border-radius: var(--r);
    outline: none;
    transition:
        border-color 0.15s,
        box-shadow 0.15s;
}
.sw-input:focus {
    border-color: var(--p);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 12%, transparent);
}
.sw-input.sw-error {
    border-color: var(--er);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--er) 10%, transparent);
}
.sw-select {
    width: 100%;
    padding: 0.65rem 1rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    color: var(--bc);
    background: var(--b1);
    border: 1.5px solid color-mix(in srgb, var(--s) 42%, transparent);
    border-radius: var(--r);
    outline: none;
    cursor: pointer;
}
.sw-select:focus {
    border-color: var(--p);
}
.sw-textarea {
    width: 100%;
    padding: 0.65rem 1rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    color: var(--bc);
    background: var(--b1);
    border: 1.5px solid color-mix(in srgb, var(--s) 42%, transparent);
    border-radius: var(--r);
    outline: none;
    resize: vertical;
}
.sw-textarea:focus {
    border-color: var(--p);
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 12%, transparent);
}
.sw-hint {
    font-size: 13px;
    color: var(--bc2);
}
.sw-hint-error {
    font-size: 13px;
    color: var(--er);
}

/* ── Input con icono ── */
.sw-input-icon-wrap {
    position: relative;
}
.sw-input-icon {
    position: absolute;
    left: 14px;
    top: 50%;
    transform: translateY(-50%);
    color: var(--bc2);
    font-size: 14px;
    pointer-events: none;
}
.sw-input-has-icon {
    padding-left: 2.5rem;
}

/* ── Select buscador ── */
.sw-ss-wrap {
    position: relative;
    width: 100%;
}
.sw-ss-trigger {
    width: 100%;
    padding: 0.65rem 1rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    color: var(--bc);
    background: var(--b1);
    border: 1.5px solid color-mix(in srgb, var(--s) 42%, transparent);
    border-radius: var(--r);
    cursor: pointer;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 8px;
    transition: border-color 0.15s;
}
.sw-ss-trigger.open {
    border-color: var(--p);
    border-bottom-left-radius: 0;
    border-bottom-right-radius: 0;
    box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 12%, transparent);
}
.sw-ss-dropdown {
    position: absolute;
    top: 100%;
    left: 0;
    right: 0;
    background: var(--b1);
    border: 1.5px solid var(--p);
    border-top: none;
    border-bottom-left-radius: var(--r);
    border-bottom-right-radius: var(--r);
    z-index: 100;
    box-shadow: var(--sh-md);
    display: none;
}
.sw-ss-dropdown.open {
    display: block;
}
.sw-ss-search {
    width: 100%;
    padding: 0.55rem 1rem 0.55rem 2.2rem;
    font-family: "Inter", sans-serif;
    font-size: 15px;
    color: var(--bc);
    background: var(--b2);
    border: var(--brd);
    border-radius: 0.35rem;
    outline: none;
    display: block;
    box-sizing: border-box;
}
.sw-ss-opt {
    padding: 0.6rem 1.1rem;
    font-size: 15px;
    cursor: pointer;
    color: var(--bc);
    display: flex;
    align-items: center;
    gap: 9px;
    transition: background 0.12s;
}
.sw-ss-opt:hover {
    background: color-mix(in srgb, var(--p) 6%, transparent);
}
.sw-ss-opt.selected {
    background: color-mix(in srgb, var(--p) 10%, transparent);
    color: var(--p);
    font-weight: 600;
}
.sw-ss-empty {
    padding: 0.8rem 1.1rem;
    font-size: 15px;
    color: var(--bc2);
    text-align: center;
}

/* ── Toggle ── */
.sw-toggle {
    width: 46px;
    height: 26px;
    background: color-mix(in srgb, var(--s) 32%, transparent);
    border-radius: 25px;
    position: relative;
    cursor: pointer;
    border: none;
    flex-shrink: 0;
    transition: background 0.2s;
}
.sw-toggle.on {
    background: var(--p);
}
.sw-toggle.on-accent {
    background: var(--a);
}
.sw-toggle::after {
    content: "";
    position: absolute;
    top: 3px;
    left: 3px;
    width: 20px;
    height: 20px;
    background: #fff;
    border-radius: 50%;
    transition: left 0.18s;
    box-shadow: 0 1px 3px rgba(0, 0, 0, 0.18);
}
.sw-toggle.on::after,
.sw-toggle.on-accent::after {
    left: 23px;
}

/* ── Checkbox y Radio ── */
.sw-checkbox {
    width: 19px;
    height: 19px;
    border: 2px solid color-mix(in srgb, var(--s) 55%, transparent);
    border-radius: 4px;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--b1);
    flex-shrink: 0;
}
.sw-checkbox.on {
    background: var(--p);
    border-color: var(--p);
    color: #fff;
}
.sw-checkbox.on-accent {
    background: var(--a);
    border-color: var(--a);
    color: var(--ac);
}
.sw-radio {
    width: 19px;
    height: 19px;
    border: 2px solid color-mix(in srgb, var(--s) 55%, transparent);
    border-radius: 50%;
    display: flex;
    align-items: center;
    justify-content: center;
    background: var(--b1);
    flex-shrink: 0;
}
.sw-radio.on {
    border-color: var(--p);
}
.sw-radio.on::after {
    content: "";
    width: 9px;
    height: 9px;
    border-radius: 50%;
    background: var(--p);
}

/* ── Tabla — header contrastado ── */
.sw-table-wrap {
    border: var(--brd);
    border-radius: var(--rl);
    overflow: hidden;
    box-shadow: var(--sh-sm);
}
.sw-table {
    width: 100%;
    border-collapse: collapse;
    font-size: 15px;
    font-variant-numeric: tabular-nums;
}
.sw-table thead th {
    background: var(--th-bg);
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--th-color);
    padding: 0.8rem 1rem;
    border-bottom: 2px solid color-mix(in srgb, var(--p) 20%, transparent);
    text-align: left;
}
.sw-table tbody td {
    padding: 0.8rem 1rem;
    border-bottom: var(--brd);
    color: var(--bc);
    vertical-align: middle;
    background: var(--b1);
}
.sw-table tbody tr:last-child td {
    border-bottom: none;
}
.sw-table tbody tr:hover td {
    background: var(--b2);
}

/* ── Stat / métrica ── */
.sw-metrics-grid {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(160px, 1fr));
    gap: 16px;
    margin-bottom: 28px;
}
.sw-metric {
    background: var(--b1);
    border-radius: var(--r);
    padding: 1.1rem 1.2rem;
    border: var(--brd);
}
.sw-metric-label {
    font-size: 12px;
    font-weight: 700;
    text-transform: uppercase;
    letter-spacing: 0.07em;
    color: var(--s);
    margin-bottom: 6px;
    display: flex;
    align-items: center;
    gap: 6px;
}
.sw-metric-value {
    font-size: 28px;
    font-weight: 700;
    font-variant-numeric: tabular-nums;
}
.sw-metric-sub {
    font-size: 14px;
    color: var(--bc2);
    margin-top: 3px;
}

/* ── Card ── */
.sw-card {
    background: var(--b1);
    border: var(--brd);
    border-radius: var(--rl);
    padding: 1.2rem 1.4rem;
    transition: box-shadow 0.18s;
}
.sw-card:hover {
    box-shadow: var(--sh-md);
}
.sw-card-title {
    font-size: 17px;
    font-weight: 700;
    color: var(--p);
    margin-bottom: 7px;
}
.sw-card-body {
    font-size: 15px;
    color: var(--bc2);
    line-height: 1.55;
}
.sw-card-footer {
    margin-top: 14px;
    padding-top: 12px;
    border-top: var(--brd);
    display: flex;
    align-items: center;
    justify-content: space-between;
}

/* ── Modal ── */
.sw-modal-backdrop {
    background: rgba(0, 0, 0, 0.42);
    display: flex;
    align-items: center;
    justify-content: center;
    padding: 24px;
}
.sw-modal {
    background: var(--b1);
    border-radius: var(--rl);
    box-shadow: var(--sh-lg);
    padding: 2rem 2.2rem;
    width: 100%;
    max-width: 460px;
    border: var(--brd);
}
.sw-modal-title {
    font-size: 19px;
    font-weight: 700;
    color: var(--p);
    margin-bottom: 9px;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 9px;
}
.sw-modal-close {
    background: transparent;
    border: none;
    color: var(--bc);
    cursor: pointer;
    font-size: 20px;
    padding: 4px 8px;
    display: flex;
    align-items: center;
    justify-content: center;
    transition: color 0.15s;
    flex-shrink: 0;
    line-height: 1;
}
.sw-modal-close:hover {
    color: var(--p);
}
.sw-modal-body {
    font-size: 15px;
    color: var(--bc2);
    line-height: 1.6;
    margin-bottom: 22px;
}
.sw-modal-actions {
    display: flex;
    gap: 10px;
    justify-content: flex-end;
}

/* ── Tabs ── */
.sw-tabs {
    display: flex;
    border-bottom: var(--brd);
    margin-bottom: 18px;
}
.sw-tab {
    padding: 0.65rem 1.2rem;
    font-size: 15px;
    font-weight: 500;
    color: var(--bc2);
    cursor: pointer;
    border-bottom: 2px solid transparent;
    margin-bottom: -1px;
    transition: color 0.15s;
}
.sw-tab.active {
    color: var(--p);
    border-bottom-color: var(--p);
}

/* ── Progress ── */
.sw-progress-track {
    height: 9px;
    background: color-mix(in srgb, var(--s) 18%, transparent);
    border-radius: 25px;
    overflow: hidden;
    margin-top: 6px;
}
.sw-progress-bar {
    height: 100%;
    border-radius: 25px;
}

/* ── Paginación ── */
.sw-pg-btn {
    display: inline-flex;
    align-items: center;
    justify-content: center;
    width: 38px;
    height: 38px;
    border-radius: var(--r);
    font-size: 14px;
    font-weight: 500;
    cursor: pointer;
    border: var(--brd);
    background: var(--b1);
    color: var(--bc);
    text-decoration: none;
}
.sw-pg-btn.active {
    background: var(--p);
    color: var(--btn-text);
    border-color: var(--p);
}
.sw-pg-btn:hover:not(.active) {
    background: var(--b2);
}

/* ── Animación ── */
@keyframes sw-fade-in {
    from {
        opacity: 0;
    }
    to {
        opacity: 1;
    }
}
.sw-fade-in {
    animation: sw-fade-in 0.22s ease-out both;
}
```

---

## Modal con botón cerrar — HTML

```html
<div class="sw-modal-backdrop" id="exampleModal">
    <div class="sw-modal">
        <div class="sw-modal-title">
            <span>Título del modal</span>
            <button type="button" class="sw-modal-close" onclick="document.getElementById('exampleModal').style.display='none'">
                <i class="fa-solid fa-xmark"></i>
            </button>
        </div>
        <div class="sw-modal-body">
            Contenido del modal aquí. Visible en ambos temas.
        </div>
        <div class="sw-modal-actions">
            <button class="sw-btn sw-btn-ghost" onclick="document.getElementById('exampleModal').style.display='none'">
                Cancelar
            </button>
            <button class="sw-btn sw-btn-primary">
                Confirmar
            </button>
        </div>
    </div>
</div>

<!-- Mostrar: -->
<script>
function showModal() {
    document.getElementById('exampleModal').style.display = 'flex';
}
</script>
```

**Notas:**
- La X (`.sw-modal-close`) usa `var(--bc)` que cambia automático light/dark
- Hover a color primary (`var(--p)`)
- Icono con Font Awesome `fa-xmark`
- `justify-content: space-between` en título alinea X a derecha
