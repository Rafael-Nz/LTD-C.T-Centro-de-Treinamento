<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Cobranças</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
  <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
  <link rel="stylesheet" href="/ctt/plugins/phosphor-icons/src/regular/style.css">
    <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
    <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <link rel="stylesheet" href="/ctt/plugins/select2/css/select2.min.css">
  <link rel="stylesheet" href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css">
    <link rel="stylesheet" href="/ctt/plugins/datatables/css/dataTables.bootstrap5.min.css">
  <link rel="stylesheet" href="/ctt/plugins/datatables/css/responsive.bootstrap5.min.css">
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
    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="/ctt/plugins/select2/js/select2.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.bootstrap5.min.js"></script>
    <script src="/ctt/plugins/datatables/js/dataTables.responsive.min.js"></script>
    <script src="/ctt/plugins/datatables/js/responsive.bootstrap5.min.js"></script>
    <script src="/ctt/js/admin/tabelas.js"></script>
    <script src="/ctt/js/admin/datatable/financeiro_cobrancas.js"></script>
    <script src="/ctt/js/admin/financeiro.js"></script>
</body>

</html>
