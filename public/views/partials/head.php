<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/dashboard.css">
    <?php if (!empty($pageCss)): ?>
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/<?= $pageCss ?>">
    <?php endif; ?>
    <?php if (!empty($extraCss) && is_array($extraCss)): ?>
    <?php foreach ($extraCss as $cssFile): ?>
    <link rel="stylesheet" href="<?= $cssFile ?>">
    <?php endforeach; ?>
    <?php endif; ?>
    <link href="/ctt/plugins/fonts/inter.css" rel="stylesheet">
    <link href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
    <title><?= $pageTitle ?? 'Cross C.T - Painel' ?></title>
    <script>
        window.AUTH_USER_ID = <?= json_encode(\Core\Auth\Auth::aluno()->id() ?? null) ?>;
    </script>
</head>
