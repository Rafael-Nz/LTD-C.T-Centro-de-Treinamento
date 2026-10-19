<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Recuperar Senha</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/login.css">
    <link rel="stylesheet" href="/ctt/plugins/phosphor-icons/src/regular/style.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="brand-title">CROSS <span>C.T</span></h1>
            <p class="brand-subtitle">Recuperação de Senha</p>
        </div>

        <div id="reset-error" class="alert alert-danger alert-custom d-none" role="alert"></div>
        <div id="reset-success" class="alert alert-success alert-custom d-none" role="alert"></div>

        <form id="password-reset-request-form">
            <div class="mb-4">
                <label for="email" class="form-label">Digite seu email cadastrado</label>
                <input type="email" class="form-control form-control-dark" id="email" name="email" required autofocus
                    placeholder="seuemail@exemplo.com">
                <div class="form-text mt-2" style="color: #aaa; font-size: 0.85rem;">
                    Enviaremos um link para redefinir sua senha.
                </div>
            </div>

            <button id="reset-submit" type="submit" class="btn btn-login w-100">
                <i class="ph ph-paper-plane-tilt me-2"></i> Enviar Link de Recuperação
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="login" class="back-link">
                <i class="ph ph-arrow-left me-1"></i> Voltar para o Login
            </a>
        </div>
    </div>

    <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/ctt/js/admin/password-reset-request.js"></script>
</body>

</html>
