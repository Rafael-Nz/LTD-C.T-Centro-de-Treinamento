<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body class="d-flex align-items-center justify-content-center vh-100 p-3">
    <div class="login-user-card position-relative text-center">
        <a href="/ctt/" class="btn-voltar">
            <i class="bi bi-arrow-left-circle"></i>
            <span>Tela Principal</span>
        </a>

        <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T" class="mb-3">
        <h1 class="fw-bold mb-2 fs-4">Cross C.T</h1>
        <p class="text-muted mb-5">Acesse sua conta para continuar</p>

        <form action="/ctt/dashboard" method="post" class="text-start">
            <label for="uEmail" class="form-label fw-semibold text-muted">E-mail:</label>
            <input type="email" id="uEmail" class="form-control mb-3" placeholder="nome@email.com" required>
            <label for="uSenha" class="form-label fw-semibold text-muted">Senha:</label>
            <input type="password" id="uSenha" class="form-control mb-3" placeholder="●●●●●●●●" required>
            <a href="#" class="esqueceu-senha">Esqueceu a senha?</a>
            <button type="submit" class="btn btn-danger w-100 fw-semibold">Entrar</button>
        </form>

    </div>
</body>
</html>
