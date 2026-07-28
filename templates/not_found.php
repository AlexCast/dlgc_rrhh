<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Error 404 - Página no encontrada</title>
    <link rel="icon" type="image/png" sizes="32x32" href="/dlgc_rrhh/assets/img/favicon.ico">
    <style>
        :root {
            --bg-main: #ffffff;
            --bg-gradient: radial-gradient(circle at 50% 0%, rgba(59, 168, 106, 0.12) 0%, transparent 55%);
            --text-main: #0D130F;
            --text-muted: #6b7280;
            --accent: #3ba86a;
            --accent-hover: #298350;
            --accent-light: #e8f5ed;
            --card-bg: #ffffff;
            --border-soft: #e5e7eb;
            --shadow-soft: 0 24px 60px rgba(13, 19, 15, 0.12);
            --font-sans: 'Inter', system-ui, -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif;
        }

        html.dark-theme,
        body.dark-theme {
            --bg-main: #0D130F;
            --bg-gradient: radial-gradient(circle at 50% 0%, rgba(95, 208, 138, 0.10) 0%, transparent 55%);
            --text-main: #f9fafb;
            --text-muted: #9ca3af;
            --accent: #5fd08a;
            --accent-hover: #3ba86a;
            --accent-light: #13261b;
            --card-bg: #131B15;
            --border-soft: #303136;
            --shadow-soft: 0 24px 60px rgba(0, 0, 0, 0.35);
        }

        * {
            box-sizing: border-box;
            margin: 0;
            padding: 0;
        }

        body {
            min-height: 100vh;
            display: flex;
            flex-direction: column;
            align-items: center;
            justify-content: center;
            padding: 24px;
            font-family: var(--font-sans);
            color: var(--text-main);
            background-color: var(--bg-main);
            background-image: var(--bg-gradient);
            transition: background-color 0.3s ease, color 0.3s ease;
        }

        .error-card {
            width: 100%;
            max-width: 680px;
            background: var(--card-bg);
            border: 1px solid var(--border-soft);
            border-radius: 20px;
            box-shadow: var(--shadow-soft);
            padding: 48px 40px;
            text-align: center;
            animation: fade-slide-up 500ms ease-out;
        }

        .logo {
            display: inline-flex;
            margin-bottom: 28px;
            line-height: 0;
        }

        .logo img {
            height: 44px;
            width: auto;
            display: block;
        }

        .error-illustration {
            width: 160px;
            height: 160px;
            margin: 0 auto 24px;
            color: var(--accent);
            background: var(--accent-light);
            border-radius: 50%;
            display: flex;
            align-items: center;
            justify-content: center;
        }

        .error-illustration svg {
            width: 84px;
            height: 84px;
        }

        .error-code {
            display: inline-block;
            margin-bottom: 12px;
            padding: 6px 16px;
            border-radius: 999px;
            font-size: 0.82rem;
            letter-spacing: 0.08em;
            font-weight: 700;
            text-transform: uppercase;
            color: var(--accent);
            background: var(--accent-light);
            border: 1px solid rgba(59, 168, 106, 0.25);
        }

        h1 {
            font-size: clamp(1.7rem, 3.5vw, 2.4rem);
            margin-bottom: 12px;
            line-height: 1.2;
        }

        p {
            font-size: 1rem;
            line-height: 1.7;
            color: var(--text-muted);
            margin: 0 auto 32px;
            max-width: 52ch;
        }

        .actions {
            display: inline-flex;
            flex-wrap: wrap;
            align-items: center;
            justify-content: center;
            gap: 12px;
        }

        .btn {
            display: inline-flex;
            align-items: center;
            justify-content: center;
            gap: 8px;
            text-decoration: none;
            border: 1px solid transparent;
            border-radius: 10px;
            padding: 12px 22px;
            font-size: 0.96rem;
            font-weight: 600;
            cursor: pointer;
            transition: background-color 160ms ease, transform 160ms ease, box-shadow 160ms ease;
        }

        .btn-primary {
            background: var(--accent);
            color: #ffffff;
            box-shadow: 0 10px 24px rgba(59, 168, 106, 0.24);
        }

        .btn-primary:hover {
            background: var(--accent-hover);
            transform: translateY(-1px);
        }

        .btn-secondary {
            background: transparent;
            color: var(--text-main);
            border-color: var(--border-soft);
        }

        .btn-secondary:hover {
            background: rgba(0, 0, 0, 0.03);
            border-color: var(--text-muted);
        }

        .btn:focus-visible {
            outline: 3px solid rgba(59, 168, 106, 0.3);
            outline-offset: 3px;
        }

        .theme-toggle {
            position: fixed;
            top: 20px;
            right: 20px;
            width: 44px;
            height: 44px;
            border-radius: 50%;
            border: 1px solid var(--border-soft);
            background: var(--card-bg);
            color: var(--text-main);
            display: flex;
            align-items: center;
            justify-content: center;
            cursor: pointer;
            transition: background-color 0.2s ease, transform 0.2s ease;
            box-shadow: var(--shadow-soft);
        }

        .theme-toggle:hover {
            transform: translateY(-1px);
        }

        .theme-toggle:focus-visible {
            outline: 3px solid rgba(59, 168, 106, 0.3);
            outline-offset: 3px;
        }

        @keyframes fade-slide-up {
            from {
                opacity: 0;
                transform: translateY(16px);
            }
            to {
                opacity: 1;
                transform: translateY(0);
            }
        }

        @media (max-width: 480px) {
            .error-card {
                padding: 36px 24px;
                border-radius: 16px;
            }

            .error-illustration {
                width: 130px;
                height: 130px;
            }

            .error-illustration svg {
                width: 68px;
                height: 68px;
            }

            .actions {
                flex-direction: column;
                width: 100%;
            }

            .btn {
                width: 100%;
            }
        }
    </style>
