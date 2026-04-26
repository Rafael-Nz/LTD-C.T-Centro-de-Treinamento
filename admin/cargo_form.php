<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pétala Floricultura e Cestas | <?php echo isset($_GET['id']) ? 'Editar Cargo' : 'Novo Cargo'; ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/form.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
  <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css"
    rel="stylesheet" />
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
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 flex-column flex-fill">
      <h1 class="h4 mb-4"><?php echo isset($_GET['id']) ? 'Editar Cargo' : 'Novo Cargo'; ?></h1>
      <div class="card border-0 shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formCargo" action="../api/cargos/" method="POST">
            <?php if (isset($_GET['id'])): ?>
              <input type="hidden" name="id" id="cargoId" value="<?php echo htmlspecialchars($_GET['id']); ?>">
            <?php endif; ?>

            <div class="row gy-4">
              <div class="col-12 col-lg-6">
                <div class="mb-4 h-100">
                  <h3 class="h6 section-title border-bottom border-1 pb-1 mb-3">Informações do Cargo</h3>

                  <div class="mb-3">
                    <label for="nomeCargo" class="form-label">Nome do Cargo</label>
                    <input type="text" class="form-control" id="nomeCargo" name="nome"
                      placeholder="Digite o nome do cargo" required>
                  </div>

                  <div class="mb-3">
                    <label for="descricaoCargo" class="form-label">Descrição</label>
                    <textarea class="form-control" id="descricaoCargo" name="descricao" rows="3"
                      placeholder="Digite a descrição do cargo"
                      style="resize: none;"></textarea>
                  </div>
                </div>
              </div>

              <div class="col-12 col-lg-6">
                <div class="mb-4 h-100">
                  <h3 class="h6 section-title border-bottom border-1 pb-1 mb-3">Detalhes Financeiros e Status</h3>

                  <div class="mb-3">
                    <label for="salarioBase" class="form-label">Salário Base</label>
                    <input type="number" step="0.50" min="0" value="0.00" class="form-control" id="salarioBase" name="salario_base">
                  </div>

                  <div class="mb-3">
                    <label for="ativoCargo" class="form-label">Status</label>
                    <select class="form-select" id="ativoCargo" name="ativo">
                      <option value="1" selected>Ativo</option>
                      <option value="0">Inativo</option>
                    </select>
                  </div>
                </div>
              </div>

              <div class="col-12">
                <div class="mb-4">
                  <h3 class="h6 section-title border-bottom border-1 pb-1 mb-3">Perfil de Permissão do Cargo</h3>
                  <p class="text-muted small mb-3">
                    <i class="fas fa-info-circle me-1"></i>
                    Este perfil será <strong>automaticamente aplicado</strong> a todos os funcionários deste cargo.
                    <br>
                  </p>

                  <div class="mb-3">
                    <label for="perfilCargo" class="form-label">Selecione o Perfil</label>
                    <select class="form-select" id="perfilCargo" name="perfil_cargo" style="width: 100%;">
                      <option value="">Nenhum perfil</option>
                      <!-- Perfis seriam carregados via JavaScript -->
                    </select>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <button type="button" class="btn btn-red color" onclick="voltar('cargos')">Voltar</button>
              <button type="submit" class="btn btn-red color">
                <?php echo isset($_GET['id']) ? 'Salvar Alterações' : 'Cadastrar'; ?>
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
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
  <script src="../public/js/admin/cargo_form.js" defer></script>

</body>
</html>