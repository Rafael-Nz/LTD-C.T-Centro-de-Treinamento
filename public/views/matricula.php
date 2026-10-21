<?php
$pageTitle = 'Cross C.T - Matrícula';
$currentPage = 'matricula';
$pageCss = 'matricula.css';

$diasSemana = ['Domingo', 'Segunda-feira', 'Terça-feira', 'Quarta-feira', 'Quinta-feira', 'Sexta-feira', 'Sábado'];
$meses = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
$hojeTexto = $diasSemana[(int)date('w')] . ', ' . date('d') . ' de ' . $meses[(int)date('m')] . ' de ' . date('Y');
$usuarioNome = htmlspecialchars(\Core\Auth\Auth::aluno()->user('nome') ?? $_SESSION['auth_aluno']['user_nome'] ?? $_SESSION['user_nome'] ?? 'Aluno');
?>
<!DOCTYPE html>
<html lang="pt-BR">
<?php include __DIR__ . '/partials/head.php'; ?>

<body>
    <div class="d-flex vh-100">
        <?php include __DIR__ . '/partials/sidebar.php'; ?>

        <div class="d-flex flex-column flex-grow-1 min-width-0">
            <main class="dashboard-main">
                <div class="section-header">
                    <h2 class="section-title">Minha Matrícula</h2>
                    <p class="section-subtitle">Acompanhe seu registro cadastral, plano contratado e turmas ativas</p>
                </div>

                <div class="matricula-badge-card mb-4">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="matricula-label">CÓDIGO DE MATRÍCULA</div>
                            <div class="matricula-code" id="matriculaCodigo">CARREGANDO...</div>
                            <div class="matricula-date">
                                Titular: <strong id="matriculaAlunoNome">—</strong> · Matrícula realizada em <strong id="matriculaData">—</strong>
                            </div>
                        </div>
                        <div class="d-flex flex-column align-items-end gap-2">
                            <span class="status-pill status-pill--active" id="matriculaStatus">Ativo</span>
                            <small class="text-white-50">Tempo de CT: <span id="matriculaTempo">—</span></small>
                        </div>
                    </div>
                </div>

                <div class="content-grid">
                    <div class="content-left">
                        <!-- Turmas Inscritas -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-collection"></i> Turmas Inscritas</h3>
                                <a href="/ctt/horarios" class="btn btn-sm btn-outline-danger">
                                    <i class="bi bi-calendar3 me-1"></i> Grade Completa
                                </a>
                            </div>
                            <div class="d-flex flex-column gap-3" id="listaTurmasMatricula">
                                <p class="text-muted small my-3">Carregando turmas vinculadas...</p>
                            </div>
                        </div>

                        <!-- Ficha Médica / Anamnese -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-clipboard2-pulse"></i> Ficha Médica e Anamnese</h3>
                                <span class="status-pill status-pill--pending" id="anamneseStatus">Consultando...</span>
                            </div>
                            <div class="anamnese-box d-flex align-items-center gap-3">
                                <div id="anamneseIcon">
                                    <i class="bi bi-clipboard-pulse text-muted fs-2"></i>
                                </div>
                                <div class="flex-grow-1">
                                    <h4 class="h6 mb-1 text-dark fw-bold">Questionário de Aptidão Física</h4>
                                    <p class="small text-muted mb-0" id="anamneseDesc">
                                        Verificando histórico de respostas no sistema...
                                    </p>
                                </div>
                            </div>
                        </div>
                    </div>

                    <div class="content-right">
                        <!-- Plano Contratado -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-award"></i> Plano Atual</h3>
                                <span class="status-pill status-pill--active" id="planoStatus">Consultando...</span>
                            </div>
                            <div class="mb-3">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Plano</span>
                                <h4 class="h5 fw-bold text-dark mb-0" id="planoNome">Carregando...</h4>
                            </div>
                            <div class="mb-3">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Valor Contratado</span>
                                <span class="h5 fw-bold text-dark" id="planoValor">R$ 0,00</span>
                                <small class="text-muted ms-1" id="planoPeriodicidade">/ MÊS</small>
                            </div>
                            <div class="mb-4">
                                <span class="d-block text-muted small text-uppercase fw-semibold">Dia de Vencimento</span>
                                <span class="fw-semibold text-dark" id="planoVencimento">—</span>
                            </div>
                            <a href="/ctt/financeiro" class="btn btn-ctt-action w-100 justify-content-center">
                                <i class="bi bi-wallet2 me-1"></i> Ver Faturas e Financeiro
                            </a>
                        </div>

                        <!-- Regras e Normas -->
                        <div class="info-card">
                            <div class="info-card-header">
                                <h3 class="info-card-title"><i class="bi bi-info-circle"></i> Regras de Matrícula</h3>
                            </div>
                            <ul class="small text-muted ps-3 mb-0 d-flex flex-column gap-2">
                                <li>Check-in obrigatório antes do início de cada aula.</li>
                                <li>Tolerância máxima de 10 minutos para entrada nas turmas.</li>
                                <li>Trancamento ou cancelamento devem ser solicitados com 15 dias de antecedência.</li>
                            </ul>
                        </div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
    <script src="<?= PUBLIC_URL ?>js/aluno/matricula.js"></script>
</body>

</html>
