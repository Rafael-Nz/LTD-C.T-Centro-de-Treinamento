<!DOCTYPE html>
<html lang="pt-BR" data-bs-theme="light">

<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Cross C.T | Novo Contrato</title>
    <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/css/admin-styles.css">
    <link rel="stylesheet" href="/ctt/css/sidebar.css">
    <link rel="stylesheet" href="/ctt/plugins/phosphor-icons/src/regular/style.css">
    <link href="/ctt/plugins/fonts/manrope.css" rel="stylesheet">
    <link rel="stylesheet" href="/ctt/plugins/select2/css/select2.min.css">
    <link rel="stylesheet" href="/ctt/plugins/select2/css/select2-bootstrap-5-theme.min.css">
    <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
</head>

<body class="d-flex flex-column min-vh-100">
    <?php include __DIR__ . '/partials/sidebar.php'; ?>
    <?php include __DIR__ . '/partials/header.php'; ?>

    <main class="flex-fill d-flex" id="mainContent">
        <div class="container-lg p-4 d-flex flex-column flex-fill">
            <div class="d-flex justify-content-between align-items-center mb-4">
                <div>
                    <h1 class="h4 mb-1">Novo Contrato</h1>
                    <span class="text-muted small">Vincule um aluno a um plano comercial ou contrate serviços avulsos</span>
                </div>
                <a href="/ctt/admin/financeiro" class="btn btn-outline-secondary">
                    <i class="ph ph-arrow-left me-1"></i>Voltar
                </a>
            </div>

            <div class="card shadow-sm border-0 d-flex flex-fill">
                <div class="card-body p-4">
                    <form id="formContrato">
                        <div class="row gy-4">

                            <!-- Seção 1: Aluno e Modalidade -->
                            <div class="col-12">
                                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">1. Aluno e Modalidade de Contratação</h3>
                                <div class="row g-3">
                                    <div class="col-md-6">
                                        <label for="alunoId" class="form-label fw-semibold">Aluno <span class="text-danger">*</span></label>
                                        <select class="form-select" id="alunoId" required>
                                            <option value="">Carregando lista de alunos...</option>
                                        </select>
                                    </div>

                                    <div class="col-md-6">
                                        <label class="form-label fw-semibold">Tipo de Contratação <span class="text-danger">*</span></label>
                                        <div class="btn-group w-100" role="group" aria-label="Tipo de contratação">
                                            <input type="radio" class="btn-check" name="tipoContratacao" id="tipoPlano" value="plano" checked autocomplete="off">
                                            <label class="btn btn-outline-danger" for="tipoPlano">
                                                <i class="ph ph-stack me-1"></i>Plano Comercial
                                            </label>

                                            <input type="radio" class="btn-check" name="tipoContratacao" id="tipoAvulso" value="avulso" autocomplete="off">
                                            <label class="btn btn-outline-danger" for="tipoAvulso">
                                                <i class="ph ph-tag me-1"></i>Serviços Avulsos
                                            </label>
                                        </div>
                                    </div>

                                    <!-- Campo de seleção de plano comercial (visível quando tipoContratacao === 'plano') -->
                                    <div class="col-12" id="boxSelecaoPlano">
                                        <label for="planoId" class="form-label fw-semibold">
                                            Plano Comercial <span class="text-danger">*</span>
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Ao escolher um plano, os serviços e valores correspondentes serão carregados automaticamente."></i>
                                        </label>
                                        <select class="form-select" id="planoId">
                                            <option value="">Selecione um plano comercial...</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                            <!-- Seção 2: Serviços Contratados -->
                            <div class="col-12">
                                <div class="d-flex justify-content-between align-items-center mb-3 border-bottom border-1 pb-1">
                                    <div>
                                        <h3 class="h6 mb-0 section-title">2. Serviços Contratados</h3>
                                        <span class="text-muted small">Serviços que compõem este contrato</span>
                                    </div>
                                    <button type="button" class="btn btn-sm btn-outline-danger" id="btnAdicionarServicoContrato">
                                        <i class="ph ph-plus me-1"></i>Adicionar serviço
                                    </button>
                                </div>

                                <div class="table-responsive">
                                    <table class="table table-hover align-middle mb-2" id="tabelaItensContrato">
                                        <thead class="table-light">
                                            <tr>
                                                <th style="min-width: 320px;">Serviço</th>
                                                <th style="width: 110px;" class="text-center">Quantidade</th>
                                                <th style="width: 160px;" class="text-end">Valor Unitário (R$)</th>
                                                <th style="width: 150px;" class="text-end">Desconto (R$)</th>
                                                <th style="width: 160px;" class="text-end">Subtotal Líquido</th>
                                                <th style="width: 60px;" class="text-center">Ação</th>
                                            </tr>
                                        </thead>
                                        <tbody id="itensContratoContainer">
                                            <!-- Linhas inseridas dinamicamente via JS -->
                                        </tbody>
                                        <tfoot class="border-top">
                                            <tr>
                                                <th colspan="4" class="text-end fw-semibold">Total dos Serviços:</th>
                                                <th class="text-end fw-bold fs-6 text-danger" id="somaItensContrato">R$ 0,00</th>
                                                <th></th>
                                            </tr>
                                        </tfoot>
                                    </table>
                                </div>

                                <div class="d-flex justify-content-between align-items-center mt-2 flex-wrap gap-2">
                                    <span class="text-muted small" id="mensagemItensContrato">Nenhum serviço adicionado.</span>
                                    <button type="button" class="btn btn-sm btn-light border" id="btnUsarSomaContrato" style="display: none;">
                                        <i class="ph ph-arrow-circle-down me-1"></i>Definir Valor Contratado igual à soma dos serviços (<span id="valorSomaContratoSugerido">R$ 0,00</span>)
                                    </button>
                                </div>
                            </div>

                            <!-- Seção 3: Condições Financeiras e Vigência -->
                            <div class="col-12">
                                <h3 class="h6 mb-3 section-title border-bottom border-1 pb-1">3. Condições Financeiras e Vigência</h3>
                                <div class="row g-3">
                                    <div class="col-md-4">
                                        <label for="dataInicio" class="form-label">Data de início <span class="text-danger">*</span></label>
                                        <input type="date" class="form-control" id="dataInicio" required>
                                    </div>

                                    <div class="col-md-4">
                                        <label for="dataFim" class="form-label">
                                            Data de término <span class="text-muted small">(opcional)</span>
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Deixe em branco para contrato indeterminado."></i>
                                        </label>
                                        <input type="date" class="form-control" id="dataFim">
                                    </div>

                                    <div class="col-md-4">
                                        <label for="periodicidade" class="form-label">Periodicidade <span class="text-danger">*</span></label>
                                        <select class="form-select" id="periodicidade" required>
                                            <option value="mensal">Mensal</option>
                                            <option value="trimestral">Trimestral</option>
                                            <option value="semestral">Semestral</option>
                                            <option value="anual">Anual</option>
                                            <option value="avulso">Avulso</option>
                                        </select>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="diaVencimento" class="form-label">
                                            Dia de vencimento <span class="text-danger">*</span>
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Dia de vencimento da cobrança. (1 a 28)."></i>
                                        </label>
                                        <input type="number" class="form-control" id="diaVencimento" min="1" max="28" value="10" required>
                                    </div>

                                    <div class="col-md-3">
                                        <label for="valorContratado" class="form-label">Valor contratado (R$) <span class="text-danger">*</span></label>
                                        <input type="number" class="form-control" id="valorContratado" min="0" step="0.01" required placeholder="0,00">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="desconto" class="form-label">
                                            Desconto total (R$)
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Desconto total concedido no valor do contrato."></i>
                                        </label>
                                        <input type="number" class="form-control" id="desconto" min="0" step="0.01" value="0.00">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="multa" class="form-label">
                                            Multa por atraso (%)
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Percentual cobrado em caso de pagamento após o vencimento."></i>
                                        </label>
                                        <input type="number" class="form-control" id="multa" min="0" max="100" step="0.01" value="2.00">
                                    </div>

                                    <div class="col-md-2">
                                        <label for="juros" class="form-label">
                                            Juros ao mês (%)
                                            <i class="ph ph-info text-muted ms-1" data-bs-toggle="tooltip" data-bs-placement="top" title="Taxa mensal de juros para dias em atraso."></i>
                                        </label>
                                        <input type="number" class="form-control" id="juros" min="0" max="100" step="0.01" value="1.00">
                                    </div>

                                    <div class="col-12 mt-2">
                                        <label for="observacoes" class="form-label">Observações</label>
                                        <textarea class="form-control" id="observacoes" rows="2" placeholder="Informações ou termos específicos do contrato..."></textarea>
                                    </div>
                                </div>
                            </div>

                        </div>

                        <div class="d-flex justify-content-end gap-2 mt-4 pt-3 border-top">
                            <a href="/ctt/admin/financeiro" class="btn btn-outline-secondary">Cancelar</a>
                            <button class="btn btn-red color" type="submit" id="btnSalvarContrato">
                                <i class="ph ph-check me-1"></i>Criar contrato
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
    </main>

    <?php include __DIR__ . '/partials/footer.php'; ?>
    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/ctt/plugins/jquery/jquery-3.7.1.min.js"></script>
    <script src="/ctt/plugins/select2/js/select2.min.js"></script>
    <script defer src="/ctt/js/admin/sidebar.js"></script>
    <script src="/ctt/js/admin/contrato_form.js"></script>
</body>

</html>
