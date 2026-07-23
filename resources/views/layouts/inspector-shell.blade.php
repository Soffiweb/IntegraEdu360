<!DOCTYPE html>
<html lang="es" id="root" data-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>@yield('title', 'Panel de inspeccion') - IntegraEdu360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;700&display=swap" rel="stylesheet">
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" rel="stylesheet">
    <style>
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

            /* Aliases legacy para las vistas actuales del inspector */
            --navy-50: #eef1fb;
            --navy-100: #ccd4f1;
            --navy-200: #9aaae3;
            --navy-300: #6880d4;
            --navy-400: #3d5fc4;
            --navy-500: #2a4aaa;
            --navy-600: #1e3890;
            --navy-700: #152b74;
            --navy-800: #0e1f55;
            --navy-900: #070f2e;
            --base-100: var(--b1);
            --base-200: var(--b2);
            --base-300: #eef1f8;
            --base-content: var(--bc);
            --muted: var(--bc2);
            --border: #dde3f0;
            --sidebar-bg: var(--sb);
            --color-primary: var(--p);
            --color-primary-hover: #1e3890;
            --color-primary-light: #eef1fb;
            --color-accent: var(--a);
            --sp-xs: 4px;
            --sp-sm: 8px;
            --sp-md: 12px;
            --sp-base: 16px;
            --sp-lg: 24px;
            --sp-xl: 32px;
            --sp-section: 64px;
            --r-md: 6px;
            --r-lg: 10px;
            --r-xl: 14px;
            --r-2xl: 20px;
            --r-full: 9999px;
            --shadow-xs: 0 1px 2px rgba(21, 43, 116, 0.06);
            --shadow-sm: 0 1px 3px rgba(21, 43, 116, 0.08);
            --shadow-md: 0 4px 6px rgba(21, 43, 116, 0.07);
            --shadow-lg: 0 10px 15px rgba(21, 43, 116, 0.08);
            --h-topbar: 68px;
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
            --base-300: #1d2432;
            --border: rgba(255, 255, 255, 0.09);
        }

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
            transition: background 0.25s, color 0.25s;
        }

        body {
            min-height: 100vh;
            font-family: "Inter", sans-serif !important;
            font-size: 14px;
            color: var(--bc);
            background: var(--b1);
        }

        .sw-app {
            display: flex;
            flex-direction: column;
            min-height: 100vh;
        }

        .sw-topbar {
            height: 68px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 1rem;
            padding: 0 20px;
            background: var(--b2);
            border-bottom: var(--brd);
            position: sticky;
            top: 0;
            z-index: 40;
            box-shadow: var(--sh-sm);
        }

        .sw-topbar-brand,
        .sw-topbar-actions {
            display: flex;
            align-items: center;
            gap: 14px;
            min-width: 0;
        }

        .sw-topbar-context {
            display: flex;
            flex-direction: column;
            gap: 1px;
            min-width: 0;
        }

        .sw-topbar-logo {
            width: 42px;
            height: 42px;
            border-radius: 0.8rem;
            background: var(--p);
            color: var(--btn-text);
            display: inline-flex;
            align-items: center;
            justify-content: center;
            font-size: 15px;
            font-weight: 700;
            letter-spacing: 0.05em;
            flex-shrink: 0;
        }

        .sw-topbar-company,
        .sw-topbar-period {
            display: block;
            max-width: min(42vw, 480px);
            overflow: hidden;
            text-overflow: ellipsis;
            white-space: nowrap;
        }

        .sw-topbar-company {
            color: var(--bc);
            font-size: 16px;
            font-weight: 700;
            line-height: 1.2;
        }

        .sw-topbar-period {
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

        .sw-btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0.6rem 1.25rem;
            font-family: "Inter", sans-serif;
            font-size: 15px;
            font-weight: 500;
            line-height: 1.4;
            border: none;
            border-radius: var(--rl);
            cursor: pointer;
            transition: opacity 0.15s, box-shadow 0.15s, background 0.15s;
            color: var(--btn-text);
            text-decoration: none;
            white-space: nowrap;
        }

        .sw-btn:hover {
            opacity: 0.88;
            box-shadow: var(--sh-md);
            text-decoration: none;
        }

        .sw-btn:focus-visible,
        .sw-user-trigger:focus-visible,
        .sw-user-action:focus-visible,
        .sw-sb-link:focus-visible {
            outline: 2px solid var(--p);
            outline-offset: 2px;
        }

        .sw-btn-primary {
            background: var(--p);
        }

        .sw-btn-ghost {
            background: transparent;
            color: var(--bc);
            border: var(--brd);
        }

        .sw-btn-ghost:hover {
            background: var(--b1);
            opacity: 1;
            box-shadow: none;
        }

        .sw-btn-sm {
            padding: 0.45rem 1rem;
            font-size: 14px;
            border-radius: var(--r);
        }

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
            transition: background 0.15s, border-color 0.15s;
        }

        .sw-user-trigger:hover,
        .sw-user-trigger[aria-expanded="true"] {
            background: var(--b1);
            border-color: color-mix(in srgb, var(--s) 34%, transparent);
        }

        .sw-user-avatar {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            width: 34px;
            height: 34px;
            border-radius: 50%;
            background: color-mix(in srgb, var(--p) 12%, var(--b1));
            color: var(--p);
            font-size: 15px;
            flex-shrink: 0;
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

        .sw-body {
            display: flex;
            flex: 1;
            overflow: hidden;
        }

        .sw-sidebar {
            width: 245px;
            background: var(--sb);
            display: flex;
            flex-direction: column;
            padding: 16px 12px;
            flex-shrink: 0;
            overflow-y: auto;
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

        .sw-sb-copy {
            padding: 0 0.75rem 1rem;
        }

        .sw-sb-copy-title {
            color: rgba(255, 255, 255, 0.94);
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 4px;
        }

        .sw-sb-copy-text {
            color: rgba(255, 255, 255, 0.52);
            font-size: 12px;
            line-height: 1.45;
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
            transition: background 0.15s, color 0.15s, border-radius 0.15s;
        }

        .sw-sb-link:hover {
            background: rgba(255, 255, 255, 0.1);
            color: #fff;
            text-decoration: none;
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

        .sw-sb-link small {
            margin-left: auto;
            color: rgba(255, 255, 255, 0.4);
            font-size: 11px;
            font-weight: 500;
            letter-spacing: normal;
            text-transform: none;
        }

        .sw-sb-form {
            margin: 0;
        }

        .sw-sb-action {
            width: 100%;
            border: 0;
            font-family: inherit;
            line-height: inherit;
            text-align: left;
            background: transparent;
        }

        .sw-sb-action:hover {
            background: color-mix(in srgb, var(--er) 18%, transparent);
        }

        .sw-sidebar-panel {
            background: rgba(255, 255, 255, 0.06);
            border: 1px solid rgba(255, 255, 255, 0.1);
            border-radius: var(--rl);
            padding: 1rem;
            margin: 1rem 0.3rem 0;
        }

        .sw-sidebar-panel h3 {
            color: #fff;
            font-size: 14px;
            font-weight: 700;
            margin-bottom: 0.25rem;
        }

        .sw-sidebar-panel > p {
            color: rgba(255, 255, 255, 0.5);
            font-size: 12px;
            margin-bottom: 0.75rem;
        }

        .sw-sidebar-list {
            display: grid;
            gap: 0.6rem;
        }

        .sw-sidebar-list div {
            padding: 0.8rem;
            border-radius: var(--r);
            background: rgba(255, 255, 255, 0.06);
        }

        .sw-sidebar-list strong {
            display: block;
            color: #fff;
            font-size: 13px;
            margin-bottom: 0.2rem;
        }

        .sw-sidebar-list p {
            color: rgba(255, 255, 255, 0.62);
            font-size: 12px;
        }

        .sw-main {
            flex: 1;
            overflow-y: auto;
            padding: 24px 28px;
            background: var(--b1);
        }

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

        .sw-table-wrap {
            border: var(--brd);
            border-radius: var(--rl);
            overflow: hidden;
            box-shadow: var(--sh-sm);
            background: var(--b1);
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

        .sw-badge-success {
            background: color-mix(in srgb, var(--su) 14%, transparent);
            color: color-mix(in srgb, var(--su) 62%, #000);
        }

        .sw-badge-warning {
            background: color-mix(in srgb, var(--wa) 16%, transparent);
            color: color-mix(in srgb, var(--wa) 52%, #000);
        }

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

        .sw-input,
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
        }

        .sw-input:focus,
        .sw-select:focus {
            border-color: var(--p);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 12%, transparent);
        }

        .sw-sidebar-toggle,
        .sw-sidebar-backdrop {
            display: none;
        }

        .sw-fade-in {
            animation: sw-fade-in 0.22s ease-out both;
        }

        @keyframes sw-fade-in {
            from { opacity: 0; }
            to { opacity: 1; }
        }

        /* Compatibilidad con vistas previas del inspector */
        .page {
            width: 100%;
            max-width: none;
            margin: 0;
            padding: 0;
        }

        .topbar {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 16px;
            min-height: 60px;
            padding: 0 24px;
            margin-bottom: 24px;
            background: var(--b1);
            border: var(--brd);
            border-radius: var(--rl);
            box-shadow: var(--sh-sm);
        }

        .crumbs {
            display: flex;
            flex-wrap: wrap;
            align-items: center;
            gap: 8px;
            font-size: 13px;
            color: var(--bc2);
        }

        .crumbs a {
            color: var(--bc2);
            text-decoration: none;
        }

        .crumbs a:hover {
            color: var(--p);
        }

        .topbar-actions {
            display: flex;
            align-items: center;
            gap: 8px;
            flex-wrap: wrap;
            margin-left: auto;
        }

        .btn,
        button.btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 7px;
            padding: 0.6rem 1.25rem;
            min-height: 42px;
            border-radius: var(--rl);
            border: var(--brd);
            background: var(--b1);
            color: var(--bc);
            font: inherit;
            font-size: 14px;
            font-weight: 600;
            text-decoration: none;
            cursor: pointer;
            transition: opacity 0.15s, box-shadow 0.15s, background 0.15s;
        }

        .btn:hover,
        button.btn:hover {
            background: var(--b2);
            box-shadow: var(--sh-sm);
            text-decoration: none;
        }

        .btn-primary {
            background: var(--p);
            border-color: var(--p);
            color: var(--btn-text);
        }

        .btn-primary:hover {
            background: color-mix(in srgb, var(--p) 88%, #000);
            color: var(--btn-text);
        }

        .card,
        .panel,
        .modal-card {
            background: var(--b1);
            border: var(--brd);
            box-shadow: var(--sh-sm);
            border-radius: var(--rl);
            padding: 24px;
        }

        dialog.modal {
            width: min(640px, calc(100% - 24px));
            border: 0;
            border-radius: var(--rl);
            padding: 0;
            box-shadow: var(--sh-lg);
        }

        dialog.modal::backdrop {
            background: rgba(7, 15, 46, 0.5);
            backdrop-filter: blur(2px);
        }

        .modal-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 12px;
            margin-bottom: 16px;
        }

        .modal-header h3 {
            margin: 0;
            color: var(--p);
        }

        .hero {
            border-radius: var(--rl);
            padding: 24px;
            margin-bottom: 24px;
            display: grid;
            grid-template-columns: minmax(0, 1.1fr) minmax(280px, 0.9fr);
            gap: 16px;
            background: linear-gradient(135deg, #152b74, #0e1f55);
            color: #fff;
            box-shadow: var(--sh-lg);
        }

        .hero h1,
        .hero h2 {
            margin: 0 0 10px;
            font-size: clamp(1.75rem, 3vw, 2.5rem);
            font-weight: 700;
            line-height: 1.1;
        }

        .hero p {
            color: rgba(255, 255, 255, 0.78);
            line-height: 1.6;
        }

        .hero-side {
            padding: 24px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            border-radius: var(--r);
        }

        .hero-side p,
        .hero-side strong {
            color: rgba(255, 255, 255, 0.9);
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 4px 12px;
            border-radius: 9999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.15);
            text-transform: uppercase;
            letter-spacing: 0.06em;
            font-size: 11px;
            font-weight: 600;
            margin-bottom: 8px;
        }

        .stats {
            display: grid;
            grid-template-columns: repeat(var(--stats-columns, 4), minmax(0, 1fr));
            gap: 16px;
            margin-bottom: 24px;
        }

        .metric {
            display: block;
            font-size: 2rem;
            font-weight: 700;
            font-variant-numeric: tabular-nums;
            color: var(--p);
            line-height: 1.1;
            margin-bottom: 6px;
        }

        .section-title {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            align-items: center;
            justify-content: space-between;
            margin-bottom: 16px;
        }

        .section-title h2 {
            margin: 0;
            font-size: 18px;
            font-weight: 700;
            color: var(--p);
        }

        .section-title span {
            color: var(--bc2);
            font-size: 13px;
        }

        .toolbar {
            margin-bottom: 16px;
        }

        .filters {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
        }

        .input {
            flex: 1 1 280px;
            min-width: 220px;
            padding: 0.65rem 1rem;
            font-family: inherit;
            font-size: 14px;
            color: var(--bc);
            background: var(--b1);
            border: 1.5px solid color-mix(in srgb, var(--s) 42%, transparent);
            border-radius: var(--r);
            outline: none;
        }

        .input:focus {
            border-color: var(--p);
            box-shadow: 0 0 0 3px color-mix(in srgb, var(--p) 12%, transparent);
        }

        .pager {
            display: flex;
            flex-wrap: wrap;
            gap: 8px;
            align-items: center;
        }

        table {
            width: 100%;
            border-collapse: collapse;
        }

        th,
        td {
            padding: 14px 12px;
            text-align: left;
            border-bottom: 1px solid var(--base-300);
        }

        th {
            color: var(--th-color);
            background: var(--th-bg);
            font-size: 12px;
            text-transform: uppercase;
            letter-spacing: 0.04em;
        }

        @media (max-width: 767.98px) {
            body.sw-sidebar-open {
                overflow: hidden;
            }

            .sw-topbar {
                padding: 0 14px;
            }

            .sw-topbar-company,
            .sw-topbar-period {
                max-width: 120px;
            }

            .sw-sidebar-toggle {
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
                transition: transform 0.2s ease, visibility 0.2s ease;
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

            .topbar {
                flex-direction: column;
                align-items: flex-start;
                padding: 14px 16px;
            }

            .hero,
            .stats {
                grid-template-columns: 1fr;
            }
        }
    </style>
    @if(file_exists(public_path('hot')) || file_exists(public_path('build/manifest.json')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
    @yield('head')
</head>
<body>
    @php
        $activeInspectorNav = trim($__env->yieldContent('inspector-nav', ''));
        $sidebarTitle = trim($__env->yieldContent('sidebar-title', 'Inspector'));
        $sidebarBody = trim($__env->yieldContent('sidebar-body', ''));
        $authUser = auth()->user();
        $displayName = trim((string) ($authUser->name ?? $authUser->nombre ?? 'Inspector'));
        $roleLabel = ucfirst((string) (session('active_role') ?: 'inspector'));
        $companyName = isset($institucionActiva) && $institucionActiva ? $institucionActiva->nombre : 'IntegraEdu360';
        $contextLabel = $sidebarTitle !== '' ? $sidebarTitle : 'Panel institucional';
    @endphp

    <div class="sw-app">
        <header class="sw-topbar">
            <div class="sw-topbar-brand">
                <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm sw-sidebar-toggle"
                        aria-controls="sw-sidebar" aria-expanded="false" aria-label="Abrir menu"
                        onclick="swToggleSidebar(this)">
                    <i class="fa-solid fa-bars"></i>
                </button>

                <span class="sw-topbar-logo" aria-hidden="true">IE</span>

                <div class="sw-topbar-context">
                    <span class="sw-topbar-company" title="{{ $companyName }}">{{ $companyName }}</span>
                    <span class="sw-topbar-period" title="{{ $contextLabel }}">
                        <i class="fa-regular fa-calendar"></i> {{ $contextLabel }}
                    </span>
                </div>
            </div>

            <div class="sw-topbar-actions">
                <button type="button" class="sw-btn sw-btn-ghost sw-btn-sm" aria-label="Cambiar tema"
                        onclick="swToggleTheme()">
                    <i class="fa-solid fa-circle-half-stroke"></i>
                </button>

                <div class="sw-user-menu">
                    <button type="button" class="sw-user-trigger" aria-haspopup="true" aria-expanded="false"
                            aria-controls="sw-user-dropdown" onclick="swToggleUserMenu(this)">
                        <span class="sw-user-avatar" aria-hidden="true"><i class="fa-solid fa-user"></i></span>
                        <span class="sw-user-name">{{ $displayName !== '' ? $displayName : 'Inspector' }}</span>
                        <i class="fa-solid fa-angle-down sw-user-arrow"></i>
                    </button>

                    <div class="sw-user-dropdown" id="sw-user-dropdown">
                        <div class="sw-user-role">
                            <span class="sw-user-role-label">Rol</span>
                            <span class="sw-user-role-value">{{ $roleLabel }}</span>
                        </div>
                        <form action="{{ route('auth.logout') }}" method="POST">
                            @csrf
                            <button type="submit" class="sw-user-action">
                                <i class="fa-solid fa-right-from-bracket"></i> Cerrar sesion
                            </button>
                        </form>
                    </div>
                </div>
            </div>
        </header>

        <div class="sw-body">
            <aside class="sw-sidebar" id="sw-sidebar" aria-label="Navegacion principal">
                <div class="sw-sb-brand">
                    <div class="sw-sb-brand-name">IntegraEdu360</div>
                    <div class="sw-sb-brand-sub">Dashboard institucional</div>
                </div>

                <div class="sw-sb-copy">
                    <div class="sw-sb-copy-title">{{ $sidebarTitle }}</div>
                    @if ($sidebarBody !== '')
                        <p class="sw-sb-copy-text">{{ $sidebarBody }}</p>
                    @endif
                </div>

                <div class="sw-sb-group">Principal</div>
                <a class="sw-sb-link {{ $activeInspectorNav === 'dashboard' ? 'active' : '' }}"
                   href="{{ route('roles.dashboard', 'inspector') }}">
                    <i class="fa-solid fa-house"></i>
                    <span>Resumen inspector</span>
                    <small>Hoy</small>
                </a>

                <div class="sw-sb-group">Operacion</div>
                <a class="sw-sb-link {{ $activeInspectorNav === 'novedades' ? 'active' : '' }}"
                   href="{{ route('inspector.novedades.index') }}">
                    <i class="fa-solid fa-clipboard-list"></i>
                    <span>Registrar novedades</span>
                    <small>Control</small>
                </a>
                <a class="sw-sb-link {{ $activeInspectorNav === 'seguimiento' ? 'active' : '' }}"
                   href="{{ route('roles.module', ['role' => 'inspector', 'module' => 'seguimiento']) }}">
                    <i class="fa-solid fa-binoculars"></i>
                    <span>Seguimiento de casos</span>
                    <small>Gestion</small>
                </a>
                <a class="sw-sb-link {{ $activeInspectorNav === 'distribucion-horas' ? 'active' : '' }}"
                   href="{{ route('inspector.distribucion-horas.index') }}">
                    <i class="fa-solid fa-chart-column"></i>
                    <span>Distribucion de horas</span>
                    <small>Docentes</small>
                </a>
                <a class="sw-sb-link {{ $activeInspectorNav === 'horario-docente' ? 'active' : '' }}"
                   href="{{ route('inspector.horario-docente.index') }}">
                    <i class="fa-regular fa-calendar-days"></i>
                    <span>Horarios docentes</span>
                    <small>Semanal</small>
                </a>
                <a class="sw-sb-link {{ $activeInspectorNav === 'asistencia-docentes' ? 'active' : '' }}"
                   href="{{ route('roles.module', ['role' => 'inspector', 'module' => 'asistencia-docentes']) }}">
                    <i class="fa-solid fa-chalkboard-user"></i>
                    <span>Asistencia docentes</span>
                    <small>Control</small>
                </a>
                <a class="sw-sb-link {{ $activeInspectorNav === 'asistencia-estudiantes' ? 'active' : '' }}"
                   href="{{ route('roles.module', ['role' => 'inspector', 'module' => 'asistencia-estudiantes']) }}">
                    <i class="fa-solid fa-user-graduate"></i>
                    <span>Asistencia estudiantes</span>
                    <small>Control</small>
                </a>

                <div class="sw-sb-group">Cuenta</div>
                <a class="sw-sb-link" href="{{ route('roles.access', 'inspector') }}">
                    <i class="fa-solid fa-id-badge"></i>
                    <span>Perfil del rol</span>
                    <small>Vista</small>
                </a>
                <a class="sw-sb-link" href="{{ route('auth.form') }}">
                    <i class="fa-solid fa-right-left"></i>
                    <span>Cambiar acceso</span>
                    <small>Menu</small>
                </a>
                <form class="sw-sb-form" method="POST" action="{{ route('auth.logout') }}">
                    @csrf
                    <button class="sw-sb-link sw-sb-action" type="submit">
                        <i class="fa-solid fa-right-from-bracket"></i>
                        <span>Cerrar sesion</span>
                        <small>Salir</small>
                    </button>
                </form>

                @hasSection('sidebar-panel-items')
                    <div class="sw-sidebar-panel">
                        <h3>@yield('sidebar-panel-title', 'Resumen del turno')</h3>
                        @hasSection('sidebar-panel-copy')
                            <p>@yield('sidebar-panel-copy')</p>
                        @endif
                        <div class="sw-sidebar-list">
                            @yield('sidebar-panel-items')
                        </div>
                    </div>
                @endif
            </aside>

            <button type="button" class="sw-sidebar-backdrop" aria-label="Cerrar menu"
                    onclick="swCloseSidebar()"></button>

            <main class="sw-main sw-fade-in">
                <div class="page">
                    @hasSection('crumbs')
                        <div class="topbar">
                            <div class="crumbs">
                                @yield('crumbs')
                            </div>
                            <div class="topbar-actions">
                                @hasSection('topbar-secondary')
                                    @yield('topbar-secondary')
                                @endif
                                @hasSection('topbar-action')
                                    @yield('topbar-action')
                                @else
                                    <a class="btn" href="{{ route('roles.dashboard', 'inspector') }}">Volver al dashboard</a>
                                @endif
                            </div>
                        </div>
                    @endif

                    @yield('content')
                </div>
            </main>
        </div>
    </div>

    @yield('dialogs')
    @yield('scripts')
    <script>
        function swToggleTheme() {
            const root = document.getElementById('root') || document.documentElement;
            root.dataset.theme = root.dataset.theme === 'dark' ? 'light' : 'dark';
            localStorage.setItem('theme', root.dataset.theme);
        }

        function swToggleSidebar(trigger) {
            const open = document.body.classList.toggle('sw-sidebar-open');
            trigger.setAttribute('aria-expanded', open ? 'true' : 'false');
            trigger.setAttribute('aria-label', open ? 'Cerrar menu' : 'Abrir menu');
        }

        function swCloseSidebar() {
            document.body.classList.remove('sw-sidebar-open');
            const trigger = document.querySelector('.sw-sidebar-toggle');
            if (trigger) {
                trigger.setAttribute('aria-expanded', 'false');
                trigger.setAttribute('aria-label', 'Abrir menu');
            }
        }

        function swToggleUserMenu(trigger) {
            const menu = document.getElementById(trigger.getAttribute('aria-controls'));
            const open = menu && !menu.classList.contains('open');

            swCloseUserMenu();
            if (open) {
                menu.classList.add('open');
                trigger.setAttribute('aria-expanded', 'true');
            }
        }

        function swCloseUserMenu(restoreFocus = false) {
            const trigger = document.querySelector('.sw-user-trigger');
            const menu = document.querySelector('.sw-user-dropdown');

            if (!trigger || !menu || !menu.classList.contains('open')) {
                return;
            }

            menu.classList.remove('open');
            trigger.setAttribute('aria-expanded', 'false');
            if (restoreFocus) {
                trigger.focus();
            }
        }

        document.addEventListener('click', event => {
            if (!event.target.closest('.sw-user-menu')) {
                swCloseUserMenu();
            }
        });

        document.addEventListener('DOMContentLoaded', () => {
            const savedTheme = localStorage.getItem('theme');
            if (savedTheme) {
                const root = document.getElementById('root') || document.documentElement;
                root.dataset.theme = savedTheme;
            }

            document.querySelectorAll('.sw-sidebar a').forEach(link => {
                link.addEventListener('click', () => {
                    if (window.innerWidth < 768) {
                        swCloseSidebar();
                    }
                });
            });
        });

        document.addEventListener('keydown', event => {
            if (event.key === 'Escape') {
                swCloseSidebar();
                swCloseUserMenu(true);
            }
        });

        window.addEventListener('resize', () => {
            if (window.innerWidth >= 768) {
                swCloseSidebar();
            }
        });
    </script>
</body>
</html>
