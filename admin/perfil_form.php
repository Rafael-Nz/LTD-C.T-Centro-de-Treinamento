<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Pétala Floricultura e Cestas | Novo Perfil</title>

  <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
  <link rel="stylesheet" href="../public/css/admin-styles.css">
  <link rel="stylesheet" href="../public/css/form.css">
  <link rel="stylesheet" href="../public/css/sidebar.css">

  <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
  <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>

  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
  <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
<style>
  /* Container Principal */
  .permissoes-container {
    max-height: 450px;
    overflow-y: auto;
    overflow-x: hidden; /* Garante que não haja scroll horizontal */
    padding-right: 5px; /* Espaço para a barra de rolagem não cobrir o conteúdo */
  }

  /* Cabeçalho do Módulo */
  .modulo-header {
    background: var(--primary-red);
    color: white;
    padding: 0.6rem 1rem;
    margin-bottom: 10px;
    border-radius: 4px;
    font-weight: 600;
    font-size: 0.85rem;
    position: sticky;
    top: 0;
    z-index: 10;
  }

  /* Item de Permissão */
  .permissao-item {
    transition: all 0.2s ease;
    border: 1px solid #dee2e6;
    margin-bottom: 8px;
    border-radius: 8px;
    padding: 0.75rem 1rem !important;
    background: #fff;
    display: flex;
    align-items: center;
    justify-content: space-between;
    gap: 10px; /* Espaço entre texto e o switch */
  }

  /* Ajuste para textos longos */
  .permissao-info {
    flex: 1; /* Faz o texto ocupar todo o espaço disponível */
    min-width: 0; /* Permite que o flex-item encolha se necessário */
  }

  .permissao-info .fw-bold {
    display: block;
    word-wrap: break-word; /* Quebra o texto se for muito longo */
    font-size: 0.9rem;
    line-height: 1.2;
  }

  .permissao-item:hover {
    background-color: #fff5f8;
    border-color: var(--primary-red);
  }

  /* Switch */
  .form-check-input:checked {
    background-color: var(--primary-red);
    border-color: var(--primary-red);
  }

  /* Estilização da barra de rolagem (opcional) */
  .permissoes-container::-webkit-scrollbar {
    width: 6px;
  }
  .permissoes-container::-webkit-scrollbar-thumb {
    background: #ccc;
    border-radius: 10px;
  }
</style>
</head>

<body class="d-flex flex-column min-vh-100">

<?php include __DIR__ . '/partials/sidebar.php'; ?>
<?php include __DIR__ . '/partials/header.php'; ?>

<main class="flex-fill d-flex" id="mainContent">
  <div class="container-lg p-4 flex-column flex-fill">

    <h1 class="h4 mb-4">
      Novo Perfil
    </h1>

    <div class="card border-0 shadow-sm d-flex flex-fill config-card">
      <div class="card-body p-4">
        <form id="formPerfil" action="../api/perfis/" method="POST">
          <input type="hidden" id="permissoesInput" name="permissoes">

          <div class="row gy-4">
            <div class="col-12 col-lg-6">
              <div class="border rounded p-4 h-100 config-card">
                <h3 class="h6 section-title border-bottom border-1 pb-2 mb-3 d-flex align-items-center">
                  <i class="ph ph-identification-card me-2"></i>
                  Dados do Perfil
                </h3>
                
                <div class="mb-3">
                  <label for="nomePerfil" class="form-label fw-medium">Nome do Perfil</label>
                  <input type="text" class="form-control" id="nomePerfil" name="nome_perfil"
                         placeholder="Digite o nome do perfil" required>
                </div>

                <div class="mb-3">
                  <label for="descricaoPerfil" class="form-label fw-medium">Descrição</label>
                  <textarea class="form-control" id="descricaoPerfil" name="descricao" rows="4"
                            placeholder="Digite a descrição do perfil" style="resize: none;"></textarea>
                </div>

                <div class="mb-3">
                  <label for="ativoPerfil" class="form-label fw-medium">Status</label>
                  <select class="form-select" id="ativoPerfil" name="ativo">
                    <option value="1" selected>Ativo</option>
                    <option value="0">Inativo</option>
                  </select>
                </div>
              </div>
            </div>

            <div class="col-12 col-lg-6">
              <div class="border rounded h-100 d-flex p-4 flex-column config-card">
                <h3 class="h6 section-title mb-2 d-flex align-items-center">Permissões de Acesso</h3>
                <div class="mb-2">
                  <div class="input-group">
                    <input type="text" class="form-control" id="searchPermissoes" 
                           placeholder="Buscar permissões...">
                    <button type="button" class="btn border" id="clearSearch" title="Limpar Busca">
                      <i class="ph ph-x"></i>
                    </button>
                  </div>
                </div>

                <div class="permissoes-container flex-fill">
                  <!-- As permissões seriam carregadas via JavaScript -->
                  <div class="no-results">
                    <i class="ph ph-warning-circle fs-1 mb-2"></i>
                    <p class="mb-0">Carregando permissões...</p>
                  </div>
                </div>

                <div class="contadores-container">
                  <div class="row text-center">
                    <div class="col-6 border-end">
                      <small class="text-muted d-block">Total de Permissões</small>
                      <div class="fw-bold fs-5 text-dark" id="totalPermissoes">0</div>
                    </div>
                    <div class="col-6">
                      <small class="text-muted d-block">Selecionadas</small>
                      <div class="fw-bold fs-5 text-rosa" id="permissoesSelecionadas">0</div>
                    </div>
                  </div>
                </div>
              </div>
            </div>
          </div>

          <div class="d-flex justify-content-end gap-3 mt-4 pt-3 border-top">
            <button type="button" class="btn btn-red color" onclick="voltar('perfis')">Voltar</button>
            <button type="submit" id="btnSalvarPerfil" class="btn btn-red color">
              Criar Perfil
            </button>
          </div>
        </form>
      </div>
    </div>
  </div>
</main>

<?php include __DIR__ . '/partials/footer.php'; ?>


<script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
<script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
<script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
<script defer src="../public/js/admin/sidebar.js"></script>
<script>
  function voltar(aba) {
      // Salva a aba desejada no localStorage
      localStorage.setItem('abaConfigAtiva', aba);
      // Redireciona para configurações sem hash na URL
      window.location.href = 'configuracoes';
  }
</script>
<script defer src="../public/js/admin/perfil_form.js"></script>
</body>
</html>