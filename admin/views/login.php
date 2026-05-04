<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="dark">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Login Admin</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/login.css">
  <link rel="stylesheet" href="https://unpkg.com/@phosphor-icons/web@2.1.1/src/regular/style.css">
</head>

<body>
  <div class="login-container">
    <div class="text-center mb-4">
      <h1 class="brand-title">CROSS <span>C.T</span></h1>
      <p class="brand-subtitle">Sistema Administrativo</p>
    </div>

    <?php if (!empty($loginError)): ?>
      <div class="alert alert-danger alert-custom alert-dismissible fade show" role="alert">
        <div class="d-flex align-items-center">
          <i class="ph ph-warning-circle me-2" style="font-size: 1.2rem;"></i>
          <div><?= htmlspecialchars($loginError) ?></div>
        </div>
        <button type="button" class="btn-close btn-close-white" data-bs-dismiss="alert" aria-label="Close"></button>
      </div>
    <?php endif; ?>
    <div id="error-container"></div>
    <form id="formLogin">
      <div class="mb-3">
        <label for="email" class="form-label">Email</label>
        <input type="email" class="form-control form-control-dark" id="email" name="email" required autofocus placeholder="admin@centrotreinamento.com">
      </div>

      <div class="mb-4">
        <label for="senha" class="form-label">Senha</label>
        <div class="password-container">
          <input type="password" class="form-control form-control-dark" id="senha" name="senha" autocomplete="senha" required placeholder="••••••••">
          <button type="button" class="password-toggle" id="togglePassword">
            <i class="ph ph-eye-slash" id="eyeIcon"></i>
          </button>
        </div>
      </div>

      <button type="submit" class="btn btn-login w-100">
        <i class="ph ph-sign-in me-2"></i> Login
      </button>
    </form>

    <div class="text-center mt-3">
      <a href="esqueci-senha" class="forgot-password-link" style="color: #aaa; text-decoration: none; font-size: 0.9rem;">
        <i class="ph ph-question me-1"></i> Esqueceu a senha?
      </a>
    </div>

    <div class="text-center mt-4">
      <a href="../home.php" class="back-link">
        <i class="ph ph-arrow-left me-1"></i> Página Inicial
      </a>
    </div>
  </div>
  
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script src="/ctt/js/admin/login.js"></script>
</body>

</html>