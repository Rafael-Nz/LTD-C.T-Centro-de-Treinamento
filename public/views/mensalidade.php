<?php $pageTitle = 'Cross C.T - Mensalidade'; $currentPage = 'mensalidade'; $pageCss = 'mensalidade.css'; ?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php include __DIR__ . '/partials/head.php'; ?>
<body>
    <div class="d-flex vh-100">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <div class="d-flex flex-column flex-grow-1 min-width-0">
            <main class="dashboard-main">
                <div class="greeting">
                    <button class="btn-menu d-lg-none" id="btnMenu">
                        <i class="bi bi-list"></i>
                    </button>
                    <div>
                        <p class="greeting-text">Bem vindo</p>
                        <p class="greeting-date">Segunda-feira, 15 de Setembro de 2026</p>
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
                                <span class="plan-value">Trimestral</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Valor mensal</span>
                                <span class="plan-value">R$ 119,90</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Próximo vencimento</span>
                                <span class="plan-value">01/10/2026</span>
                            </div>
                            <div class="plan-detail">
                                <span class="plan-label">Status</span>
                                <span class="status-badge status-badge--green">Em dia</span>
                            </div>
                        </div>

                        <div class="history-card">
                            <h3 class="history-title">Histórico de Pagamentos</h3>
                            <p class="history-subtitle">Últimos meses</p>

                            <div class="history-list">
                                <div class="history-item">
                                    <div>
                                        <span class="history-month">Outubro 2026</span>
                                        <span class="history-info">01/10/2026 · R$ 119,90</span>
                                    </div>
                                    <span class="status-badge status-badge--yellow">A vencer</span>
                                </div>
                                <div class="history-item">
                                    <div>
                                        <span class="history-month">Setembro 2026</span>
                                        <span class="history-info">01/09/2026 · R$ 119,90</span>
                                    </div>
                                    <span class="status-badge status-badge--green">Pago</span>
                                </div>
                                <div class="history-item">
                                    <div>
                                        <span class="history-month">Agosto 2026</span>
                                        <span class="history-info">01/08/2026 · R$ 119,90</span>
                                    </div>
                                    <span class="status-badge status-badge--green">Pago</span>
                                </div>
                                <div class="history-item">
                                    <div>
                                        <span class="history-month">Julho 2026</span>
                                        <span class="history-info">01/07/2026 · R$ 119,90</span>
                                    </div>
                                    <span class="status-badge status-badge--green">Pago</span>
                                </div>
                                <div class="history-item">
                                    <div>
                                        <span class="history-month">Junho 2026</span>
                                        <span class="history-info">01/06/2026 · R$ 119,90</span>
                                    </div>
                                    <span class="status-badge status-badge--red">Vencido</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content-right">
                        <div class="payment-card">
                            <h3 class="payment-title">Pagar Mensalidade</h3>
                            <p class="payment-ref">Outubro 2026 · R$ 119,90</p>
                            <p class="payment-choose">Escolha a forma de pagamento</p>

                            <div class="payment-option">
                                <h4 class="payment-method">PIX</h4>
                                <p class="payment-desc">Aprovado na hora</p>
                                <button class="btn-pay btn-pay--pix">Pagar com PIX</button>
                            </div>

                            <div class="payment-option">
                                <h4 class="payment-method">Cartão de Crédito</h4>
                                <p class="payment-desc">Visa, Mastercard, Elo</p>
                                <button class="btn-pay btn-pay--card">Pagar com Cartão</button>
                            </div>

                            <div class="payment-option">
                                <h4 class="payment-method">Boleto Bancário</h4>
                                <p class="payment-desc">Vencimento em 3 dias úteis</p>
                                <button class="btn-pay btn-pay--boleto">Gerar Boleto</button>
                            </div>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
</body>
</html>
