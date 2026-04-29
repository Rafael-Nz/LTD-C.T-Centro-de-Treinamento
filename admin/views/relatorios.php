<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Relatórios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/locales/pt.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
    <h1 class="h4 mb-4">Relatórios</h1>

      <div class="row g-3 mb-4">

        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0">Filtros</h5>
            </div>
            <div class="card-body">
              <form id="filtroRelatorios" class="row g-3">
                <div class="col-md-3">
                  <label for="dataInicio" class="form-label">Data Início</label>
                  <div class="input-group" id="datetimepicker_inicio" data-td-target-input="nearest" data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataInicio" name="dataInicio" data-td-target="#dataInicio">
                    <span class="input-group-text" data-td-target="#dataInicio" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>
                <div class="col-md-3">
                  <label for="dataFim" class="form-label">Data Fim</label>
                  <div class="input-group" id="datetimepicker_fim" data-td-target-input="nearest" data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataFim" name="dataFim" data-td-target="#dataFim">
                    <span class="input-group-text" data-td-target="#dataFim" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>
                <div class="col-md-3">
                  <label for="tipoRelatorio" class="form-label">Tipo de Relatório</label>
                  <select class="form-select select2" id="tipoRelatorio" name="tipoRelatorio">
                    <option value="vendas">Vendas</option>
                    <option value="produtos">Produtos</option>
                    <option value="clientes">Clientes</option>
                    <option value="estoque">Estoque</option>
                  </select>
                </div>
                <div class="col-md-3 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100" id="btnGerarRelatorio">
                    <i class="ph ph-magnifying-glass me-2"></i>Gerar Relatório
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-primary text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1" id="tituloCard1">Total de Vendas</h6>
                </div>
                <i class="ph ph-shopping-cart h3 opacity-75"></i>
              </div>
              <div class="fs-6 fw-bold" id="totalVendas">0</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-success text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1" id="tituloCard2">Receita Total</h6>
                </div>
                <i class="ph ph-currency-dollar h3 opacity-75"></i>
              </div>
              <div class="fs-6 fw-bold" id="receitaTotal">R$ 0,00</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-info text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1" id="tituloCard3">Produtos Vendidos</h6>
                </div>
                <i class="ph ph-package h3 opacity-75"></i>
              </div>
              <div class="fs-6 fw-bold" id="produtosVendidos">0</div>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-warning text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1" id="tituloCard4">Ticket Médio</h6>
                </div>
                <i class="ph ph-trend-up h3 opacity-75"></i>
              </div>
              <div class="fs-6 fw-bold" id="ticketMedio">R$ 0,00</div>
            </div>
          </div>
        </div>

        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
              <h5 class="section-title mb-0" id="tituloTabela">Relatório de Vendas</h5>
              <button class="btn btn-primary text-center btn-sm" id="btnExportar">
                <i class="ph ph-download me-2"></i>Exportar
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead id="cabecalhoTabela">
                    <tr>
                      <th>Data</th>
                      <th>Venda ID</th>
                      <th>Cliente</th>
                      <th>Valor Total</th>
                      <th>Status</th>
                      <th>Método Pagamento</th>
                    </tr>
                  </thead>
                  <tbody id="tabelaRelatorios">
                    <tr>
                      <td colspan="6" class="text-center">Selecione os filtros e gere o relatório</td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0" id="tituloGrafico1">Vendas por Categoria</h5>
            </div>
            <div class="card-body">
              <canvas id="graficoCategorias" height="300"></canvas>
            </div>
          </div>
        </div>
        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0" id="tituloGrafico2">Vendas por Período</h5>
            </div>
            <div class="card-body">
              <canvas id="graficoPeriodo" height="300"></canvas>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
</body>
</html>