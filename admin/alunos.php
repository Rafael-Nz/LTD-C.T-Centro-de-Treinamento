<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Alunos</title>
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
  <?php include __DIR__ . "/partials/sidebar.php"; ?>
  <?php include __DIR__ . "/partials/header.php"; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Alunos</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar aluno..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle border border-white" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" title="Filtrar Alunos">
                <i class="ph ph-funnel me-1"></i>
              </button>

              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton"
                style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Plano</p>
                <li>
                  <select class="form-select" id="filtroPlanosSelect" multiple="multiple">
                    <option value="Mensal">Mensal</option>
                    <option value="Trimestral">Trimestral</option>
                    <option value="Semestral">Semestral</option>
                    <option value="Anual">Anual</option>
                  </select>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Ativo" id="alunoAtivo">
                    <label class="form-check-label" style="font-size: 0.975rem" for="alunoAtivo">Ativo</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Inativo" id="alunoInativo">
                    <label class="form-check-label" style="font-size: 0.975rem" for="alunoInativo">Inativo</label>
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
          <a href="aluno_form" class="btn btn-red d-flex align-items-center color border border-white"
            title="Adicionar Novo Aluno">
            <i class="ph ph-plus me-1"></i> Novo Aluno
          </a>
        </div>

        <div class="card-body">
          <table id="tabelaAlunos" class="table table-hover align-middle w-100" aria-label="Lista de Alunos">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome</th>
                <th scope="col" class="text-start">Email</th>
                <th scope="col" class="text-center">Data Matrícula</th>
                <th scope="col" class="text-center">Plano</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Exemplo de aluno 1 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  Pedro Henrique Oliveira
                </td>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  pedro.oliveira@email.com
                </td>
                <td class="text-center">15/03/2024</td>
                <td class="text-center">
                  <span class="badge bg-primary-subtle text-primary-emphasis">Mensal</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="aluno_form.php?id=1" title="Ver/Editar Aluno">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2 btn-toggle-status" data-id="1"
                    data-nome="Pedro Henrique Oliveira" data-ativo="true" title="Desativar Aluno">
                    <i class="ph ph-x-circle"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo de aluno 2 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  Ana Carolina Silva
                </td>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  ana.silva@email.com
                </td>
                <td class="text-center">10/01/2024</td>
                <td class="text-center">
                  <span class="badge bg-warning-subtle text-warning-emphasis">Anual</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="aluno_form.php?id=2" title="Ver/Editar Aluno">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2 btn-toggle-status" data-id="2"
                    data-nome="Ana Carolina Silva" data-ativo="true" title="Desativar Aluno">
                    <i class="ph ph-x-circle"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo de aluno 3 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  Carlos Eduardo Santos
                </td>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  carlos.santos@email.com
                </td>
                <td class="text-center">22/08/2023</td>
                <td class="text-center">
                  <span class="badge bg-info-subtle text-info-emphasis">Trimestral</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-secondary-subtle text-secondary-emphasis">Inativo</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="aluno_form.php?id=3" title="Ver/Editar Aluno">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <button class="btn btn-sm btn-success ms-2 btn-toggle-status" data-id="3"
                    data-nome="Carlos Eduardo Santos" data-ativo="false" title="Reativar Aluno">
                    <i class="ph ph-arrow-counter-clockwise"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo de aluno 4 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  Mariana Costa Lima
                </td>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  mariana.lima@email.com
                </td>
                <td class="text-center">05/11/2023</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Semestral</span>
                </td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="aluno_form.php?id=4" title="Ver/Editar Aluno">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2 btn-toggle-status" data-id="4"
                    data-nome="Mariana Costa Lima" data-ativo="true" title="Desativar Aluno">
                    <i class="ph ph-x-circle"></i>
                  </button>
                </td>
              </tr>
            </tbody>
          </table>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . "/partials/footer.php"; ?>
  
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="../public/js/admin/sidebar.js"></script>
</body>
</html>