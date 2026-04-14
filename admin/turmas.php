<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Turmas </title>
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
      <h1 class="h4 mb-4">Turmas</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar turma..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button"
                data-bs-toggle="dropdown" aria-expanded="false" title="Filtrar Turmas">
                <i class="ph ph-funnel me-1"></i>
              </button>
              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton" style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Aberta" id="turmaAberta">
                    <label class="form-check-label" style="font-size: 0.975rem" for="turmaAberta">Aberta</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Fechada" id="turmaFechada">
                    <label class="form-check-label" style="font-size: 0.975rem" for="turmaFechada">Fechada</label>
                  </div>
                </li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li class="d-grid">
                  <button id="aplicarFiltrosTurmas" class="btn btn-sm btn-red color">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
          </div>
          <a href="turma_form" class="btn btn-red d-flex align-items-center color border border-white"
            title="Adicionar Nova Turma">
            <i class="ph ph-plus me-1"></i> Nova Turma
          </a>
        </div>
        <div class="card-body">
          <table id="tabelaTurmas" class="table table-hover align-middle w-100" aria-label="Lista de Turmas">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome</th>
                <th scope="col" class="text-center">Capacidade</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Exemplo de turma 1 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  CrossFit Iniciantes Manhã
                </td>
                <td class="text-center">8/15</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Aberta</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="turma_form.php?id=1" title="Ver/Editar Turma">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <a class="btn btn-sm btn-success ms-2" href="turma_alunos.php?id=1" title="Gerenciar Alunos">
                    <i class="ph ph-users"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2 btn-toggle-status" data-id="1"
                    data-nome="CrossFit Iniciantes Manhã" data-aberta="true" title="Fechar Turma">
                    <i class="ph ph-x-circle"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo de turma 2 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  HIIT Avançado Noite
                </td>
                <td class="text-center">12/12</td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary-emphasis">Fechada</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="turma_form.php?id=2" title="Ver/Editar Turma">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <a class="btn btn-sm btn-success ms-2" href="turma_alunos.php?id=2" title="Gerenciar Alunos">
                    <i class="ph ph-users"></i>
                  </a>
                  <button class="btn btn-sm btn-success ms-2 btn-toggle-status" data-id="2"
                    data-nome="HIIT Avançado Noite" data-aberta="false" title="Abrir Turma">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
  <script defer
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
</body>

</html>