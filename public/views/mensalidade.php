<?php
$pageTitle = 'Cross C.T - Mensalidade';
$currentPage = 'mensalidade';
$pageCss = 'mensalidade.css';

$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$hojeTexto = $diasSemana[(int)date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('m')] . ' de ' . date('Y');
$usuarioNome = htmlspecialchars(\Core\Auth\Auth::user('nome') ?? 'Aluno');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
    <div class="d-flex vh-100">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <div class="d-flex flex-column flex-grow-1 min-width-0">
            <main class="dashboard-main">
                <div class="greeting">
                    <button class="btn-menu d-lg-none" id="btnMenu" aria-label="Abrir menu">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <p class="greeting-text">Olá, <?= $usuarioNome ?></p>
                        <p class="greeting-date"><?= $hojeTexto ?></p>
                    </div>
                </div>

                <div class="section-header">
                    <h2 class="section-title">Mensalidade</h2>
                    <p class="section-subtitle">Gerencie seus pagamentos e formas de pagamento</p>
                </div>

                <div class="content-grid">
                    <div class="content-left">
                        <div class="plan-card">
                            <h3 class="plan-title">Plano Atual</h3>
                            <p class="plan-subtitle">Detalhes da sua assinatura</p>

                            <div class="plan-detail">
                                <span class="plan-label">Plano</span>
                                <span class="plan-value" id="alunoPlanoNome">Carregando plano...</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Valor contratado</span>
                                <span class="plan-value" id="alunoPlanoValor">R$ 0,00</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Vencimento</span>
                                <span class="plan-value" id="alunoPlanoVencimento">—</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Situação</span>
                                <span class="status-badge status-badge--green" id="alunoPlanoStatus">Consultando...</span>
                            </div>
                        </div>

                        <div class="history-card">
                            <h3 class="history-title">Histórico de Cobranças</h3>
                            <p class="history-subtitle">Faturas emitidas e pagamentos</p>

                            <div class="history-list" id="historicoPagamentos">
                                <p class="text-muted small my-3">Carregando histórico financeiro...</p>
                            </div>
                        </div>
                    </div>

                    <div class="content-right">
                        <div class="payment-card" id="cardPagarMensalidade">
                            <p class="text-muted small py-3">Consultando cobranças em aberto...</p>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="/ctt/plugins/sweetalert2/sweetalert2.all.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
    <script src="<?= PUBLIC_URL ?>js/aluno/mensalidade.js"></script>
</body>
</html>