</head>
<body>
    <button class="theme-toggle" id="theme-toggle" aria-label="Cambiar modo oscuro/claro">
        <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
            <path d="M21 12.79A9 9 0 1 1 11.21 3 7 7 0 0 0 21 12.79z"></path>
        </svg>
    </button>

    <main class="error-card" role="main" aria-labelledby="page-title">
        <a href="/dlgc_rrhh/templates/index.html" class="logo" aria-label="Volver al inicio">
            <img src="/dlgc_rrhh/assets/img/logo1.png" alt="Distribuciones La Gran Cacharrería">
        </a>

        <div class="error-illustration" aria-hidden="true">
            <svg viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.6" stroke-linecap="round" stroke-linejoin="round">
                <path d="M10 13a5 5 0 0 0 7.54.54l3-3a5 5 0 0 0-7.07-7.07l-1.72 1.71"></path>
                <path d="M14 11a5 5 0 0 0-7.54-.54l-3 3a5 5 0 0 0 7.07 7.07l1.71-1.71"></path>
                <line x1="4" y1="4" x2="20" y2="20"></line>
            </svg>
        </div>

        <span class="error-code">Error 404</span>
        <h1 id="page-title">Página no encontrada</h1>
        <p>
            Parece que el enlace que seguiste ya no existe o la dirección fue escrita incorrectamente. Verifica la URL o regresa al inicio.
        </p>

        <div class="actions">
            <a class="btn btn-primary" href="/dlgc_rrhh/templates/firstpage.php">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M3 9l9-7 9 7v11a2 2 0 0 1-2 2H5a2 2 0 0 1-2-2z"></path>
                    <polyline points="9 22 9 12 15 12 15 22"></polyline>
                </svg>
                Ir al inicio
            </a>
            <button class="btn btn-secondary" type="button" onclick="history.back()">
                <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" aria-hidden="true">
                    <path d="M15 18l-6-6 6-6"></path>
                </svg>
                Volver atrás
            </button>
        </div>
    </main>

    <script>
        (function () {
            const key = 'dlgc_theme';
            const root = document.documentElement;
            const saved = localStorage.getItem(key);
            if (saved === 'dark') root.classList.add('dark-theme');

            document.getElementById('theme-toggle').addEventListener('click', () => {
                root.classList.toggle('dark-theme');
                localStorage.setItem(key, root.classList.contains('dark-theme') ? 'dark' : 'light');
            });
        })();
    </script>
</body>
</html>
