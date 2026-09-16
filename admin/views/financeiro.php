<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Financeiro</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <div class="d-flex justify-content-between align-items-center flex-wrap gap-3 mb-4">
                <h1 class="h4 mb-0">Visão geral</h1>
            </div>

            <div class="row g-3 mb-4" id="financeiroResumo">
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body"><span class="text-muted small">Em aberto</span>
                            <div class="h4 mb-0 mt-2" id="totalAberto">R$ 0,00</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body"><span class="text-muted small">Vencido</span>
                            <div class="h4 text-danger mb-0 mt-2" id="totalVencido">R$ 0,00</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body"><span class="text-muted small">Recebido</span>
                            <div class="h4 text-success mb-0 mt-2" id="totalPago">R$ 0,00</div>
                        </div>
                    </div>
                </div>
                <div class="col-sm-6 col-xl-3">
                    <div class="card border-0 shadow-sm h-100">
                        <div class="card-body"><span class="text-muted small">Cobranças</span>
                            <div class="h4 mb-0 mt-2" id="totalCobrancas">0</div>
                        </div>
                    </div>
                </div>
            </div>

            <div class="card border-0 p-2 shadow-sm mb-4">
                <div class="card-header bg-body border-0">
                    <h2 class="h6 mb-3">Contratos de alunos</h2>
                    <div class="d-flex gap-2 flex-wrap">
                        <div class="input-group w-auto" role="search">
                            <input id="campoBuscaContratos" class="form-control" type="search" placeholder="Buscar aluno ou plano..." aria-label="Buscar contratos">
                            <button class="btn border border-start-0" type="button" id="botaoBuscarContratos" aria-label="Buscar"><i class="ph ph-magnifying-glass"></i></button>
                        </div>
                        <div class="dropdown-center">
                            <button id="filtrosContratosBtn" class="btn btn-red dropdown-toggle color border border-white" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Filtrar contratos"><i class="ph ph-funnel me-1"></i></button>
                            <div class="dropdown-menu p-3 dropdown-menu-lg" style="min-width: 280px" aria-labelledby="filtrosContratosBtn">
                                <label for="filtroPlanoContrato" class="form-label small">Plano</label>
                                <select class="form-select" id="filtroPlanoContrato"><option></option></select>
                                <hr class="dropdown-divider">
                                <p class="h6 small">Status</p>
                                <?php foreach (['ativo' => 'Ativo', 'pausado' => 'Pausado', 'encerrado' => 'Encerrado', 'cancelado' => 'Cancelado'] as $valor => $rotulo): ?>
                                    <div class="form-check"><input class="form-check-input filtro-status-contrato" type="checkbox" value="<?= $valor ?>" id="contratoStatus-<?= $valor ?>"><label class="form-check-label" for="contratoStatus-<?= $valor ?>"><?= $rotulo ?></label></div>
                                <?php endforeach; ?>
                                <hr class="dropdown-divider">
                                <div class="d-flex gap-2 mt-3"><button id="aplicarFiltrosContratos" class="btn btn-sm btn-red">Aplicar filtros</button><button id="limparFiltrosContratos" class="btn btn-sm btn-outline-secondary">Limpar</button></div>
                            </div>
                        </div>
                        <a href="/ctt/admin/financeiro/contratos/cadastrar" class="btn btn-red d-flex align-items-center color border border-white"><i class="ph ph-plus me-1"></i>Novo contrato</a>
                    </div>
                </div>
                <div class="card-body">
                        <table id="tabelaContratos" class="table table-hover align-middle w-100" aria-label="Contratos de alunos">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Plano</th>
                                    <th>Início</th>
                                    <th>Vencimento</th>
                                    <th>Status</th>
                                    <th class="text-end">Valor</th>
                                    <th class="text-center">Ações</th>
                                </tr>
                            </thead>
                            <tbody></tbody>
                        </table>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/js/admin/tabelas.js"></script>
    <script src="/ctt/js/admin/datatable/financeiro_contratos.js"></script>
    <script src="/ctt/js/admin/financeiro.js"></script>
</body>

</html>
