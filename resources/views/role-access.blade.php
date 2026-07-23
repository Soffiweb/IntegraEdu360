<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Acceso {{ $role['name'] }} - IntegraEdu360</title>
    <style>
        :root {
            --navy-950: #0a1f33;
            --navy-900: #11314f;
            --slate-50: #f4f7fb;
            --slate-100: #e8eef5;
            --slate-200: #d4dfeb;
            --slate-500: #5a6c80;
            --slate-700: #2b3d51;
            --white: #ffffff;
            --success-soft: #e8f6ee;
            --success-text: #1f6b43;
            --danger-soft: #fdecec;
            --danger-text: #a63d3d;
            --shadow-soft: 0 22px 54px rgba(7, 25, 45, 0.14);
            --accent: {{ $role['accent'] }};
            --accent-soft: color-mix(in srgb, {{ $role['accent'] }} 16%, white);
            --accent-strong: color-mix(in srgb, {{ $role['accent'] }} 72%, #0a1f33);
        }

        * {
            box-sizing: border-box;
        }

        body {
            margin: 0;
            min-height: 100vh;
            font-family: "Segoe UI", "Helvetica Neue", Arial, sans-serif;
            color: var(--slate-700);
            background:
                radial-gradient(circle at top right, color-mix(in srgb, var(--accent) 20%, transparent), transparent 24%),
                radial-gradient(circle at bottom left, rgba(17, 49, 79, 0.08), transparent 28%),
                linear-gradient(160deg, #edf3f8 0%, #f8fbfd 52%, #eef3f8 100%);
        }

        .page {
            width: min(1180px, calc(100% - 32px));
            margin: 0 auto;
            padding: 32px 0 48px;
        }

        .back-link {
            display: inline-flex;
            align-items: center;
            gap: 10px;
            margin-bottom: 20px;
            color: var(--navy-900);
            text-decoration: none;
            font-weight: 800;
        }

        .layout {
            display: grid;
            grid-template-columns: minmax(0, 1.05fr) minmax(360px, 0.95fr);
            gap: 24px;
            align-items: start;
        }

        .hero,
        .panel {
            border-radius: 30px;
            box-shadow: var(--shadow-soft);
        }

        .hero {
            position: relative;
            overflow: hidden;
            padding: 36px;
            color: var(--white);
            background: linear-gradient(135deg, rgba(10, 31, 51, 0.98), rgba(17, 49, 79, 0.95));
        }

        .hero::after {
            content: "";
            position: absolute;
            width: 320px;
            height: 320px;
            right: -120px;
            top: -110px;
            border-radius: 50%;
            background: radial-gradient(circle, color-mix(in srgb, var(--accent) 34%, transparent), transparent 68%);
        }

        .hero > * {
            position: relative;
            z-index: 1;
        }

        .eyebrow {
            display: inline-flex;
            align-items: center;
            padding: 8px 14px;
            border-radius: 999px;
            background: rgba(255, 255, 255, 0.1);
            border: 1px solid rgba(255, 255, 255, 0.12);
            font-size: 0.8rem;
            text-transform: uppercase;
            letter-spacing: 0.12em;
            font-weight: 800;
            margin-bottom: 18px;
        }

        h1 {
            margin: 0 0 12px;
            font-size: clamp(1.8rem, 3vw, 2.8rem);
            line-height: 1.12;
        }

        .lead {
            margin: 0 0 26px;
            color: rgba(255, 255, 255, 0.82);
            line-height: 1.7;
            max-width: 640px;
        }

        .identity-card,
        .info-card,
        .form-panel {
            background: rgba(255, 255, 255, 0.96);
            color: var(--slate-700);
            border-radius: 24px;
            padding: 24px;
        }

        .identity-card {
            border-top: 5px solid var(--accent);
            margin-bottom: 16px;
        }

        .role-mark {
            width: 62px;
            height: 62px;
            border-radius: 18px;
            display: grid;
            place-items: center;
            background: linear-gradient(145deg, var(--navy-950), var(--navy-900));
            color: var(--white);
            font-size: 1.4rem;
            font-weight: 800;
            margin-bottom: 16px;
        }

        .identity-card strong,
        .info-card strong,
        .form-panel h2 {
            display: block;
            color: var(--navy-950);
        }

        .identity-card span,
        .info-card span {
            color: var(--slate-500);
            line-height: 1.6;
        }

        .panel {
            background: rgba(255, 255, 255, 0.94);
            border: 1px solid rgba(193, 206, 219, 0.56);
            padding: 28px;
        }

        .form-panel h2 {
            margin: 0 0 8px;
            font-size: 1.7rem;
        }

        .form-panel > p {
            margin: 0 0 22px;
            color: var(--slate-500);
            line-height: 1.6;
        }

        .alert {
            margin-bottom: 18px;
            padding: 14px 16px;
            border-radius: 16px;
            font-weight: 700;
            line-height: 1.5;
        }

        .alert-error {
            background: var(--danger-soft);
            color: var(--danger-text);
        }

        .alert-success {
            background: var(--success-soft);
            color: var(--success-text);
        }

        form {
            display: grid;
            gap: 16px;
        }

        .field {
            display: grid;
            gap: 8px;
        }

        label {
            color: var(--navy-900);
            font-weight: 800;
            font-size: 0.94rem;
        }

        input {
            width: 100%;
            min-height: 52px;
            padding: 0 16px;
            border-radius: 16px;
            border: 1px solid var(--slate-200);
            background: var(--white);
            color: var(--slate-700);
            font-size: 1rem;
            outline: none;
            transition: border-color 0.18s ease, box-shadow 0.18s ease;
        }

        input:focus {
            border-color: var(--accent);
            box-shadow: 0 0 0 4px color-mix(in srgb, var(--accent) 18%, white);
        }

        .field-error {
            color: var(--danger-text);
            font-size: 0.88rem;
            font-weight: 700;
        }

        .form-actions {
            display: flex;
            flex-wrap: wrap;
            gap: 12px;
            margin-top: 4px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            min-height: 52px;
            padding: 0 20px;
            border-radius: 16px;
            border: 0;
            text-decoration: none;
            font-weight: 800;
            cursor: pointer;
            transition: transform 0.18s ease, box-shadow 0.18s ease;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--accent-strong), var(--accent));
            color: var(--white);
            box-shadow: 0 16px 30px color-mix(in srgb, var(--accent) 28%, transparent);
        }

        .btn-secondary {
            background: var(--slate-50);
            color: var(--navy-900);
            border: 1px solid var(--slate-200);
        }

        .btn:hover {
            transform: translateY(-2px);
        }

        .helper {
            margin-top: 20px;
            padding: 18px;
            border-radius: 18px;
            background: linear-gradient(180deg, #fff, #f6f9fc);
            border: 1px solid var(--slate-100);
        }

        .helper strong {
            display: block;
            margin-bottom: 8px;
            color: var(--navy-900);
        }

        .helper p {
            margin: 0;
            color: var(--slate-500);
            line-height: 1.6;
        }

        .helper code {
            display: inline-block;
            margin-top: 10px;
            padding: 8px 10px;
            border-radius: 12px;
            background: var(--accent-soft);
            color: var(--navy-900);
            font-weight: 800;
        }

        @media (max-width: 980px) {
            .layout {
                grid-template-columns: 1fr;
            }
        }

        @media (max-width: 680px) {
            .page {
                width: min(100% - 20px, 1180px);
                padding-top: 20px;
            }

            .hero,
            .panel {
                padding: 24px;
            }

            .form-actions {
                flex-direction: column;
            }
        }
    </style>
</head>
<body>
    <div class="page">
        <a class="back-link" href="{{ route('auth.form') }}">&#8592; Volver al menú principal</a>

        <div class="layout">
            <section class="hero">
                <div class="eyebrow">{{ $role['category'] }}</div>
                <h1>Acceso de {{ $role['name'] }}</h1>
                <p class="lead">
                    {{ $role['description'] }}
                    Ingrese con su usuario y contraseña para activar el entorno correspondiente a este perfil.
                </p>

                <div class="identity-card">
                    <div class="role-mark">{{ $role['short'] }}</div>
                    <strong>{{ $role['name'] }}</strong>
                    <span>{{ $role['summary'] }}</span>
                </div>

                <div class="info-card">
                    <strong>Perfil seleccionado</strong>
                    <span>Este acceso queda vinculado al rol <b>{{ $role['name'] }}</b>. Si necesita entrar con otro perfil, cambie de selección antes de iniciar sesión.</span>
                </div>
            </section>

            <aside class="panel form-panel">
                <h2>Ingrese sus credenciales</h2>
                <p>Complete su usuario y contraseña para acceder con el perfil seleccionado.</p>

                @if (session('error'))
                    <div class="alert alert-error">{{ session('error') }}</div>
                @endif

                @if (session('status'))
                    <div class="alert alert-success">{{ session('status') }}</div>
                @endif

                <form method="POST" action="{{ route('auth.post') }}">
                    @csrf
                    <input type="hidden" name="role" value="{{ old('role', $roleKey) }}">

                    <div class="field">
                        <label for="username">Usuario</label>
                        <input
                            id="username"
                            name="username"
                            type="text"
                            value="{{ old('username') }}"
                            placeholder="Ingrese su usuario"
                            autocomplete="username"
                            required
                        >
                        @error('username')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    <div class="field">
                        <label for="password">Contraseña</label>
                        <input
                            id="password"
                            name="password"
                            type="password"
                            placeholder="Ingrese su contraseña"
                            autocomplete="current-password"
                            required
                        >
                        @error('password')
                            <div class="field-error">{{ $message }}</div>
                        @enderror
                    </div>

                    @error('role')
                        <div class="field-error">{{ $message }}</div>
                    @enderror

                    <div class="form-actions">
                        <button class="btn btn-primary" type="submit">Ingresar al sistema</button>
                        <a class="btn btn-secondary" href="{{ route('auth.form') }}">Cambiar perfil</a>
                    </div>
                </form>

                <div class="helper">
                    <strong>Credenciales de demostración</strong>
                    <p>Para pruebas rápidas, puede usar el mismo identificador del rol como usuario. Ejemplo para este perfil:</p>
                    <code>{{ $roleKey }}</code>
                </div>
            </aside>
        </div>
    </div>
</body>
</html>
