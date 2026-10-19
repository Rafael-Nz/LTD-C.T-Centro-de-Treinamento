<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Turmas</title>
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
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Turmas</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap ">
          <div class="d-flex gap-2 align-items-center flex-wrap">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar turma..." aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar" title="Buscar turmas" aria-label="Buscar turmas">
                  <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filtrar turmas" aria-label="Filtrar turmas">
                <i class="ph ph-funnel me-1" aria-hidden="true"></i>
              </button>
              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton" style="min-width: 300px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="ativo" id="turmaAtiva">
                    <label class="form-check-label" style="font-size: 0.975rem" for="turmaAtiva">Ativa</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="inativo" id="turmaInativa">
                    <label class="form-check-label" style="font-size: 0.975rem" for="turmaInativa">Inativa</label>
                  </div>
                </li>
                <li><hr class="dropdown-divider"></li>
                <li class="d-grid">
                  <button id="aplicarFiltros" class="btn btn-sm btn-red">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
          </div>
          <a href="/ctt/admin/turmas/cadastrar" class="btn btn-red d-flex align-items-center color border border-white" title="Cadastrar turma" aria-label="Cadastrar turma">
            <i class="ph ph-plus me-1" aria-hidden="true"></i> Nova Turma
          </a>
        </div>
        <div class="card-body">
          <table id="tabelaTurmas" class="table table-hover align-middle w-100" aria-label="Lista de Turmas">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome</th>
                <th scope="col" class="text-start">Instrutor</th>
                <th scope="col" class="text-center">Alunos</th>
                <th scope="col" class="text-center">Capacidade</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Acoes</th>
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
  <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script src="/ctt/plugins/select2/js/select2.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/tabelas.js"></script>
  <script src="/ctt/js/admin/datatable/turmas.js"></script>
</body>
</html>
