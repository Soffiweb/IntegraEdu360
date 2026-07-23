<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso Institucional — IntegraEdu360</title>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&family=DM+Mono:wght@400;500&display=swap" rel="stylesheet">
    <style>
        /* ── Tokens Soffiweb — paleta teal académica ─────────────── */
        :root {
            --teal-50:#ebfafd;--teal-100:#cdf2fb;--teal-200:#99e5f5;--teal-300:#5dd4eb;
            --teal-400:#28bcd8;--teal-500:#12a0bf;--teal-600:#0e8099;--teal-700:#0a6578;
            --teal-800:#064c5c;--teal-900:#042f3a;
            --rosa-400:#d05c88;--rosa-500:#c4527a;--rosa-600:#a03560;
            --steel-300:#8fb3d9;--steel-400:#6a97c5;
            --gold-400:#edc46c;--gold-500:#d9a441;
            --base-50:#ffffff;--base-100:#fdfdff;--base-200:#f5fafb;--base-300:#eaf5f7;
            --base-content:#303237;--muted:#64748b;--border:#d6e8eb;
            --shadow-xs:0 1px 2px rgba(4,47,58,.06);
            --shadow-sm:0 1px 3px rgba(4,47,58,.08),0 1px 2px rgba(4,47,58,.06);
            --shadow-md:0 4px 6px rgba(4,47,58,.07),0 2px 4px rgba(4,47,58,.06);
            --shadow-lg:0 10px 15px rgba(4,47,58,.08),0 4px 6px rgba(4,47,58,.05);
            --shadow-xl:0 20px 40px rgba(4,47,58,.12),0 8px 16px rgba(4,47,58,.08);
            --r-md:6px;--r-lg:10px;--r-xl:14px;--r-2xl:20px;--r-3xl:28px;--r-full:9999px;
        }

        *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

        body {
            font-family: 'Plus Jakarta Sans', system-ui, sans-serif;
            font-size: 14px;
            line-height: 1.6;
            color: var(--base-content);
            background:
                radial-gradient(ellipse 80% 60% at 0% 0%, rgba(4,47,58,.07) 0%, transparent 60%),
                radial-gradient(ellipse 60% 50% at 100% 100%, rgba(196,82,122,.06) 0%, transparent 55%),
                var(--base-200);
            min-height: 100vh;
        }

        /* ── Mesh grid background ────────────────────────────────── */
        body::before {
            content: "";
            position: fixed;
            inset: 0;
            background-image:
                linear-gradient(rgba(4,47,58,.025) 1px, transparent 1px),
                linear-gradient(90deg, rgba(4,47,58,.025) 1px, transparent 1px);
            background-size: 40px 40px;
            pointer-events: none;
            z-index: 0;
        }

        /* ── Shell ───────────────────────────────────────────────── */
        .shell {
            position: relative;
            z-index: 1;
            width: min(1280px, calc(100% - 48px));
            margin: 0 auto;
            padding: 36px 0 64px;
        }

        /* ── Header ──────────────────────────────────────────────── */
        .site-header {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 20px;
            margin-bottom: 32px;
        }

        .brand {
            display: flex;
            align-items: center;
            gap: 14px;
        }

        .brand-mark {
            width: 48px;
            height: 48px;
            border-radius: var(--r-xl);
            background: linear-gradient(135deg, var(--teal-700), var(--teal-900));
            display: grid;
            place-items: center;
            color: #fff;
            font-size: 12px;
            font-weight: 800;
            letter-spacing: 0.06em;
            box-shadow: 0 8px 20px rgba(4,47,58,.28);
            flex-shrink: 0;
        }

        .brand-name {
            font-size: 15px;
            font-weight: 800;
            color: var(--teal-900);
            letter-spacing: -0.01em;
        }

        .brand-sub {
            font-size: 12px;
            color: var(--muted);
            font-weight: 400;
        }

        .header-pill {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 8px 16px;
            border-radius: var(--r-full);
            background: var(--base-100);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-xs);
            font-size: 11px;
            font-weight: 700;
            letter-spacing: 0.08em;
            text-transform: uppercase;
            color: var(--teal-700);
        }

        .header-pill-dot {
            width: 6px;
            height: 6px;
            border-radius: 50%;
            background: #22c55e;
            box-shadow: 0 0 0 2px rgba(34,197,94,.25);
        }

        /* ── Hero ────────────────────────────────────────────────── */
        .hero {
            display: grid;
            grid-template-columns: minmax(0, 1.2fr) minmax(320px, 0.8fr);
            gap: 2px;
            border-radius: var(--r-3xl);
            overflow: hidden;
            box-shadow: var(--shadow-xl);
            margin-bottom: 28px;
        }

        .hero-main {
            padding: 52px 48px;
            background: linear-gradient(145deg, var(--teal-900) 0%, #053342 50%, var(--teal-800) 100%);
            color: #fff;
            position: relative;
            overflow: hidden;
        }

        /* Decorative orbs */
        .hero-main::before {
            content: "";
            position: absolute;
            width: 500px;
            height: 500px;
            right: -200px;
            top: -200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(18,160,191,.18) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-main::after {
            content: "";
            position: absolute;
            width: 400px;
            height: 400px;
            left: -150px;
            bottom: -200px;
            border-radius: 50%;
            background: radial-gradient(circle, rgba(196,82,122,.12) 0%, transparent 65%);
            pointer-events: none;
        }

        .hero-main > * { position: relative; z-index: 1; }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            gap: 8px;
            padding: 6px 14px;
            border-radius: var(--r-full);
            background: rgba(255,255,255,.08);
            border: 1px solid rgba(255,255,255,.12);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.14em;
            text-transform: uppercase;
            color: rgba(255,255,255,.85);
            margin-bottom: 20px;
        }

        .eyebrow-dot {
            width: 5px;
            height: 5px;
            border-radius: 50%;
            background: var(--gold-400);
        }

        .hero-headline {
            font-size: clamp(2.4rem, 4.5vw, 4rem);
            font-weight: 800;
            line-height: 1.04;
            letter-spacing: -0.04em;
            margin-bottom: 20px;
            color: #fff;
        }

        .hero-headline em {
            font-style: normal;
            background: linear-gradient(90deg, var(--gold-400), var(--steel-300));
            -webkit-background-clip: text;
            -webkit-text-fill-color: transparent;
            background-clip: text;
        }

        .hero-body {
            color: rgba(255,255,255,.7);
            line-height: 1.75;
            max-width: 560px;
            font-size: 14px;
            margin-bottom: 36px;
        }

        .hero-stats {
            display: grid;
            grid-template-columns: repeat(3, minmax(0,1fr));
            gap: 12px;
        }

        .stat {
            padding: 18px 16px;
            border-radius: var(--r-xl);
            background: rgba(255,255,255,.07);
            border: 1px solid rgba(255,255,255,.1);
            backdrop-filter: blur(8px);
        }

        .stat-value {
            display: block;
            font-family: 'DM Mono', monospace;
            font-size: 1.75rem;
            font-weight: 500;
            color: #fff;
            line-height: 1;
            margin-bottom: 6px;
        }

        .stat-label {
            font-size: 11px;
            color: rgba(255,255,255,.55);
            line-height: 1.4;
        }

        /* ── Hero sidebar ────────────────────────────────────────── */
        .hero-side {
            padding: 40px 32px;
            background: var(--base-100);
            display: flex;
            flex-direction: column;
            gap: 20px;
        }

        .hero-side-title {
            font-size: 15px;
            font-weight: 700;
            color: var(--teal-800);
        }

        .hero-side-sub {
            font-size: 12px;
            color: var(--muted);
            margin-top: 4px;
        }

        .role-preview-list {
            display: flex;
            flex-direction: column;
            gap: 10px;
            flex: 1;
        }

        .role-preview {
            display: flex;
            align-items: center;
            gap: 14px;
            padding: 14px 16px;
            border-radius: var(--r-xl);
            background: var(--base-200);
            border: 1px solid var(--border);
            transition: border-color 180ms, background 180ms, box-shadow 180ms;
        }

        .role-preview:hover {
            background: var(--base-100);
            border-color: var(--teal-200);
            box-shadow: var(--shadow-sm);
        }

        .rp-icon {
            width: 36px;
            height: 36px;
            border-radius: var(--r-lg);
            display: grid;
            place-items: center;
            font-size: 10px;
            font-weight: 800;
            letter-spacing: 0.04em;
            flex-shrink: 0;
            font-family: 'DM Mono', monospace;
            color: #fff;
        }

        .rp-name {
            font-size: 13px;
            font-weight: 600;
            color: var(--teal-800);
        }

        .rp-cat {
            font-size: 11px;
            color: var(--muted);
        }

        .rp-arrow {
            margin-left: auto;
            color: var(--muted);
            font-size: 16px;
            transition: transform 180ms, color 180ms;
        }

        .role-preview:hover .rp-arrow {
            transform: translateX(3px);
            color: var(--teal-600);
        }

        .hero-side-note {
            padding: 14px 16px;
            border-radius: var(--r-xl);
            background: linear-gradient(135deg, rgba(4,47,58,.05), rgba(196,82,122,.04));
            border: 1px solid var(--teal-100);
            font-size: 12px;
            color: var(--teal-700);
            line-height: 1.55;
        }

        /* ── Section: role grid ──────────────────────────────────── */
        .grid-section {
            background: var(--base-100);
            border: 1px solid var(--border);
            border-radius: var(--r-3xl);
            padding: 40px 40px 44px;
            box-shadow: var(--shadow-sm);
        }

        .grid-header {
            display: flex;
            align-items: flex-end;
            justify-content: space-between;
            gap: 24px;
            margin-bottom: 32px;
            padding-bottom: 24px;
            border-bottom: 1px solid var(--border);
        }

        .grid-label {
            display: inline-flex;
            align-items: center;
            gap: 6px;
            padding: 4px 12px;
            border-radius: var(--r-full);
            background: rgba(4,47,58,.07);
            border: 1px solid rgba(4,47,58,.12);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.1em;
            text-transform: uppercase;
            color: var(--teal-700);
            margin-bottom: 12px;
        }

        .grid-title {
            font-size: clamp(1.6rem, 3vw, 2.2rem);
            font-weight: 800;
            color: var(--teal-900);
            letter-spacing: -0.03em;
            line-height: 1.1;
        }

        .grid-badge {
            flex-shrink: 0;
            padding: 10px 18px;
            border-radius: var(--r-full);
            background: var(--base-200);
            border: 1px solid var(--border);
            font-size: 11px;
            font-weight: 700;
            color: var(--muted);
            letter-spacing: 0.04em;
            white-space: nowrap;
        }

        /* ── Role card grid ──────────────────────────────────────── */
        .role-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(230px, 1fr));
            gap: 16px;
        }

        .role-card {
            position: relative;
            display: flex;
            flex-direction: column;
            padding: 24px 22px 20px;
            border-radius: var(--r-2xl);
            background: var(--base-100);
            border: 1px solid var(--border);
            box-shadow: var(--shadow-xs);
            text-decoration: none;
            overflow: hidden;
            transition: transform 200ms ease, box-shadow 200ms ease, border-color 200ms ease;
        }

        .role-card::before {
            content: "";
            position: absolute;
            inset: 0 0 auto 0;
            height: 3px;
            background: var(--card-accent, var(--teal-700));
            opacity: 0.8;
        }

        .role-card::after {
            content: "";
            position: absolute;
            right: -32px;
            bottom: -32px;
            width: 120px;
            height: 120px;
            border-radius: 50%;
            background: radial-gradient(circle, var(--card-glow, rgba(4,47,58,.07)) 0%, transparent 70%);
            pointer-events: none;
        }

        .role-card:hover {
            transform: translateY(-4px);
            box-shadow: var(--shadow-lg);
            border-color: rgba(4,47,58,.18);
        }

        /* Category-specific accents */
        .role-card[data-cat="gobierno"]   { --card-accent: linear-gradient(90deg,#042f3a,#28bcd8); --card-glow: rgba(4,47,58,.09); }
        .role-card[data-cat="gestion"]    { --card-accent: linear-gradient(90deg,#064c5c,#5dd4eb); --card-glow: rgba(4,47,58,.08); }
        .role-card[data-cat="direccion"]  { --card-accent: linear-gradient(90deg,#d9a441,#edc46c); --card-glow: rgba(217,164,65,.1); }
        .role-card[data-cat="supervision"]{ --card-accent: linear-gradient(90deg,#6a97c5,#8fb3d9); --card-glow: rgba(106,151,197,.1); }
        .role-card[data-cat="academico"]  { --card-accent: linear-gradient(90deg,#0a6578,#12a0bf); --card-glow: rgba(4,47,58,.07); }
        .role-card[data-cat="estudiante"] { --card-accent: linear-gradient(90deg,#22c55e,#4ade80); --card-glow: rgba(34,197,94,.08); }
        .role-card[data-cat="familia"]    { --card-accent: linear-gradient(90deg,#c4527a,#d05c88); --card-glow: rgba(196,82,122,.09); }
        .role-card[data-cat="operacion"]  { --card-accent: linear-gradient(90deg,#64748b,#94a3b8); --card-glow: rgba(100,116,139,.08); }
        .role-card[data-cat="seguimiento"]{ --card-accent: linear-gradient(90deg,#6a97c5,#28bcd8); --card-glow: rgba(106,151,197,.09); }
        .role-card[data-cat="infra"]      { --card-accent: linear-gradient(90deg,#475569,#94a3b8); --card-glow: rgba(71,85,105,.08); }
        .role-card[data-cat="coordinacion"]{ --card-accent: linear-gradient(90deg,#d9a441,#c4527a); --card-glow: rgba(217,164,65,.09); }
        .role-card[data-cat="bienestar"]  { --card-accent: linear-gradient(90deg,#c4527a,#a03560); --card-glow: rgba(196,82,122,.1); }

        .card-icon {
            width: 46px;
            height: 46px;
            border-radius: var(--r-xl);
            display: grid;
            place-items: center;
            background: var(--teal-900);
            color: #fff;
            font-family: 'DM Mono', monospace;
            font-size: 11px;
            font-weight: 500;
            letter-spacing: 0.06em;
            margin-bottom: 16px;
            box-shadow: 0 6px 14px rgba(4,47,58,.22);
            position: relative;
            z-index: 1;
        }

        .card-name {
            font-size: 14px;
            font-weight: 700;
            color: var(--teal-900);
            margin-bottom: 6px;
            position: relative;
            z-index: 1;
        }

        .card-summary {
            font-size: 12px;
            color: var(--muted);
            line-height: 1.6;
            flex: 1;
            position: relative;
            z-index: 1;
        }

        .card-footer {
            display: flex;
            align-items: center;
            justify-content: space-between;
            gap: 10px;
            margin-top: 18px;
            position: relative;
            z-index: 1;
        }

        .card-pill {
            padding: 4px 10px;
            border-radius: var(--r-full);
            font-size: 10px;
            font-weight: 700;
            letter-spacing: 0.06em;
            text-transform: uppercase;
            background: var(--base-200);
            color: var(--muted);
            border: 1px solid var(--border);
        }

        .card-cta {
            font-size: 12px;
            font-weight: 700;
            color: var(--teal-700);
            display: flex;
            align-items: center;
            gap: 4px;
            letter-spacing: -0.01em;
            transition: gap 180ms;
        }

        .role-card:hover .card-cta { gap: 8px; }

        .card-cta svg {
            width: 14px;
            height: 14px;
            opacity: 0.7;
        }

        /* ── Footer note ─────────────────────────────────────────── */
        .grid-footer {
            margin-top: 28px;
            padding-top: 20px;
            border-top: 1px solid var(--border);
            text-align: center;
            color: var(--muted);
            font-size: 12px;
        }

        /* ── Icon background per category ───────────────────────── */
        .icon-gobierno    { background: #042f3a; }
        .icon-gestion     { background: #064c5c; }
        .icon-direccion   { background: #9a6a05; }
        .icon-supervision { background: #3d6a9a; }
        .icon-academico   { background: #0a6578; }
        .icon-estudiante  { background: #166534; }
        .icon-familia     { background: #862d52; }
        .icon-operacion   { background: #475569; }
        .icon-seguimiento { background: #085a70; }
        .icon-infra       { background: #334155; }
        .icon-coordinacion{ background: #8a5a15; }
        .icon-bienestar   { background: #8a2452; }

        /* ── Responsive ──────────────────────────────────────────── */
        @media (max-width: 1024px) {
            .hero {
                grid-template-columns: 1fr;
            }
            .hero-side {
                border-top: 1px solid var(--border);
            }
            .hero-stats {
                grid-template-columns: repeat(3, minmax(0,1fr));
            }
        }

        @media (max-width: 768px) {
            .shell {
                width: calc(100% - 32px);
                padding: 24px 0 48px;
            }
            .hero-main {
                padding: 36px 28px;
            }
            .hero-side {
                padding: 28px 24px;
            }
            .grid-section {
                padding: 28px 24px 32px;
            }
            .grid-header {
                flex-direction: column;
                align-items: flex-start;
            }
            .hero-stats {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 640px) {
            .site-header {
                flex-direction: column;
                align-items: flex-start;
                gap: 12px;
            }
            .role-grid {
                grid-template-columns: 1fr;
            }
        }
    </style>
</head>
<body>
    @php
        $roles = [
            ['key'=>'superusuario','short'=>'SU','name'=>'Superusuario','summary'=>'Supervisión global de instituciones, usuarios y configuraciones críticas del ecosistema.','category'=>'Gobierno institucional','cat'=>'gobierno'],
            ['key'=>'admin','short'=>'AD','name'=>'Administrador','summary'=>'Gestión de usuarios, configuración institucional, permisos y control operativo.','category'=>'Gestión central','cat'=>'gestion'],
            ['key'=>'directivo','short'=>'DI','name'=>'Directivo','summary'=>'Supervisión estratégica y análisis de indicadores institucionales.','category'=>'Alta dirección','cat'=>'direccion'],
            ['key'=>'inspector','short'=>'IN','name'=>'Inspector','summary'=>'Seguimiento de convivencia, control disciplinario y cumplimiento institucional.','category'=>'Supervisión operativa','cat'=>'supervision'],
            ['key'=>'docente','short'=>'DO','name'=>'Docente','summary'=>'Clases, planificación, evaluación, seguimiento académico y comunicación formativa.','category'=>'Área académica','cat'=>'academico'],
            ['key'=>'estudiante','short'=>'ES','name'=>'Estudiante','summary'=>'Asignaturas, tareas, calificaciones, asistencia y recursos de aprendizaje.','category'=>'Vida estudiantil','cat'=>'estudiante'],
            ['key'=>'padre-tutor','short'=>'PT','name'=>'Padre o Tutor','summary'=>'Monitoreo del progreso del estudiante, avisos institucionales y seguimiento familiar.','category'=>'Acompañamiento','cat'=>'familia'],
            ['key'=>'administrativo','short'=>'PA','name'=>'Personal Administrativo','summary'=>'Matrícula, documentación, reportes internos y soporte a la operación diaria.','category'=>'Operación interna','cat'=>'operacion'],
            ['key'=>'asesor-academico','short'=>'AA','name'=>'Asesor Académico','summary'=>'Orientación curricular, análisis de desempeño y acompañamiento pedagógico.','category'=>'Seguimiento','cat'=>'seguimiento'],
            ['key'=>'soporte-tecnico','short'=>'ST','name'=>'Soporte Técnico','summary'=>'Atención a incidencias, continuidad tecnológica y mantenimiento digital.','category'=>'Infraestructura','cat'=>'infra'],
            ['key'=>'coordinador-curso','short'=>'CC','name'=>'Coordinador de Curso','summary'=>'Supervisión de grupos, seguimiento disciplinario y articulación entre áreas.','category'=>'Coordinación','cat'=>'coordinacion'],
            ['key'=>'dece','short'=>'DE','name'=>'DECE','summary'=>'Apoyo socioemocional, observación integral y acompañamiento especializado.','category'=>'Bienestar','cat'=>'bienestar'],
        ];

        $previewRoles = array_slice($roles, 0, 5);
    @endphp

    <div class="shell">

        {{-- ── Header ─────────────────────────────────────────────── --}}
        <header class="site-header">
            <div class="brand">
                <div class="brand-mark">IE</div>
                <div>
                    <div class="brand-name">IntegraEdu360</div>
                    <div class="brand-sub">Plataforma de gestión académica y administrativa</div>
                </div>
            </div>
            <div class="header-pill">
                <span class="header-pill-dot"></span>
                Sistema activo — Acceso por roles
            </div>
        </header>

        {{-- ── Hero ───────────────────────────────────────────────── --}}
        <section class="hero">
            <div class="hero-main">
                <div class="eyebrow">
                    <span class="eyebrow-dot"></span>
                    Gestión educativa integral
                </div>

                <h1 class="hero-headline">
                    Conectando<br>
                    <em>toda la comunidad</em><br>
                    educativa
                </h1>

                <p class="hero-body">
                    IntegraEdu360 unifica en un solo entorno digital la comunicación, la gestión académica
                    y el seguimiento del aprendizaje. Cada rol accede a un espacio diseñado para sus
                    responsabilidades específicas dentro de la institución.
                </p>

                <div class="hero-stats">
                    <div class="stat">
                        <span class="stat-value">12</span>
                        <span class="stat-label">Roles estratégicos con acceso diferenciado</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">24/7</span>
                        <span class="stat-label">Disponibilidad para operaciones académicas</span>
                    </div>
                    <div class="stat">
                        <span class="stat-value">360°</span>
                        <span class="stat-label">Visión integral del ecosistema institucional</span>
                    </div>
                </div>
            </div>

            <div class="hero-side">
                <div>
                    <div class="hero-side-title">Acceso organizado para toda la comunidad</div>
                    <div class="hero-side-sub">Seleccione su rol para ingresar al panel correspondiente</div>
                </div>

                <div class="role-preview-list">
                    @foreach ($previewRoles as $r)
                    <div class="role-preview">
                        <div class="rp-icon icon-{{ $r['cat'] }}">
                            {{ $r['short'] }}
                        </div>
                        <div>
                            <div class="rp-name">{{ $r['name'] }}</div>
                            <div class="rp-cat">{{ $r['category'] }}</div>
                        </div>
                        <span class="rp-arrow">›</span>
                    </div>
                    @endforeach
                </div>

                <div class="hero-side-note">
                    Cada perfil abre un dashboard institucional con enfoque operativo según su rol y nivel de acceso.
                </div>
            </div>
        </section>

        {{-- ── Role grid ──────────────────────────────────────────── --}}
        <main class="grid-section">
            <div class="grid-header">
                <div class="grid-header-copy">
                    <div class="grid-label">Navegación institucional</div>
                    <h2 class="grid-title">Seleccione su perfil de acceso</h2>
                </div>
                <div class="grid-badge">{{ count($roles) }} roles disponibles</div>
            </div>

            <div class="role-grid">
                @foreach ($roles as $role)
                <a href="{{ route('roles.access', $role['key']) }}" class="role-card" data-cat="{{ $role['cat'] }}">
                    <div class="card-icon icon-{{ $role['cat'] }}">
                        {{ $role['short'] }}
                    </div>
                    <div class="card-name">{{ $role['name'] }}</div>
                    <div class="card-summary">{{ $role['summary'] }}</div>
                    <div class="card-footer">
                        <span class="card-pill">{{ $role['category'] }}</span>
                        <span class="card-cta">
                            Ingresar
                            <svg viewBox="0 0 16 16" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round">
                                <path d="M3 8h10M9 4l4 4-4 4"/>
                            </svg>
                        </span>
                    </div>
                </a>
                @endforeach
            </div>

            <div class="grid-footer">
                Cada perfil abre su propio dashboard institucional con enfoque operativo según su rol.
            </div>
        </main>

    </div>
</body>
</html>
