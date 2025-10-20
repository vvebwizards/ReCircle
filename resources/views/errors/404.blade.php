<!doctype html>
<html lang="en">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Page not found — 404</title>
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@300;400;600;700&display=swap" rel="stylesheet">
    <style>
        :root{
            --bg:#0f1724; /* dark-blue background similar to auth pages */
            --card:#0b1220;
            --muted:#94a3b8;
            --accent:#10b981; /* green */
            --danger:#ef4444;
            --glass:rgba(255,255,255,0.03);
            font-family: 'Inter', system-ui, -apple-system, 'Segoe UI', Roboto, 'Helvetica Neue', Arial;
            color-scheme: dark;
        }

        html,body{height:100%;}
        body{
            margin:0;
            background:linear-gradient(180deg,var(--bg) 0%, #071224 100%);
            display:flex;
            align-items:center;
            justify-content:center;
            color:#e6eef8;
            -webkit-font-smoothing:antialiased;
            -moz-osx-font-smoothing:grayscale;
        }

        .container{
            width:min(920px,94%);
            display:grid;
            grid-template-columns: 1fr 420px;
            gap:28px;
            align-items:center;
        }

        .hero{
            padding:36px 40px;
            background:linear-gradient(180deg, rgba(255,255,255,0.02), rgba(255,255,255,0.01));
            border-radius:12px;
            box-shadow: 0 6px 30px rgba(2,6,23,0.6);
        }

        .kicker{display:inline-block;padding:6px 10px;border-radius:999px;background:var(--glass);color:var(--muted);font-weight:600;font-size:13px}

        h1{margin:12px 0 8px;font-size:34px;line-height:1.02}
        p.lead{margin:0 0 18px;color:var(--muted);font-size:15px}

        .actions{display:flex;gap:12px;margin-top:18px}

        .btn{
            display:inline-flex;align-items:center;gap:10px;padding:10px 14px;border-radius:10px;border:0;font-weight:600;cursor:pointer;text-decoration:none;color:inherit
        }

        .btn-primary{background:linear-gradient(90deg,var(--accent),#06a86b);color:#03201a}
        .btn-muted{background:transparent;border:1px solid rgba(255,255,255,0.06);color:var(--muted)}

        .visual{
            padding:34px;background: linear-gradient(180deg, rgba(255,255,255,0.01), rgba(255,255,255,0.015));border-radius:12px;text-align:center;box-shadow: inset 0 -2px 0 rgba(255,255,255,0.02);
        }

        .chip{display:inline-block;padding:8px 12px;border-radius:999px;background:rgba(255,255,255,0.02);color:var(--muted);font-weight:600;margin-bottom:14px}

        .code{
            font-family: ui-monospace, SFMono-Regular, Menlo, Monaco, 'Roboto Mono', 'Courier New', monospace;
            font-weight:700;font-size:86px;letter-spacing:-4px;color:transparent;background:linear-gradient(90deg,#fff, #d9fbd8);-webkit-background-clip:text;background-clip:text;margin:6px 0
        }

        .subtext{color:var(--muted);font-size:14px}

        /* small animated illustration */
        .illustration{
            width:200px;height:200px;margin:12px auto 0;position:relative;display:block
        }

        .dot{width:16px;height:16px;border-radius:50%;position:absolute;background:rgba(255,255,255,0.06)}
        .dot.a{left:10%;top:20%;background:rgba(16,185,129,0.95)}
        .dot.b{right:12%;top:18%;background:rgba(255,255,255,0.06)}
        .dot.c{left:35%;bottom:12%;background:rgba(16,185,129,0.6)}

        @keyframes floaty {0%{transform:translateY(0)}50%{transform:translateY(-8px)}100%{transform:translateY(0)}}
        .dot.a{animation:floaty 3s ease-in-out infinite}
        .dot.c{animation:floaty 4s ease-in-out 0.3s infinite}

        /* reduce motion support */
        @media (prefers-reduced-motion: reduce){
            .dot{animation:none!important}
        }

        @media (max-width:920px){
            .container{grid-template-columns:1fr;}
            .visual{order:-1}
        }

    </style>
</head>
<body>
    <main class="container" role="main">
        <section class="hero" aria-labelledby="title">
            <span class="kicker">Oops — not found</span>
            <h1 id="title">We couldn't find that page</h1>
            <p class="lead">The link you followed may be broken, or the page may have been removed. If you typed the address manually, double-check it.</p>

            <div class="actions" role="group" aria-label="Helpful action">
                <a href="{{ route('dashboard') }}" class="btn btn-primary">Go to dashboard</a>
            </div>
            <p class="subtext" style="margin-top:18px">If you think this is an error, contact support or use the main menu.</p>
        </section>

        <aside class="visual" aria-hidden="true">
            <span class="chip">Error 404</span>
            <div class="code">404</div>
            <div class="illustration" aria-hidden="true">
                <span class="dot a" aria-hidden="true"></span>
                <span class="dot b" aria-hidden="true"></span>
                <span class="dot c" aria-hidden="true"></span>
            </div>
        </aside>
    </main>
</body>
</html>
