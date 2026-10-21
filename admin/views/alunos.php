<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Alunos</title>
  <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css" />
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
  <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css" />
  <link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <link href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . "/partials/sidebar.php"; ?>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Alunos</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap ">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar aluno..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar" title="Buscar alunos" aria-label="Buscar alunos">
                  <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" id="filtrosAlunosBtn" type="button" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="false" title="Filtrar alunos" aria-label="Filtrar alunos">
                <i class="ph ph-funnel me-1" aria-hidden="true"></i>
              </button>

              <div class="dropdown-menu p-3 dropdown-menu-lg" style="min-width: 250px">
                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <div class="form-check">
                  <input class="form-check-input filtro-status" type="checkbox" value="Ativo" id="alunoAtivo">
                  <label class="form-check-label" for="alunoAtivo">Ativo</label>
                </div>
                <div class="form-check">
                  <input class="form-check-input filtro-status" type="checkbox" value="Inativo" id="alunoInativo">
                  <label class="form-check-label" for="alunoInativo">Inativo</label>
                </div>

                <hr class="dropdown-divider">

                <div class="d-grid gap-2">
                  <button class="btn btn-sm btn-red" id="aplicarFiltros" type="button">Aplicar Filtros</button>
                  <button class="btn btn-sm btn-red" id="limparFiltros" type="button">Limpar</button>
                </div>
              </div>
            </div>
          </div>
          <?php if (\Core\Auth\Auth::can('alunos.criar')): ?>
          <a href="alunos/cadastrar" class="btn btn-red d-flex align-items-center color border border-white" title="Cadastrar aluno" aria-label="Cadastrar aluno">
            <i class="ph ph-plus me-1" aria-hidden="true"></i> Novo Aluno
          </a>
          <?php endif; ?>
        </div>

        <div class="card-body">
          <table id="tabelaAlunos" class="table table-hover align-middle w-100" aria-label="Lista de Alunos">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome</th>
                <th scope="col" class="text-center">Matrícula</th>
                <th scope="col" class="text-center">Data Matrícula</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody></tbody>
          </table>
        </div>
      </div>
    </div>
  </main>
  
  <?php include __DIR__ . "/partials/footer.php"; ?>

  <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
  <script src="/ctt/plugins/datatables/js/dataTables.min.js"></script>
  <script src="/ctt/plugins/datatables/js/dataTables.bootstrap5.min.js"></script>
  <script src="/ctt/plugins/datatables/js/dataTables.responsive.min.js"></script>
  <script src="/ctt/plugins/datatables/js/responsive.bootstrap5.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/tabelas.js?v=2"></script>
  <script src="/ctt/js/admin/datatable/alunos.js"></script>
</body>
</html>
