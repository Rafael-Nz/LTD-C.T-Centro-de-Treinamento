<?php

$acao = $_GET['acao'] ?? 'novo';

$id = isset($_GET['id']) ? (int)$_GET['id'] : null;

if (!$acao && $id) {
    $acao = 'editar';
}

$isEdit = ($acao === 'editar' && $id);
$pageTitle = $isEdit ? 'Editar Funcionário' : 'Cadastrar Funcionário';
?>

<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
  <head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | <?= $pageTitle ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-sRIl4kxILFvY47J16cr9ZwB07vP4J8+LH7qKQnuqkuIAvNWLzeN8tE5YBujZqJLB" crossorigin="anonymous">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/form.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css"/>
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <script src="https://kit.fontawesome.com/2748b3b4b0.js" crossorigin="anonymous"></script>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet"/>
    <link href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css" rel="stylesheet"/>
  </head>
  <body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '../partials/sidebar.php'; ?>
    <?php include __DIR__ . '../partials/header.php'; ?>
    
    <main class="flex-fill d-flex" id="mainContent">
      <div class="container-lg p-4 d-flex flex-column flex-fill">
        <h1 class="h4 mb-4"><?= $pageTitle ?></h1>
        <div class="card shadow-sm d-flex flex-fill">
          <div class="card-body">
            <form id="formFuncionario" data-id="<?= $id ?>" action="" method="POST">
              <?php if ($id): ?><input type="hidden" name="id" value="<?= $id ?>"><?php endif; ?>
              
              <div class="row gy-4">
                <!-- Informações Pessoais -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Pessoais</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="nome" class="form-label">Nome:</label>
                      <input type="text" name="nome" id="nome" class="form-control" 
                             placeholder="Digite o nome" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="sobrenome" class="form-label">Sobrenome:</label>
                      <input type="text" name="sobrenome" id="sobrenome" class="form-control" 
                             placeholder="Digite o sobrenome" required>
                    </div>
                  </div>

                  <div class="row g-3 mb-3">
                    <div class="col-md-12">
                      <label for="email" class="form-label">E-mail:</label>
                      <input type="email" name="email" id="email" class="form-control" placeholder="email@exemplo.com" required>
                    </div>
                  </div>

                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="cpf" class="form-label">CPF:</label>
                      <input type="text" name="cpf" id="cpf" class="form-control" 
                             placeholder="___.___.___-__" maxlength="14" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="nascimento" class="form-label">Data de Nascimento:</label>
                      <input type="date" name="nascimento" id="nascimento" class="form-control" required>
                    </div>
                  </div>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="genero" class="form-label">Gênero:</label>
                      <select name="genero" id="genero" class="form-select" required>
                        <option value="">Selecione</option>
                        <option value="M">Masculino</option>
                        <option value="F">Feminino</option>
                        <option value="O">Outro / Prefiro não informar</option>
                      </select>
                    </div>
                    
                    <div class="col-md-6"></div>
                  </div>
                </div>

                <!-- Contatos -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Contatos</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="telefone1" class="form-label">Telefone:</label>
                      <input type="tel" name="telefone1" id="telefone1" class="form-control" 
                             placeholder="(__) _____-____" maxlength="15" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="telefone2" class="form-label">WhatsApp (Opcional):</label>
                      <input type="tel" name="telefone2" id="telefone2" class="form-control" 
                             placeholder="(__) _____-____" maxlength="15">
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="email2" class="form-label">E-mail Secundário:</label>
                    <input type="email" name="email2" id="email2" class="form-control" placeholder="email@exemplo.com">
                  </div>
                </div>

                <!-- Endereço -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Endereço</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-8">
                      <label for="logradouro" class="form-label">Logradouro/Rua:</label>
                      <input type="text" name="logradouro" id="logradouro" class="form-control" placeholder="Rua, Avenida, etc." required>
                    </div>
                    
                    <div class="col-md-4">
                      <label for="numero" class="form-label">Nº:</label>
                      <input type="text" name="numero" id="numero" class="form-control" placeholder="Ex: 123" required>
                    </div>
                  </div>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="cidade" class="form-label">Cidade:</label>
                      <input type="text" name="cidade" id="cidade" class="form-control" placeholder="Ex: São Luís" required>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="bairro" class="form-label">Bairro:</label>
                      <input type="text" name="bairro" id="bairro" class="form-control" placeholder="Ex: Centro" required>
                    </div>
                  </div>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="cep" class="form-label">CEP:</label>
                      <div class="input-group">
                        <input type="text" name="cep" id="cep" class="form-control" placeholder="00000-000" maxlength="9" required>
                        <button class="btn border border-start-0 border-left-0 rounded-end p-auto " style="min-height: 0; font-size: 1.05rem;" type="button" id="buscarCep" title="Buscar CEP">
                          <i class="ph ph-magnifying-glass"></i>
                        </button>
                      </div>
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="complemento" class="form-label">Complemento (Opcional):</label>
                    <textarea name="complemento" id="complemento" class="form-control" 
                              rows="3" placeholder="Complemento do endereço (opcional)" 
                              style="resize: none;"></textarea>
                  </div>
                </div>

                <!-- Informações Profissionais -->
                <div class="col-12">
                  <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações Profissionais</h3>
                  
                  <div class="row g-3 mb-3">
                    <div class="col-md-6">
                      <label for="cargo" class="form-label">Cargo / Função:</label>
                      <select name="cargo" id="cargo" class="form-select" required>
                        <option value="">Selecione</option>
                      </select>
                    </div>
                    
                    <div class="col-md-6">
                      <label for="registro_profissional" class="form-label">Registro Profissional:</label>
                      <input type="text" name="registro_profissional" id="registro_profissional" class="form-control" 
                             placeholder="Ex: CREF 123456-G/MA">
                    </div>
                  </div>
                  
                  <div class="mb-3">
                    <label for="observacoes" class="form-label">Observações (Opcional):</label>
                    <textarea name="observacoes" id="observacoes" class="form-control" rows="3" placeholder="Anotações internas sobre o funcionário." style="resize: none;"></textarea>
                  </div>
                </div>
              </div>

              <div class="d-flex justify-content-end gap-2 mt-4">
                <a href="/ctt/admin/funcionarios" class="btn btn-red">Voltar</a>
                <button type="submit" class="btn btn-red">
                  <?= $id ? "Salvar Alterações" : "Cadastrar Funcionário" ?>
                </button>
              </div>
            </form>
          </div>
        </div>
      </div>
    </main>

    <?php include __DIR__ . '../partials/footer.php'; ?>

    <script src="https://code.jquery.com/jquery-3.7.1.min.js" integrity="sha256-/JqT3SQfawRcv/BIHPThkBvs0OEvtFFmqPF/lYI/Cxo=" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://unpkg.com/imask"></script>
    <script src="https://cdn.jsdelivr.net/npm/@popperjs/core@2.11.8/dist/umd/popper.min.js" integrity="sha384-I7E8VVD/ismYTF4hNIPjVp/Zjvgyol6VFvRkX/vR+Vc4jQkC+hVqc2pM8ODewa9r" crossorigin="anonymous"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.min.js" integrity="sha384-G/EV+4j2dNv+tEPo3++6LCgdCROaejBqfUeNjuKAiuXbjrxilcCdDz6ZAVfHWe1Y" crossorigin="anonymous"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/js/admin/form/funcionario_form.js"></script>

  </body>
</html>