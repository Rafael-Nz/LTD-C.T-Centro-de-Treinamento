<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <div class="d-flex justify-content-between align-items-center mb-4">
        <h1 class="h4 mb-1">Meu Perfil</h1>
      </div>

      <div class="row">
        <!-- Coluna lateral com informações do usuário -->
        <div class="col-lg-4 col-md-5 mb-4">
          <!-- Card de Perfil -->
          <div class="config-card border-0 p-4 text-center">
            <div class="position-relative d-inline-block mb-3">
              <div class="avatar-container">
                <img id="avatarPreview" src="/floricultura/public/img/avatar-placeholder.png"
                  class="rounded-circle avatar-lg" alt="Foto do usuário">
                <input type="file" id="avatarUpload" accept="image/*" style="display: none;">
              </div>
            </div>

            <h4 class="mb-1" id="userNameDisplay"><?php echo $_SESSION['usuario_nome'] ?? 'Usuário'; ?></h4>
            <p class="text-muted mb-3" id="userEmailDisplay">
              <?php echo $_SESSION['usuario_email'] ?? 'email@exemplo.com'; ?>
            </p>

            <div class="d-flex justify-content-center gap-2 mb-3">
              <span class="badge bg-primary"><?php echo $_SESSION['usuario_cargo'] ?? 'Funcionário'; ?></span>
            </div>
          </div>

          <!-- Card de Estatísticas Pessoais
          <div class="config-card border-0  mt-4 p-3">
            <h6 class="section-title">Minhas Estatísticas</h6>
            <div class="d-flex flex-column gap-2">
              <div class="d-flex justify-content-between">
                <span>Vendas Realizadas:</span>
                <strong id="vendasRealizadas">0</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Clientes Atendidos:</span>
                <strong id="clientesAtendidos">0</strong>
              </div>
              <div class="d-flex justify-content-between">
                <span>Membro desde:</span>
                <strong id="dataCadastro"><?php echo date('d/m/Y'); ?></strong>
              </div>
            </div>
          </div>-->
        </div>

        <!-- Conteúdo principal do perfil -->
        <div class="col-lg-8 col-md-7">
          <!-- Abas de configurações -->
          <div class="config-card border-0 p-4">
            <ul class="nav nav-tabs mb-4 gap-2" id="perfilTabs" role="tablist">
              <li class="nav-item" role="presentation">
                <button class="nav-link active" id="dados-tab" data-bs-toggle="tab" data-bs-target="#dados"
                  type="button" role="tab">
                  <i class="ph ph-user me-2"></i>Dados Pessoais
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="senha-tab" data-bs-toggle="tab" data-bs-target="#senha" type="button"
                  role="tab">
                  <i class="ph ph-lock me-2"></i>Alterar Senha
                </button>
              </li>
              <li class="nav-item" role="presentation">
                <button class="nav-link" id="preferencias-tab" data-bs-toggle="tab" data-bs-target="#preferencias"
                  type="button" role="tab">
                  <i class="ph ph-gear me-2"></i>Preferências
                </button>
              </li>
            </ul>

            <div class="tab-content" id="perfilTabsContent">
              <!-- Aba: Dados Pessoais -->
              <div class="tab-pane fade show active" id="dados" role="tabpanel">
                <form id="formDadosPessoais">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nome Completo</label>
                      <input type="text" class="form-control" id="nomeCompleto"
                        value="<?php echo $_SESSION['usuario_nome'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Apelido/Nickname</label>
                      <input type="text" class="form-control" id="apelido" placeholder="">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Email Principal</label>
                      <input type="email" class="form-control" id="email"
                        value="<?php echo $_SESSION['usuario_email'] ?? ''; ?>" required>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Email Secundário</label>
                      <input type="email" class="form-control" id="emailSecundario" placeholder="email@exemplo.com">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Telefone Principal</label>
                      <input type="text" class="form-control" id="telefone" placeholder="(00) 00000-0000">
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Telefone Secundário</label>
                      <input type="text" class="form-control" id="telefoneSecundario" placeholder="(00) 00000-0000">
                    </div>
                  </div>

                  <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-red color" id="btnSalvarDados">
                      <i class="ph ph-floppy-disk me-1"></i>Salvar Alterações
                    </button>
                  </div>
                </form>
              </div>

              <!-- Aba: Alterar Senha -->
              <div class="tab-pane fade" id="senha" role="tabpanel">
                <form id="formAlterarSenha">
                  <div class="row">
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Nova Senha</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="novaSenha" required>
                        <button class="btn border" type="button" id="toggleNovaSenha">
                          <i class="ph ph-eye-closed"></i>
                        </button>
                      </div>
                      <div class="form-text" id="forcaSenha"></div>
                    </div>
                    <div class="col-md-6 mb-3">
                      <label class="form-label">Confirmar Nova Senha</label>
                      <div class="input-group">
                        <input type="password" class="form-control" id="confirmarSenha" required>
                        <button class="btn border" type="button" id="toggleConfirmarSenha">
                          <i class="ph ph-eye-closed"></i>
                        </button>
                      </div>
                      <div class="form-text" id="confirmacaoSenha"></div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-end gap-2">
                    <button type="submit" class="btn btn-red color" id="btnSalvarSenha">
                      <i class="ph ph-lock-key me-1"></i>Alterar Senha
                    </button>
                  </div>
                </form>
              </div>

              <!-- Aba: Preferências -->
              <div class="tab-pane fade" id="preferencias" role="tabpanel">
                <form id="formPreferencias">
                  <div class="row mb-4">
                    <div class="col-12">
                      <h6 class="section-title">Notificações</h6>
                      <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="notificacoesEmail" checked>
                        <label class="form-check-label" for="notificacoesEmail">Receber notificações por email</label>
                      </div>
                      <div class="form-check form-switch mb-2">
                        <input class="form-check-input" type="checkbox" id="notificacoesSistema" checked>
                        <label class="form-check-label" for="notificacoesSistema">Notificações no sistema</label>
                      </div>
                      <div class="form-check form-switch">
                        <input class="form-check-input" type="checkbox" id="resumoDiario">
                        <label class="form-check-label" for="resumoDiario">Resumo diário de atividades</label>
                      </div>
                    </div>
                  </div>

                  <div class="d-flex justify-content-end gap-2 mt-4">
                    <button type="submit" class="btn btn-red color" id="btnSalvarPreferencias">
                      <i class="ph ph-floppy-disk me-1"></i>Salvar Preferências
                    </button>
                  </div>
                </form>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>
  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
</body>

</html>