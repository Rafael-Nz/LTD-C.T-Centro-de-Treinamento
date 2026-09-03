<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Relatórios</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet"
    integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
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
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js"
    crossorigin="anonymous"></script>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css"
    crossorigin="anonymous">
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/locales/pt.js"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4">Relatórios</h1>

      <div class="row g-3 mb-4">
        <!-- FILTROS -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0">Filtros de Busca</h5>
            </div>
            <div class="card-body">
              <form id="filtroRelatorios" class="row g-3">
                <div class="col-md-6">
                  <label for="tipoRelatorio" class="form-label">Tipo de Relatório</label>
                  <select class="form-select select2" id="tipoRelatorio" name="tipoRelatorio">
                    <option value="">Selecione um relatório</option>
                    <option value="alunos">Alunos</option>
                    <option value="presenca">Presença em Treinos</option>
                    <option value="avaliacoes">Avaliações Físicas</option>
                    <option value="turmas">Turmas</option>
                    <option value="funcionarios">Funcionários</option>
                    <option value="treinos">Agenda de treinos</option>
                  </select>
                </div>

                <div class="col-md-6" id="filtroModalidade" style="display: none;">
                  <label for="modalidade" class="form-label">Modalidade</label>
                  <select class="form-select select2" id="modalidade" name="modalidade">
                    <option value="">Todas as modalidades</option>
                  </select>
                </div>

                <div class="col-md-6" id="filtroAluno" style="display: none;">
                  <label for="aluno" class="form-label">Aluno</label>
                  <select class="form-select select2" id="aluno" name="aluno">
                    <option value="">Todos os alunos</option>
                  </select>
                </div>

                <div class="col-md-6" id="filtroTurma" style="display: none;">
                  <label for="turma" class="form-label">Turma</label>
                  <select class="form-select select2" id="turma" name="turma">
                    <option value="">Todas as turmas</option>
                  </select>
                </div>

                <div class="col-md-6" id="filtroPeriodo" style="display: none;">
                  <label for="dataInicio" class="form-label">Data Início</label>
                  <div class="input-group" id="datetimepicker_inicio" data-td-target-input="nearest"
                    data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataInicio" name="dataInicio"
                      data-td-target="#dataInicio" autocomplete="off">
                    <span class="input-group-text" data-td-target="#dataInicio" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>

                <div class="col-md-6" id="filtroDataFim" style="display: none;">
                  <label for="dataFim" class="form-label">Data Fim</label>
                  <div class="input-group" id="datetimepicker_fim" data-td-target-input="nearest"
                    data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataFim" name="dataFim" data-td-target="#dataFim"
                      autocomplete="off">
                    <span class="input-group-text" data-td-target="#dataFim" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>

                <div class="col-md-6" id="filtroStatus" style="display: none;">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-select select2" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
                  </select>
                </div>

                <div class="col-md-6" id="filtroCargo" style="display: none;">
                  <label for="cargo" class="form-label">Cargo</label>
                  <select class="form-select select2" id="cargo" name="cargo">
                    <option value="">Todos os cargos</option>
                  </select>
                </div>

                <div class="col-12 d-flex align-items-end">
                  <button type="submit" class="btn btn-primary w-100" id="btnGerarRelatorio">
                    <i class="ph ph-magnifying-glass me-2"></i>Gerar Relatório
                  </button>
                </div>
              </form>
            </div>
          </div>
        </div>

        <!-- TABELA -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
              <h5 class="section-title mb-0" id="tituloTabela">Selecione um relatório</h5>
              <div class="dropdown" id="btnExportar" style="display: none;">
                <button class="btn btn-primary btn-sm dropdown-toggle" type="button" data-bs-toggle="dropdown"
                  aria-expanded="false">
                  <i class="ph ph-download me-2"></i>Exportar
                </button>
                <ul class="dropdown-menu dropdown-menu-end">
                  <li><button class="dropdown-item" type="button" data-formato="xlsx"><i
                        class="ph ph-file-xls me-2"></i>Excel (.xlsx)</button></li>
                  <li><button class="dropdown-item" type="button" data-formato="csv"><i
                        class="ph ph-file-csv me-2"></i>CSV (.csv)</button></li>
                </ul>
              </div>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead id="cabecalhoTabela">
                    <tr>
                    </tr>
                  </thead>
                  <tbody id="tabelaRelatorios">
                    <tr>
                      <td class="text-center text-muted py-5">
                        <p>Selecione os filtros acima e clique em "Gerar Relatório"</p>
                      </td>
                    </tr>
                  </tbody>
                </table>
              </div>
            </div>
          </div>
        </div>

        <!-- GRÁFICOS -->
        <div class="row g-3" id="secaoGraficos" style="display: none;">
          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-0">
                <h5 class="section-title mb-0" id="tituloGraficoDistribuicao">Distribuição</h5>
              </div>
              <div class="card-body">
                <canvas id="graficoDistribuicao" height="300"></canvas>
              </div>
            </div>
          </div>

          <div class="col-md-6">
            <div class="card border-0 shadow-sm">
              <div class="card-header bg-white border-0">
                <h5 class="section-title mb-0" id="tituloGraficoPresenca">Distribuição</h5>
              </div>
              <div class="card-body">
                <canvas id="graficoPresenca" height="300"></canvas>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>
  <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/relatorios.js"></script>
</body>

</html>
