<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Planos & Preços</title>
  <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Planos & Preços</h1>

      <!-- Cards de Estatísticas -->
      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Total de Planos</h5>
              <h2 class="text-success">8</h2>
              <p class="text-muted">Planos ativos no sistema</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Plano Mais Popular</h5>
              <h2 class="text-success">CrossFit Mensal</h2>
              <p class="text-muted">42 alunos</p>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card border-0 shadow-sm">
            <div class="card-body text-center">
              <h5 class="card-title">Faturamento Médio</h5>
              <h2 class="text-success">R$ 8.450</h2>
              <p class="text-muted">por mês</p>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBuscaPlanos" class="form-control" type="search" placeholder="Buscar plano..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscarPlanos">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button"
                data-bs-toggle="dropdown" aria-expanded="false" title="Filtrar Planos">
                <i class="ph ph-funnel me-1"></i>
              </button>
              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton" style="min-width: 200px;">
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
                  <button id="aplicarFiltrosPlanos" class="btn btn-sm btn-red color">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
            <a href="plano_form" class="btn btn-red d-flex align-items-center color border border-white"
              title="Criar Novo Plano">
              <i class="ph ph-plus me-1"></i> Novo Plano
            </a>
          </div>
        </div>
        <div class="card-body">
          <table id="tabelaPlanos" class="table table-hover align-middle w-100" aria-label="Lista de Planos">
            <thead>
              <tr>
                <th scope="col" class="text-start">Plano</th>
                <th scope="col" class="text-center">Periodicidade</th>
                <th scope="col" class="text-center">Valor</th>
                <th scope="col" class="text-center">Alunos</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>

            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Scripts -->
<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<!-- Adicione DataTables JS -->
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
<script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script>
<script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.min.js"></script>

<script src="../public/js/admin/tabelas.js"></script>

<script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
<script defer src="../public/js/admin/sidebar.js"></script>
<script defer src="../public/js/admin/planos.js"></script>
</body>
</html>