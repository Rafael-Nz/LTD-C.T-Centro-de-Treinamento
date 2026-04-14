<?php
$id = isset($_GET['id']) ? (int)$_GET['id'] : null;
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Cross C.T | <?= $id ? 'Editar Plano' : 'Cadastrar Plano' ?></title>
  <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
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
  <style>
    .select2-container--bootstrap-5 .select2-selection--multiple {
        max-height: 100px !important; /* Ajuste a altura conforme preferir */
        overflow-y: auto !important;
    }

    /* Ajuste para as "tags" internas não ficarem muito grandes */
    .select2-selection__choice {
        font-size: 0.85rem !important;
        margin-top: 4px !important;
    }
  </style>
</head>
<body class="d-flex flex-column min-vh-100">
  <?php include __DIR__ . '/partials/sidebar.php'; ?>
  <?php include __DIR__ . '/partials/header.php'; ?>
  
  <main class="flex-fill d-flex" id="mainContent">
    <div class="container-lg p-4 d-flex flex-column flex-fill">
      <h1 class="h4 mb-4"><?= $id ? "Editar Plano" : "Cadastrar Plano" ?></h1>
      <div class="card shadow-sm d-flex flex-fill">
        <div class="card-body">
          <form id="formPlano" action="../api/planos/" method="POST">
            <?php if ($id): ?><input type="hidden" name="id" id="planoId" value="<?= $id ?>"><?php endif; ?>
            
            <div class="row gy-4">
              <!-- Informações Básicas do Plano -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Básicas</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="nome" class="form-label">Nome do Plano:</label>
                    <input type="text" name="nome" id="nome" class="form-control" 
                           placeholder="Ex: CrossFit Mensal" required>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="descricao" class="form-label">Descrição Curta:</label>
                    <input type="text" name="descricao" id="descricao" class="form-control" 
                           placeholder="Ex: Acesso ilimitado às aulas" required>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="periodicidade" class="form-label">Periodicidade:</label>
                    <select name="periodicidade" id="periodicidade" class="form-select" required>
                      <option value="">Selecione</option>
                      <option value="semanal">Semanal</option>
                      <option value="quinzenal">Quinzenal</option>
                      <option value="mensal">Mensal</option>
                      <option value="bimestral">Bimestral</option>
                      <option value="trimestral">Trimestral</option>
                      <option value="semestral">Semestral</option>
                      <option value="anual">Anual</option>
                    </select>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="status" class="form-label">Status do Plano:</label>
                    <select name="status" id="status" class="form-select" required>
                      <option value="ativo">Ativo</option>
                      <option value="inativo">Inativo</option>
                    </select>
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="modalidades" class="form-label">Modalidades Inclusas:</label>
                  <select name="modalidades[]" id="modalidades" class="form-select" multiple="multiple" style="width: 100%;">
                    <!-- Modalidades serão carregadas via JavaScript -->
                  </select>
                  <small class="text-muted">Selecione as modalidades disponíveis neste plano</small>
                </div>
              </div>

              <!-- Valores e Limitações -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Valores e Limitações</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="valor" class="form-label">Valor (R$):</label>
                    <div class="input-group">
                      <span class="input-group-text">R$</span>
                      <input type="text" name="valor" id="valor" class="form-control" 
                             placeholder="0,00" required>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="limite_treinos_semana" class="form-label">Limite de Treinos por Semana:</label>
                    <select name="limite_treinos_semana" id="limite_treinos_semana" class="form-select">
                      <option value="0">ilimitado</option>
                      <option value="1">1 treino</option>
                      <option value="2">2 treinos</option>
                      <option value="3">3 treinos</option>
                      <option value="4">4 treinos</option>
                      <option value="5">5 treinos</option>
                      <option value="6">6 treinos</option>
                      <option value="7">7 treinos</option>
                    </select>
                  </div>
                </div>
              </div>

              <!-- Características e Restrições -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Características e Restrições</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="idade_minima" class="form-label">Idade Mínima:</label>
                    <input type="number" name="idade_minima" id="idade_minima" class="form-control" 
                           min="0" value="0">
                  </div>
                  
                  <div class="col-md-6">
                    <label for="idade_maxima" class="form-label">Idade Máxima:</label>
                    <input type="number" name="idade_maxima" id="idade_maxima" class="form-control" 
                           min="0" value="99">
                  </div>
                </div>
                
                <div class="mb-3">
                  <label for="restricoes" class="form-label">Restrições Especiais:</label>
                  <textarea name="restricoes" id="restricoes" class="form-control" rows="2" 
                            placeholder="Ex: Necessário atestado médico para maiores de 60 anos"></textarea>
                </div>
              </div>

              <!-- Configurações de Renovação -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Configurações de Renovação</h3>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="renovacao_automatica" class="form-label">Renovação Automática:</label>
                    <select name="renovacao_automatica" id="renovacao_automatica" class="form-select">
                      <option value="1">Sim, renovar automaticamente</option>
                      <option value="0" selected>Não, renovação manual</option>
                    </select>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="dias_aviso_vencimento" class="form-label">Dias de Aviso antes do Vencimento:</label>
                    <input type="number" name="dias_aviso_vencimento" id="dias_aviso_vencimento" class="form-control" 
                           min="0" max="30" value="7">
                    <small class="text-muted">Número de dias para enviar aviso antes do vencimento</small>
                  </div>
                </div>
                
                <div class="row g-3 mb-3">
                  <div class="col-md-6">
                    <label for="multa_atraso" class="form-label">Multa por Atraso (%):</label>
                    <div class="input-group">
                      <input type="number" name="multa_atraso" id="multa_atraso" class="form-control" 
                             min="0" max="100" value="2" step="0.5">
                      <span class="input-group-text">%</span>
                    </div>
                  </div>
                  
                  <div class="col-md-6">
                    <label for="tolerancia_atraso" class="form-label">Tolerância de Atraso (dias):</label>
                    <input type="number" name="tolerancia_atraso" id="tolerancia_atraso" class="form-control" 
                           min="0" max="30" value="5">
                  </div>
                </div>
              </div>

              <!-- Observações -->
              <div class="col-12">
                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Observações</h3>
                
                <div class="mb-3">
                  <label for="observacoes" class="form-label">Observações Internas:</label>
                  <textarea name="observacoes" id="observacoes" class="form-control" rows="3" 
                            placeholder="Anotações internas sobre o plano." style="resize: none;"></textarea>
                </div>
              </div>
            </div>

            <div class="d-flex justify-content-end gap-2 mt-4">
              <a href="planos" class="btn btn-red color">Voltar</a>
              <button type="submit" class="btn btn-red color">
                <?= $id ? "Salvar Alterações" : "Cadastrar Plano" ?>
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
  <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
  <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
  <script defer src="../public/js/admin/sidebar.js"></script>
  <script>
    window.isEditMode = <?= $id ? 'true' : 'false'; ?>;
    window.planoId = <?= $id ? json_encode($id) : 'null'; ?>;
  </script>
  <script defer src="../public/js/admin/plano_form.js"></script>
</body>
</html>