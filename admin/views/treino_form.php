<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $id ? 'Editar Treino' : 'Cadastrar Treino' ?></title>
  <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/form.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css"/>
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css"/>
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
  <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet"/>
  <link href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? 'Editar Treino' : 'Cadastrar Treino' ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">

          <form id="formTreino" method="POST" data-id="<?= $id ?>">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

            <div class="row gy-4">
              <div class="col-12">
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="nome" class="form-label">Nome do treino</label>
                    <input type="text" name="nome" id="nome" class="form-control" maxlength="100" placeholder="Ex: WOD Iniciantes, Funcional Avancado" required>
                  </div>

                  <div class="col-md-6">
                    <label for="modalidade_id" class="form-label">Modalidade</label>
                    <select name="modalidade_id" id="modalidade_id" class="form-select" required>
                      <option value="">Selecione a modalidade...</option>
                    </select>
                  </div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-12">
                    <label for="descricao" class="form-label">Descricao</label>
                    <textarea name="descricao" id="descricao" class="form-control" rows="4" placeholder="Descreva rapidamente a atividade, objetivos ou observacoes."></textarea>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="/ctt/admin/treinos" class="btn btn-red">Voltar</a>
              <button type="submit" class="btn btn-red">
                <?= $id ? 'Salvar Alteracoes' : 'Cadastrar Treino' ?>
              </button>
            </div>
          </form>

          <div class="alert alert-info d-flex align-items-start gap-2 mt-3" role="alert">
            <i class="ph ph-info mt-1"></i>
            <div>
              Este formulario cadastra apenas o <strong>treino-base</strong>. O agendamento deste treino, com horario, local e instrutor, deve ser feito no gerenciamento da turma.
            </div>
          </div>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
  <script src="/ctt/plugins/select2/js/select2.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/form/treino_form.js"></script>
</body>
</html>
