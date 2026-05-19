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
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
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
      <h1 class="h4 mb-4">Relatórios do Centro de Treinamento</h1>

      <div class="row g-3 mb-4">
        <!-- FILTROS -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0">Filtros de Busca</h5>
            </div>
            <div class="card-body">
              <form id="filtroRelatorios" class="row g-3">
                <div class="col-md-3">
                  <label for="tipoRelatorio" class="form-label">Tipo de Relatório</label>
                  <select class="form-select select2" id="tipoRelatorio" name="tipoRelatorio">
                    <option value="">Selecione um relatório</option>
                    <option value="alunos">Alunos</option>
                    <option value="presenca">Presença em Treinos</option>
                    <option value="avaliacoes">Avaliações Físicas</option>
                    <option value="turmas">Turmas</option>
                    <option value="funcionarios">Funcionários</option>
                  </select>
                </div>

                <div class="col-md-3" id="filtroModalidade" style="display: none;">
                  <label for="modalidade" class="form-label">Modalidade</label>
                  <select class="form-select select2" id="modalidade" name="modalidade">
                    <option value="">Todas as modalidades</option>
                  </select>
                </div>

                <div class="col-md-3" id="filtroTurma" style="display: none;">
                  <label for="turma" class="form-label">Turma</label>
                  <select class="form-select select2" id="turma" name="turma">
                    <option value="">Todas as turmas</option>
                  </select>
                </div>

                <div class="col-md-3" id="filtroPeriodo" style="display: none;">
                  <label for="dataInicio" class="form-label">Data Início</label>
                  <div class="input-group" id="datetimepicker_inicio" data-td-target-input="nearest" data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataInicio" name="dataInicio" data-td-target="#dataInicio">
                    <span class="input-group-text" data-td-target="#dataInicio" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>

                <div class="col-md-3" id="filtroDataFim" style="display: none;">
                  <label for="dataFim" class="form-label">Data Fim</label>
                  <div class="input-group" id="datetimepicker_fim" data-td-target-input="nearest" data-td-target-toggle="nearest">
                    <input type="text" class="form-control" id="dataFim" name="dataFim" data-td-target="#dataFim">
                    <span class="input-group-text" data-td-target="#dataFim" data-td-toggle="datetimepicker">
                      <i class="ph ph-calendar"></i>
                    </span>
                  </div>
                </div>

                <div class="col-md-3" id="filtroStatus" style="display: none;">
                  <label for="status" class="form-label">Status</label>
                  <select class="form-select select2" id="status" name="status">
                    <option value="">Todos</option>
                    <option value="ativo">Ativo</option>
                    <option value="inativo">Inativo</option>
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

        <!-- CARDS COM MÉTRICAS -->
        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-primary text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1">Total de Alunos</h6>
                </div>
                <i class="ph ph-users h3 opacity-75"></i>
              </div>
              <div class="fs-5 fw-bold" id="totalAlunos">0</div>
              <small class="opacity-75" id="detalheTotalAlunos"></small>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-success text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1">Turmas Ativas</h6>
                </div>
                <i class="ph ph-chalkboard h3 opacity-75"></i>
              </div>
              <div class="fs-5 fw-bold" id="turmasAtivas">0</div>
              <small class="opacity-75" id="detalheturmasAtivas"></small>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-info text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1">Taxa de Presença</h6>
                </div>
                <i class="ph ph-check-circle h3 opacity-75"></i>
              </div>
              <div class="fs-5 fw-bold" id="taxaPresenca">0%</div>
              <small class="opacity-75" id="detalhetaxaPresenca"></small>
            </div>
          </div>
        </div>

        <div class="col-xl-3 col-md-6">
          <div class="card border-0 bg-warning text-white h-100 shadow-sm">
            <div class="card-body p-3">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="card-title text-uppercase opacity-75 mb-1">Avaliações</h6>
                </div>
                <i class="ph ph-activity h3 opacity-75"></i>
              </div>
              <div class="fs-5 fw-bold" id="avaliacoesRealizadas">0</div>
              <small class="opacity-75" id="detalheavacoesRealizadas"></small>
            </div>
          </div>
        </div>

        <!-- TABELA -->
        <div class="col-12">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0 d-flex justify-content-between align-items-center">
              <h5 class="section-title mb-0" id="tituloTabela">Selecione um relatório</h5>
              <button class="btn btn-primary text-center btn-sm" id="btnExportar" style="display: none;">
                <i class="ph ph-download me-2"></i>Exportar
              </button>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-striped table-hover">
                  <thead id="cabecalhoTabela">
                    <tr><th>Carregando...</th></tr>
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
        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0">Distribuição por Modalidade</h5>
            </div>
            <div class="card-body">
              <canvas id="graficoDistribuicao" height="300"></canvas>
            </div>
          </div>
        </div>

        <div class="col-md-6">
          <div class="card border-0 shadow-sm">
            <div class="card-header bg-white border-0">
              <h5 class="section-title mb-0">Presença por Turma</h5>
            </div>
            <div class="card-body">
              <canvas id="graficoPresenca" height="300"></canvas>
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

  <script>
    let graficoDist = null;
    let graficoPresenca = null;

    $(document).ready(function () {
      $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        language: 'pt-BR'
      });

      carregarFiltros();

      $('#tipoRelatorio').on('change', function () {
        atualizarFiltros($(this).val());
        carregarMetricas($(this).val());
      });

      $('#filtroRelatorios').on('submit', function (e) {
        e.preventDefault();
        gerarRelatorio();
      });

      $('#btnExportar').on('click', function () {
        exportarRelatorio();
      });

      new window.tempusdominus.TempusDominus(document.getElementById('datetimepicker_inicio'), {
        localization: window.tempusdominus.locales.pt,
      });

      new window.tempusdominus.TempusDominus(document.getElementById('datetimepicker_fim'), {
        localization: window.tempusdominus.locales.pt,
      });
    });

    function carregarFiltros() {
      $.ajax({
        url: '/ctt/api/modalidades',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          const select = $('#modalidade');
          select.empty();
          select.append('<option value="">Todas as modalidades</option>');
          data.forEach(item => select.append(`<option value="${item.id}">${item.nome}</option>`));
        }
      });

      $.ajax({
        url: '/ctt/api/turmas',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
          const select = $('#turma');
          select.empty();
          select.append('<option value="">Todas as turmas</option>');
          data.forEach(item => select.append(`<option value="${item.id}">${item.nome}</option>`));
        }
      });
    }

    function atualizarFiltros(tipoRelatorio) {
      $('#filtroModalidade, #filtroTurma, #filtroPeriodo, #filtroDataFim, #filtroStatus').hide();
      $('#btnExportar').hide();

      switch (tipoRelatorio) {
        case 'alunos':
          $('#filtroStatus').show();
          $('#btnExportar').show();
          break;
        case 'presenca':
          $('#filtroTurma, #filtroPeriodo, #filtroDataFim').show();
          $('#btnExportar').show();
          break;
        case 'avaliacoes':
          $('#filtroModalidade, #filtroPeriodo, #filtroDataFim').show();
          $('#btnExportar').show();
          break;
        case 'turmas':
          $('#filtroModalidade').show();
          $('#btnExportar').show();
          break;
        case 'funcionarios':
          $('#btnExportar').show();
          break;
      }
    }

    function carregarMetricas(tipoRelatorio) {
      $.ajax({
        url: '/ctt/api/relatorios/metricas',
        type: 'GET',
        data: { tipo: tipoRelatorio },
        dataType: 'json',
        success: function (data) {
          $('#totalAlunos').text(data.totalAlunos || 0);
          $('#detalheTotalAlunos').text(`${data.alunosAtivos || 0} ativos`);
          $('#turmasAtivas').text(data.turmasAtivas || 0);
          $('#detalheturmasAtivas').text(`${data.turmasTotal || 0} total`);
          $('#taxaPresenca').text((data.taxaPresenca || 0) + '%');
          $('#detalhetaxaPresenca').text('últimos 30 dias');
          $('#avaliacoesRealizadas').text(data.avaliacoesRealizadas || 0);
          $('#detalheavacoesRealizadas').text('este mês');
        }
      });
    }

    function gerarRelatorio() {
      const tipoRelatorio = $('#tipoRelatorio').val();
      if (!tipoRelatorio) {
        Swal.fire('Atenção', 'Selecione um tipo de relatório', 'warning');
        return;
      }

      const params = {
        tipo: tipoRelatorio,
        modalidade: $('#modalidade').val(),
        turma: $('#turma').val(),
        dataInicio: $('#dataInicio').val(),
        dataFim: $('#dataFim').val(),
        status: $('#status').val()
      };

      $.ajax({
        url: '/ctt/api/relatorios/gerar',
        type: 'GET',
        data: params,
        dataType: 'json',
        beforeSend: () => {
          $('#tabelaRelatorios').html('<tr><td colspan="6"><div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div></td></tr>');
        },
        success: (data) => {
          renderizarRelatorio(data, tipoRelatorio);
          renderizarGraficos(data, tipoRelatorio);
        },
        error: () => {
          Swal.fire('Erro', 'Erro ao gerar relatório', 'error');
        }
      });
    }

    function renderizarRelatorio(data, tipoRelatorio) {
      let html = '';
      let cabecalho = '';

      switch (tipoRelatorio) {
        case 'alunos':
          cabecalho = '<tr><th>Matrícula</th><th>Nome</th><th>CPF</th><th>Email</th><th>Data</th><th>Status</th></tr>';
          data.registros.forEach(a => {
            const status = a.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
            html += `<tr><td>${a.codigo_matricula}</td><td>${a.nome} ${a.sobrenome}</td><td>${a.cpf}</td><td>${a.email}</td><td>${new Date(a.data_matricula).toLocaleDateString('pt-BR')}</td><td>${status}</td></tr>`;
          });
          $('#tituloTabela').text('Relatório de Alunos');
          break;

        case 'presenca':
          cabecalho = '<tr><th>Data</th><th>Turma</th><th>Aluno</th><th>Situação</th></tr>';
          data.registros.forEach(p => {
            let badge = '';
            if (p.situacao === 'presente') badge = '<span class="badge bg-success">Presente</span>';
            else if (p.situacao === 'ausente') badge = '<span class="badge bg-danger">Ausente</span>';
            else badge = '<span class="badge bg-warning">Justificado</span>';
            html += `<tr><td>${new Date(p.data_treino).toLocaleDateString('pt-BR')}</td><td>${p.turma}</td><td>${p.aluno}</td><td>${badge}</td></tr>`;
          });
          $('#tituloTabela').text('Relatório de Presença');
          break;

        case 'avaliacoes':
          cabecalho = '<tr><th>Data</th><th>Aluno</th><th>Avaliador</th><th>Peso (kg)</th><th>Altura (m)</th><th>% Gordura</th></tr>';
          data.registros.forEach(av => {
            html += `<tr><td>${new Date(av.data_avaliacao).toLocaleDateString('pt-BR')}</td><td>${av.aluno}</td><td>${av.avaliador}</td><td>${av.peso}</td><td>${av.altura}</td><td>${av.percentual_gordura}%</td></tr>`;
          });
          $('#tituloTabela').text('Relatório de Avaliações');
          break;

        case 'turmas':
          cabecalho = '<tr><th>Turma</th><th>Modalidade</th><th>Instrutor</th><th>Horarios</th><th>Alunos</th><th>Capacidade</th></tr>';
          data.registros.forEach(t => {
            html += `<tr><td>${t.nome}</td><td>${t.modalidade}</td><td>${t.instrutor || '-'}</td><td>${t.horarios_resumo || t.horarios || '-'}</td><td>${t.alunos}</td><td>${t.alunos}/${t.capacidade_maxima}</td></tr>`;
          });
          $('#tituloTabela').text('Relatório de Turmas');
          break;

        case 'funcionarios':
          cabecalho = '<tr><th>Nome</th><th>CPF</th><th>Email</th><th>Cargo</th><th>Registro</th><th>Status</th></tr>';
          data.registros.forEach(f => {
            const status = f.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
            html += `<tr><td>${f.nome} ${f.sobrenome}</td><td>${f.cpf}</td><td>${f.email}</td><td>${f.cargo}</td><td>${f.registro_profissional || '-'}</td><td>${status}</td></tr>`;
          });
          $('#tituloTabela').text('Relatório de Funcionários');
          break;
      }

      $('#cabecalhoTabela').html(cabecalho);
      $('#tabelaRelatorios').html(html || '<tr><td class="text-center text-muted">Nenhum registro encontrado</td></tr>');
    }

    function renderizarGraficos(data, tipoRelatorio) {
      if (graficoDist) graficoDist.destroy();
      if (graficoPresenca) graficoPresenca.destroy();

      if (tipoRelatorio === 'alunos' && data.graficoModalidade) {
        const ctx = document.getElementById('graficoDistribuicao').getContext('2d');
        graficoDist = new Chart(ctx, {
          type: 'doughnut',
          data: {
            labels: data.graficoModalidade.labels,
            datasets: [{
              data: data.graficoModalidade.valores,
              backgroundColor: ['rgba(54, 162, 235, 0.8)', 'rgba(75, 192, 192, 0.8)', 'rgba(255, 206, 86, 0.8)', 'rgba(153, 102, 255, 0.8)'],
            }]
          },
          options: { responsive: true, maintainAspectRatio: false }
        });
      }

      if (tipoRelatorio === 'presenca' && data.graficoPresenca) {
        const ctx = document.getElementById('graficoPresenca').getContext('2d');
        graficoPresenca = new Chart(ctx, {
          type: 'bar',
          data: {
            labels: data.graficoPresenca.labels,
            datasets: [
              { label: 'Presentes', data: data.graficoPresenca.presentes, backgroundColor: 'rgba(75, 192, 192, 0.8)' },
              { label: 'Ausentes', data: data.graficoPresenca.ausentes, backgroundColor: 'rgba(255, 99, 132, 0.8)' }
            ]
          },
          options: { responsive: true, maintainAspectRatio: false, scales: { y: { beginAtZero: true } } }
        });
      }
    }

    function exportarRelatorio() {
      const params = new URLSearchParams({
        tipo: $('#tipoRelatorio').val(),
        modalidade: $('#modalidade').val(),
        turma: $('#turma').val(),
        dataInicio: $('#dataInicio').val(),
        dataFim: $('#dataFim').val(),
        status: $('#status').val(),
        formato: 'xlsx'
      });
      window.location.href = `/ctt/api/relatorios/exportar?${params.toString()}`;
    }
  </script>
</body>
</html>
