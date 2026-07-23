<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>IntegraEdu360 - Instituciones Educativas</title>
    <style>
        :root {
            --primary: #1b4f72;
            --secondary: #f39c12;
            --bg: #f4f6f8;
            --text: #34495e;
            --card: #ffffff;
        }

        * { box-sizing: border-box; }
        body { margin: 0; font-family: 'Segoe UI', Roboto, Helvetica, Arial, sans-serif; color: var(--text); background: var(--bg); }

        .hero {
            min-height: 100vh;
            background: linear-gradient(135deg, #1b4f72 0%, #1e6f8c 80%);
            color: white;
            display: flex;
            align-items: center;
            justify-content: center;
            text-align: center;
            padding: 3rem 1rem;
        }

        .hero h1 { font-size: clamp(2rem, 7vw, 4rem); margin: 0 0 0.75rem; line-height: 1.1; }
        .hero p { font-size: clamp(1rem, 2.3vw, 1.5rem); margin-bottom: 1.6rem; }
        .hero .cta { background: var(--secondary); border: 0; color: #fff; border-radius: 999px; padding: 0.95rem 1.7rem; font-weight: 700; font-size: 1rem; cursor: pointer; text-decoration: none; transition: transform .18s ease, box-shadow .18s ease; }
        .hero .cta:hover { transform: translateY(-2px); box-shadow: 0 12px 25px rgba(243, 156, 18, .35); }

        .features { padding: 3rem 1rem; max-width: 1100px; margin: 0 auto; }
        .grid { display: grid; grid-template-columns: repeat(auto-fit, minmax(240px, 1fr)); gap: 1.1rem; }
        .card { background: var(--card); border-radius: 1rem; padding: 1.4rem; box-shadow: 0 10px 26px rgba(0,0,0,.08); border: 1px solid #e2e8f0; }
        .card h3 { margin-top: 0; color: #12416b; }

        .footer { background: #fff; color: #556b7a; text-align: center; padding: 1rem; border-top: 1px solid #d8e2ea; }

        @media (max-width: 768px) {
            .hero { padding: 3rem .75rem; }
        }

    </style>
</head>
<body>
    <header class="hero" role="banner">
        <div>
            <h1>IntegraEdu360: educación digital para instituciones líderes</h1>
            <p>Pantalla única para gestión de alumnos, docentes, cursos y métricas en tiempo real. Más poder para tus decisiones, más claridad para cada ciclo educativo.</p>
            <a class="cta" href="#features">Ver características</a>
        </div>
    </header>

    <main class="features" id="features" role="main">
        <section class="grid">
            <article class="card">
                <h3>Gestión Integral</h3>
                <p>Control total de matrículas, grupos, calificaciones y asistencia desde una experiencia web moderna.</p>
            </article>
            <article class="card">
                <h3>Comunicación en Tiempo Real</h3>
                <p>Mensajería interna, anuncios y notificaciones para familias y docentes en un solo flujo.</p>
            </article>
            <article class="card">
                <h3>Analítica con Impacto</h3>
                <p>Gráficos de rendimiento, tasas de retención y reportes automáticos para mejorar resultados académicos.</p>
            </article>
            <article class="card">
                <h3>Seguridad y Escalabilidad</h3>
                <p>Arquitectura que crece con tu institución, con autenticación, roles y permisos granulares.</p>
            </article>
        </section>
    </main>

    <footer class="footer">
        <p>© {{ date('Y') }} IntegraEdu360 | Para instituciones que quieren transformar la educación.</p>
    </footer>
</body>
</html>