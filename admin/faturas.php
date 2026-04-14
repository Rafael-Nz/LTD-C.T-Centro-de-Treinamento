<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Faturas</title>
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
      <h1 class="h4 mb-4">Faturas</h1>
      
      <!-- Cards de Resumo -->
      <div class="row mb-4">
        <div class="col-md-4 mb-3">
          <div class="card bg-primary border-0 text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">R$ 10.250,00</h4>
                  <p class="card-text">Total Emitido</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-receipt fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card bg-success border-0 text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">R$ 8.450,00</h4>
                  <p class="card-text">Total Pago</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-check-circle fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-4 mb-3">
          <div class="card bg-warning border-0 text-dark">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">R$ 1.800,00</h4>
                  <p class="card-text">Total Pendente</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-clock fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="card border-0 p-2 shadow-sm mb-4">
        <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
          <div class="d-flex gap-2 align-items-center">
            <div class="d-flex" role="search">
              <div class="input-group">
                <input id="campoBuscaFaturas" class="form-control" type="search" placeholder="Buscar por aluno ou código..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscarFaturas">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button"
                data-bs-toggle="dropdown" aria-expanded="false" title="Filtrar Faturas">
                <i class="ph ph-funnel me-1"></i>
              </button>
              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton"
                style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <select class="form-select" id="filtroStatusFatura">
                    <option value="">Todos</option>
                    <option value="Pago">Pago</option>
                    <option value="Pendente">Pendente</option>
                    <option value="Atrasado">Atrasado</option>
                    <option value="Cancelado">Cancelado</option>
                  </select>
                </li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <p class="h6 text-start" style="font-size: 0.875rem">Período</p>
                <li>
                  <select class="form-select" id="filtroPeriodo">
                    <option value="">Todos</option>
                    <option value="este_mes">Este Mês</option>
                    <option value="mes_passado">Mês Passado</option>
                    <option value="ultimos_3_meses">Últimos 3 Meses</option>
                    <option value="ultimos_6_meses">Últimos 6 Meses</option>
                  </select>
                </li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <li class="d-grid">
                  <button id="aplicarFiltrosFaturas" class="btn btn-sm btn-red color">Aplicar Filtros</button>
                </li>
              </ul>
            </div>
          </div>
          <div class="d-flex gap-2">
            <a href="nova_fatura.php" class="btn btn-red d-flex align-items-center color border border-white"
              title="Emitir Nova Fatura">
              <i class="ph ph-plus me-1"></i> Nova Fatura
            </a>
            <button class="btn btn-outline-primary d-flex align-items-center" id="btnExportar">
              <i class="ph ph-file-csv me-1"></i> Exportar
            </button>
          </div>
        </div>
        <div class="card-body">
          <table id="tabelaFaturas" class="table table-hover align-middle w-100" aria-label="Lista de Faturas">
            <thead>
              <tr>
                <th scope="col" class="text-start">Código</th>
                <th scope="col" class="text-start">Aluno</th>
                <th scope="col" class="text-center">Emissão</th>
                <th scope="col" class="text-center">Vencimento</th>
                <th scope="col" class="text-center">Valor</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Exemplo 1 -->
              <tr>
                <td class="text-start fw-semibold">FAT-2024-001</td>
                <td class="text-start">
                  João Silva
                </td>
                <td class="text-center">01/03/2024</td>
                <td class="text-center">10/03/2024</td>
                <td class="text-center">R$ 150,00</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="visualizar_fatura.php?id=1" target="_blank" title="Visualizar">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-primary ms-2" title="Imprimir">
                    <i class="ph ph-printer"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-success ms-2" title="Reenviar">
                    <i class="ph ph-paper-plane-tilt"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-danger ms-2" title="Cancelar">
                    <i class="ph ph-x-circle"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo 2 -->
              <tr>
                <td class="text-start fw-semibold">FAT-2024-002</td>
                <td class="text-start">
                  Maria Souza
                </td>
                <td class="text-center">28/02/2024</td>
                <td class="text-center">05/03/2024</td>
                <td class="text-center">R$ 180,00</td>
                <td class="text-center">
                  <span class="badge bg-danger-subtle text-danger-emphasis">Atrasado</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="visualizar_fatura.php?id=2" target="_blank" title="Visualizar">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-success ms-2" title="Registrar Pagamento">
                    <i class="ph ph-currency-dollar"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-warning ms-2" title="Enviar Lembrete">
                    <i class="ph ph-bell"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo 3 -->
              <tr>
                <td class="text-start fw-semibold">FAT-2024-003</td>
                <td class="text-start">
                  Carlos Lima
                </td>
                <td class="text-center">05/03/2024</td>
                <td class="text-center">15/03/2024</td>
                <td class="text-center">R$ 120,00</td>
                <td class="text-center">
                  <span class="badge bg-warning-subtle text-warning-emphasis">Pendente</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="visualizar_fatura.php?id=3" target="_blank" title="Visualizar">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-success ms-2" title="Registrar Pagamento">
                    <i class="ph ph-currency-dollar"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo 4 -->
              <tr>
                <td class="text-start fw-semibold">FAT-2024-004</td>
                <td class="text-start">
                  Ana Santos
                </td>
                <td class="text-center">01/02/2024</td>
                <td class="text-center">10/02/2024</td>
                <td class="text-center">R$ 160,00</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="visualizar_fatura.php?id=4" target="_blank" title="Visualizar">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-primary ms-2" title="Imprimir">
                    <i class="ph ph-printer"></i>
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
  
  <script>
    $(document).ready(function() {
      // Inicializar DataTable
      $('#tabelaFaturas').DataTable({
        responsive: true,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        order: [[0, 'desc']] // Ordenar por código (mais recente primeiro)
      });

      // Botão Exportar
      $('#btnExportar').on('click', function() {
        Swal.fire({
          title: 'Exportar Faturas',
          html: `
            <div class="text-start">
              <label class="form-label">Formato</label>
              <select class="form-select mb-3">
                <option value="csv">CSV</option>
                <option value="excel">Excel</option>
                <option value="pdf">PDF</option>
              </select>
              <label class="form-label">Período</label>
              <select class="form-select mb-3">
                <option value="todos">Todos</option>
                <option value="este_mes">Este Mês</option>
                <option value="mes_passado">Mês Passado</option>
              </select>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Exportar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            // Simular download
            Swal.fire('Sucesso!', 'Exportação iniciada.', 'success');
          }
        });
      });

      // Aplicar filtros
      $('#aplicarFiltrosFaturas').on('click', function() {
        Swal.fire({
          icon: 'info',
          title: 'Filtros Aplicados',
          text: 'Os filtros foram aplicados à lista de faturas.',
          timer: 1500
        });
      });
    });
  </script>
</body>
</html>