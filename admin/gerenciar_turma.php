<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Gerenciar Alunos da Turma</title>
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
      <?php
      // Simulando dados da turma (em um sistema real, isso viria do banco de dados)
      $turma_id = isset($_GET['id']) ? intval($_GET['id']) : 0;
      $turmas = [
        1 => ['nome' => 'CrossFit Iniciantes Manhã', 'capacidade' => 15, 'alunos_ativos' => 8, 'status' => 'Aberta'],
        2 => ['nome' => 'HIIT Avançado Noite', 'capacidade' => 12, 'alunos_ativos' => 12, 'status' => 'Fechada'],
        3 => ['nome' => 'Funcional Intermediário Tarde', 'capacidade' => 15, 'alunos_ativos' => 10, 'status' => 'Aberta'],
        4 => ['nome' => 'Musculação Iniciante', 'capacidade' => 10, 'alunos_ativos' => 6, 'status' => 'Aberta']
      ];
      
      $turma = isset($turmas[$turma_id]) ? $turmas[$turma_id] : null;
      
      if (!$turma) {
        echo '<div class="alert alert-danger">Turma não encontrada!</div>';
        exit;
      }
      ?>
      
      <div class="d-flex justify-content-between align-items-center mb-4">
        <div>
          <h1 class="h4 mb-1">Gerenciar Alunos</h1>
          <p class="text-muted mb-0">Turma: <strong><?= htmlspecialchars($turma['nome']) ?></strong></p>
        </div>
        <a href="turmas.php" class="btn btn-outline-secondary">
          <i class="ph ph-arrow-left me-2"></i>Voltar para Turmas
        </a>
      </div>

      <!-- Card de Informações da Turma -->
      <div class="card border-0 shadow-sm mb-4">
        <div class="card-body">
          <div class="row">
            <div class="col-md-3">
              <div class="d-flex align-items-center">
                <div class="bg-primary bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-users-three text-primary fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0">Alunos Matriculados</p>
                  <h3 class="mb-0"><?= $turma['alunos_ativos'] ?>/<?= $turma['capacidade'] ?></h3>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex align-items-center">
                <div class="bg-info bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-users text-info fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0">Vagas Disponíveis</p>
                  <h3 class="mb-0"><?= $turma['capacidade'] - $turma['alunos_ativos'] ?></h3>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex align-items-center">
                <div class="bg-success bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-check-circle text-success fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0">Status da Turma</p>
                  <span class="badge <?= $turma['status'] == 'Aberta' ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis' ?> fs-6">
                    <?= $turma['status'] ?>
                  </span>
                </div>
              </div>
            </div>
            <div class="col-md-3">
              <div class="d-flex align-items-center">
                <div class="bg-warning bg-opacity-10 p-3 rounded-circle me-3">
                  <i class="ph ph-calendar-blank text-warning fs-4"></i>
                </div>
                <div>
                  <p class="text-muted mb-0">Horário</p>
                  <h5 class="mb-0">Seg, Qua, Sex - 08:00</h5>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>

      <div class="row">
        <!-- Lista de Alunos Matriculados -->
        <div class="col-lg-8 mb-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-body border-0 d-flex justify-content-between align-items-center">
              <h5 class="mb-0">Alunos Matriculados</h5>
              <span class="badge bg-primary"><?= $turma['alunos_ativos'] ?> alunos</span>
            </div>
            <div class="card-body">
              <div class="table-responsive">
                <table class="table table-hover align-middle">
                  <thead>
                    <tr>
                      <th scope="col" class="text-start">Aluno</th>
                      <th scope="col" class="text-center">Matrícula</th>
                      <th scope="col" class="text-center">Status</th>
                      <th scope="col" class="text-center">Data de Entrada</th>
                      <th scope="col" class="text-center">Ações</th>
                    </tr>
                  </thead>
                  <tbody>
                    <?php
                    // Dados simulados dos alunos da turma
                    $alunos_turma = [
                      ['id' => 101, 'nome' => 'João Silva', 'matricula' => 'MAT001', 'status' => 'Ativo', 'data_entrada' => '15/03/2024'],
                      ['id' => 102, 'nome' => 'Maria Santos', 'matricula' => 'MAT002', 'status' => 'Ativo', 'data_entrada' => '20/03/2024'],
                      ['id' => 103, 'nome' => 'Carlos Oliveira', 'matricula' => 'MAT003', 'status' => 'Ativo', 'data_entrada' => '05/04/2024'],
                      ['id' => 104, 'nome' => 'Ana Paula Costa', 'matricula' => 'MAT004', 'status' => 'Ativo', 'data_entrada' => '10/04/2024'],
                      ['id' => 105, 'nome' => 'Pedro Henrique Lima', 'matricula' => 'MAT005', 'status' => 'Ativo', 'data_entrada' => '12/04/2024'],
                      ['id' => 106, 'nome' => 'Fernanda Souza', 'matricula' => 'MAT006', 'status' => 'Ativo', 'data_entrada' => '18/04/2024'],
                      ['id' => 107, 'nome' => 'Ricardo Almeida', 'matricula' => 'MAT007', 'status' => 'Ativo', 'data_entrada' => '22/04/2024'],
                      ['id' => 108, 'nome' => 'Juliana Pereira', 'matricula' => 'MAT008', 'status' => 'Ativo', 'data_entrada' => '25/04/2024']
                    ];
                    
                    // Ajustar número de alunos de acordo com a turma
                    $alunos_turma = array_slice($alunos_turma, 0, $turma['alunos_ativos']);
                    
                    foreach ($alunos_turma as $aluno) {
                    ?>
                    <tr>
                      <td class="text-start">
                        <div class="d-flex align-items-center">
                          <div class="bg-primary bg-opacity-10 rounded-circle d-flex align-items-center justify-content-center me-3" style="width: 40px; height: 40px;">
                            <i class="ph ph-user text-primary"></i>
                          </div>
                          <div>
                            <p class="mb-0 fw-medium"><?= htmlspecialchars($aluno['nome']) ?></p>
                            <small class="text-muted">aluno@email.com</small>
                          </div>
                        </div>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-secondary bg-opacity-10 text-secondary"><?= $aluno['matricula'] ?></span>
                      </td>
                      <td class="text-center">
                        <span class="badge bg-success-subtle text-success-emphasis"><?= $aluno['status'] ?></span>
                      </td>
                      <td class="text-center"><?= $aluno['data_entrada'] ?></td>
                      <td class="text-center">
                        <button class="btn btn-sm btn-outline-danger btn-remover-aluno" 
                                data-aluno-id="<?= $aluno['id'] ?>"
                                data-aluno-nome="<?= htmlspecialchars($aluno['nome']) ?>"
                                data-turma-id="<?= $turma_id ?>"
                                title="Remover da Turma">
                          <i class="ph ph-user-minus"></i>
                        </button>
                      </td>
                    </tr>
                    <?php } ?>
                  </tbody>
                </table>
              </div>
              
              <?php if (empty($alunos_turma)) { ?>
              <div class="text-center py-5">
                <div class="mb-3">
                  <i class="ph ph-users-three fs-1 text-muted"></i>
                </div>
                <h5 class="text-muted">Nenhum aluno matriculado</h5>
                <p class="text-muted">Adicione alunos a esta turma usando o formulário ao lado.</p>
              </div>
              <?php } ?>
            </div>
          </div>
        </div>

        <!-- Painel para Adicionar Alunos -->
        <div class="col-lg-4 mb-4">
          <div class="card border-0 shadow-sm h-100">
            <div class="card-header bg-body border-0">
              <h5 class="mb-0">Adicionar Alunos</h5>
            </div>
            <div class="card-body">
              <?php if ($turma['status'] == 'Fechada') { ?>
              <div class="alert alert-warning">
                <i class="ph ph-warning-circle me-2"></i>
                Esta turma está fechada. É necessário reabri-la para adicionar novos alunos.
              </div>
              <?php } elseif ($turma['alunos_ativos'] >= $turma['capacidade']) { ?>
              <div class="alert alert-warning">
                <i class="ph ph-warning-circle me-2"></i>
                Turma com capacidade máxima atingida. Não é possível adicionar mais alunos.
              </div>
              <?php } ?>
              
              <form id="formAdicionarAluno" <?= ($turma['status'] == 'Fechada' || $turma['alunos_ativos'] >= $turma['capacidade']) ? 'onsubmit="return false;"' : '' ?>>
                <input type="hidden" name="turma_id" value="<?= $turma_id ?>">
                
                <div class="mb-3">
                  <label for="selectAluno" class="form-label">Selecionar Aluno</label>
                  <select class="form-select" id="selectAluno" name="aluno_id" <?= ($turma['status'] == 'Fechada' || $turma['alunos_ativos'] >= $turma['capacidade']) ? 'disabled' : '' ?>>
                    <option value="">Selecione um aluno...</option>
                    <option value="201">Lucas Mendes - MAT009</option>
                    <option value="202">Patrícia Rocha - MAT010</option>
                    <option value="203">Roberto Santos - MAT011</option>
                    <option value="204">Camila Oliveira - MAT012</option>
                    <option value="205">Marcos Vinícius - MAT013</option>
                  </select>
                  <div class="form-text">
                    <i class="ph ph-info me-1"></i> Apenas alunos ativos no sistema aparecem na lista.
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="dataMatricula" class="form-label">Data de Matrícula</label>
                  <input type="date" class="form-control" id="dataMatricula" name="data_matricula" 
                         value="<?= date('Y-m-d') ?>" 
                         <?= ($turma['status'] == 'Fechada' || $turma['alunos_ativos'] >= $turma['capacidade']) ? 'disabled' : '' ?>>
                </div>
                
                <div class="d-grid">
                  <button type="submit" class="btn btn-red color" 
                          <?= ($turma['status'] == 'Fechada' || $turma['alunos_ativos'] >= $turma['capacidade']) ? 'disabled' : '' ?>>
                    <i class="ph ph-user-plus me-2"></i>Adicionar à Turma
                  </button>
                </div>
              </form>
              
              <hr class="my-4">
              
              <div class="d-grid gap-2">
                <a href="turma_form.php?id=<?= $turma_id ?>" class="btn btn-outline-info">
                  <i class="ph ph-pencil-simple me-2"></i>Editar Informações da Turma
                </a>
                
                <button class="btn btn-outline-<?= $turma['status'] == 'Aberta' ? 'danger' : 'success' ?> btn-toggle-turma-status"
                        data-turma-id="<?= $turma_id ?>"
                        data-turma-nome="<?= htmlspecialchars($turma['nome']) ?>"
                        data-turma-status="<?= $turma['status'] ?>">
                  <i class="ph <?= $turma['status'] == 'Aberta' ? 'ph-x-circle' : 'ph-arrow-counter-clockwise' ?> me-2"></i>
                  <?= $turma['status'] == 'Aberta' ? 'Fechar Turma' : 'Reabrir Turma' ?>
                </button>
              </div>
              
              <hr class="my-4">
              
              <div class="alert alert-info">
                <div class="d-flex">
                  <i class="ph ph-info text-info me-2"></i>
                  <div>
                    <p class="mb-1"><strong>Dicas de Gerenciamento:</strong></p>
                    <ul class="mb-0 ps-3" style="font-size: 0.875rem;">
                      <li>Verifique a frequência dos alunos regularmente</li>
                      <li>Monitore o limite de capacidade da turma</li>
                      <li>Comunique mudanças de horário com antecedência</li>
                    </ul>
                  </div>
                </div>
              </div>
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  
  <script>
  $(document).ready(function() {
    // Inicializar Select2
    $('#selectAluno').select2({
      theme: 'bootstrap-5',
      placeholder: 'Selecione um aluno...',
      width: '100%'
    });
    
    // Submissão do formulário para adicionar aluno
    $('#formAdicionarAluno').on('submit', function(e) {
      e.preventDefault();
      
      const turmaId = $('input[name="turma_id"]').val();
      const alunoId = $('#selectAluno').val();
      const alunoNome = $('#selectAluno option:selected').text();
      const dataMatricula = $('#dataMatricula').val();
      
      if (!alunoId) {
        Swal.fire({
          icon: 'warning',
          title: 'Seleção Necessária',
          text: 'Por favor, selecione um aluno para adicionar à turma.',
          confirmButtonColor: '#d33'
        });
        return;
      }
      
      Swal.fire({
        title: 'Confirmar Adição',
        html: `Deseja adicionar <strong>${alunoNome}</strong> à turma?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: 'Sim, adicionar',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#198754',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          // Simulação de requisição AJAX
          // Em um sistema real, aqui seria uma chamada AJAX para o backend
          setTimeout(() => {
            Swal.fire({
              icon: 'success',
              title: 'Aluno Adicionado!',
              text: `${alunoNome} foi adicionado à turma com sucesso.`,
              confirmButtonColor: '#198754'
            }).then(() => {
              // Recarregar a página para atualizar a lista
              location.reload();
            });
          }, 1000);
        }
      });
    });
    
    // Remover aluno da turma
    $('.btn-remover-aluno').on('click', function() {
      const alunoId = $(this).data('aluno-id');
      const alunoNome = $(this).data('aluno-nome');
      const turmaId = $(this).data('turma-id');
      
      Swal.fire({
        title: 'Confirmar Remoção',
        html: `Deseja remover <strong>${alunoNome}</strong> desta turma?`,
        icon: 'warning',
        showCancelButton: true,
        confirmButtonText: 'Sim, remover',
        cancelButtonText: 'Cancelar',
        confirmButtonColor: '#d33',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          // Simulação de requisição AJAX
          // Em um sistema real, aqui seria uma chamada AJAX para o backend
          setTimeout(() => {
            Swal.fire({
              icon: 'success',
              title: 'Aluno Removido!',
              text: `${alunoNome} foi removido da turma com sucesso.`,
              confirmButtonColor: '#198754'
            }).then(() => {
              // Recarregar a página para atualizar a lista
              location.reload();
            });
          }, 1000);
        }
      });
    });
    
    // Alterar status da turma (Abrir/Fechar)
    $('.btn-toggle-turma-status').on('click', function() {
      const turmaId = $(this).data('turma-id');
      const turmaNome = $(this).data('turma-nome');
      const turmaStatus = $(this).data('turma-status');
      const novoStatus = turmaStatus === 'Aberta' ? 'Fechada' : 'Aberta';
      const acao = turmaStatus === 'Aberta' ? 'fechar' : 'reabrir';
      
      Swal.fire({
        title: 'Confirmar Alteração',
        html: `Deseja <strong>${acao}</strong> a turma <strong>${turmaNome}</strong>?`,
        icon: 'question',
        showCancelButton: true,
        confirmButtonText: `Sim, ${acao}`,
        cancelButtonText: 'Cancelar',
        confirmButtonColor: turmaStatus === 'Aberta' ? '#d33' : '#198754',
        cancelButtonColor: '#6c757d'
      }).then((result) => {
        if (result.isConfirmed) {
          // Simulação de requisição AJAX
          // Em um sistema real, aqui seria uma chamada AJAX para o backend
          setTimeout(() => {
            Swal.fire({
              icon: 'success',
              title: 'Status Alterado!',
              text: `A turma ${turmaNome} foi ${acao}da com sucesso.`,
              confirmButtonColor: '#198754'
            }).then(() => {
              // Redirecionar para a página de turmas
              window.location.href = 'turmas.php';
            });
          }, 1000);
        }
      });
    });
    
    // Desabilitar formulário se turma estiver fechada ou cheia
    <?php if ($turma['status'] == 'Fechada' || $turma['alunos_ativos'] >= $turma['capacidade']) { ?>
    $('#formAdicionarAluno input, #formAdicionarAluno select, #formAdicionarAluno button').prop('disabled', true);
    <?php } ?>
  });
  </script>
</body>
</html>