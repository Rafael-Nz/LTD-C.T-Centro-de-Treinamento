<?php
$canCargos = \Core\Auth\Auth::canAny(['cargos.visualizar', 'cargos.gerenciar']);
$canModalidades = \Core\Auth\Auth::canAny(['modalidades.visualizar', 'modalidades.gerenciar']);
$canEmpresa = \Core\Auth\Auth::can('configuracoes.gerenciar');
$canConfiguracoes = $canCargos || $canModalidades || $canEmpresa || \Core\Auth\Auth::can('usuarios.gerenciar');

if (!$canConfiguracoes) {
    header('Location: /ctt/admin/inicio?error=sem_permissao');
    exit;
}

$tabPermitidas = ['cargos', 'modalidades', 'empresa', 'sobre'];
$tabInicial = in_array($_GET['tab'] ?? '', $tabPermitidas, true) ? $_GET['tab'] : '';

$activeTab = 'sobre';
if ($canCargos) {
    $activeTab = 'cargos';
} elseif ($canModalidades) {
    $activeTab = 'modalidades';
} elseif ($canEmpresa) {
    $activeTab = 'empresa';
}

if ($tabInicial) {
    if ($tabInicial === 'cargos' && $canCargos) {
        $activeTab = 'cargos';
    } elseif ($tabInicial === 'modalidades' && $canModalidades) {
        $activeTab = 'modalidades';
    } elseif ($tabInicial === 'empresa' && $canEmpresa) {
        $activeTab = 'empresa';
    } elseif ($tabInicial === 'sobre') {
        $activeTab = 'sobre';
    }
}
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Configuracoes</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/css/form.css">
    <link href="/ctt/plugins/overlayscrollbars/css/overlayscrollbars.min.css" rel="stylesheet" />
  <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/regular/style.css" />
    <link rel="stylesheet" type="text/css" href="/ctt/plugins/phosphor-icons/src/bold/style.css" />
  <link rel="stylesheet" href="/ctt/plugins/bootstrap-icons/font/bootstrap-icons.min.css">
    <link rel="stylesheet" href="/ctt/plugins/fontawesome/css/all.min.css">
  <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css" />
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css" />
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill config-page-shell">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-1">Configuracoes do Sistema</h1>
            </div>

            <div class="row align-items-stretch config-layout-row">
                <div class="col-lg-3 col-md-4 mb-4 config-menu-container d-flex">
                    <div class="config-card border-0 config-menu-card p-3 w-100 h-100">
                        <div class="nav flex-column nav-pills config-menu" id="configTabs" role="tablist">
                            <?php if ($canCargos): ?>
                            <button class="nav-link <?= $activeTab === 'cargos' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#cargos" type="button">
                                <i class="ph ph-briefcase" aria-hidden="true"></i>
                                <span class="menu-text">Cargos</span>
                            </button>
                            <?php endif; ?>
                            <?php if ($canModalidades): ?>
                            <button class="nav-link <?= $activeTab === 'modalidades' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#modalidades" type="button">
                                <i class="ph ph-barbell" aria-hidden="true"></i>
                                <span class="menu-text">Modalidades</span>
                            </button>
                            <?php endif; ?>
                            <?php if ($canEmpresa): ?>
                            <button class="nav-link <?= $activeTab === 'empresa' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#empresa" type="button">
                                <i class="ph ph-buildings" aria-hidden="true"></i>
                                <span class="menu-text">Dados da Empresa</span>
                            </button>
                            <?php endif; ?>
                            <button class="nav-link <?= $activeTab === 'sobre' ? 'active' : '' ?>" data-bs-toggle="pill" data-bs-target="#sobre" type="button">
                                <i class="ph ph-info" aria-hidden="true"></i>
                                <span class="menu-text">Sobre o Sistema</span>
                            </button>
                        </div>
                    </div>
                </div>

                <div class="col-lg-9 col-md-8 d-flex config-content-container">
                    <div class="tab-content w-100 h-100" id="configContent">
                        <?php if ($canCargos): ?>
                        <div class="tab-pane fade <?= $activeTab === 'cargos' ? 'show active' : '' ?> h-100" id="cargos">
                            <div class="config-card border-0 p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Gestao de Cargos</h5>
                                </div>

                                <div class="mb-4 d-flex gap-2 flex-wrap">
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <form id="buscaCargos" class="d-flex" role="search" onsubmit="return false;">
                                            <div class="input-group">
                                                <input id="campoBuscaCargos" class="form-control" type="search" placeholder="Buscar cargos..." aria-label="Buscar">
                                                <button class="btn border border-start-0" type="button" id="botaoBuscarCargos" title="Buscar cargos" aria-label="Buscar cargos">
                                                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <div class="dropdown-center">
                                            <button class="btn btn-red color dropdown-toggle border border-white" id="filtrosCargosBtn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filtrar status" aria-label="Filtrar status">
                                                <i class="ph ph-funnel me-1" aria-hidden="true"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end p-3 dropdown-menu-lg" style="min-width: 250px">
                                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                                                <div class="form-check">
                                                    <input class="form-check-input filtro-status-cargo" type="checkbox" value="Ativo" id="statusCargoAtivo">
                                                    <label class="form-check-label" for="statusCargoAtivo">Ativo</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input filtro-status-cargo" type="checkbox" value="Inativo" id="statusCargoInativo">
                                                    <label class="form-check-label" for="statusCargoInativo">Inativo</label>
                                                </div>
                                                <hr class="dropdown-divider">
                                                <div class="d-grid gap-2">
                                                    <button id="aplicarFiltrosCargos" class="btn btn-sm btn-red" type="button">Aplicar Filtros</button>
                                                    <button id="limparFiltrosCargos" class="btn btn-sm btn-red" type="button">Limpar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (\Core\Auth\Auth::can('cargos.gerenciar')): ?>
                                    <a href="/ctt/admin/cargos/cadastrar" class="btn btn-red color d-flex align-items-center border border-white" title="Cadastrar cargo" aria-label="Cadastrar cargo">
                                        <i class="ph ph-plus me-1" aria-hidden="true"></i> Novo Cargo
                                    </a>
                                    <?php endif; ?>
                                </div>

                                <table id="tabelaCargos" class="table table-hover align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-start">Cargo</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <?php if ($canEmpresa): ?>
                        <div class="tab-pane fade <?= $activeTab === 'empresa' ? 'show active' : '' ?> h-100" id="empresa">
                            <div class="config-card border-0 p-4 h-100">
                                <h5 class="section-title">Dados da Empresa</h5>

                                <form id="formEmpresa">
                                    <div class="row">
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Nome da Empresa</label>
                                            <input type="text" class="form-control" id="empresa_nome" name="empresa_nome">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">CNPJ</label>
                                            <input type="text" class="form-control" id="empresa_cnpj" name="empresa_cnpj">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Telefone</label>
                                            <input type="text" class="form-control" id="empresa_telefone" name="empresa_telefone">
                                        </div>
                                        <div class="col-md-6 mb-3">
                                            <label class="form-label">Email</label>
                                            <input type="email" class="form-control" id="empresa_email" name="empresa_email">
                                        </div>
                                        <div class="col-12 mb-3">
                                            <label class="form-label">Endereco Completo</label>
                                            <input type="text" class="form-control" id="empresa_endereco" name="empresa_endereco">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Cidade</label>
                                            <input type="text" class="form-control" id="empresa_cidade" name="empresa_cidade">
                                        </div>
                                        <div class="col-md-4 mb-3">
                                            <label class="form-label">Estado</label>
                                            <input type="text" class="form-control" id="empresa_estado" name="empresa_estado">
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
                        <?php endif; ?>

                        <?php if ($canModalidades): ?>
                        <div class="tab-pane fade <?= $activeTab === 'modalidades' ? 'show active' : '' ?> h-100" id="modalidades">
                            <div class="config-card border-0 p-4 h-100">
                                <div class="d-flex justify-content-between align-items-center mb-4">
                                    <h5 class="section-title mb-0">Gestao de Modalidades</h5>
                                </div>

                                <div class="mb-4 d-flex gap-2 flex-wrap">
                                    <div class="d-flex gap-2 align-items-center flex-wrap">
                                        <form id="buscaModalidades" class="d-flex" role="search" onsubmit="return false;">
                                            <div class="input-group">
                                                <input id="campoBuscaModalidades" class="form-control" type="search" placeholder="Buscar modalidades..." aria-label="Buscar">
                                                <button class="btn border border-start-0" type="button" id="botaoBuscarModalidades" title="Buscar modalidades" aria-label="Buscar modalidades">
                                                    <i class="ph ph-magnifying-glass" aria-hidden="true"></i>
                                                </button>
                                            </div>
                                        </form>

                                        <div class="dropdown-center">
                                            <button class="btn btn-red color dropdown-toggle border border-white" id="filtrosModalidadesBtn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" title="Filtrar status" aria-label="Filtrar status">
                                                <i class="ph ph-funnel me-1" aria-hidden="true"></i>
                                            </button>

                                            <div class="dropdown-menu dropdown-menu-end p-3 dropdown-menu-lg" style="min-width: 250px">
                                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                                                <div class="form-check">
                                                    <input class="form-check-input filtro-status-modalidade" type="checkbox" value="Ativo" id="statusModalidadeAtivo">
                                                    <label class="form-check-label" for="statusModalidadeAtivo">Ativo</label>
                                                </div>
                                                <div class="form-check">
                                                    <input class="form-check-input filtro-status-modalidade" type="checkbox" value="Inativo" id="statusModalidadeInativo">
                                                    <label class="form-check-label" for="statusModalidadeInativo">Inativo</label>
                                                </div>
                                                <hr class="dropdown-divider">
                                                <div class="d-grid gap-2">
                                                    <button id="aplicarFiltrosModalidades" class="btn btn-sm btn-red" type="button">Aplicar Filtros</button>
                                                    <button id="limparFiltrosModalidades" class="btn btn-sm btn-red" type="button">Limpar</button>
                                                </div>
                                            </div>
                                        </div>
                                    </div>

                                    <?php if (\Core\Auth\Auth::can('modalidades.gerenciar')): ?>
                                    <a href="/ctt/admin/modalidades/cadastrar" class="btn btn-red color d-flex align-items-center border border-white" title="Cadastrar modalidade" aria-label="Cadastrar modalidade">
                                        <i class="ph ph-plus me-1" aria-hidden="true"></i> Nova Modalidade
                                    </a>
                                    <?php endif; ?>
                                </div>

                                <table id="tabelaModalidades" class="table table-hover align-middle w-100">
                                    <thead>
                                        <tr>
                                            <th class="text-start">Modalidade</th>
                                            <th class="text-center">Status</th>
                                            <th class="text-end">Acoes</th>
                                        </tr>
                                    </thead>
                                    <tbody></tbody>
                                </table>
                            </div>
                        </div>
                        <?php endif; ?>

                        <div class="tab-pane fade <?= $activeTab === 'sobre' ? 'show active' : '' ?> h-100" id="sobre">
                            <div class="config-card border-0 p-4 h-100">
                                <h5 class="section-title mb-3">Sobre o Sistema</h5>
                                <div class="d-flex align-items-center gap-3 mb-3">
                                    <i class="ph ph-barbell fs-1 text-danger" aria-hidden="true"></i>
                                    <div>
                                        <h2 class="h5 mb-1">Cross C.T</h2>
                                        <p class="text-body-secondary mb-0">Sistema de gestão do centro de treinamento: Sempre em forma</p>
                                    </div>
                                </div>
                                <p class="text-body-secondary mb-4">
                                    O Cross C.T reúne as informações e atividades do centro de treinamento em um só lugar,
                                    facilitando a organização da equipe, o acompanhamento dos alunos e a rotina administrativa.
                                </p>

                                <h3 class="h6 mb-3">Principais funcionalidades</h3>
                                <div class="row g-4">
                                    <div class="col-md-6">
                                        <h4 class="h6"><i class="ph ph-users-three me-2" aria-hidden="true"></i>Alunos e equipe</h4>
                                        <p class="small text-body-secondary mb-0">Cadastro e gerenciamento de alunos, funcionários e cargos.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="h6"><i class="ph ph-barbell me-2" aria-hidden="true"></i>Organização dos treinos</h4>
                                        <p class="small text-body-secondary mb-0">Gestão de turmas, treinos, modalidades e locais de treino.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="h6"><i class="ph ph-wallet me-2" aria-hidden="true"></i>Gestão financeira</h4>
                                        <p class="small text-body-secondary mb-0">Organização de serviços, planos, contratos e cobranças.</p>
                                    </div>
                                    <div class="col-md-6">
                                        <h4 class="h6"><i class="ph ph-chart-line me-2" aria-hidden="true"></i>Relatórios</h4>
                                        <p class="small text-body-secondary mb-0">Consulta de informações para acompanhar as atividades e apoiar as decisões da gestão.</p>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>

    <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.responsive.min.js"></script>
    <script src="/ctt/plugins/datatables/js/responsive.bootstrap5.min.js"></script>
    <script src="/ctt/plugins/popper/popper.min.js"></script>
    <script src="/ctt/plugins/bootstrap/js/bootstrap.min.js"></script>
    <script defer src="/ctt/plugins/overlayscrollbars/js/overlayscrollbars.browser.es6.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/js/admin/tabelas.js"></script>
    <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tabInicial = <?= json_encode($tabInicial, JSON_HEX_TAG | JSON_HEX_AMP | JSON_HEX_APOS | JSON_HEX_QUOT | JSON_UNESCAPED_UNICODE) ?>;
            const tabSalva = localStorage.getItem('abaConfigAtiva');
            const tabDesejada = tabInicial || tabSalva;

            if (tabDesejada) {
                const botaoTab = document.querySelector(`#configTabs [data-bs-target="#${tabDesejada}"]`);
                if (botaoTab) {
                    bootstrap.Tab.getOrCreateInstance(botaoTab).show();
                }
            }

            if (!tabInicial && tabSalva) {
                localStorage.removeItem('abaConfigAtiva');
            }

            document.querySelectorAll('#configTabs [data-bs-toggle="pill"]').forEach((botao) => {
                botao.addEventListener('shown.bs.tab', function(event) {
                    const destino = event.target.getAttribute('data-bs-target');
                    if (destino) {
                        localStorage.setItem('abaConfigAtiva', destino.replace('#', ''));
                    }
                });
            });
        });
    </script>
    <?php if ($canCargos): ?><script src="/ctt/js/admin/configuracoes/cargos.js"></script><?php endif; ?>
    <?php if ($canModalidades): ?><script src="/ctt/js/admin/configuracoes/modalidades.js"></script><?php endif; ?>
</body>

</html>
