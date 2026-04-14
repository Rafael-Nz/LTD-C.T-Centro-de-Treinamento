<?php
// Simulação de envio de email
$message = '';
$error = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $email = filter_input(INPUT_POST, 'email', FILTER_SANITIZE_EMAIL);
    
    if (filter_var($email, FILTER_VALIDATE_EMAIL)) {
        // Simulação: email enviado com sucesso
        $message = 'Um link de recuperação foi enviado para seu email. Verifique sua caixa de entrada.';
    } else {
        $error = 'Por favor, insira um email válido.';
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Recuperar Senha</title>
    <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
    <link rel="stylesheet" href="../public/css/login.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="brand-title">CROSS <span>C.T</span></h1>
            <p class="brand-subtitle">Recuperação de Senha</p>
        </div>

        <?php if (!empty($error)): ?>
            <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ph ph-warning-circle me-2" style="font-size: 1.2rem;"></i>
                    <div><?= htmlspecialchars($error) ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (!empty($message)): ?>
            <div class="alert alert-success alert-custom alert-dismissible fade show" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ph ph-check-circle me-2" style="font-size: 1.2rem;"></i>
                    <div><?= htmlspecialchars($message) ?></div>
                </div>
                <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
            </div>
        <?php endif; ?>

        <?php if (empty($message)): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF']); ?>">
                <div class="mb-4">
                    <label for="email" class="form-label">Digite seu email cadastrado</label>
                    <input type="email" class="form-control form-control-dark" id="email" name="email" required autofocus
                        placeholder="seuemail@exemplo.com">
                    <div class="form-text mt-2" style="color: #aaa; font-size: 0.85rem;">
                        Enviaremos um link para redefinir sua senha.
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="ph ph-paper-plane-tilt me-2"></i> Enviar Link de Recuperação
                </button>
            </form>
        <?php else: ?>
            <div class="text-center py-4">
                <div class="mb-4">
                    <i class="ph ph-envelope-open" style="font-size: 3rem; color: var(--bg-thema);"></i>
                </div>
                <p class="mb-4">Verifique sua caixa de entrada e siga as instruções do email.</p>
                <a href="login.php" class="btn btn-login">
                    <i class="ph ph-arrow-left me-2"></i> Voltar para o Login
                </a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="login.php" class="back-link">
                <i class="ph ph-arrow-left me-1"></i> Voltar para o Login
            </a>
        </div>
    </div>

    <script src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
</body>

</html>