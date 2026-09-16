<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Novo contrato</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/@phosphor-icons/web@2.1.1/src/regular/style.css">
    <link href="https://fonts.googleapis.com/css2?family=Manrope:wght@200..800&display=swap" rel="stylesheet">
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>
    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <h1 class="h4 mb-0">Novo contrato financeiro</h1><a href="/ctt/admin/financeiro" class="btn btn-outline-secondary">Voltar</a>
            </div>
            <div class="card border-0 shadow-sm">
                <div class="card-body">
                    <form id="formContrato">
                        <div class="row g-3">
                            <div class="col-md-6"><label class="form-label" for="alunoId">Aluno</label><select class="form-select" id="alunoId" required>
                                    <option value="">Carregando alunos...</option>
                                </select></div>
                            <div class="col-md-6"><label class="form-label" for="planoId">Plano</label><select class="form-select" id="planoId">
                                    <option value="">Sem plano</option>
                                </select></div>
                            <div class="col-md-4"><label class="form-label" for="dataInicio">Data de início</label><input class="form-control" id="dataInicio" type="date" required></div>
                            <div class="col-md-4"><label class="form-label" for="diaVencimento">Dia de vencimento</label><input class="form-control" id="diaVencimento" type="number" min="1" max="28" value="10" required></div>
                            <div class="col-md-4"><label class="form-label" for="valorContratado">Valor contratado</label><input class="form-control" id="valorContratado" type="number" min="0" step="0.01" required></div>
                            <div class="col-md-4"><label class="form-label" for="desconto">Desconto</label><input class="form-control" id="desconto" type="number" min="0" step="0.01" value="0"></div>
                            <div class="col-md-4"><label class="form-label" for="multa">Multa (%)</label><input class="form-control" id="multa" type="number" min="0" step="0.01" value="0"></div>
                            <div class="col-md-4"><label class="form-label" for="juros">Juros (%)</label><input class="form-control" id="juros" type="number" min="0" step="0.01" value="0"></div>
                            <div class="col-md-4"><label class="form-label" for="dataFim">Data de término (opcional)</label><input class="form-control" id="dataFim" type="date"></div>
                            <div class="col-md-4"><label class="form-label" for="periodicidade">Periodicidade sem plano</label><select class="form-select" id="periodicidade"><option value="mensal">Mensal</option><option value="trimestral">Trimestral</option><option value="semestral">Semestral</option><option value="anual">Anual</option><option value="avulso">Avulso</option></select></div>
                            <div class="col-12"><label class="form-label" for="observacoes">Observações</label><textarea class="form-control" id="observacoes" rows="3"></textarea></div>
                        </div>
                        <div class="d-flex justify-content-end mt-4"><button class="btn btn-red color" type="submit"><i class="ph ph-check me-1"></i>Criar contrato</button></div>
                    </form>
                </div>
            </div>
        </div>
    </main>
    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/js/admin/contrato_form.js"></script>
</body>

</html>
