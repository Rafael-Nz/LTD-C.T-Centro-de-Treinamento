<?php
if (!isset($catalogo, $formulario) || !in_array($catalogo, ['servicos', 'planos'], true)) {
    http_response_code(404);
    exit;
}
$servico = $catalogo === 'servicos';
$titulo = $servico ? 'Serviços' : 'Planos';
$novo = $servico ? 'Novo serviço' : 'Novo plano';
$id = $formulario ? max(0, (int) ($_GET['id'] ?? 0)) : 0;
$tituloForm = ($id ? 'Editar ' : 'Cadastrar ') . ($servico ? 'Serviço' : 'Plano');
$categoria = $servico ? 'Tipo' : 'Periodicidade';
$opcoes = $servico
    ? ['mensalidade' => 'Mensalidade', 'avaliacao' => 'Avaliação', 'personal' => 'Personal', 'taxa' => 'Taxa', 'outro' => 'Outro']
    : ['mensal' => 'Mensal', 'trimestral' => 'Trimestral', 'semestral' => 'Semestral', 'anual' => 'Anual', 'avulso' => 'Avulso'];
?>
<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | <?= $formulario ? $tituloForm : $titulo ?></title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
    <?php if ($formulario): ?>
    <link rel="stylesheet" href="/ctt/css/form.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/select2-bootstrap-5-theme@1.3.0/dist/select2-bootstrap-5-theme.min.css">
    <?php else: ?>
    <link rel="stylesheet" href="https://cdn.datatables.net/2.3.4/css/dataTables.bootstrap5.min.css">
    <link rel="stylesheet" href="https://cdn.datatables.net/responsive/3.0.7/css/responsive.bootstrap5.min.css">
    <?php endif; ?>
    <script src="https://cdn.jsdelivr.net/npm/sweetalert2@11"></script>
