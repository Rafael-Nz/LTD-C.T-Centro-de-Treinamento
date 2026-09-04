<?php $token = $_GET['token'] ?? ''; ?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Ativar conta</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIlFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/ctt/css/login.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="brand-title">CROSS <span>C.T</span></h1>
            <p class="brand-subtitle">Ativar conta</p>
        </div>
        <div id="activation-error" class="alert alert-danger alert-custom d-none" role="alert"></div>
        <div id="activation-success" class="alert alert-success alert-custom d-none" role="alert"></div>
        <form id="account-activation-form" data-token="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="nova_senha" class="form-label">Defina sua senha</label>
                <input type="password" class="form-control form-control-dark" id="nova_senha" required minlength="12" maxlength="128" autocomplete="new-password">
            </div>
            <div class="mb-3">
                <label for="confirmar_senha" class="form-label">Confirme sua senha</label>
                <input type="password" class="form-control form-control-dark" id="confirmar_senha" required minlength="12" maxlength="128" autocomplete="new-password">
            </div>
            <p class="form-text" style="color:#aaa">12 a 128 caracteres, com numero, maiuscula, minuscula e simbolo.</p>
            <button id="activation-submit" type="submit" class="btn btn-login w-100">Ativar conta</button>
        </form>
        <div class="text-center mt-4"><a href="/ctt/admin/login" class="back-link">Voltar para o login</a></div>
    </div>
    <script src="/ctt/js/admin/account-activation.js"></script>
</body>

</html>
