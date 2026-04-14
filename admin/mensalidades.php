<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Mensalidades</title>
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
      <h1 class="h4 mb-4">Mensalidades</h1>
      
      <!-- Cards de Resumo -->
      <div class="row mb-4">
        <div class="col-md-3 mb-3">
          <div class="card bg-primary border-0 text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">R$ 8.450,00</h4>
                  <p class="card-text">Recebido (Mês)</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-currency-dollar fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card bg-warning border-0 text-dark">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">R$ 2.150,00</h4>
                  <p class="card-text">Pendente</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-clock fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card bg-success border-0 text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">42</h4>
                  <p class="card-text">Pagamentos Realizados</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-check-circle fs-1"></i>
                </div>
              </div>
            </div>
          </div>
        </div>
        <div class="col-md-3 mb-3">
          <div class="card bg-danger border-0 text-white">
            <div class="card-body">
              <div class="d-flex justify-content-between">
                <div>
                  <h4 class="card-title">8</h4>
                  <p class="card-text">Em Atraso</p>
                </div>
                <div class="align-self-center">
                  <i class="ph ph-warning-circle fs-1"></i>
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
                <input id="campoBusca" class="form-control" type="search" placeholder="Buscar aluno..."
                  aria-label="Buscar">
                <button class="btn border border-start-0" type="button" id="botaoBuscar">
                  <i class="ph ph-magnifying-glass"></i>
                </button>
              </div>
            </div>
            <div class="dropdown-center">
              <button class="btn btn-red dropdown-toggle color border border-white" type="button"
                data-bs-toggle="dropdown" aria-expanded="false" title="Filtrar Mensalidades">
                <i class="ph ph-funnel me-1"></i>
              </button>
              <ul class="dropdown-menu p-3 dropdown-menu-lg" aria-labelledby="dropdownMenuButton"
                style="min-width: 250px;">
                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Pago" id="statusPago">
                    <label class="form-check-label" style="font-size: 0.975rem" for="statusPago">Pago</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Pendente" id="statusPendente">
                    <label class="form-check-label" style="font-size: 0.975rem" for="statusPendente">Pendente</label>
                  </div>
                </li>
                <li>
                  <div class="form-check">
                    <input class="form-check-input filtro-status" type="checkbox" value="Atrasado" id="statusAtrasado">
                    <label class="form-check-label" style="font-size: 0.975rem" for="statusAtrasado">Atrasado</label>
                  </div>
                </li>
                <li>
                  <hr class="dropdown-divider">
                </li>
                <p class="h6 text-start" style="font-size: 0.875rem">Mês</p>
                <li>
                  <select class="form-select" id="filtroMes">
                    <option value="">Todos</option>
                    <option value="01">Janeiro</option>
                    <option value="02">Fevereiro</option>
                    <option value="03">Março</option>
                    <option value="04">Abril</option>
                    <option value="05">Maio</option>
                    <option value="06">Junho</option>
                    <option value="07">Julho</option>
                    <option value="08">Agosto</option>
                    <option value="09">Setembro</option>
                    <option value="10">Outubro</option>
                    <option value="11">Novembro</option>
                    <option value="12">Dezembro</option>
                  </select>
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
          <a href="nova_mensalidade.php" class="btn btn-red d-flex align-items-center color border border-white"
            title="Registrar Novo Pagamento">
            <i class="ph ph-plus me-1"></i> Novo Pagamento
          </a>
          <button class="btn btn-outline-primary d-flex align-items-center" id="btnGerarLote">
            <i class="ph ph-file-text me-1"></i> Gerar Lote
          </button>
        </div>
        <div class="card-body">
          <table id="tabelaMensalidades" class="table table-hover align-middle w-100" aria-label="Lista de Mensalidades">
            <thead>
              <tr>
                <th scope="col" class="text-start">Aluno</th>
                <th scope="col" class="text-center">Referência</th>
                <th scope="col" class="text-center">Vencimento</th>
                <th scope="col" class="text-center">Valor</th>
                <th scope="col" class="text-center">Status</th>
                <th scope="col" class="text-center">Ações</th>
              </tr>
            </thead>
            <tbody>
              <!-- Exemplo 1 -->
              <tr>
                <td class="text-start">
                  <div>
                    <div class="fw-semibold">João Silva</div>
                    <small class="text-muted">CrossFit Iniciante</small>
                  </div>
                </td>
                <td class="text-center">MAR/2024</td>
                <td class="text-center">10/03/2024</td>
                <td class="text-center">R$ 150,00</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="detalhes_mensalidade.php?id=1" title="Ver Detalhes">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-success ms-2" title="Reenviar Comprovante">
                    <i class="ph ph-paper-plane-tilt"></i>
                  </button>
                  <button class="btn btn-sm btn-outline-primary ms-2" title="Gerar 2ª Via">
                    <i class="ph ph-receipt"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo 2 -->
              <tr>
                <td class="text-start">
                  <div>
                    <div class="fw-semibold">Maria Souza</div>
                    <small class="text-muted">HIIT Avançado</small>
                  </div>
                </td>
                <td class="text-center">MAR/2024</td>
                <td class="text-center">05/03/2024</td>
                <td class="text-center">R$ 180,00</td>
                <td class="text-center">
                  <span class="badge bg-danger-subtle text-danger-emphasis">Atrasado</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="detalhes_mensalidade.php?id=2" title="Ver Detalhes">
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
                <td class="text-start">
                  <div>
                    <div class="fw-semibold">Carlos Lima</div>
                    <small class="text-muted">Musculação</small>
                  </div>
                </td>
                <td class="text-center">MAR/2024</td>
                <td class="text-center">15/03/2024</td>
                <td class="text-center">R$ 120,00</td>
                <td class="text-center">
                  <span class="badge bg-warning-subtle text-warning-emphasis">Pendente</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="detalhes_mensalidade.php?id=3" title="Ver Detalhes">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-success ms-2" title="Registrar Pagamento">
                    <i class="ph ph-currency-dollar"></i>
                  </button>
                </td>
              </tr>

              <!-- Exemplo 4 -->
              <tr>
                <td class="text-start">
                  <div>
                    <div class="fw-semibold">Ana Santos</div>
                    <small class="text-muted">Funcional</small>
                  </div>
                </td>
                <td class="text-center">FEV/2024</td>
                <td class="text-center">10/02/2024</td>
                <td class="text-center">R$ 160,00</td>
                <td class="text-center">
                  <span class="badge bg-success-subtle text-success-emphasis">Pago</span>
                </td>
                <td class="text-center text-nowrap">
                  <a class="btn btn-sm btn-info text-white" href="detalhes_mensalidade.php?id=4" title="Ver Detalhes">
                    <i class="ph ph-eye"></i>
                  </a>
                  <button class="btn btn-sm btn-outline-success ms-2" title="Reenviar Comprovante">
                    <i class="ph ph-paper-plane-tilt"></i>
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
    // Script para filtros e DataTables
    $(document).ready(function() {
      // Inicializar DataTable
      $('#tabelaMensalidades').DataTable({
        responsive: true,
        language: {
          url: 'https://cdn.datatables.net/plug-ins/1.11.5/i18n/pt-BR.json'
        },
        order: [[2, 'asc']] // Ordenar por vencimento
      });

      // Aplicar filtros
      $('#aplicarFiltros').on('click', function() {
        // Implementar lógica de filtro aqui
        Swal.fire({
          icon: 'info',
          title: 'Filtros Aplicados',
          text: 'Os filtros foram aplicados à lista.',
          timer: 1500
        });
      });

      // Botão Gerar Lote
      $('#btnGerarLote').on('click', function() {
        Swal.fire({
          title: 'Gerar Lote de Mensalidades',
          html: `
            <div class="text-start">
              <label class="form-label">Mês de Referência</label>
              <select class="form-select mb-3" id="mesLote">
                <option value="03">Março 2024</option>
                <option value="04">Abril 2024</option>
                <option value="05">Maio 2024</option>
              </select>
              <label class="form-label">Data de Vencimento</label>
              <input type="date" class="form-control mb-3" id="vencimentoLote" value="2024-04-10">
              <div class="form-check">
                <input class="form-check-input" type="checkbox" id="enviarNotificacoes">
                <label class="form-check-label" for="enviarNotificacoes">
                  Enviar notificações por e-mail
                </label>
              </div>
            </div>
          `,
          showCancelButton: true,
          confirmButtonText: 'Gerar Lote',
          cancelButtonText: 'Cancelar',
          preConfirm: () => {
            // Implementar lógica de geração de lote
            return { success: true };
          }
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire('Sucesso!', 'Lote de mensalidades gerado com sucesso.', 'success');
          }
        });
      });
    });
  </script>
</body>
</html>