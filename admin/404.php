<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Página não encontrada</title>
    <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
    <link rel="stylesheet" href="../public/css/admin-styles.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill justify-content-center align-items-center text-center">
            <h1 class="display-1 fw-bold text-danger">404</h1>
            <h2 class="mb-4">Ops! Página não encontrada</h2>
            <p class="mb-4">A página que você está tentando acessar não existe ou foi movida.</p>
            <a href="../admin" class="btn btn-rosa">
                <i class="ph ph-house me-2"></i> Voltar para o Dashboard
            </a>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script defer src="../public/js/admin/sidebar.js"></script>
</body>
</html>
