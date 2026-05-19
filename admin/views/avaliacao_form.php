<?php
$acao = $_GET['acao'] ?? 'cadastrar';
$avaliacaoId = isset($_GET['id']) ? (int) $_GET['id'] : 0;
$alunoId = isset($_GET['aluno_id']) ? (int) $_GET['aluno_id'] : (isset($_GET['aluno']) ? (int) $_GET['aluno'] : 0);
$isEdit = $acao === 'editar' && $avaliacaoId > 0;
$tituloPagina = $isEdit ? 'Editar Avaliacao Fisica' : 'Nova Avaliacao Fisica';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $tituloPagina ?></title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
  <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/css/form.css">
  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/css/tempus-dominus.min.css" crossorigin="anonymous">
</head>

<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>

  <main class="flex-fill d-flex" id="mainContent" data-aluno-id="<?= $alunoId ?>" data-avaliacao-id="<?= $avaliacaoId ?>" data-acao="<?= htmlspecialchars($acao, ENT_QUOTES, 'UTF-8') ?>">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <div class="d-flex justify-content-between align-items-center mb-4 flex-wrap gap-3">
        <div>
          <h1 class="h4 mb-1"><?= $tituloPagina ?></h1>
          <p class="text-muted mb-0">Ficha completa de avaliacao fisica com classificacoes automaticas.</p>
        </div>
        <a href="<?= $alunoId > 0 ? "/ctt/admin/alunos/visualizar/{$alunoId}" : '/ctt/admin/alunos' ?>" class="btn btn-secondary">
          <i class="ph ph-arrow-left me-1"></i>Voltar
        </a>
      </div>

      <div class="alert alert-danger d-none" id="avaliacaoFormError" role="alert"></div>

      <div class="card border-0 shadow-sm">
        <div class="card-body">
          <form id="formAvaliacao" novalidate>
            <div class="row g-3 mb-4">
              <div class="col-md-4">
                <label class="form-label">Aluno</label>
                <input type="text" id="alunoNome" class="form-control" readonly>
              </div>
              <div class="col-md-2">
                <label class="form-label">Idade</label>
                <input type="text" id="alunoIdade" class="form-control" readonly>
              </div>
              <div class="col-md-2">
                <label class="form-label">Sexo</label>
                <input type="text" id="alunoSexo" class="form-control" readonly>
              </div>
              <div class="col-md-4">
                <label class="form-label">Professor avaliador</label>
                <input type="text" id="avaliadorNome" class="form-control" readonly value="<?= htmlspecialchars($_SESSION['user_nome'] ?? '', ENT_QUOTES, 'UTF-8') ?>">
              </div>

              <div class="col-md-3">
                <label for="dataAvaliacao" class="form-label">Data da avaliacao</label>
                <div class="input-group" id="dataAvaliacaoPicker" data-td-target-input="nearest" data-td-target-toggle="nearest">
                  <input type="text" class="form-control" id="dataAvaliacao" data-td-target="#dataAvaliacao" autocomplete="off" required>
                  <span class="input-group-text" data-td-target="#dataAvaliacao" data-td-toggle="datetimepicker">
                    <i class="bi bi-calendar-event"></i>
                  </span>
                </div>
              </div>
              <div class="col-md-3">
                <label for="peso" class="form-label">Peso (kg)</label>
                <input type="number" step="0.01" class="form-control" id="peso" name="peso">
              </div>
              <div class="col-md-3">
                <label for="altura" class="form-label">Altura (m)</label>
                <input type="number" step="0.01" class="form-control" id="altura" name="altura">
              </div>
              <div class="col-md-3">
                <label for="cintura" class="form-label">Cintura (cm)</label>
                <input type="number" step="0.01" class="form-control" id="cintura" name="cintura">
              </div>
            </div>

            <h2 class="h6 section-title border-bottom pb-2 mb-3">Perimetros</h2>
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label for="torax" class="form-label">Torax (cm)</label>
                <input type="number" step="0.01" class="form-control" id="torax" name="torax">
              </div>
              <div class="col-md-3">
                <label for="bracoDC" class="form-label">Braco D.C (cm)</label>
                <input type="number" step="0.01" class="form-control" id="bracoDC" name="bracoDC">
              </div>
              <div class="col-md-3">
                <label for="bracoD" class="form-label">Braco D (cm)</label>
                <input type="number" step="0.01" class="form-control" id="bracoD" name="bracoD">
              </div>
              <div class="col-md-3">
                <label for="coxaD" class="form-label">Coxa D (cm)</label>
                <input type="number" step="0.01" class="form-control" id="coxaD" name="coxaD">
              </div>
              <div class="col-md-3">
                <label for="panturrilhaD" class="form-label">PT. D (cm)</label>
                <input type="number" step="0.01" class="form-control" id="panturrilhaD" name="panturrilhaD">
              </div>
              <div class="col-md-3">
                <label for="bracoEC" class="form-label">Braco E.C (cm)</label>
                <input type="number" step="0.01" class="form-control" id="bracoEC" name="bracoEC">
              </div>
              <div class="col-md-3">
                <label for="bracoE" class="form-label">Braco E (cm)</label>
                <input type="number" step="0.01" class="form-control" id="bracoE" name="bracoE">
              </div>
              <div class="col-md-3">
                <label for="coxaE" class="form-label">Coxa E (cm)</label>
                <input type="number" step="0.01" class="form-control" id="coxaE" name="coxaE">
              </div>
              <div class="col-md-3">
                <label for="panturrilhaE" class="form-label">PT. E (cm)</label>
                <input type="number" step="0.01" class="form-control" id="panturrilhaE" name="panturrilhaE">
              </div>
            </div>

            <h2 class="h6 section-title border-bottom pb-2 mb-3">Bioimpedancia e Classificacoes</h2>
            <div class="row g-3 mb-4">
              <div class="col-md-3">
                <label for="imc" class="form-label">IMC</label>
                <input type="number" step="0.01" class="form-control" id="imc" readonly>
              </div>
              <div class="col-md-3">
                <label for="imcClassificacao" class="form-label">Classificacao IMC</label>
                <input type="text" class="form-control" id="imcClassificacao" readonly>
              </div>
              <div class="col-md-3">
                <label for="bodyFat" class="form-label">% Gordura Corporal</label>
                <input type="number" step="0.01" class="form-control" id="bodyFat" name="bodyFat">
              </div>
              <div class="col-md-3">
                <label for="bodyFatClassificacao" class="form-label">Classificacao Body Fat</label>
                <input type="text" class="form-control" id="bodyFatClassificacao" readonly>
              </div>
              <div class="col-md-3">
                <label for="muscle" class="form-label">% Musculo Esqueletico</label>
                <input type="number" step="0.01" class="form-control" id="muscle" name="muscle">
              </div>
              <div class="col-md-3">
                <label for="muscleClassificacao" class="form-label">Classificacao Muscle</label>
                <input type="text" class="form-control" id="muscleClassificacao" readonly>
              </div>
              <div class="col-md-3">
                <label for="rm" class="form-label">Metabolismo em Repouso</label>
                <input type="number" class="form-control" id="rm" name="rm">
              </div>
              <div class="col-md-3">
                <label for="bodyAge" class="form-label">Idade Biologica</label>
                <input type="number" class="form-control" id="bodyAge" name="bodyAge">
              </div>
              <div class="col-md-3">
                <label for="visceralFat" class="form-label">Gordura Visceral</label>
                <input type="number" step="0.01" class="form-control" id="visceralFat" name="visceralFat">
              </div>
              <div class="col-md-3">
                <label for="visceralFatClassificacao" class="form-label">Classificacao Visceral</label>
                <input type="text" class="form-control" id="visceralFatClassificacao" readonly>
              </div>
            </div>

            <div class="row g-3 mb-4">
              <div class="col-12">
                <label for="observacoes" class="form-label">Observacoes</label>
                <textarea class="form-control" id="observacoes" rows="4" placeholder="Anotacoes relevantes desta avaliacao."></textarea>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2">
              <a href="<?= $alunoId > 0 ? "/ctt/admin/alunos/visualizar/{$alunoId}" : '/ctt/admin/alunos' ?>" class="btn btn-secondary">Cancelar</a>
              <button type="submit" class="btn btn-red"><?= $isEdit ? 'Salvar alteracoes' : 'Salvar avaliacao' ?></button>
            </div>
          </form>
        </div>
      </div>
    </div>
  </main>

  <?php include __DIR__ . '/partials/footer.php'; ?>

  <script src="https://code.jquery.com/jquery-3.7.1.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/tempus-dominus.min.js" crossorigin="anonymous"></script>
  <script src="https://cdn.jsdelivr.net/npm/@eonasdan/tempus-dominus@6.9.4/dist/js/locales/pt.js"></script>
  <script src="/ctt/js/admin/sidebar.js"></script>
  <script src="/ctt/js/admin/form/avaliacao_form.js"></script>
</body>
</html>
