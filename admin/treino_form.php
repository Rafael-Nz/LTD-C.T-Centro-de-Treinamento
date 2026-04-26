<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | <?= $id ? 'Editar Treino' : 'Cadastrar Treino' ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="../public/css/admin-styles.css">
    <link rel="stylesheet" href="../public/css/form.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
  </head>
  <body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>
    
    <main class="flex-fill d-flex" id="mainContent">
      <div class="container-lg p-4 flex-column flex-fill">
        <h1 class="h4 mb-4"><?= $id ? "Editar Treino" : "Cadastrar Treino" ?></h1>
        <div class="card shadow-sm d-flex flex-fill">
          <div class="card-body">
            <form id="formTreino" action="/api/treinos/save.php" method="POST">
              <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
              
              <div class="row gy-4">
                <!-- Informações Básicas -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Básicas</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="nomeTreino" class="form-label">Nome do Treino:</label>
                      <input type="text" name="nomeTreino" id="nomeTreino" class="form-control" placeholder="Digite o nome do treino" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="instrutor" class="form-label">Instrutor:</label>
                      <select name="instrutor" id="instrutor" class="form-select" required>
                        <option value="">Selecione um instrutor</option>
                      </select>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="descricao" class="form-label">Descrição:</label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="2" placeholder="Descreva o treino (opcional)" style="resize: none;"></textarea>
                  </div>
                </div>

                <!-- Horário e Local -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Horário e Local</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="inicioTreino" class="form-label">Data/Horário de Início:</label>
                      <div class="input-group" id="inicioTreinoPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <input type="text" name="inicioTreino" id="inicioTreino" class="form-control" data-td-target="#inicioTreino" required />
                        <span class="input-group-text" data-td-toggle="datetimepicker" data-td-target="#inicioTreino">
                          <i class="bi bi-calendar-event"></i>
                        </span>
                      </div>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="fimTreino" class="form-label">Data/Horário de Término:</label>
                      <div class="input-group" id="fimTreinoPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                        <input type="text" name="fimTreino" id="fimTreino" class="form-control" data-td-target="#fimTreino" required />
                        <span class="input-group-text" data-td-toggle="datetimepicker" data-td-target="#fimTreino">
                          <i class="bi bi-calendar-event"></i>
                        </span>
                      </div>
                    </div>
                  </div>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="sala" class="form-label">Local do Treino:</label>
                      <select name="sala" id="sala" class="form-select" required>
                        <option value="">Selecione um local</option>
                      </select>
                    </div>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="treinos.php" class="btn btn-red">Voltar</a>
                <button type="submit" class="btn btn-red">
                  <?= $id ? "Salvar Alterações" : "Cadastrar Treino" ?>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
    <script defer src="../public/js/admin/sidebar.js"></script>
    
    <script>
    document.addEventListener('DOMContentLoaded', function() {
        // Inicializar datetime pickers
        const inicioPicker = new tempusDominus.TempusDominus(document.getElementById('inicioTreinoPicker'), {
            localization: {
                locale: 'pt-br',
                format: 'dd/MM/yyyy HH:mm'
            }
        });
        
        const fimPicker = new tempusDominus.TempusDominus(document.getElementById('fimTreinoPicker'), {
            localization: {
                locale: 'pt-br',
                format: 'dd/MM/yyyy HH:mm'
            }
        });
        
        // Validação do formulário
        document.getElementById('formTreino').addEventListener('submit', function(e) {
            let valid = true;
            
            // Limpar erros anteriores
            document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
            document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
            
            // Validação de datas
            const inicio = document.getElementById('inicioTreino').value;
            const fim = document.getElementById('fimTreino').value;
            
            if (inicio && fim) {
                const inicioDate = new Date(inicio);
                const fimDate = new Date(fim);
                
                if (inicioDate >= fimDate) {
                    showError('fimTreino', 'A data de término deve ser posterior à data de início');
                    valid = false;
                }
            }
            
            // Validação de capacidade
            const capacidade = parseInt(document.getElementById('capacidade').value);
            if (capacidade < 1) {
                showError('capacidade', 'A capacidade deve ser no mínimo 1');
                valid = false;
            }
            
            if (!valid) {
                e.preventDefault();
                Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
            }
        });
        
        function showError(fieldId, message) {
            const field = document.getElementById(fieldId);
            field.classList.add('is-invalid');
            
            const errorDiv = document.createElement('div');
            errorDiv.className = 'invalid-feedback';
            errorDiv.textContent = message;
            field.parentNode.appendChild(errorDiv);
        }
    });
    </script>
  </body>
</html>