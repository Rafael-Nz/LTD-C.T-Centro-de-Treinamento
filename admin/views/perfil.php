<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Perfil</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/css/perfil.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
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
    <div class="container-lg p-4 d-flex flex-column profile-shell">
      <div>
        <h1 class="h4 mb-0">Meu perfil</h1>
      </div>

      <div class="alert alert-danger d-none" id="profileError" role="alert"></div>

      <section class="card profile-hero">
        <div class="card-body p-4 p-lg-5 position-relative">
          <div class="d-flex flex-column flex-sm-row align-items-sm-center gap-4">
            <div class="profile-avatar" id="profileAvatar">--</div>

            <div>
              <h2 class="h2 fw-bold mb-2" id="profileName">Carregando...</h2>
              <p class="mb-2 fs-6 opacity-75" id="profileEmail">Carregando...</p>
            </div>
          </div>
        </div>
      </section>

      <div class="row g-4">
        <div class="col-xl-8">
          <div class="d-grid gap-4">
            <section class="card border-0 profile-panel">
              <div class="card-body">
                <div class="profile-panel-header">
                  <h3 class="profile-panel-title">
                    <i class="ph ph-user-circle"></i>
                    <span>Dados do usuario</span>
                  </h3>
                </div>

                <div class="profile-data-grid">
                  <div class="profile-data-item">
                    <div class="profile-data-label">Nome completo</div>
                    <div class="profile-data-value" id="profileNomeCompleto">Carregando...</div>
                  </div>
                  <div class="profile-data-item">
                    <div class="profile-data-label">Email principal</div>
                    <div class="profile-data-value" id="profileEmailPrincipal">Carregando...</div>
                  </div>
                </div>
              </div>
            </section>

            <section class="card border-0 profile-panel">
              <div class="card-body">
                <div class="profile-panel-header">
                  <h3 class="profile-panel-title">
                    <i class="ph ph-phone-call"></i>
                    <span>Contatos e comunicacao</span>
                  </h3>
                </div>

                <div class="profile-data-grid">
                  <div class="profile-data-item">
                    <div class="profile-data-label">Telefone</div>
                    <div class="profile-data-value" id="profileTelefone">Carregando...</div>
                  </div>
                  <div class="profile-data-item">
                    <div class="profile-data-label">WhatsApp</div>
                    <div class="profile-data-value" id="profileWhatsapp">Carregando...</div>
                  </div>
                  <div class="profile-data-item full">
                    <div class="profile-data-label">Email secundario</div>
                    <div class="profile-data-value" id="profileEmailSecundario">Carregando...</div>
                  </div>
                </div>
              </div>
            </section>

            <section class="card border-0 profile-panel">
              <div class="card-body">
                <div class="profile-panel-header">
                  <h3 class="profile-panel-title">
                    <i class="ph ph-map-pin"></i>
                    <span>Endereço</span>
                  </h3>
                </div>

                <div class="profile-data-grid">
                  <div class="profile-data-item full">
                    <div class="profile-data-label">Logradouro</div>
                    <div class="profile-data-value" id="profileLogradouro">Carregando...</div>
                  </div>
                  <div class="profile-data-item">
                    <div class="profile-data-label">Bairro e cidade</div>
                    <div class="profile-data-value" id="profileBairroCidade">Carregando...</div>
                  </div>
                  <div class="profile-data-item">
                    <div class="profile-data-label">CEP</div>
                    <div class="profile-data-value" id="profileCep">Carregando...</div>
                  </div>
                  <div class="profile-data-item full">
                    <div class="profile-data-label">Complemento</div>
                    <div class="profile-data-value" id="profileComplemento">Carregando...</div>
                  </div>
                </div>
              </div>
            </section>
          </div>
        </div>

        <div class="col-xl-4">
          <div class="profile-side-stack">
            <section class="card border-0 profile-panel">
              <div class="card-body">
                <div class="profile-panel-header">
                  <h3 class="profile-panel-title">
                    <i class="ph ph-shield-check"></i>
                    <span>Conta e acesso</span>
                  </h3>
                </div>

                <div class="profile-data-grid">
                  <div class="profile-data-item full">
                    <div class="profile-data-label">Cargo atual</div>
                    <div class="profile-data-value" id="profileCargoAtual">Carregando...</div>
                  </div>
                  <div class="profile-data-item full">
                    <div class="profile-data-label">Registro profissional</div>
                    <div class="profile-data-value" id="profileRegistroProfissional">Carregando...</div>
                  </div>
                </div>
              </div>
            </section>

            <section class="card border-0 profile-panel">
              <div class="card-body">
                <div class="profile-panel-header">
                  <h3 class="profile-panel-title">
                    <i class="ph ph-lightning"></i>
                    <span>Acoes rapidas</span>
                  </h3>
                </div>

                <div class="profile-action-list">
                  <a href="/ctt/admin/" class="profile-action-item">
                    <div class="profile-action-icon"><i class="ph ph-house-line"></i></div>
                    <div>
                      <div class="profile-action-title">Voltar ao dashboard</div>
                      <div class="profile-action-copy">Retome rapidamente o painel principal e acompanhe os modulos que voce usa no dia a dia.</div>
                    </div>
                  </a>

                  <a href="/ctt/admin/logout" class="profile-action-item">
                    <div class="profile-action-icon"><i class="ph ph-sign-out"></i></div>
                    <div>
                      <div class="profile-action-title">Encerrar sessao</div>
                      <div class="profile-action-copy">Finalize seu acesso com seguranca ao terminar o uso do sistema.</div>
                    </div>
                  </a>
                </div>
              </div>
            </section>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
  <script defer src="/ctt/js/admin/perfil.js"></script>
</body>
</html>
