<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | Cadastrar Avaliação Física</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="../public/css/index.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css"
    href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="stylesheet"
    href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css"
    crossorigin="anonymous">
</head>

<body class="d-flex flex-column min-vh-100 sidebar-closed">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <div class="card shadow border-0 bg-white mx-auto" style="max-width: 800px;">
        <div class="card-header bg-dark border-0 text-white text-center py-3">
          <span class="mb-3 fs-5 text-white fw-semibold">Ficha de Avaliação</span>
        </div>
        <div class="card-body bg-white p-4">
          <form action="#" method="post" id="fichaAvaliacao">

            <!-- Métricas -->
            <h5 class="text-dark fw-normal mb-3 border-bottom pb-2 mt-2">Métricas</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="data" class="form-label">Data da Avaliação:</label>
                <div class="input-group" id="data_avaliacao" data-td-target-input="nearest"
                  data-td-target-toggle="nearest">
                  <input type="text" class="form-control" id="data" name="data" data-td-target="#data" required />
                  <span class="input-group-text" data-td-toggle="datetimepicker" data-td-target="#data">
                    <i class="bi bi-calendar-event"></i>
                  </span>
                </div>
                <div id="dataError" class="error-message"></div>
              </div>
              <div class="col-md-6">
                <label for="peso" class="form-label">Peso:</label>
                <div class="input-group">
                  <input type="number" step="0.1" class="form-control" id="peso" name="peso" aria-describedby="peso"
                    required>
                  <span class="input-group-text" id="peso">kg</span>
                </div>
                <div id="pesoError" class="error-message"></div>
              </div>

              <div class="col-md-6">
                <label for="cintura" class="form-label">Cintura:</label>
                <input type="number" step="0.1" class="form-control" id="cintura" name="cintura" required>
              </div>
              <div class="col-md-6"></div>
              <div class="col-md-6">
                <label for="bracoDC" class="form-label">Braço Direito Contraído:</label>
                <input type="number" step="0.1" class="form-control" id="bracoDC" name="bracoDC">
              </div>

              <div class="col-md-6">
                <label for="bracoEC" class="form-label">Braço Esquerdo Contraído:</label>
                <input type="number" step="0.1" class="form-control" id="bracoEC" name="bracoEC">
              </div>
              <div class="col-md-6">
                <label for="bracoD" class="form-label">Braço Direito:</label>
                <input type="number" step="0.1" class="form-control" id="bracoD" name="bracoD">
              </div>

              <div class="col-md-6">
                <label for="bracoE" class="form-label">Braço Esquerdo:</label>
                <input type="number" step="0.1" class="form-control" id="bracoE" name="bracoE">
              </div>
              <div class="col-md-6">
                <label for="coxaD" class="form-label">Coxa Direita:</label>
                <input type="number" step="0.1" class="form-control" id="coxaD" name="coxaD">
              </div>

              <div class="col-md-6">
                <label for="coxaE" class="form-label">Coxa Esquerda:</label>
                <input type="number" step="0.1" class="form-control" id="coxaE" name="coxaE">
              </div>
              <div class="col-md-6">
                <label for="ptD" class="form-label">Panturrilha Direita:</label>
                <input type="number" step="0.1" class="form-control" id="ptD" name="ptD">
              </div>

              <div class="col-md-6">
                <label for="ptE" class="form-label">Panturrilha Esquerda:</label>
                <input type="number" step="0.1" class="form-control" id="ptE" name="ptE">
              </div>
            </div>

            <!-- Índices e Percentuais -->
            <h5 class="text-dark fw-normal mb-3 border-bottom pb-2 mt-4">Índices Corporais e Bioimpedância</h5>
            <div class="row g-3 mb-4">
              <div class="col-md-6">
                <label for="imc" class="form-label">Índice de Massa Corporal:</label>
                <input type="number" step="0.1" class="form-control" id="imc" name="imc">
              </div>
              <div class="col-md-6">
                <label for="bodyFat" class="form-label">Percentual de Gordura Corporal:</label>
                <input type="number" step="0.1" class="form-control" id="bodyFat" name="bodyFat">
              </div>

              <div class="col-md-6">
                <label for="muscle" class="form-label">Percentual de Músculo Esquelético:</label>
                <input type="number" step="0.1" class="form-control" id="muscle" name="muscle">
              </div>
              <div class="col-md-6">
                <label for="rm" class="form-label">Metabolismo em Repouso:</label>
                <input type="number" class="form-control" id="rm" name="rm">
              </div>

              <div class="col-md-6">
                <label for="bodyAge" class="form-label">Idade Biológica:</label>
                <input type="number" class="form-control" id="bodyAge" name="bodyAge">
              </div>
              <div class="col-md-6">
                <label for="visceralFat" class="form-label">Gordura Visceral:</label>
                <input type="number" step="0.1" class="form-control" id="visceralFat" name="visceralFat">
              </div>
            </div>

            <!-- Botão -->
            <div class="d-grid gap-2 pt-3">
              <button type="submit" class="btn btn-danger btn-md">
                <span class="text-white fw-semibold">Salvar Avaliação</span>
              </button>
            </div>

          </form>
        </div>
        <div class="card-footer bg-white border-0"></div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>
  
  <script src="js/formUtils.js"></script>
  <script src="https://unpkg.com/imask"></script>
  <script src="js/validar-cadastro-avaliacao.js"></script>
  <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.6/dist/umd/popper.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
  <script src="../public/js/admin/sidebar.js"></script>
</body>

</html>