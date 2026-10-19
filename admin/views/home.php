<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Home</title>
  <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/home-calendar.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css" />
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
  <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css" />
  <link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <link href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">

  <?php include __DIR__ . "../partials/sidebar.php"; ?>
  <?php include __DIR__ . "../partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-3 p-md-4 d-flex flex-column flex-fill">

      <!-- Alerta de ativação se pendente -->
      <div id="admin-activation-card" class="alert alert-warning d-none d-flex align-items-center justify-content-between gap-2 py-2 px-3 mb-3 border-0 shadow-sm rounded-3" role="alert">
        <div class="d-flex align-items-center flex-wrap gap-2 small">
          <strong class="d-inline-flex align-items-center"><i class="ph ph-warning fs-5 me-1"></i> Alerta:</strong>
          <span>Sua senha inicial permite o acesso, mas sua conta ainda precisa ser ativada.</span>
          <span id="admin-activation-email" class="fw-semibold"></span>
          <span id="admin-activation-feedback" class="small"></span>
        </div>
        <button type="button" id="admin-resend-activation" class="btn btn-warning btn-sm text-nowrap fw-semibold">Enviar link de ativação</button>
      </div>

      <!-- Header Principal -->
      <div class="d-flex flex-column flex-md-row justify-content-between align-items-start align-items-md-center gap-2 mb-4">
        <div>
          <h1 class="h4 mb-1 fw-bold text-body">Painel de Controle</h1>
        </div>
      </div>

      <!-- Ações Rápidas de Operação -->
      <div class="dashboard-v2-quickbar mb-4 d-flex flex-wrap align-items-center justify-content-between gap-3">
        <div class="d-flex align-items-center gap-2">
          <div class="stat-card-icon stat-icon-rose">
            <i class="ph ph-lightning"></i>
          </div>
          <div>
            <strong class="d-block text-body">Ações Rápidas de Operação</strong>
            <span class="small text-body-secondary">Acesse os fluxos mais usados no dia a dia</span>
          </div>
        </div>
        <div class="d-flex align-items-center gap-2 flex-wrap">
          <a href="/ctt/admin/treinos/cadastrar" class="btn btn-red btn-sm v2-quick-btn">
            <i class="ph ph-plus-circle"></i> Novo Treino
          </a>
          <a href="/ctt/admin/alunos/cadastrar" class="btn btn-red btn-sm v2-quick-btn">
            <i class="ph ph-user-plus"></i> Matricular Aluno
          </a>
          <a href="/ctt/admin/financeiro" class="btn btn-red btn-sm v2-quick-btn">
            <i class="ph ph-wallet"></i> Financeiro
          </a>
        </div>
      </div>

      <!-- Cards de Métricas -->
      <div class="row g-3 mb-4">
        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-admin p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-body-secondary small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Receita Mensal</span>
              <i class="ph ph-trend-up text-success fs-5"></i>
            </div>
            <div class="stat-card-value text-body stat-receita-mes">R$ 0,00</div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-admin p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-body-secondary small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Alunos Ativos</span>
              <i class="ph ph-user-check text-primary fs-5"></i>
            </div>
            <div class="stat-card-value text-body stat-alunos-ativos">0</div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-admin p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-body-secondary small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Pagamentos Atrasados</span>
              <i class="ph ph-warning-circle text-danger fs-5"></i>
            </div>
            <div class="stat-card-value text-danger stat-pagamentos-atrasados">0</div>
          </div>
        </div>

        <div class="col-sm-6 col-xl-3">
          <div class="stat-card-admin p-3">
            <div class="d-flex align-items-center justify-content-between mb-2">
              <span class="text-body-secondary small fw-bold text-uppercase" style="letter-spacing: 0.05em;">Pagamentos Pendentes</span>
              <i class="ph ph-hourglass-simple text-warning fs-5"></i>
            </div>
            <div class="stat-card-value text-body stat-pagamentos-pendentes">0</div>
          </div>
        </div>
      </div>

      <!-- Seção de Calendário e Próximos Treinos -->
      <div class="row g-4">
        <!-- Calendário de Treinos -->
        <div class="col-lg-8">
          <div class="card dashboard-card">
            <div class="dashboard-card-header d-flex align-items-center justify-content-between flex-wrap gap-2">
              <div class="d-flex align-items-center gap-2">
                <i class="ph ph-calendar-check fs-5 text-danger"></i>
                <h5 class="card-title mb-0 fw-bold fs-6 text-body">Agenda de Treinos</h5>
              </div>
              <span class="badge rounded-pill bg-danger-subtle text-danger-emphasis px-3 py-2 fw-semibold" id="calendarStatus">
                Carregando...
              </span>
            </div>
            <div class="card-body p-3 p-md-4">
              <div id="calendar"></div>
            </div>
          </div>
        </div>

        <!-- Próximos Treinos -->
        <div class="col-lg-4">
          <div class="card dashboard-card h-100 d-flex flex-column">
            <div class="dashboard-card-header d-flex align-items-center justify-content-between">
              <div class="d-flex align-items-center gap-2">
                <i class="ph ph-clock fs-5 text-danger"></i>
                <h5 class="card-title mb-0 fw-bold fs-6 text-body">Próximos Treinos</h5>
              </div>
              <span class="small text-body-secondary">Agenda rápida</span>
            </div>
            <div class="card-body p-3 p-md-4 flex-fill d-flex flex-column">
              <div id="eventList" class="flex-fill">
                <!-- Preenchido dinamicamente via home.js com estrutura moderna -->
              </div>
            </div>
          </div>
        </div>
      </div>

    </div>
  </main>

  <?php include __DIR__ . "../partials/footer.php"; ?>

  <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/plugins/fullcalendar/index.global.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/account-status.js"></script>
  <script src="/ctt/js/admin/home.js"></script>
</body>

</html>
