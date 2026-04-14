<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Treinos</title>
  <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/calendar.css">
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
      <h1 class="h4 mb-4">Treinos</h1>
      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar treino..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button" data-bs-toggle="dropdown"
                aria-expanded="false" title="Filtrar Treinos">
                <i class="ph ph-funnel me-1"></i>
              </button>

              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton"
                style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Tipo de Treino</p>
                <li>
                  <select class="form-select" id="filtroTiposSelect" multiple="multiple">
                    <option value="CrossFit">CrossFit</option>
                    <option value="Musculação">Musculação</option>
                    <option value="Funcional">Funcional</option>
                    <option value="Aeróbico">Aeróbico</option>
                    <option value="HIIT">HIIT</option>
                  </select>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <p class="h6 text-start" style="font-size: 0.875rem">Nível</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-nivel" type="checkbox" value="Iniciante" id="treinoIniciante">
                    <label class="form-check-label" style="font-size: 0.975rem" for="treinoIniciante">Iniciante</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-nivel" type="checkbox" value="Intermediário"
                      id="treinoIntermediario">
                    <label class="form-check-label" style="font-size: 0.975rem"
                      for="treinoIntermediario">Intermediário</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-nivel" type="checkbox" value="Avançado" id="treinoAvancado">
                    <label class="form-check-label" style="font-size: 0.975rem" for="treinoAvancado">Avançado</label>
                  </div>
                </li>

                <li>
                  <hr class="dropdown-divider">
                </li>

                <li class="d-grid">
                  <button id="aplicarFiltros" class="btn btn-sm btn-red color">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
          </div>
          <a href="treino_form" class="btn btn-red d-flex align-items-center color border border-white">
            <i class="ph ph-plus me-1"></i> Novo Treino
          </a>
        </div>

        <div class="card-body">
          <table id="tabelaTreinos" class="table table-hover align-middle w-100 mb-4" aria-label="Lista de Treinos">
            <thead>
              <tr>
                <th scope="col" class="text-start">Nome do Treino</th>
                <th scope="col" class="text-start">Instrutor</th>
                <th scope="col" class="text-center">Duração</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Exemplo de treino 1 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  WOD Força Máxima
                </td>
                <td class="text-start">João Silva</td>
                <td class="text-center">60 min</td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="treino_form.php?id=1" title="Ver/Editar">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <a class="btn btn-sm btn-success ms-2" href="#" title="Reagendar">
                    <i class="ph ph-calendar-plus"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2" title="Remover">
                    <i class="ph ph-trash"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo de treino 2 -->
              <tr>
                <td class="text-start text-truncate" style="max-width: 150px;">
                  Circuito Funcional
                </td>
                <td class="text-start">Maria Costa</td>
                <td class="text-center">45 min</td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="treino_form.php?id=2" title="Ver/Editar">
                    <i class="ph ph-pencil-simple"></i>
                  </a>
                  <a class="btn btn-sm btn-success ms-2" href="#" title="Reagendar">
                    <i class="ph ph-calendar-plus"></i>
                  </a>
                  <button class="btn btn-sm btn-danger ms-2" title="Remover">
                    <i class="ph ph-trash"></i>
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
<script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
<script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
<script src="../public/js/admin/sidebar.js"></script>
</body>
</html>