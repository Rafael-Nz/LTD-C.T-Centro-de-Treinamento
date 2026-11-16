<?php

namespace Docs;

class DocsController
{
    public function ui(): void
    {
        // Envia cabeçalho HTML explicitamente para sobrepor o default JSON do bootstrap
        header('Content-Type: text/html; charset=utf-8');
        header('X-Frame-Options: SAMEORIGIN');
        // Swagger UI é servido localmente (sem CDN); a CSP global da API é relaxada somente aqui.
        header("Content-Security-Policy: default-src 'none'; script-src 'self' 'unsafe-inline'; style-src 'self' 'unsafe-inline'; img-src 'self' data:; font-src 'self' data:; connect-src 'self'; base-uri 'none'; form-action 'none'; frame-ancestors 'self'");

        $csrf = $_COOKIE['CTT_CSRF_TOKEN'] ?? '';

        echo <<<HTML
<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Documentação da API (Swagger)</title>
    <link rel="icon" type="image/png" href="/ctt/public/img/logo.png">
    <link rel="stylesheet" href="/ctt/plugins/swagger-ui/swagger-ui.css">
    <style>
        :root {
            --brand-primary: #dc3545;
            --brand-dark: #1e293b;
        }
        body {
            margin: 0;
            padding: 0;
            background: #fafafa;
            font-family: 'Manrope', -apple-system, BlinkMacSystemFont, sans-serif;
        }
        .topbar-custom {
            background-color: var(--brand-dark);
            padding: 12px 24px;
            display: flex;
            align-items: center;
            justify-content: space-between;
            color: #ffffff;
            box-shadow: 0 2px 8px rgba(0,0,0,0.15);
        }
        .topbar-brand {
            display: flex;
            align-items: center;
            gap: 12px;
            text-decoration: none;
            color: #ffffff;
        }
        .topbar-brand img {
            width: 34px;
            height: 34px;
            border-radius: 50%;
        }
        .topbar-title {
            font-size: 1.15rem;
            font-weight: 700;
            letter-spacing: -0.02em;
        }
        .topbar-badge {
            background-color: var(--brand-primary);
            color: #fff;
            padding: 2px 8px;
            font-size: 0.75rem;
            font-weight: 700;
            border-radius: 9999px;
            text-transform: uppercase;
        }
        .topbar-links {
            display: flex;
            align-items: center;
            gap: 16px;
        }
        .topbar-link {
            color: #cbd5e1;
            text-decoration: none;
            font-size: 0.875rem;
            font-weight: 600;
            transition: color 0.2s;
        }
        .topbar-link:hover {
            color: #ffffff;
        }
        .swagger-ui .topbar {
            display: none !important;
        }
        .swagger-ui .info {
            margin: 25px 0;
        }
        .swagger-ui .info .title {
            font-family: 'Manrope', sans-serif;
            font-weight: 800;
            color: #0f172a;
        }
        .swagger-ui .scheme-container {
            background: #ffffff;
            box-shadow: 0 1px 3px rgba(0,0,0,0.05);
            padding: 16px;
            border-radius: 8px;
            margin-bottom: 20px;
        }
        .swagger-ui .opblock {
            border-radius: 8px;
            box-shadow: 0 1px 2px rgba(0,0,0,0.04);
        }
        .swagger-ui .btn.authorize {
            border-color: var(--brand-primary);
            color: var(--brand-primary);
        }
        .swagger-ui .btn.authorize svg {
            fill: var(--brand-primary);
        }
    </style>
</head>
<body>
    <header class="topbar-custom">
        <a href="/ctt/admin" class="topbar-brand">
            <img src="/ctt/public/img/logo.png" alt="Cross C.T Logo">
            <span class="topbar-title">Cross C.T</span>
            <span class="topbar-badge">API Docs</span>
        </a>
        <nav class="topbar-links">
            <a href="/ctt/api/openapi.json" target="_blank" class="topbar-link">OpenAPI JSON</a>
            <a href="/ctt/admin" class="topbar-link">Painel Administrativo &rarr;</a>
        </nav>
    </header>

    <div id="swagger-ui"></div>

    <script src="/ctt/plugins/swagger-ui/swagger-ui-bundle.js"></script>
    <script src="/ctt/plugins/swagger-ui/swagger-ui-standalone-preset.js"></script>
    <script>
        window.onload = function() {
            const ui = SwaggerUIBundle({
                url: "/ctt/api/openapi.json",
                dom_id: '#swagger-ui',
                deepLinking: true,
                presets: [
                    SwaggerUIBundle.presets.apis,
                    SwaggerUIStandalonePreset
                ],
                plugins: [
                    SwaggerUIBundle.plugins.DownloadUrl
                ],
                layout: "StandaloneLayout",
                persistAuthorization: true,
                displayRequestDuration: true,
                docExpansion: "list",
                filter: true,
                tryItOutEnabled: true,
                requestInterceptor: function(req) {
                    // Garante que o cookie da sessão seja enviado nas requisições do Try It Out
                    req.credentials = 'same-origin';
                    
                    // Anexa token CSRF automático caso exista o cookie CTT_CSRF_TOKEN
                    const csrfMatch = document.cookie.match(/(?:^|; )CTT_CSRF_TOKEN=([^;]*)/);
                    if (csrfMatch && csrfMatch[1] && !req.headers['X-CSRF-Token']) {
                        req.headers['X-CSRF-Token'] = decodeURIComponent(csrfMatch[1]);
                    }
                    return req;
                }
            });
            window.ui = ui;
        };
    </script>
</body>
</html>
HTML;
    }

    public function openapi(): void
    {
        header('Content-Type: application/json; charset=utf-8');

        $specFile = __DIR__ . '/openapi.json';
        if (is_file($specFile)) {
            readfile($specFile);
            return;
        }

        http_response_code(404);
        echo json_encode([
            'success' => false,
            'message' => 'Especificação OpenAPI não encontrada.'
        ], JSON_UNESCAPED_UNICODE);
    }
}
