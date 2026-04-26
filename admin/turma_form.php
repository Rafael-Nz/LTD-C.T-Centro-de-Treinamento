<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $id ? 'Editar Turma' : 'Cadastrar Turma' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/form.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? "Editar Turma" : "Cadastrar Turma" ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formTurma" action="" method="POST">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

            <div class="row gy-4">
              <!-- Informações Básicas da Turma -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Básicas da Turma</h3>

                <div class="row g-3 mb-3">
                  <!-- Nome da Turma -->
                  <div class="col-md-6">
                    <label for="nome_turma" class="form-label">Nome da Turma:</label>
                    <input type="text" class="form-control" id="nome_turma" name="nome_turma" placeholder="Ex: CrossFit Iniciantes, Musculação Avançada, Yoga Relax" required>
                  </div>
                  <!-- Turno -->
                  <div class="col-md-6">
                      <label for="turno" class="form-label">Turno:</label>
                      <select class="form-select" id="turno" name="turno" required>
                          <option value="" selected disabled>Selecione um turno</option>
                          <option value="manha">Manhã</option>
                          <option value="tarde">Tarde</option>
                          <option value="noite">Noite</option>
                      </select>
                  </div>
                </div>

                <!-- Capacidade da Turma -->
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="capacidade_minima" class="form-label">Capacidade Mínima:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" id="capacidade_minima" name="capacidade_minima" 
                        min="1" max="100" value="1" required>
                      <span class="input-group-text"><i class="ph ph-user"></i></span>
                    </div>
                    <div class="form-text">Número mínimo de alunos para a turma funcionar</div>
                  </div>
                  <div class="col-md-6">
                    <label for="capacidade_maxima" class="form-label">Capacidade Máxima:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" 
                        min="1" max="100" value="15" required>
                      <span class="input-group-text"><i class="ph ph-user"></i></span>
                    </div>
                    <div class="form-text">Número máximo de alunos permitidos na turma</div>
                  </div>
                </div>

                <!-- Descrição -->
                <div class="mb-3">
                  <label for="descricao" class="form-label">Descrição:</label>
                  <textarea class="form-control" id="descricao" name="descricao" rows="3"
                    placeholder="Descreva os objetivos, foco e características desta turma"
                    style="resize: none;"></textarea>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="turmas.php" class="btn btn-red">Voltar</a>
              <button type="submit" class="btn btn-red">
                <?= $id ? "Salvar Alterações" : "Cadastrar Turma" ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <!-- Scripts -->
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer
    src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>

  <!-- Script de validação para turmas -->
  <script>
    document.addEventListener('DOMContentLoaded', function () {
      const form = document.getElementById('formTurma');
      const capacidadeMinInput = document.getElementById('capacidade_minima');
      const capacidadeMaxInput = document.getElementById('capacidade_maxima');

      // Validação da capacidade
      function validarCapacidade() {
        const capacidadeMin = parseInt(capacidadeMinInput.value) || 0;
        const capacidadeMax = parseInt(capacidadeMaxInput.value) || 0;

        if (capacidadeMin > capacidadeMax) {
          capacidadeMinInput.setCustomValidity('A capacidade mínima não pode ser maior que a máxima');
          capacidadeMinInput.classList.add('is-invalid');
          capacidadeMaxInput.classList.add('is-invalid');
          return false;
        } else if (capacidadeMin < 1) {
          capacidadeMinInput.setCustomValidity('A capacidade mínima deve ser pelo menos 1');
          capacidadeMinInput.classList.add('is-invalid');
          return false;
        } else if (capacidadeMax < capacidadeMin) {
          capacidadeMaxInput.setCustomValidity('A capacidade máxima não pode ser menor que a mínima');
          capacidadeMaxInput.classList.add('is-invalid');
          return false;
        } else {
          capacidadeMinInput.setCustomValidity('');
          capacidadeMaxInput.setCustomValidity('');
          capacidadeMinInput.classList.remove('is-invalid');
          capacidadeMaxInput.classList.remove('is-invalid');
          return true;
        }
      }

      // Event listeners para validação em tempo real
      capacidadeMinInput.addEventListener('input', function() {
        if (parseInt(this.value) > parseInt(capacidadeMaxInput.value)) {
          capacidadeMaxInput.value = this.value;
        }
        validarCapacidade();
      });

      capacidadeMaxInput.addEventListener('input', function() {
        if (parseInt(this.value) < parseInt(capacidadeMinInput.value)) {
          capacidadeMinInput.value = this.value;
        }
        validarCapacidade();
      });

      // Validação do formulário
      form.addEventListener('submit', function (e) {
        e.preventDefault();

        // Valida o nome
        const nome = document.getElementById('nome_treino').value.trim();
        if (nome.length < 3) {
          Swal.fire('Atenção', 'O nome da turma deve ter pelo menos 3 caracteres', 'warning');
          return;
        }

        // Valida a capacidade
        if (!validarCapacidade()) {
          Swal.fire('Atenção', 'Por favor, ajuste os valores de capacidade', 'warning');
          return;
        }

        // Valida o turno
        const turno = document.getElementById('turno').value;
        if (!turno) {
          Swal.fire('Atenção', 'Por favor, selecione um turno', 'warning');
          return;
        }

        // Se tudo estiver válido, pode enviar
        if (form.checkValidity()) {
          // Simulação de sucesso
          Swal.fire({
            title: 'Sucesso!',
            text: 'Turma <?= $id ? "atualizada" : "cadastrada" ?> com sucesso!',
            icon: 'success',
            confirmButtonText: 'OK'
          }).then(() => {
            // Redirecionar após sucesso
            window.location.href = 'turmas.php';
          });
        } else {
          Swal.fire('Atenção', 'Por favor, preencha todos os campos obrigatórios corretamente.', 'warning');
        }
      });
    });
  </script>
</body>
</html>