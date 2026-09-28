<!DOCTYPE html>
<html lang="pt-BR">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Login</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/home-login.css">
    <link href="https://fonts.googleapis.com/css2?family=Inter:wght@400;500;600;700&display=swap" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.11.3/font/bootstrap-icons.min.css" rel="stylesheet">
</head>
<body>
    <div class="login-wrapper">
        <div class="login-split">
            <aside class="login-branding">
                <div class="branding-content">
                    <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T" class="branding-logo">
                    <h1 class="branding-title">Cross C.T</h1>
                    <p class="branding-subtitle">Centro de Treinamento</p>
                    <p class="branding-tagline">Transformando vidas através<br>do treino funcional.</p>
                    <div class="branding-accent"></div>
                </div>
            </aside>

            <main class="login-form-area">
                <div class="login-form-content">
                    <h2 class="form-title">Bem-vindo de volta</h2>
                    <p class="form-subtitle">Acesse sua conta para continuar</p>

                    <form action="/ctt/dashboard" method="post">
                        <label for="uEmail" class="form-label fw-semibold">E-mail</label>
                        <input type="email" id="uEmail" class="form-control" placeholder="nome@email.com" required>

                        <label for="uSenha" class="form-label fw-semibold">Senha</label>
                        <input type="password" id="uSenha" class="form-control" placeholder="●●●●●●●●" required>

                        <a href="#" class="esqueceu-senha">Esqueceu a senha?</a>

                        <button type="submit" class="btn btn-entrar">Entrar</button>

                        <a href="/ctt/" class="btn-voltar">
                            <span>&#8249;</span> Tela Principal
                        </a>
                    </form>
                </div>
            </main>
        </div>
    </div>
</body>
</html>
