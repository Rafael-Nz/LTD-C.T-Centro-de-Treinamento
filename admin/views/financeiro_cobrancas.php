<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Cobranças</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <h1 class="h4 mb-4">Cobranças</h1>
            <div class="card border-0 p-2 shadow-sm mb-4">
                <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="d-flex" role="search"><div class="input-group">
                            <input id="campoBuscaCobrancas" class="form-control" type="search" placeholder="Buscar cobrança..." aria-label="Buscar cobrança">
                            <button class="btn border border-start-0" id="botaoBuscarCobrancas" type="button" aria-label="Buscar"><i class="ph ph-magnifying-glass"></i></button>
                        </div></div>
                        <div class="dropdown-center">
                            <button class="btn btn-red dropdown-toggle color border border-white" id="filtrosCobrancasBtn" type="button" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Filtrar cobranças"><i class="ph ph-funnel me-1"></i></button>
                            <div class="dropdown-menu p-3 dropdown-menu-lg" style="min-width: 250px">
                                <p class="h6 text-start" style="font-size: 0.875rem">Status</p>
                                <?php foreach (['aberta' => 'Aberta', 'paga' => 'Paga', 'vencida' => 'Vencida', 'cancelada' => 'Cancelada'] as $valor => $rotulo): ?>
                                <div class="form-check">
                                    <input class="form-check-input filtro-status-cobranca" type="checkbox" value="<?= $valor ?>" id="cobranca-<?= $valor ?>">
                                    <label class="form-check-label" for="cobranca-<?= $valor ?>"><?= $rotulo ?></label>
                                </div>
                                <?php endforeach; ?>
                                <hr class="dropdown-divider">
                                <div class="d-grid gap-2">
                                    <button class="btn btn-sm btn-red" id="aplicarFiltrosCobrancas" type="button">Aplicar Filtros</button>
                                    <button class="btn btn-sm btn-red" id="limparFiltrosCobrancas" type="button">Limpar</button>
                                </div>
                            </div>
                        </div>
                    </div>
                <button class="btn btn-red d-flex align-items-center color border border-white" id="atualizarVencidas" type="button">
                    <i class="ph ph-arrows-clockwise me-1"></i>Atualizar vencidas
                </button>
            </div>

                <div class="card-body">
                        <table id="tabelaCobrancas" class="table table-hover align-middle w-100 mb-0" aria-label="Lista de cobranças">
                            <thead>
                                <tr>
                                    <th>Aluno</th>
                                    <th>Descrição</th>
                                    <th>Competência</th>
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
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.min.js"></script>
    <script src="/ctt/js/admin/tabelas.js"></script>
    <script src="/ctt/js/admin/datatable/financeiro_cobrancas.js"></script>
    <script src="/ctt/js/admin/financeiro.js"></script>
</body>

</html>
