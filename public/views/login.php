<!DOCTYPE html>
<html lang="pt-BR">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T - Login</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="<?= PUBLIC_URL ?>css/home-login.css">
    <link href="/ctt/plugins/fonts/inter.css" rel="stylesheet">
  <link href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/plugins/phosphor-icons/src/regular/style.css">
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
                    <h2 class="form-title">Área do Aluno</h2>
                    <div id="login-error" class="alert alert-danger d-none" role="alert"></div>
                    <form id="student-login-form" action="<?= BASE_URL ?>api/auth/login" method="post">
                        <label for="uEmail" class="form-label fw-semibold">E-mail:</label>
                        <input type="text" id="uEmail" name="login" class="form-control" placeholder="nome@email.com" autocomplete="username" required>

                        <label for="uSenha" class="form-label fw-semibold">Senha:</label>
                        <div class="password-container">
                            <input type="password" id="uSenha" name="senha" class="form-control" placeholder="••••••••" autocomplete="current-password" required>
                            <button type="button" class="password-toggle" id="toggleStudentPassword" aria-label="Mostrar senha">
                                <i class="ph ph-eye-slash" id="studentEyeIcon"></i>
                            </button>
                        </div>

                        <a href="#" class="esqueceu-senha">Esqueceu a senha?</a>

                        <button type="submit" class="btn btn-entrar">Entrar</button>

                        <a href="/ctt/" class="btn-voltar">
                            <span>&#8249;</span> Página Principal
                        </a>
                    </form>
                </div>
            </main>
        </div>
    </div>
    <script src="<?= PUBLIC_URL ?>js/login.js"></script>
</body>

</html>
