<?php
$token = $_GET['token'] ?? '';
$perfil = in_array($_GET['perfil'] ?? '', ['aluno', 'funcionario'], true)
    ? $_GET['perfil']
    : 'aluno';
$loginUrl = $perfil === 'funcionario' ? '/ctt/admin/login' : '/ctt/login';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Ativar conta</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/login.css">
    <link rel="stylesheet" href="/ctt/plugins/phosphor-icons/src/regular/style.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="brand-title">CROSS <span>C.T</span></h1>
            <p class="brand-subtitle">Ativar conta</p>
        </div>

        <div id="activation-error" class="alert alert-danger alert-custom d-none" role="alert"></div>
        <div id="activation-success" class="alert alert-success alert-custom d-none" role="alert"></div>

        <form id="account-activation-form"
            data-token="<?= htmlspecialchars($token, ENT_QUOTES, 'UTF-8') ?>"
            data-login-url="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>">
            <div class="mb-3">
                <label for="nova_senha" class="form-label">Nova Senha</label>
                <div class="password-container">
                    <input type="password" class="form-control form-control-dark" id="nova_senha" name="nova_senha" required
                        placeholder="••••••••" minlength="12" maxlength="128" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="nova_senha" data-icon="eyeIcon1" aria-label="Mostrar senha">
                        <i class="ph ph-eye-slash" id="eyeIcon1"></i>
                    </button>
                </div>
                <div class="password-rules" aria-label="Regras da senha">
                    <div class="password-rule" data-password-rule="length"><i class="ph ph-x-circle"></i><span>Conter de 12 a 128 caracteres.</span></div>
                    <div class="password-rule" data-password-rule="number"><i class="ph ph-x-circle"></i><span>Conter ao menos um número.</span></div>
                    <div class="password-rule" data-password-rule="uppercase"><i class="ph ph-x-circle"></i><span>Conter ao menos uma letra maiúscula.</span></div>
                    <div class="password-rule" data-password-rule="lowercase"><i class="ph ph-x-circle"></i><span>Conter ao menos uma letra minúscula.</span></div>
                    <div class="password-rule" data-password-rule="symbol"><i class="ph ph-x-circle"></i><span>Conter ao menos um caractere especial. "@#$!%"</span></div>
                </div>
            </div>

            <div class="mb-4">
                <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                <div class="password-container">
                    <input type="password" class="form-control form-control-dark" id="confirmar_senha" name="confirmar_senha" required
                        placeholder="••••••••" minlength="12" maxlength="128" autocomplete="new-password">
                    <button type="button" class="password-toggle" data-password-toggle="confirmar_senha" data-icon="eyeIcon2" aria-label="Mostrar senha">
                        <i class="ph ph-eye-slash" id="eyeIcon2"></i>
                    </button>
                </div>
            </div>

            <button id="activation-submit" type="submit" class="btn btn-login w-100">
                <i class="ph ph-check-circle me-2"></i> Ativar Conta
            </button>
        </form>

        <div class="text-center mt-4">
            <a href="<?= htmlspecialchars($loginUrl, ENT_QUOTES, 'UTF-8') ?>" class="back-link"><i class="ph ph-arrow-left me-1"></i> Voltar para o Login</a>
        </div>
    </div>

    <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script src="/ctt/js/account-activation.js"></script>
</body>

</html>
