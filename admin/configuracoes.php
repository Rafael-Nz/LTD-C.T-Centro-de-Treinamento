<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Configurações</title>
    <link rel="stylesheet" href="../public/css/bootstrap-5.3.8/bootstrap.css">
    <link rel="stylesheet" href="../public/css/admin-styles.css">
    <link rel="stylesheet" href="../public/css/sidebar.css">
    <link rel="stylesheet" href="../public/css/form.css">
    <link href="https://cdn.jsdelivr.net/npm/overlayscrollbars/styles/overlayscrollbars.min.css" rel="stylesheet" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css" />
    <link rel="stylesheet" type="text/css" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.2/src/bold/style.css" />
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/bootstrap-icons@1.13.1/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
    <link rel="preconnect" href="https://fonts.googleapis.com">
    <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css" />
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css" />
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-1">Configurações do Sistema</h1>
            </div>

            <div class="row">
                <!-- Menu Lateral de Configurações -->
                <div class="col-lg-3 col-md-4 mb-4 config-menu-container">
                    <div class="config-card border-0 config-menu-card p-3">
                        <div class="nav flex-column nav-pills config-menu" id="configTabs" role="tablist">
                            <button class="nav-link active" data-bs-toggle="pill" data-bs-target="#perfis"
                                type="button">
                                <i class="ph ph-users-three"></i>
                                <span class="menu-text">Perfis de Usuário</span>
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#cargos" type="button">
                                <i class="ph ph-briefcase"></i>
                                <span class="menu-text">Cargos</span>
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#empresa" type="button">
                                <i class="ph ph-buildings"></i>
                                <span class="menu-text">Dados da Empresa</span>
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#modalidades" type="button">
                                <i class="ph ph-barbell"></i>
                                <span class="menu-text">Modalidades</span>
                            </button>
                            <button class="nav-link" data-bs-toggle="pill" data-bs-target="#sobre" type="button">
                                <i class="ph ph-info"></i>
                                <span class="menu-text">Sobre o Sistema</span>
                            </button>
                        </div>
                    </div>

                    <!-- Card de Estatísticas Rápidas -->
                    <div class="config-card border-0 mt-4 p-3">
                        <h6 class="section-title">Estatísticas do Sistema</h6>
                        <div class="d-flex flex-column gap-2">
                            <div class="d-flex justify-content-between">
                                <span>Total de Usuários:</span>
                                <strong id="totalUsuariosGeral"></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Perfis Ativos:</span>
                                <strong id="perfisAtivosGeral"></strong>
                            </div>
                            <div class="d-flex justify-content-between">
                                <span>Cargos Cadastrados:</span>
                                <strong id="cargosCadastradosGeral"></strong>
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Conteúdo das Configurações -->
                <div class="col-lg-9 col-md-8">
                    <div class="tab-content" id="configContent">

                        <!-- Aba: Perfis e Permissões -->
                        <div class="tab-pane fade show active" id="perfis">
                            <div class="config-card border-0 p-4">

                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Gestão de Perfis de Usuário</h5>
                                    <a href="perfil_form" class="btn btn-red color btn-sm d-flex align-items-center">
                                        <i class="ph ph-plus me-1"></i>Novo Perfil
                                    </a>
                                </div>

                                <!-- BUSCA + FILTROS -->
                                <div class="row mb-4">
                                    <div class="col-md-6 d-flex gap-2 align-items-center flex-wrap">

                                        <!-- Busca -->
                                        <form id="buscaPerfis" class="d-flex" role="search" onsubmit="return false;">
                                            <div class="input-group">
                                                <input id="campoBuscaPerfis"
                                                    class="form-control"
                                                    type="search"
                                                    placeholder="Buscar perfis..."
                                                    aria-label="Buscar">
                                                <button class="btn border border-start-0"
                                                        type="button"
                                                        id="botaoBuscarPerfis">
                                                    <i class="ph ph-magnifying-glass"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <!-- Filtro -->
                                        <div class="dropdown-center">
                                            <button class="btn color dropdown-toggle border-1 border-white"
                                                    type="button"
                                                    data-bs-toggle="dropdown"
                                                    aria-expanded="false"
                                                    title="Filtrar Status">
                                                <i class="ph ph-funnel me-2"></i>
                                            </button>

                                            <ul class="dropdown-menu dropdown-menu-end p-3">
                                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>

                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-perfil"
                                                            type="checkbox"
                                                            value="1"
                                                            id="perfilAtivo">
                                                        <label class="form-check-label" for="perfilAtivo">Ativo</label>
                                                    </div>
                                                </li>

                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-perfil"
                                                            type="checkbox"
                                                            value="0"
                                                            id="perfilInativo">
                                                        <label class="form-check-label" for="perfilInativo">Inativo</label>
                                                    </div>
                                                </li>

                                                <li><hr class="dropdown-divider"></li>

                                                <li class="d-grid">
                                                    <button id="aplicarFiltrosPerfis"
                                                            class="btn btn-sm btn-red color">
                                                        Aplicar Filtros
                                                    </button>
                                                </li>
                                            </ul>
                                        </div>

                                    </div>
                                </div>

                                <!-- TABELA -->
                                <table id="tabelaPerfis" class="table table-hover align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-start">Nome</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-center">Data Criação</th>
                                            <th class="text-center">Ações</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>

                            </div>
                        </div>

                        <!-- Aba: Cargos -->
                        <div class="tab-pane fade" id="cargos">
                            <div class="config-card border-0 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Gestão de Cargos</h5>
                                    <a href="cargo_form" class="btn btn-red color btn-sm d-flex align-items-center">
                                        <i class="ph ph-plus me-1"></i>Novo Cargo
                                    </a>
                                </div>

                                <div class="row mb-4">
                                    <div class="col-md-6 d-flex gap-2 align-items-center flex-wrap">
                                        <form id="buscaCargos" class="d-flex" role="search" onsubmit="return false;">
                                            <div class="input-group">
                                                <input id="campoBuscaCargos" class="form-control" type="search" placeholder="Buscar cargos..." aria-label="Buscar">
                                                <button class="btn border border-start-0" type="button" id="botaoBuscarCargos">
                                                    <i class="ph ph-magnifying-glass"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <div class="dropdown-center">
                                            <button class="btn color dropdown-toggle border-1 border-white"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                title="Filtrar Status">
                                                <i class="ph ph-funnel me-2"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end p-3"
                                                aria-labelledby="dropdownMenuButton">
                                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-cargo" type="checkbox"
                                                            value="Ativo" id="statusCargoAtivo">
                                                        <label class="form-check-label" for="statusCargoAtivo">Ativo</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-cargo" type="checkbox"
                                                            value="Inativo" id="statusCargoInativo">
                                                        <label class="form-check-label"
                                                            for="statusCargoInativo">Inativo</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li class="d-grid">
                                                    <button id="aplicarFiltrosCargos" class="btn btn-sm btn-red color">Aplicar
                                                        Filtros</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>

                                <div class="">
                                    <table id="tabelaCargos" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Nome</th>
                                                <th class="text-center">Descrição</th>
                                                <th class="text-center">Salário Base</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Dados da Empresa -->
                        <div class="tab-pane fade" id="empresa">
                            <div class="config-card border-0  p-4">
                                <h5 class="section-title">Dados da Empresa</h5>

                                <form id="formEmpresa">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nome da Empresa</label>
                                            <input type="text" class="form-control" id="empresa_nome"
                                                name="empresa_nome">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CNPJ</label>
                                            <input type="text" class="form-control" id="empresa_cnpj"
                                                name="empresa_cnpj">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="empresa_telefone"
                                                name="empresa_telefone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" id="empresa_email"
                                                name="empresa_email">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Endereço Completo</label>
                                            <input type="text" class="form-control" id="empresa_endereco"
                                                name="empresa_endereco">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Cidade</label>
                                            <input type="text" class="form-control" id="empresa_cidade"
                                                name="empresa_cidade">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado</label>
                                            <input type="text" class="form-control" id="empresa_estado"
                                                name="empresa_estado">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">CEP</label>
                                            <input type="text" class="form-control" id="empresa_cep" name="empresa_cep">
                                        </div>
                                    </div>
                                    <button type="submit" class="btn btn-red color">Salvar Dados</button>
                                </form>
                            </div>
                        </div>

                        <!-- Aba: Modalidades -->
                        <div class="tab-pane fade" id="modalidades">
                            <div class="config-card vorder-0 p-4">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Gestão de Modalidades</h5>
                                    <a href="modalidade_form" class="btn btn-red color btn-sm d-flex align-items-center">
                                        <i class="ph ph-plus me-1"></i>Nova Modalidade
                                    </a>
                                </div>
                                
                                <div class="row mb-4">
                                    <div class="col-md-6 d-flex gap-2 align-items-center flex-wrap">
                                        <form id="buscaModalidades" class="d-flex" role="search" onsubmit="return false;">
                                            <div class="input-group">
                                                <input id="campoBuscaModalidades" class="form-control" type="search" placeholder="Buscar modalidades..." aria-label="Buscar">
                                                <button class="btn border border-start-0" type="button" id="botaoBuscarModalidades">
                                                    <i class="ph ph-magnifying-glass"></i>
                                                </button>
                                            </div>
                                        </form>
                                        <div class="dropdown-center">
                                            <button class="btn color dropdown-toggle border-1 border-white"
                                                type="button" data-bs-toggle="dropdown" aria-expanded="false"
                                                title="Filtrar Status">
                                                <i class="ph ph-funnel me-2"></i>
                                            </button>
                                            <ul class="dropdown-menu dropdown-menu-end p-3"
                                                aria-labelledby="dropdownMenuButton">
                                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-modalidade" type="checkbox"
                                                            value="Ativo" id="statusModalidadeAtivo">
                                                        <label class="form-check-label" for="statusModalidadeAtivo">Ativo</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <div class="form-check">
                                                        <input class="form-check-input filtro-status-modalidade" type="checkbox"
                                                            value="Inativo" id="statusModalidadeInativo">
                                                        <label class="form-check-label"
                                                            for="statusModalidadeInativo">Inativo</label>
                                                    </div>
                                                </li>
                                                <li>
                                                    <hr class="dropdown-divider">
                                                </li>
                                                <li class="d-grid">
                                                    <button id="aplicarFiltrosModalidades" class="btn btn-sm btn-red color">Aplicar
                                                        Filtros</button>
                                                </li>
                                            </ul>
                                        </div>
                                    </div>
                                </div>


                                <div class="">
                                    <table id="tabelaModalidades" class="table table-hover align-middle w-100">
                                        <thead>
                                            <tr>
                                                <th class="text-start">Nome</th>
                                                <th class="text-center">Descrição</th>
                                                <th class="text-center">Status</th>
                                                <th class="text-center">Ações</th>
                                            </tr>
                                        </thead>
                                        <tbody></tbody>
                                    </table>
                                </div>
                            </div>
                        </div>

                        <!-- Aba: Sobre o Sistema -->
                        <div class="tab-pane fade" id="sobre">
                            <div class="config-card border-0  p-4">
                                <h5 class="section-title mb-3">Sobre</h5>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.min.js"></script>
    <script src="../public/js/admin/tabelas.js"></script>
    <script defer src="../public/js/bootstrap-5.3.8/bootstrap.bundle.js"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/overlayscrollbars/browser/overlayscrollbars.browser.es6.min.js"></script>
    <script defer src="../public/js/admin/sidebar.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <script src="../public/js/admin/configuracoes.js"></script>
</body>

</html>