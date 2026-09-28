<?php $pageTitle = 'Cross C.T - Painel'; $currentPage = 'dashboard'; $pageCss = 'visao-geral.css'; ?>
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
                    <h2 class="section-title">Visão Geral</h2>
                    <p class="section-subtitle">Resumo da sua conta e atividades recentes</p>
                </div>

                <div class="stat-cards">
                    <div class="stat-card">
                        <div class="stat-icon stat-icon--red">
                            <i class="bi bi-calendar-event"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">Trimestral</span>
                            <span class="stat-label">Plano ativo · vence em 45 dias</span>
                            <span class="stat-badge stat-badge--green">Ativo</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stat-icon--green">
                            <i class="bi bi-clock"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">07:00</span>
                            <span class="stat-label">Próxima aula · Amanhã</span>
                            <span class="stat-detail">Cross Training</span>
                        </div>
                    </div>

                    <div class="stat-card">
                        <div class="stat-icon stat-icon--yellow">
                            <i class="bi bi-credit-card"></i>
                        </div>
                        <div class="stat-info">
                            <span class="stat-value">R$ 119,90</span>
                            <span class="stat-label">Pago em 01/09/2026</span>
                            <span class="stat-badge stat-badge--green">Em dia</span>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-left">
                        <div class="freq-card">
                            <div class="freq-header">
                                <h3 class="freq-title">Minha Frequência</h3>
                                <span class="freq-month">Setembro 2026</span>
                            </div>

                            <div class="freq-body">
                                <div class="freq-count">
                                    <span class="freq-number">16</span>
                                    <span class="freq-total">de 20 treinos</span>
                                </div>

                                <div class="freq-progress-wrap">
                                    <div class="freq-progress-bar">
                                        <div class="freq-progress-fill" style="width: 80%"></div>
                                    </div>
                                    <div class="freq-stats">
                                        <span>Dias restantes: 12</span>
                                        <span>Média semanal: 4,2</span>
                                    </div>
                                </div>
                            </div>

                            <hr class="freq-divider">

                            <div class="freq-trend">
                                <div class="freq-trend-icon">↑</div>
                                <span class="freq-trend-text">3 treinos a mais que agosto</span>
                            </div>
                        </div>

                        <div class="quick-card">
                            <h3 class="quick-title">Acesso Rápido</h3>
                            <a href="#" class="quick-link">
                                <div class="quick-icon quick-icon--red"><i class="bi bi-person"></i></div>
                                <span>Informações Pessoais</span>
                            </a>
                            <a href="#" class="quick-link">
                                <div class="quick-icon quick-icon--yellow"><i class="bi bi-currency-dollar"></i></div>
                                <span>Financeiro</span>
                            </a>
                            <a href="#" class="quick-link">
                                <div class="quick-icon quick-icon--green"><i class="bi bi-clock"></i></div>
                                <span>Horários</span>
                            </a>
                        </div>
                    </div>

                    <div class="content-right">
                        <div class="email-card">
                            <span class="email-label">AÇÃO NECESSÁRIA</span>
                            <h4 class="email-title">Confirme seu e-mail</h4>
                            <p class="email-text">Enviamos um link para</p>
                            <p class="email-address">carlos.silva@email.com</p>
                            <button class="btn-resend">Reenviar e-mail</button>
                        </div>

                        <div class="contact-card">
                            <h4 class="contact-title">Fale com o CT</h4>
                            <a href="#" class="btn-whatsapp">WhatsApp</a>
                        </div>

                        <div class="eval-card">
                            <h4 class="eval-title">Última Avaliação</h4>
                            <span class="eval-date">Agosto 2026</span>

                            <div class="eval-item">
                                <span class="eval-label">Peso</span>
                                <span class="eval-value">78,4 kg</span>
                            </div>
                            <div class="eval-item">
                                <span class="eval-label">Gordura Corporal</span>
                                <span class="eval-value">16,2 %</span>
                            </div>
                            <div class="eval-item">
                                <span class="eval-label">Massa Muscular</span>
                                <span class="eval-value">65,7 kg</span>
                            </div>
                            <div class="eval-item">
                                <span class="eval-label">IMC</span>
                                <span class="eval-value">24,1</span>
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
