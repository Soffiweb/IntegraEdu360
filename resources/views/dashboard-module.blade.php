@if ($roleKey === 'admin')
    @extends('layouts.admin-shell')

    @section('title', $module['title'])
    @section('admin-nav', $moduleKey)
    @section('sidebar-panel-copy', 'Este modulo tambien usa la misma vista base del dashboard administrativo para que toda la navegacion de administracion conserve estructura, contexto y estilo.')

    @section('sidebar-panel-items')
        @foreach ($module['side'] as $item)
            <div>
                <strong>{{ $item['title'] }}</strong>
                <p>{{ $item['body'] }}</p>
            </div>
        @endforeach
    @endsection

    @section('crumbs')
        <a href="{{ route('auth.form') }}">Menu principal</a>
        <span>/</span>
        <a href="{{ route('roles.dashboard', 'admin') }}">Dashboard admin</a>
        <span>/</span>
        <span>{{ $module['title'] }}</span>
    @endsection

    @section('content')
        <section class="hero">
            <div>
                <h1>{{ $module['title'] }}</h1>
                <p>{{ $module['subtitle'] }}</p>
            </div>
        </section>

        <section class="stats" style="--stats-columns: 3;">
            @foreach ($module['stats'] as $stat)
                <article class="card">
                    <strong class="metric">{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="content">
            <article class="panel">
                <div class="section-title">
                    <h2>Vista operativa</h2>
                    <span>Datos de ejemplo</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            @foreach ($module['table']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($module['table']['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>

            <aside class="stack">
                @foreach ($module['side'] as $item)
                    <div class="info-block">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['body'] }}</p>
                    </div>
                @endforeach
                <div class="info-block">
                    <strong>Continuidad del flujo</strong>
                    <p>Este acceso ya navega sobre la base comun del administrador y queda listo para evolucionar a CRUD real cuando corresponda.</p>
                </div>
            </aside>
        </section>
    @endsection
@elseif ($roleKey === 'inspector')
    @extends('layouts.inspector-shell')

    @section('title', $module['title'])
    @section('inspector-nav', $moduleKey)
    @section('sidebar-title', 'Inspector institucional')
    @section('sidebar-body', 'Panel de control diario para convivencia, observaciones y seguimiento de casos.')
    @section('sidebar-panel-copy', 'Cada acceso del inspector mantiene la misma base visual del panel principal para conservar contexto y continuidad operativa.')

    @section('sidebar-panel-items')
        @foreach ($module['side'] as $item)
            <div>
                <strong>{{ $item['title'] }}</strong>
                <p>{{ $item['body'] }}</p>
            </div>
        @endforeach
    @endsection

    @section('crumbs')
        <a href="{{ route('auth.form') }}">Menu principal</a>
        <span>/</span>
        <a href="{{ route('roles.dashboard', 'inspector') }}">Dashboard inspector</a>
        <span>/</span>
        <span>{{ $module['title'] }}</span>
    @endsection

    @section('content')
        <section class="hero">
            <div>
                <h1>{{ $module['title'] }}</h1>
                <p>{{ $module['subtitle'] }}</p>
            </div>
            <div class="hero-side">
                <strong>Resumen del modulo</strong>
                <p>{{ $module['summary'] }}</p>
            </div>
        </section>

        <section class="stats" style="--stats-columns: 3;">
            @foreach ($module['stats'] as $stat)
                <article class="card">
                    <strong class="metric">{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="content">
            <article class="panel">
                <div class="section-title">
                    <h2>Vista operativa</h2>
                    <span>Datos de ejemplo</span>
                </div>

                <table>
                    <thead>
                        <tr>
                            @foreach ($module['table']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($module['table']['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>

            <aside class="stack">
                @foreach ($module['side'] as $item)
                    <div class="info-block">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['body'] }}</p>
                    </div>
                @endforeach
                <div class="info-block">
                    <strong>Continuidad del flujo</strong>
                    <p>Este acceso ya navega sobre la misma base del dashboard del inspector y queda listo para conectarse a procesos reales cuando corresponda.</p>
                </div>
            </aside>
        </section>
    @endsection
@else
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>{{ $module['title'] }} - {{ $role['name'] }} - IntegraEdu360</title>
    <style>
        :root {
            --navy-950: #081a2b;
            --navy-900: #11314f;
            --slate-50: #f4f7fb;
            --slate-100: #e6edf5;
            --slate-200: #d4dfeb;
            --slate-500: #5c6f83;
            --slate-700: #2a3d51;
            --white: #ffffff;
            --accent: {{ $role['accent'] }};
            --shadow-soft: 0 20px 46px rgba(8, 26, 43, 0.12);
            --shadow-card: 0 14px 30px rgba(8, 26, 43, 0.08);
        }
        * { box-sizing: border-box; }
        body {
            margin: 0;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--slate-700);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--accent) 14%, transparent), transparent 22%),
                linear-gradient(160deg, #eef3f8 0%, #f8fbfd 54%, #eef3f8 100%);
        }
        .page { width: min(1240px, calc(100% - 32px)); margin: 0 auto; padding: 28px 0 42px; }
        .topbar, .hero, .card, .panel { background: rgba(255,255,255,.95); border: 1px solid rgba(212,223,235,.9); border-radius: 24px; box-shadow: var(--shadow-card); }
        .topbar { padding: 18px 22px; display: flex; align-items: center; justify-content: space-between; gap: 16px; margin-bottom: 18px; }
        .crumbs { display: flex; flex-wrap: wrap; gap: 10px; color: var(--slate-500); font-weight: 700; }
        .crumbs a { color: var(--navy-900); text-decoration: none; }
        .btn { display: inline-flex; align-items: center; justify-content: center; min-height: 44px; padding: 0 16px; border-radius: 14px; border: 1px solid var(--slate-200); background: #fff; color: var(--navy-900); text-decoration: none; font-weight: 800; }
        .hero { padding: 28px; margin-bottom: 22px; background: linear-gradient(135deg, rgba(8,26,43,.98), rgba(17,49,79,.95)); color: var(--white); box-shadow: var(--shadow-soft); }
        .hero p { color: rgba(255,255,255,.8); max-width: 820px; }
        .eyebrow { display: inline-flex; padding: 8px 12px; border-radius: 999px; background: rgba(255,255,255,.1); border: 1px solid rgba(255,255,255,.12); text-transform: uppercase; font-size: .78rem; font-weight: 800; margin-bottom: 14px; }
        .stats { display: grid; grid-template-columns: repeat(3, minmax(0,1fr)); gap: 18px; margin-bottom: 22px; }
        .card, .panel { padding: 22px; }
        .metric { display: block; color: var(--navy-950); font-size: 2rem; margin-bottom: 8px; }
        .content { display: grid; grid-template-columns: minmax(0, 1.15fr) minmax(300px, 0.85fr); gap: 20px; }
        .section-title { display: flex; align-items: center; justify-content: space-between; gap: 12px; margin-bottom: 18px; }
        .section-title h2 { margin: 0; color: var(--navy-950); }
        .section-title span, .card span, .info-block p { color: var(--slate-500); }
        .stack { display: grid; gap: 12px; }
        .info-block { padding: 16px; border-radius: 18px; background: linear-gradient(180deg, #ffffff, #f7f9fc); border: 1px solid var(--slate-100); }
        .info-block strong { display: block; margin-bottom: 8px; color: var(--navy-900); }
        table { width: 100%; border-collapse: collapse; }
        th, td { padding: 14px 12px; text-align: left; border-bottom: 1px solid var(--slate-100); }
        th { color: var(--navy-900); font-size: .84rem; text-transform: uppercase; }
        @media (max-width: 980px) { .stats, .content { grid-template-columns: 1fr; } }
        @media (max-width: 720px) { .topbar { flex-direction: column; align-items: flex-start; } table, thead, tbody, th, td, tr { display: block; } thead { display: none; } td { padding: 6px 0; border-bottom: 0; } }
    </style>
</head>
<body>
    <div class="page">
        <div class="topbar">
            <div class="crumbs">
                <a href="{{ route('auth.form') }}">Menu principal</a>
                <span>/</span>
                <a href="{{ route('roles.dashboard', $roleKey) }}">{{ $role['name'] }}</a>
                <span>/</span>
                <span>{{ $module['title'] }}</span>
            </div>
            <a class="btn" href="{{ route('roles.dashboard', $roleKey) }}">Volver al dashboard</a>
        </div>

        <section class="hero">
            <div class="eyebrow">{{ $role['category'] }}</div>
            <h1>{{ $module['title'] }}</h1>
            <p>{{ $module['subtitle'] }}</p>
        </section>

        <section class="stats">
            @foreach ($module['stats'] as $stat)
                <article class="card">
                    <strong class="metric">{{ $stat['value'] }}</strong>
                    <span>{{ $stat['label'] }}</span>
                </article>
            @endforeach
        </section>

        <section class="content">
            <article class="panel">
                <div class="section-title">
                    <h2>Vista operativa</h2>
                    <span>Datos de ejemplo</span>
                </div>
                <table>
                    <thead>
                        <tr>
                            @foreach ($module['table']['columns'] as $column)
                                <th>{{ $column }}</th>
                            @endforeach
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($module['table']['rows'] as $row)
                            <tr>
                                @foreach ($row as $cell)
                                    <td>{{ $cell }}</td>
                                @endforeach
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </article>

            <aside class="stack">
                @foreach ($module['side'] as $item)
                    <div class="info-block">
                        <strong>{{ $item['title'] }}</strong>
                        <p>{{ $item['body'] }}</p>
                    </div>
                @endforeach
            </aside>
        </section>
    </div>
</body>
</html>
@endif
