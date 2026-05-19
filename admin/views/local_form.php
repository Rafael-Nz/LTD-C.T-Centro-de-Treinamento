<?php
$id = isset($_GET['id']) ? (int) $_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $id ? 'Editar Local' : 'Cadastrar Local' ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/form.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? 'Editar Local' : 'Cadastrar Local' ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formLocal" method="POST" data-id="<?= $id ?>">
            <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>

            <div class="row gy-4">
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informacoes do Local</h3>

                <div class="mb-3">
                  <label for="nome" class="form-label">Nome do Local</label>
                  <input type="text" class="form-control" id="nome" name="nome" placeholder="Ex: CrossFit Principal" required>
                </div>

                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="capacidade_minima" class="form-label">Capacidade Minima</label>
                    <input type="number" class="form-control" id="capacidade_minima" name="capacidade_minima" min="1" required>
                  </div>

                  <div class="col-md-6">
                    <label for="capacidade_maxima" class="form-label">Capacidade Maxima</label>
                    <input type="number" class="form-control" id="capacidade_maxima" name="capacidade_maxima" min="1" required>
                  </div>
                </div>

                <div class="mb-3">
                  <label for="equipamentos" class="form-label">Equipamentos</label>
                  <textarea class="form-control" id="equipamentos" name="equipamentos" rows="3" placeholder="Descreva os principais equipamentos do local"></textarea>
                </div>

              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="/ctt/admin/locais" class="btn btn-red color">Voltar</a>
              <button type="submit" class="btn btn-red color">
                <?= $id ? 'Salvar Alteracoes' : 'Cadastrar Local' ?>
              </button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/form/local_form.js"></script>
</body>
</html>
