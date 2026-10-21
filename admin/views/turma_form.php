<?php
$acao = $_GET['acao'] ?? 'novo';
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;

$isEdit = ($acao === 'editar' && $id);
$pageTitle = $isEdit ? 'Editar Turma' : 'Cadastrar Turma';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $pageTitle ?></title>
  <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/form.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css" />
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
  <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
  <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
  <link href="/ctt/plugins/select2/css/select2.min.css" rel="stylesheet" />
  <link href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css" rel="stylesheet" />
  <link rel="stylesheet" href="/ctt/plugins/tempus-dominus/css/tempus-dominus.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? "Editar Turma" : "Cadastrar Turma" ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formTurma" action="" method="POST" data-id="<?= $id ?>">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

            <div class="row gy-4">
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informacoes Basicas da Turma</h3>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="nome" class="form-label">Nome da Turma:</label>
                    <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: CrossFit Iniciantes, Funcional 06h, Yoga Relax" required>
                  </div>

                  <div class="col-md-6">
                    <label for="instrutor_id" class="form-label">Instrutor: (Opcional)</label>
                    <select class="form-select" id="instrutor_id" name="instrutor_id">
                      <option value="" selected>Selecione um instrutor...</option>
                    </select>
                  </div>

                  <div class="col-md-6"></div>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="capacidade_minima" class="form-label">Capacidade Minima:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" id="capacidade_minima" name="capacidade_minima" min="1" max="100" value="1" required>
                      <span class="input-group-text"><i class="ph ph-user"></i></span>
                    </div>
                    <div class="form-text">Numero minimo de alunos para a turma funcionar</div>
                  </div>

                  <div class="col-md-6">
                    <label for="capacidade_maxima" class="form-label">Capacidade Maxima:</label>
                    <div class="input-group">
                      <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" min="1" max="100" value="15" required>
                      <span class="input-group-text"><i class="ph ph-user"></i></span>
                    </div>
                    <div class="form-text">Numero maximo de alunos permitidos na turma</div>
                  </div>
                </div>

                <div class="row g-3">
                  <div class="col-12">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                      <div>
                        <label class="form-label mb-0">Horarios da Turma</label>
                      </div>
                      <button type="button" class="btn btn-outline-secondary btn-sm" id="addHorarioBtn">
                        <i class="bi bi-plus-lg"></i> Adicionar horario
                      </button>
                    </div>

                    <div id="horariosContainer" class="d-flex flex-column gap-2"></div>

                    <template id="horarioRowTemplate">
                      <div class="border rounded p-3 mb-2 horario-row">
                        <div class="row g-2 align-items-end">
                          <div class="col-md-4">
                            <label class="form-label small fw-bold">Dia da Semana</label>
                            <select class="form-select horario-dia">
                              <option value="">Selecione...</option>
                              <option value="segunda">Segunda-feira</option>
                              <option value="terca">Terca-feira</option>
                              <option value="quarta">Quarta-feira</option>
                              <option value="quinta">Quinta-feira</option>
                              <option value="sexta">Sexta-feira</option>
                              <option value="sabado">Sabado</option>
                              <option value="domingo">Domingo</option>
                            </select>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">Horario Inicio</label>
                            <div class="input-group horario-picker horario-inicio-picker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                              <input type="text" class="form-control horario-inicio" data-td-target="input" readonly/>
                              <span class="input-group-text" data-td-toggle="datetimepicker">
                                <i class="bi bi-clock"></i>
                              </span>
                            </div>
                          </div>
                          <div class="col-md-3">
                            <label class="form-label small fw-bold">Horario Fim</label>
                            <div class="input-group horario-picker horario-fim-picker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                              <input type="text" class="form-control horario-fim" data-td-target="input" readonly/>
                              <span class="input-group-text" data-td-toggle="datetimepicker">
                                <i class="bi bi-clock"></i>
                              </span>
                            </div>
                          </div>
                          <div class="col-md-2">
                            <button type="button" class="btn btn-outline-danger w-100 remove-horario-btn">Remover</button>
                          </div>
                        </div>
                      </div>
                    </template>
                  </div>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="/ctt/admin/turmas" class="btn btn-red">Voltar</a>
              <button type="submit" class="btn btn-red">
                <?= $id ? "Salvar Alteracoes" : "Cadastrar Turma" ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
  <script src="/ctt/plugins/select2/js/select2.min.js"></script>
  <script src="/ctt/plugins/popper/popper.min.js"></script>
  <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
  <script src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
  <script src="/ctt/plugins/tempus-dominus/js/tempus-dominus.min.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/form/turma_form.js"></script>
</body>
</html>
