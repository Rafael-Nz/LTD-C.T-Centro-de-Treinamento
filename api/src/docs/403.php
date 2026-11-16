<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Acesso Restrito</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css"/>
    <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
    <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <main class="flex-fill d-flex">
        <div class="container-lg p-4 d-flex flex-column flex-fill justify-content-center align-items-center text-center">
            <h1 class="display-1 fw-bold text-danger">403</h1>
            <h2 class="mb-4"><?= htmlspecialchars($errorTitle ?? 'Ops! Documentação restrita', ENT_QUOTES, 'UTF-8') ?></h2>
            <p class="mb-4"><?= htmlspecialchars($errorMessage ?? 'A documentação Swagger da API está disponível apenas para administradores autenticados.', ENT_QUOTES, 'UTF-8') ?></p>
            <a href="/ctt/admin" class="btn btn-rosa">
                <i class="ph ph-house me-2"></i> Voltar para a página inicial.
            </a>
        </div>
    </main>

    <!-- Scripts -->
    <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
</body>
</html>
