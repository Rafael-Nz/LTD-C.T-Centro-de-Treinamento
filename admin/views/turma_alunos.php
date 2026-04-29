<?php
// Simulação de dados da turma (em produção, viria do banco de dados)
$turma_id = isset($_GET['id']) ? (int) $_GET['id'] : 1;
$turma_nome = "CrossFit Iniciantes Manhã"; // Buscaria do banco baseado no ID
$turma_capacidade_maxima = 15;
$turma_capacidade_atual = 8;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Gerenciar Alunos - <?= htmlspecialchars($turma_nome) ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css" />
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100 bg-light">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">

      <!-- Cabeçalho da Turma -->
      <div class="d-flex justify-content-between align-items-start flex-wrap gap-3 mb-4">
        <div>
          <h1 class="h3 mb-2"><?= htmlspecialchars($turma_nome) ?></h1>
          <div class="d-flex gap-3 text-muted">
            <span><i class="ph ph-users me-1"></i> <?= $turma_capacidade_atual ?>/<?= $turma_capacidade_maxima ?> alunos</span>
            <span><i class="ph ph-calendar me-1"></i> Turma ativa</span>
          </div>
        </div>
        <div class="d-flex gap-2">
          <a href="turmas.php" class="btn btn-outline-secondary">
            <i class="ph ph-arrow-left me-1"></i> Voltar
          </a>
          <button class="btn btn-red" data-bs-toggle="modal" data-bs-target="#modalAdicionarAluno" id="btnAdicionarAluno">
            <i class="ph ph-plus me-1"></i> Adicionar Aluno
          </button>
        </div>
      </div>

      <!-- Cards de Estatísticas -->
      <div class="row g-3 mb-4">
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Ocupação</h6>
                  <h3 class="mb-0"><?= round(($turma_capacidade_atual / $turma_capacidade_maxima) * 100) ?>%</h3>
                </div>
                <div class="bg-success bg-opacity-10 rounded-circle p-3">
                  <i class="ph ph-chart-line fs-4 text-success"></i>
                </div>
              </div>
              <div class="progress mt-3" style="height: 8px;">
                <div class="progress-bar bg-success" style="width: <?= ($turma_capacidade_atual / $turma_capacidade_maxima) * 100 ?>%"></div>
              </div>
              <small class="text-muted mt-2 d-block"><?= $turma_capacidade_maxima - $turma_capacidade_atual ?> vagas disponíveis</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Frequência Média</h6>
                  <h3 class="mb-0">87%</h3>
                </div>
                <div class="bg-primary bg-opacity-10 rounded-circle p-3">
                  <i class="ph ph-calendar-check fs-4 text-primary"></i>
                </div>
              </div>
              <small class="text-muted mt-2 d-block">Últimos 30 dias</small>
            </div>
          </div>
        </div>
        <div class="col-md-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-body">
              <div class="d-flex justify-content-between align-items-center">
                <div>
                  <h6 class="text-muted mb-1">Mensalidades em Dia</h6>
                  <h3 class="mb-0">92%</h3>
                </div>
                <div class="bg-warning bg-opacity-10 rounded-circle p-3">
                  <i class="ph ph-currency-circle-dollar fs-4 text-warning"></i>
                </div>
              </div>
              <small class="text-muted mt-2 d-block"><?= $turma_capacidade_atual - 1 ?> alunos em dia</small>
            </div>
          </div>
        </div>
      </div>

      <!-- Tabela de Alunos -->
      <div class="card border-0 shadow-sm">
        <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center flex-wrap gap-2 p-3">
          <h5 class="mb-0">Lista de Alunos</h5>
          <div class="d-flex gap-2">
            <div class="input-group" style="width: 250px;">
              <input type="text" id="campoBuscaAlunos" class="form-control form-control-sm" placeholder="Buscar aluno...">
              <button class="btn btn-outline-secondary btn-sm" type="button">
                <i class="ph ph-magnifying-glass"></i>
              </button>
            </div>
            <button class="btn btn-outline-secondary btn-sm" id="btnExportar">
              <i class="ph ph-file-csv"></i> Exportar
            </button>
          </div>
        </div>
        <div class="card-body p-2">
          <div class="table-responsive">
            <table class="table table-hover align-middle mb-0" id="tabelaAlunos">
              <thead class="table-light">
                <tr>
                  <th scope="col" style="width: 50px;">
                    <input type="checkbox" class="form-check-input" id="selecionarTodos">
                  </th>
                  <th scope="col">Aluno</th>
                  <th scope="col">Contato</th>
                  <th scope="col">Data de Matrícula</th>
                  <th scope="col">Status</th>
                  <th scope="col">Frequência</th>
                  <th scope="col" style="width: 100px;">Ações</th>
                </tr>
              </thead>
              <tbody>
                <!-- Aluno 1 -->
                <tr>
                  <td>
                    <input type="checkbox" class="form-check-input selecionarAluno">
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="ph ph-user fs-5 text-secondary"></i>
                      </div>
                      <div>
                        <div class="fw-semibold">Ana Carolina Silva</div>
                        <small class="text-muted">Matrícula: A001</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div><i class="ph ph-envelope me-1 text-muted"></i> ana.silva@email.com</div>
                    <small><i class="ph ph-phone me-1 text-muted"></i> (11) 98765-4321</small>
                  </td>
                  <td>15/01/2024</td>
                  <td>
                    <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span>85%</span>
                      <div class="progress flex-grow-1" style="height: 5px; width: 80px;">
                        <div class="progress-bar bg-success" style="width: 85%"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button class="btn btn-sm btn-outline-info" title="Ver detalhes" data-bs-toggle="modal" data-bs-target="#modalDetalhesAluno">
                        <i class="ph ph-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Remover da turma">
                        <i class="ph ph-user-minus"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <!-- Aluno 2 -->
                <tr>
                  <td>
                    <input type="checkbox" class="form-check-input selecionarAluno">
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="ph ph-user fs-5 text-secondary"></i>
                      </div>
                      <div>
                        <div class="fw-semibold">Bruno Mendes Rocha</div>
                        <small class="text-muted">Matrícula: A002</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div><i class="ph ph-envelope me-1 text-muted"></i> bruno.mendes@email.com</div>
                    <small><i class="ph ph-phone me-1 text-muted"></i> (11) 91234-5678</small>
                  </td>
                  <td>20/01/2024</td>
                  <td>
                    <span class="badge bg-success-subtle text-success-emphasis">Ativo</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span>92%</span>
                      <div class="progress flex-grow-1" style="height: 5px; width: 80px;">
                        <div class="progress-bar bg-success" style="width: 92%"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button class="btn btn-sm btn-outline-info" title="Ver detalhes">
                        <i class="ph ph-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Remover da turma">
                        <i class="ph ph-user-minus"></i>
                      </button>
                    </div>
                  </td>
                </tr>
                <!-- Aluno 3 -->
                <tr>
                  <td>
                    <input type="checkbox" class="form-check-input selecionarAluno">
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-3">
                      <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center" style="width: 40px; height: 40px;">
                        <i class="ph ph-user fs-5 text-secondary"></i>
                      </div>
                      <div>
                        <div class="fw-semibold">Carla Fernanda Lima</div>
                        <small class="text-muted">Matrícula: A003</small>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div><i class="ph ph-envelope me-1 text-muted"></i> carla.lima@email.com</div>
                    <small><i class="ph ph-phone me-1 text-muted"></i> (11) 99876-5432</small>
                  </td>
                  <td>10/02/2024</td>
                  <td>
                    <span class="badge bg-warning-subtle text-warning-emphasis">Inativo</span>
                  </td>
                  <td>
                    <div class="d-flex align-items-center gap-2">
                      <span>45%</span>
                      <div class="progress flex-grow-1" style="height: 5px; width: 80px;">
                        <div class="progress-bar bg-warning" style="width: 45%"></div>
                      </div>
                    </div>
                  </td>
                  <td>
                    <div class="btn-group" role="group">
                      <button class="btn btn-sm btn-outline-info" title="Ver detalhes">
                        <i class="ph ph-eye"></i>
                      </button>
                      <button class="btn btn-sm btn-outline-danger" title="Remover da turma">
                        <i class="ph ph-user-minus"></i>
                      </button>
                    </div>
                  </td>
                </tr>
              </tbody>
            </table>
          </div>
        </div>
        <div class="card-footer bg-body border-0 d-flex justify-content-between align-items-center">
          <div>
            <span class="text-muted small">Mostrando <span id="quantidadeSelecionados">0</span> de <?= $turma_capacidade_atual ?> alunos selecionados</span>
          </div>
          <div>
            <button class="btn btn-sm btn-outline-danger" id="btnRemoverSelecionados" disabled>
              <i class="ph ph-user-minus me-1"></i> Remover Selecionados
            </button>
          </div>
        </div>
      </div>
    </div>
  </main>

  <!-- Modal Adicionar Aluno -->
  <div class="modal fade" id="modalAdicionarAluno" tabindex="-1" aria-labelledby="modalAdicionarAlunoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered modal-lg">
      <div class="modal-content">
        <div class="modal-header border-0 pb-0">
          <h5 class="modal-title" id="modalAdicionarAlunoLabel">Adicionar Aluno à Turma</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="alert alert-info">
            <i class="ph ph-info me-2"></i>
            Vagas disponíveis: <strong><?= $turca_capacidade_maxima - $turma_capacidade_atual ?></strong> de <?= $turca_capacidade_maxima ?>
          </div>
          
          <div class="mb-3">
            <label for="buscarAluno" class="form-label">Buscar Aluno</label>
            <div class="input-group">
              <input type="text" class="form-control" id="buscarAluno" placeholder="Digite nome, CPF ou e-mail">
              <button class="btn btn-red" type="button">
                <i class="ph ph-magnifying-glass"></i> Buscar
              </button>
            </div>
          </div>

          <div class="mb-3">
            <label class="form-label">Ou cadastrar novo aluno</label>
            <a href="aluno_form.php" class="btn btn-outline-secondary w-100">
              <i class="ph ph-user-plus me-1"></i> Cadastrar Novo Aluno
            </a>
          </div>

          <hr>

          <div class="mb-3">
            <label for="selectAluno" class="form-label">Selecionar Aluno Existente</label>
            <select class="form-select select2" id="selectAluno">
              <option value="">Selecione um aluno...</option>
            </select>
          </div>

          <div class="mb-3">
            <label for="dataMatricula" class="form-label">Data de Matrícula</label>
            <input type="date" class="form-control" id="dataMatricula" value="<?= date('Y-m-d') ?>">
          </div>
        </div>
        <div class="modal-footer border-0 pt-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancelar</button>
          <button type="button" class="btn btn-red" id="confirmarAdicionar">
            <i class="ph ph-user-plus me-1"></i> Adicionar à Turma
          </button>
        </div>
      </div>
    </div>
  </div>

  <!-- Modal Detalhes do Aluno -->
  <div class="modal fade" id="modalDetalhesAluno" tabindex="-1" aria-labelledby="modalDetalhesAlunoLabel" aria-hidden="true">
    <div class="modal-dialog modal-dialog-centered">
      <div class="modal-content">
        <div class="modal-header border-0">
          <h5 class="modal-title" id="modalDetalhesAlunoLabel">Detalhes do Aluno</h5>
          <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Close"></button>
        </div>
        <div class="modal-body">
          <div class="text-center mb-3">
            <div class="bg-secondary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center mx-auto mb-3" style="width: 80px; height: 80px;">
              <i class="ph ph-user fs-1 text-secondary"></i>
            </div>
            <h4>Ana Carolina Silva</h4>
            <span class="badge bg-success">Ativo</span>
          </div>
          
          <hr>
          
          <div class="row g-3">
            <div class="col-12">
              <label class="text-muted small mb-1">E-mail</label>
              <p class="mb-0">ana.silva@email.com</p>
            </div>
            <div class="col-12">
              <label class="text-muted small mb-1">Telefone</label>
              <p class="mb-0">(11) 98765-4321</p>
            </div>
            <div class="col-12">
              <label class="text-muted small mb-1">Data de Nascimento</label>
              <p class="mb-0">15/03/1995</p>
            </div>
            <div class="col-12">
              <label class="text-muted small mb-1">Data de Matrícula</label>
              <p class="mb-0">15/01/2024</p>
            </div>
            <div class="col-12">
              <label class="text-muted small mb-1">CPF</label>
              <p class="mb-0">***.***.***-**</p>
            </div>
          </div>
        </div>
        <div class="modal-footer border-0">
          <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
          <button type="button" class="btn btn-red">Editar Dados</button>
        </div>
      </div>
    </div>
  </div>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>

  <script>
    $(document).ready(function() {
      // Inicializar Select2
      $('.select2').select2({
        theme: 'bootstrap-5',
        dropdownParent: $('#modalAdicionarAluno'),
        placeholder: 'Selecione um aluno...',
        allowClear: true
      });

      // Busca na tabela
      $('#campoBuscaAlunos').on('keyup', function() {
        const value = $(this).val().toLowerCase();
        $('#tabelaAlunos tbody tr').filter(function() {
          $(this).toggle($(this).text().toLowerCase().indexOf(value) > -1);
        });
      });

      // Selecionar todos os checkboxes
      $('#selecionarTodos').on('change', function() {
        const isChecked = $(this).prop('checked');
        $('.selecionarAluno').prop('checked', isChecked);
        atualizarContadorSelecionados();
      });

      // Atualizar contador quando checkbox individual for alterado
      $('.selecionarAluno').on('change', function() {
        atualizarContadorSelecionados();
        const total = $('.selecionarAluno').length;
        const selecionados = $('.selecionarAluno:checked').length;
        $('#selecionarTodos').prop('checked', total === selecionados && total > 0);
      });

      // Função para atualizar contador
      function atualizarContadorSelecionados() {
        const selecionados = $('.selecionarAluno:checked').length;
        $('#quantidadeSelecionados').text(selecionados);
        $('#btnRemoverSelecionados').prop('disabled', selecionados === 0);
      }

      // Remover selecionados
      $('#btnRemoverSelecionados').on('click', function() {
        const selecionados = $('.selecionarAluno:checked').length;
        if (selecionados === 0) return;

        Swal.fire({
          title: 'Confirmar remoção',
          text: `Deseja realmente remover ${selecionados} aluno(s) desta turma?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sim, remover',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            // Simular remoção
            $('.selecionarAluno:checked').each(function() {
              $(this).closest('tr').remove();
            });
            Swal.fire('Removido!', 'Aluno(s) removido(s) com sucesso.', 'success');
            atualizarContadorSelecionados();
          }
        });
      });

      // Remover aluno individual
      $('.btn-outline-danger').on('click', function(e) {
        e.preventDefault();
        const row = $(this).closest('tr');
        const nomeAluno = row.find('.fw-semibold').text();

        Swal.fire({
          title: 'Remover aluno',
          text: `Deseja remover ${nomeAluno} desta turma?`,
          icon: 'warning',
          showCancelButton: true,
          confirmButtonColor: '#d33',
          cancelButtonColor: '#6c757d',
          confirmButtonText: 'Sim, remover',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            row.remove();
            Swal.fire('Removido!', 'Aluno removido da turma.', 'success');
          }
        });
      });

      // Exportar CSV
      $('#btnExportar').on('click', function() {
        const dados = [];
        $('#tabelaAlunos tbody tr').each(function() {
          const nome = $(this).find('.fw-semibold').text();
          const email = $(this).find('td:nth-child(3) div:first-child').text().replace('✉️ ', '');
          const telefone = $(this).find('td:nth-child(3) small').text().replace('📞 ', '');
          const dataMatricula = $(this).find('td:nth-child(4)').text();
          const status = $(this).find('.badge').text();
          dados.push([nome, email, telefone, dataMatricula, status]);
        });

        const csvContent = "data:text/csv;charset=utf-8,Nome,Email,Telefone,Data Matrícula,Status\n" +
          dados.map(row => row.join(",")).join("\n");

        const encodedUri = encodeURI(csvContent);
        const link = document.createElement("a");
        link.setAttribute("href", encodedUri);
        link.setAttribute("download", "alunos_turma.csv");
        document.body.appendChild(link);
        link.click();
        document.body.removeChild(link);

        Swal.fire('Sucesso!', 'Arquivo exportado com sucesso!', 'success');
      });

      // Adicionar aluno
      $('#confirmarAdicionar').on('click', function() {
        const alunoSelecionado = $('#selectAluno').val();
        const dataMatricula = $('#dataMatricula').val();

        if (!alunoSelecionado) {
          Swal.fire('Atenção', 'Selecione um aluno para adicionar à turma', 'warning');
          return;
        }

        if (!dataMatricula) {
          Swal.fire('Atenção', 'Informe a data de matrícula', 'warning');
          return;
        }

        Swal.fire({
          title: 'Confirmar adição',
          text: 'Deseja adicionar este aluno à turma?',
          icon: 'question',
          showCancelButton: true,
          confirmButtonText: 'Sim, adicionar',
          cancelButtonText: 'Cancelar'
        }).then((result) => {
          if (result.isConfirmed) {
            Swal.fire('Sucesso!', 'Aluno adicionado à turma com sucesso!', 'success');
            $('#modalAdicionarAluno').modal('hide');
            // Recarregar página para mostrar o novo aluno
            setTimeout(() => location.reload(), 1500);
          }
        });
      });
    });
  </script>
</body>

</html>