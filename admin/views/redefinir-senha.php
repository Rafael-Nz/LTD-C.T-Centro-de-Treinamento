<?php
// Em um sistema real, aqui você validaria o token da URL
// Exemplo: ?token=abc123
$token = $_GET['token'] ?? '';
$message = '';
$error = '';
$validToken = false;

// Simulação: token válido (em produção, valide no banco de dados)
if ($token === 'demo-token' || !empty($token)) {
    $validToken = true;
    
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        $novaSenha = $_POST['nova_senha'] ?? '';
        $confirmarSenha = $_POST['confirmar_senha'] ?? '';
        
        if (strlen($novaSenha) < 6) {
            $error = 'A senha deve ter pelo menos 6 caracteres.';
        } elseif ($novaSenha !== $confirmarSenha) {
            $error = 'As senhas não coincidem.';
        } else {
            // Simulação: senha alterada com sucesso
            $message = 'Senha redefinida com sucesso!';
            $validToken = true; // Token usado, não mostrar mais o formulário
        }
    }
}
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Redefinir Senha</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/ctt/css/login.css">
    <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
</head>

<body>
    <div class="login-container">
        <div class="text-center mb-4">
            <h1 class="brand-title">CROSS <span>C.T</span></h1>
            <p class="brand-subtitle">Redefinir Senha</p>
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
            
            <div class="text-center py-4">
                <a href="login.php" class="btn btn-login w-100">
                    <i class="ph ph-sign-in me-2"></i> Fazer Login com Nova Senha
                </a>
            </div>
        <?php elseif ($validToken): ?>
            <form method="POST" action="<?php echo htmlspecialchars($_SERVER['PHP_SELF'] . '?token=' . $token); ?>">
                <div class="mb-3">
                    <label for="nova_senha" class="form-label">Nova Senha</label>
                    <div class="password-container">
                        <input type="password" class="form-control form-control-dark" id="nova_senha" name="nova_senha" required
                            placeholder="••••••••" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('nova_senha', 'eyeIcon1')">
                            <i class="ph ph-eye-slash" id="eyeIcon1"></i>
                        </button>
                    </div>
                    <div class="form-text mt-1" style="color: #aaa; font-size: 0.85rem;">
                        Mínimo de 6 caracteres.
                    </div>
                </div>

                <div class="mb-4">
                    <label for="confirmar_senha" class="form-label">Confirmar Nova Senha</label>
                    <div class="password-container">
                        <input type="password" class="form-control form-control-dark" id="confirmar_senha" name="confirmar_senha" required
                            placeholder="••••••••" minlength="6">
                        <button type="button" class="password-toggle" onclick="togglePassword('confirmar_senha', 'eyeIcon2')">
                            <i class="ph ph-eye-slash" id="eyeIcon2"></i>
                        </button>
                    </div>
                </div>

                <button type="submit" class="btn btn-login w-100">
                    <i class="ph ph-check-circle me-2"></i> Redefinir Senha
                </button>
            </form>
        <?php else: ?>
            <div class="alert alert-danger alert-custom" role="alert">
                <div class="d-flex align-items-center">
                    <i class="ph ph-warning-circle me-2" style="font-size: 1.2rem;"></i>
                    <div>Link inválido ou expirado. Solicite uma nova recuperação de senha.</div>
                </div>
            </div>
            
            <div class="text-center py-4">
                <a href="recuperar-senha.php" class="btn btn-login w-100">
                    <i class="ph ph-arrow-clockwise me-2"></i> Solicitar Novo Link
                </a>
            </div>
        <?php endif; ?>

        <div class="text-center mt-4">
            <a href="login.php" class="back-link">
                <i class="ph ph-arrow-left me-1"></i> Voltar para o Login
            </a>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script>
        function togglePassword(inputId, iconId) {
            const passwordInput = document.getElementById(inputId);
            const eyeIcon = document.getElementById(iconId);
            
            const isPassword = passwordInput.type === "password";
            passwordInput.type = isPassword ? "text" : "password";
            
            eyeIcon.classList.toggle("ph-eye");
            eyeIcon.classList.toggle("ph-eye-slash");
        }
    </script>
</body>

</html>