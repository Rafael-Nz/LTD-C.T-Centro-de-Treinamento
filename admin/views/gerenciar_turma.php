<?php
$turmaId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$editarUrl = $turmaId > 0 ? "/ctt/admin/turmas/editar/{$turmaId}" : '/ctt/admin/turmas';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Gerenciar Turma</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/css/gerenciar_turma.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent" data-turma-id="<?= $turmaId ?>" data-editar-url="<?= htmlspecialchars($editarUrl, ENT_QUOTES, 'UTF-8') ?>">
    <div class="container-lg p-4">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h1 class="h4 mb-1" id="pageTitle">Gerenciar Turma</h1>
          <small class="text-muted" id="pageSubtitle">Carregando dados da turma...</small>
        </div>
        <div class="d-flex gap-2">
          <a href="/ctt/admin/turmas" class="btn btn-secondary btn-sm">
            <i class="ph ph-arrow-left me-1"></i>Voltar
          </a>
          <button type="button" class="btn btn-primary btn-sm" id="abrirAgendarTreinoBtn">
            <i class="ph ph-calendar-plus me-1"></i>Agendar treino
          </button>
          <a href="<?= htmlspecialchars($editarUrl, ENT_QUOTES, 'UTF-8') ?>" class="btn btn-red btn-sm" id="editarTurmaBtn">
            <i class="ph ph-pencil-simple me-1"></i>Editar turma
          </a>
        </div>
      </div>

      <div class="alert alert-danger d-none" id="gerenciarTurmaError" role="alert"></div>

      <div class="row g-3 mb-4">
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-calendar text-primary fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0 small">Total de Treinos</p>
                  <h5 class="mb-0" id="metricTotalTreinos">--</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-check-circle text-success fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0 small">Concluidos</p>
                  <h5 class="mb-0" id="metricTreinosConcluidos">--</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-clock-countdown text-info fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0 small">Agendados</p>
                  <h5 class="mb-0" id="metricTreinosAgendados">--</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-chart-line text-warning fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0 small">Taxa de Presenca</p>
                  <h5 class="mb-0" id="metricTaxaPresenca">--</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 shadow-sm mb-4">
        <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center flex-wrap gap-2">
          <div>
            <h2 class="h5 mb-1">Calendario da turma</h2>
            <small class="text-muted" id="calendarPeriodLabel">Carregando periodo...</small>
          </div>
          <span class="badge rounded-pill text-bg-light border" id="calendarLegend">
            Clique em um treino para ver as presencas
          </span>
        </div>
        <div class="card-body">
          <div id="calendar"></div>
        </div>
      </div>

      <div class="card border-0 shadow-sm">
        <div class="card-header bg-white border-bottom">
          <ul class="nav nav-tabs card-header-tabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="alunos-tab" data-bs-toggle="tab" data-bs-target="#alunos-content" type="button" role="tab">
                <i class="ph ph-users-three me-2"></i>Alunos
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="treinos-tab" data-bs-toggle="tab" data-bs-target="#treinos-content" type="button" role="tab">
                <i class="ph ph-list-checks me-2"></i>Treinos
              </button>
            </li>
          </ul>
        </div>

        <div class="tab-content p-3 tab-content-shell">
          <div class="tab-pane fade show active" id="alunos-content" role="tabpanel">
            <div class="table-responsive">
              <table class="table table-hover align-middle mb-0">
                <thead class="table-light">
                  <tr>
                    <th>Aluno</th>
                    <th>Matricula</th>
                    <th class="text-center">Inscricao</th>
                    <th class="text-center">Status</th>
                  </tr>
                </thead>
                <tbody id="alunosTableBody"></tbody>
              </table>
            </div>
          </div>

          <div class="tab-pane fade" id="treinos-content" role="tabpanel">
            <div class="d-flex flex-column gap-3" id="treinosList"></div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <div class="modal fade" id="agendarTreinoModal" tabindex="-1" aria-labelledby="agendarTreinoModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-lg modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5 mb-1" id="agendarTreinoModalLabel">Agendar treino</h2>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-danger d-none" id="agendarTreinoError" role="alert"></div>

          <form id="agendarTreinoForm" novalidate>
            <div class="row g-3">
              <div class="col-md-6">
                <label for="agendarTreinoId" class="form-label">Treino base</label>
                <select id="agendarTreinoId" class="form-select" required>
                  <option value="">Selecione um treino...</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="agendarInstrutorId" class="form-label">Instrutor</label>
                <select id="agendarInstrutorId" class="form-select">
                  <option value="">Selecione um instrutor...</option>
                </select>
              </div>

              <div class="col-md-6">
                <label for="agendarInicio" class="form-label">Inicio</label>
                <div class="input-group" id="agendarInicioPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                  <input type="text" id="agendarInicio" class="form-control" data-td-target="#agendarInicio" autocomplete="off" required>
                  <span class="input-group-text" data-td-target="#agendarInicio" data-td-toggle="datetimepicker">
                    <i class="ph ph-calendar"></i>
                  </span>
                </div>
              </div>

              <div class="col-md-6">
                <label for="agendarFim" class="form-label">Fim</label>
                <div class="input-group" id="agendarFimPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                  <input type="text" id="agendarFim" class="form-control" data-td-target="#agendarFim" autocomplete="off" required>
                  <span class="input-group-text" data-td-target="#agendarFim" data-td-toggle="datetimepicker">
                    <i class="ph ph-calendar"></i>
                  </span>
                </div>
              </div>

              <div class="col-md-6">
                <label for="agendarEspacoId" class="form-label">Local de treino</label>
                <select id="agendarEspacoId" class="form-select" required>
                  <option value="">Selecione um local...</option>
                </select>
              </div>
              
              <div class="col-12">
                <label for="agendarObservacoes" class="form-label">Observacoes</label>
                <textarea id="agendarObservacoes" class="form-control" rows="3" placeholder="Observacoes opcionais sobre esta ocorrencia."></textarea>
              </div>
            </div>
          </form>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-red" id="confirmarAgendarTreinoBtn">Salvar agendamento</button>
        </div>
      </div>
    </div>
  </div>

  <div class="modal fade" id="presencasModal" tabindex="-1" aria-labelledby="presencasModalLabel" aria-hidden="true">
    <div class="modal-dialog modal-xl modal-dialog-scrollable">
      <div class="modal-content">
        <div class="modal-header">
          <div>
            <h2 class="modal-title h5 mb-1" id="presencasModalLabel">Lista de Presenca</h2>
            <small class="text-muted" id="presencasModalMeta">Selecione um treino para visualizar os detalhes.</small>
          </div>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
        </div>
        <div class="modal-body">
          <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
            <div class="d-flex gap-2 flex-wrap" id="presencasResumoBadges"></div>
            <button type="button" class="btn btn-red btn-sm" id="salvarPresencasBtn">Salvar presencas</button>
          </div>

          <div class="table-responsive">
            <table class="table align-middle presenca-table">
              <thead>
                <tr>
                  <th>Aluno</th>
                  <th class="text-center">Situacao atual</th>
                  <th class="text-center">Lancar presenca</th>
                  <th class="text-center">Check-in</th>
                </tr>
              </thead>
              <tbody id="presencasTableBody"></tbody>
            </table>
          </div>
        </div>
      </div>
    </div>
  </div>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/fullcalendar@6.1.20/index.global.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/locales/pt.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
  <script defer src="/ctt/js/admin/gerenciar_turma.js"></script>
</body>
</html>
