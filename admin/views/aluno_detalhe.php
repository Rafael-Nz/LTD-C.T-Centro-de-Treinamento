<?php
$alunoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Perfil do Aluno</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/css/aluno_detalhe.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . "/partials/sidebar.php"; ?>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent" data-aluno-id="<?= $alunoId ?>">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h1 class="h4 mb-1" id="alunoNome">Perfil do Aluno</h1>
          <p class="text-muted mb-0" id="alunoMeta">Carregando informacoes...</p>
        </div>
        <div class="d-flex gap-2">
          <a href="/ctt/admin/alunos" class="btn btn-secondary">
            <i class="ph ph-arrow-left me-1"></i>Voltar
          </a>
          <a href="/ctt/admin/alunos/editar/<?= $alunoId ?>" class="btn btn-primary" id="editarAlunoBtn">
            <i class="ph ph-pencil-simple me-1"></i>Editar aluno
          </a>
          <a href="/ctt/admin/alunos/<?= $alunoId ?>/avaliacoes/cadastrar" class="btn btn-red" id="novaAvaliacaoBtn">
            <i class="ph ph-clipboard-text me-1"></i>Nova avaliacao
          </a>
        </div>
      </div>

      <div class="alert alert-danger d-none" id="alunoDetalheError" role="alert"></div>

      <div class="row g-3 mb-4" id="alunoResumoCards">
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted small mb-1">Matricula</p>
              <h5 class="mb-0" id="cardMatricula">--</h5>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted small mb-1">Idade</p>
              <h5 class="mb-0" id="cardIdade">--</h5>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted small mb-1">Sexo</p>
              <h5 class="mb-0" id="cardSexo">--</h5>
            </div>
          </div>
        </div>
        <div class="col-md-3">
          <div class="card shadow-sm border-0 h-100">
            <div class="card-body">
              <p class="text-muted small mb-1">Turma atual</p>
              <h5 class="mb-0" id="cardTurmaAtual">--</h5>
            </div>
          </div>
        </div>
      </div>

      <div class="row g-4">
        <div class="col-lg-5">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom">
              <h2 class="h5 mb-0">Dados do Aluno</h2>
            </div>
            <div class="card-body">
              <div class="detail-grid" id="alunoDadosGrid"></div>
            </div>
          </div>
        </div>

        <div class="col-lg-7">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-white border-bottom d-flex justify-content-between align-items-center">
              <h2 class="h5 mb-0">Avaliacoes Fisicas</h2>
              <span class="badge text-bg-light border" id="avaliacoesCount">0 registros</span>
            </div>
            <div class="card-body">
              <div class="d-flex flex-column gap-3" id="avaliacoesList"></div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-bottom">
              <h2 class="h5 mb-0">Anamnese</h2>
            </div>
            <div class="card-body">
              <div id="anamneseList" class="d-flex flex-column gap-3"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . "/partials/footer.php"; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/aluno_detalhe.js"></script>
</body>
</html>
