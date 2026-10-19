<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Funcionários</title>
  <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css" />
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
  <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css" />
  <link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . "/partials/sidebar.php"; ?>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Funcionários</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar funcionário..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar" title="Buscar funcionarios" aria-label="Buscar funcionarios">
                  <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button" data-bs-toggle="dropdown"
                data-bs-auto-close="outside" aria-expanded="false" title="Filtrar funcionários" aria-label="Filtrar funcionários">
                <i class="ph ph-funnel me-1" aria-hidden="true"></i>
              </button>

              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton"
                style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Cargo</p>
                <li>
                  <select class="form-select" id="filtroCargosSelect">
                    <option></option>
                  </select>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="ativo" id="funcAtivo">
                    <label class="form-check-label" style="font-size: 0.975rem" for="funcAtivo">Ativo</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="inativo" id="funcInativo">
                    <label class="form-check-label" style="font-size: 0.975rem" for="funcInativo">Inativo</label>
                  </div>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <li class="d-grid">
                  <button id="aplicarFiltros" class="btn btn-sm btn-red">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
          </div>
          <a href="funcionarios/cadastrar" class="btn btn-red d-flex align-items-center color border border-white" title="Cadastrar funcionário" aria-label="Cadastrar funcionário">
            <i class="ph ph-plus me-1" aria-hidden="true"></i> Novo Funcionário
          </a>
        </div>

        <div class="card-body">
          <table id="tabelaFuncionarios" class="table table-hover align-middle w-100"
            aria-label="Lista de Funcionários">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome</th>
                <th scope="col" class="text-start">Cargo</th>
                <th scope="col" class="text-start">Email</th>
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
  <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script src="/ctt/plugins/select2/js/select2.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/tabelas.js"></script>
  <script src="/ctt/js/admin/datatable/funcionarios.js"></script>
</body>
</html>