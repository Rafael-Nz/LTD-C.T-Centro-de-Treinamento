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
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css" />
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
        </div>
      </div>

      <div class="alert alert-danger d-none" id="alunoDetalheError" role="alert"></div>

      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 pb-0">
          <div class="mb-3">
            <h2 class="h5 mb-1">Central do Aluno</h2>
          </div>
          <ul class="nav nav-tabs" id="alunoDetalheTabs" role="tablist">
            <li class="nav-item" role="presentation">
              <button class="nav-link active" id="resumo-tab" data-bs-toggle="tab" data-bs-target="#resumo-pane" type="button" role="tab" aria-controls="resumo-pane" aria-selected="true">
                Dados Gerais
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="avaliacoes-tab" data-bs-toggle="tab" data-bs-target="#avaliacoes-pane" type="button" role="tab" aria-controls="avaliacoes-pane" aria-selected="false">
                Avaliações Físicas
              </button>
            </li>
            <li class="nav-item" role="presentation">
              <button class="nav-link" id="anamnese-tab" data-bs-toggle="tab" data-bs-target="#anamnese-pane" type="button" role="tab" aria-controls="anamnese-pane" aria-selected="false">
                Anamnese
              </button>
            </li>
          </ul>
        </div>
        <div class="card-body pt-3">
          <div class="tab-content" id="alunoDetalheTabsContent">
            <div class="tab-pane fade show active" id="resumo-pane" role="tabpanel" aria-labelledby="resumo-tab" tabindex="0">
              <div class="detail-grid detail-grid-expanded" id="alunoDadosGrid"></div>
            </div>

            <div class="tab-pane fade" id="avaliacoes-pane" role="tabpanel" aria-labelledby="avaliacoes-tab" tabindex="0">
              <div class="d-flex justify-content-start align-items-center gap-3 flex-wrap mb-3">
                <div class="d-flex" role="search">
                  <div class="input-group">
                    <input id="campoBusca" class="form-control" type="search" placeholder="Buscar..." aria-label="Buscar">
                    <button class="btn border border-start-0" type="button" id="botaoBuscar">
                      <i class="ph ph-magnifying-glass"></i>
                    </button>
                  </div>
                </div>
                <a href="/ctt/admin/alunos/<?= $alunoId ?>/avaliacoes/cadastrar" class="btn btn-red" id="novaAvaliacaoBtn">
                  <i class="ph ph-clipboard-text me-1"></i>Nova avaliacao
                </a>
              </div>
              <table id="tabelaAvaliacoes" class="table table-hover align-middle w-100" aria-label="Lista de Avaliacoes">
                <thead>
                  <tr>
                    <th scope="col" class="text-start">Data</th>
                    <th scope="col" class="text-start">Avaliador</th>
                    <th scope="col" class="text-center">IMC</th>
                    <th scope="col" class="text-center">% Gordura</th>
                    <th scope="col" class="text-center">% Musculo</th>
                    <th scope="col" class="text-center">Visceral</th>
                    <th scope="col" class="text-center">Peso</th>
                    <th scope="col" class="text-center">Altura</th>
                    <th scope="col" class="text-center">Acoes</th>
                  </tr>
                </thead>
                <tbody></tbody>
              </table>
            </div>

            <div class="tab-pane fade" id="anamnese-pane" role="tabpanel" aria-labelledby="anamnese-tab" tabindex="0">
              <div class="d-flex justify-content-between align-items-center gap-3 flex-wrap mb-3">
                <div>
                  <h3 class="h6 mb-1">Questionario Médico</h3>
                  <p class="text-muted small mb-0">Respostas preenchidas no formulario inicial do aluno.</p>
                </div>
              </div>
              <div id="anamneseList" class="d-flex flex-column gap-3"></div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . "/partials/footer.php"; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.min.js"></script>
  <script src="https://cdn.datatables.net/2.3.8/js/dataTables.bootstrap5.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.8/js/dataTables.responsive.min.js"></script>
  <script src="https://cdn.datatables.net/responsive/3.0.8/js/responsive.bootstrap5.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/tabelas.js"></script>
  <script src="/ctt/js/admin/aluno_detalhe.js"></script>
</body>
</html>