</head>
<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/sidebar.php'; ?>
    <?php include __DIR__ . '/header.php'; ?>
    <main class="flex-fill d-flex" id="mainContent" data-catalogo="<?= $catalogo ?>">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <h1 class="h4 mb-4"><?= $formulario ? $tituloForm : $titulo ?></h1>
            <?php if ($formulario): ?>
            <div class="card shadow-sm d-flex flex-fill"><div class="card-body">
                <form id="formCatalogo" data-id="<?= $id ?>">
                    <div class="row gy-4"><div class="col-12">
                    <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">Informações do <?= $servico ? 'Serviço' : 'Plano' ?></h3>
                    <div class="row g-3 mb-3">
                        <div class="col-12">
                            <label class="form-label" for="catalogoNome">Nome <span class="text-danger">*</span></label>
                            <input class="form-control" id="catalogoNome" name="nome" maxlength="120" placeholder="<?= $servico ? 'Ex: Avaliação física' : 'Ex: Plano mensal' ?>" required>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="catalogoCategoria"><?= $categoria ?> <span class="text-danger">*</span></label>
                            <select class="form-select" id="catalogoCategoria" required>
                                <?php foreach ($opcoes as $valor => $rotulo): ?><option value="<?= $valor ?>"><?= $rotulo ?></option><?php endforeach; ?>
                            </select>
                        </div>
                        <div class="col-md-6">
                            <label class="form-label" for="catalogoValor"><?= $servico ? 'Valor base' : 'Valor' ?> (R$) <span class="text-danger">*</span></label>
                            <input class="form-control" id="catalogoValor" type="number" min="0" max="99999999.99" step="0.01" required>
                        </div>
                        <div class="col-12">
                            <label class="form-label" for="catalogoDescricao">Descrição</label>
                            <textarea class="form-control" id="catalogoDescricao" rows="3" maxlength="5000"></textarea>
                        </div>
                        <?php if ($servico): ?>
                        <div class="col-md-6">
                            <label class="form-label" for="catalogoRecorrente">Serviço recorrente</label>
                            <select class="form-select" id="catalogoRecorrente" name="recorrente">
                                <option value="1" selected>Sim</option>
                                <option value="0">Não</option>
                            </select>
                        </div>
                        <?php endif; ?>
                    </div>
                    </div></div>
                    <div class="d-flex justify-content-end gap-2 mt-4">
                        <a class="btn btn-red color" href="/ctt/admin/financeiro/<?= $catalogo ?>">Voltar</a>
                        <button class="btn btn-red color" id="salvarCatalogo" type="submit"><?= $id ? 'Salvar alterações' : $tituloForm ?></button>
                    </div>
                </form>
            </div></div>
            <?php else: ?>
            <div class="card border-0 p-2 shadow-sm mb-4">
                <div class="card-header bg-body border-0 d-flex gap-2 flex-wrap">
                    <div class="d-flex gap-2 align-items-center flex-wrap">
                        <div class="d-flex" role="search"><div class="input-group">
                            <input class="form-control" type="search" id="campoBuscaCatalogo" placeholder="Buscar <?= $servico ? 'serviço' : 'plano' ?>..." aria-label="Buscar <?= strtolower($titulo) ?>">
                            <button class="btn border border-start-0" id="botaoBuscarCatalogo" type="button" aria-label="Buscar"><i class="ph ph-magnifying-glass"></i></button>
                        </div></div>
                        <div class="dropdown-center">
                            <button class="btn btn-red dropdown-toggle color border border-white" id="filtrosCatalogoBtn" data-bs-toggle="dropdown" data-bs-auto-close="outside" aria-expanded="false" aria-label="Filtrar <?= strtolower($titulo) ?>" title="Filtrar <?= strtolower($titulo) ?>" type="button"><i class="ph ph-funnel me-1"></i></button>
                            <div class="dropdown-menu p-3 dropdown-menu-lg" style="min-width: 250px">
                                <label class="form-label" for="filtroCategoria"><?= $categoria ?></label>
                                <select class="form-select mb-3" id="filtroCategoria">
                                    <option value="">Todos</option>
                                    <?php foreach ($opcoes as $valor => $rotulo): ?><option value="<?= $valor ?>"><?= $rotulo ?></option><?php endforeach; ?>
                                </select>
                                <label class="form-label" for="filtroAtivo">Status</label>
                                <select class="form-select mb-3" id="filtroAtivo"><option value="">Todos</option><option value="1">Ativo</option><option value="0">Inativo</option></select>
                                <hr class="dropdown-divider">
                                <div class="d-flex gap-2"><button class="btn btn-sm btn-red" id="aplicarFiltrosCatalogo" type="button">Aplicar Filtros</button><button class="btn btn-sm btn-red" id="limparFiltrosCatalogo" type="button">Limpar</button></div>
                            </div>
                        </div>
                    </div>
                    <a class="btn btn-red d-flex align-items-center color border border-white" href="/ctt/admin/financeiro/<?= $catalogo ?>/cadastrar"><i class="ph ph-plus me-1"></i><?= $servico ? 'Novo Serviço' : 'Novo Plano' ?></a>
                </div>
                <div class="card-body">
                    <table class="table table-hover align-middle w-100 mb-0" id="tabelaCatalogo" aria-label="Lista de <?= $titulo ?>">
                        <thead><tr><th scope="col" class="text-start">Nome</th><th scope="col"><?= $categoria ?></th><th scope="col" class="text-end">Valor</th><th scope="col" class="text-center">Status</th><th scope="col" class="text-center">Ações</th></tr></thead>
                        <tbody></tbody>
                    </table>
                </div>
            </div>
            <?php endif; ?>
        </div>
    </main>
    <?php include __DIR__ . '/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="https://code.jquery.com/jquery-3.7.1.min.js"></script>
    <?php if ($formulario): ?>
    <script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
    <?php else: ?>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.min.js"></script>
    <script src="https://cdn.datatables.net/2.3.4/js/dataTables.bootstrap5.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/dataTables.responsive.min.js"></script>
    <script src="https://cdn.datatables.net/responsive/3.0.7/js/responsive.bootstrap5.min.js"></script>
    <script src="/ctt/js/admin/tabelas.js"></script>
    <?php endif; ?>
    <script src="/ctt/js/admin/financeiro_catalogo.js"></script>
</body>
</html>
