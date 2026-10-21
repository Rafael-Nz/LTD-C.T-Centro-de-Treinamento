<?php
$pageTitle = 'Cross C.T - Avaliações Físicas';
$currentPage = 'avaliacoes';
$pageCss = 'avaliacoes.css';

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
                    <h2 class="section-title">Avaliações Físicas</h2>
                    <p class="section-subtitle">Acompanhe seus resultados, evolução de medidas e composição corporal</p>
                </div>

                <!-- Stats da Última Avaliação -->
                <div id="evalStatsWrap">
                    <div class="d-flex justify-content-between align-items-center mb-2">
                        <h3 class="h6 fw-bold text-dark text-uppercase mb-0">Último Registro</h3>
                        <span class="small text-muted" id="statDataUltima">Consultando...</span>
                    </div>

                    <div class="eval-stats-grid">
                        <div class="eval-stat-card eval-stat-card--red">
                            <span class="eval-stat-label">Peso Corporal</span>
                            <span class="eval-stat-value" id="statPeso">—</span>
                            <span class="eval-stat-sub">Altura: <span id="statAltura">—</span></span>
                        </div>

                        <div class="eval-stat-card eval-stat-card--yellow">
                            <span class="eval-stat-label">IMC</span>
                            <span class="eval-stat-value" id="statImc">—</span>
                            <span class="eval-stat-sub">
                                <span class="badge-imc badge-imc--normal" id="statImcBadge">—</span>
                            </span>
                        </div>

                        <div class="eval-stat-card eval-stat-card--green">
                            <span class="eval-stat-label">Gordura Corporal</span>
                            <span class="eval-stat-value" id="statGordura">—</span>
                            <span class="eval-stat-sub">Percentual de gordura</span>
                        </div>

                        <div class="eval-stat-card eval-stat-card--blue">
                            <span class="eval-stat-label">Massa Muscular</span>
                            <span class="eval-stat-value" id="statMusculo">—</span>
                            <span class="eval-stat-sub">Percentual de massa magra</span>
                        </div>
                    </div>
                </div>

                <!-- Tabela de Histórico -->
                <div class="info-card" id="avaliacoesContainer">
                    <div class="info-card-header">
                        <h3 class="info-card-title"><i class="bi bi-clock-history"></i> Histórico de Avaliações</h3>
                    </div>
                    <div class="table-responsive">
                        <table class="table table-eval align-middle">
                            <thead>
                                <tr>
                                    <th>Data</th>
                                    <th>Peso</th>
                                    <th>IMC</th>
                                    <th>% Gordura</th>
                                    <th>% Músculo</th>
                                    <th>Avaliador</th>
                                    <th class="text-end">Ações</th>
                                </tr>
                            </thead>
                            <tbody id="tabelaHistoricoBody">
                                <tr>
                                    <td colspan="7" class="text-center text-muted py-4">Carregando avaliações...</td>
                                </tr>
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Estado Vazio -->
                <div class="eval-empty-card d-none" id="avaliacoesEmpty">
                    <i class="bi bi-clipboard2-data text-danger fs-1 mb-2"></i>
                    <h3 class="h5 fw-bold text-dark mb-2">Nenhuma avaliação física cadastrada</h3>
                    <p class="text-muted small mb-4" style="max-width: 500px; margin: 0 auto;">
                        Você ainda não realizou nenhuma avaliação física no CT. A avaliação é essencial para acompanhar seu progresso e personalizar seus treinos.
                    </p>
                    <a href="https://wa.me/5500000000000" target="_blank" class="btn btn-primary-ctt">
                        <i class="bi bi-calendar-check me-1"></i> Agendar Avaliação com Instrutor
                    </a>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Detalhe da Avaliação -->
    <div class="modal fade" id="modalDetalheAvaliacao" tabindex="-1" aria-labelledby="modalDetalheAvaliacaoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered modal-lg">
            <div class="modal-content">
                <div class="modal-header">
                    <div>
                        <h5 class="modal-title" id="modalDetalheAvaliacaoLabel"><i class="bi bi-clipboard2-check text-danger me-2"></i>Ficha de Avaliação Física</h5>
                        <p class="small text-muted mb-0">Avaliação de <span id="modalDataAvaliacao">—</span> · Profissional: <strong id="modalAvaliador">—</strong></p>
                    </div>
                    <button type="button" class="btn-close" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body">
                    <!-- Composição Corporal -->
                    <h6 class="detail-section-title"><i class="bi bi-speedometer2 text-danger"></i> Composição Corporal & Bioimpedância</h6>
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Peso</span>
                                <span class="detail-metric-value" id="mPeso">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Altura</span>
                                <span class="detail-metric-value" id="mAltura">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">IMC</span>
                                <span class="detail-metric-value" id="mImc">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">% Gordura</span>
                                <span class="detail-metric-value" id="mGordura">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">% Músculo</span>
                                <span class="detail-metric-value" id="mMusculo">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Taxa Metabólica</span>
                                <span class="detail-metric-value" id="mMetabolismo">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Idade Corporal</span>
                                <span class="detail-metric-value" id="mIdadeBio">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Gordura Visceral</span>
                                <span class="detail-metric-value" id="mVisceral">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Perimetria -->
                    <h6 class="detail-section-title"><i class="bi bi-rulers text-danger"></i> Perimetria (Circunferências em cm)</h6>
                    <div class="row g-2 mb-4">
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Tórax</span>
                                <span class="detail-metric-value" id="mTorax">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Cintura</span>
                                <span class="detail-metric-value" id="mCintura">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Braço D. (Relaxado)</span>
                                <span class="detail-metric-value" id="mBracoD">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Braço D. (Contraído)</span>
                                <span class="detail-metric-value" id="mBracoDc">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Braço E. (Relaxado)</span>
                                <span class="detail-metric-value" id="mBracoE">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Braço E. (Contraído)</span>
                                <span class="detail-metric-value" id="mBracoEc">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Coxa Direita</span>
                                <span class="detail-metric-value" id="mCoxaD">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Coxa Esquerda</span>
                                <span class="detail-metric-value" id="mCoxaE">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Panturrilha Direita</span>
                                <span class="detail-metric-value" id="mPanturrilhaD">—</span>
                            </div>
                        </div>
                        <div class="col-6 col-md-3">
                            <div class="detail-metric-box">
                                <span class="detail-metric-label">Panturrilha Esquerda</span>
                                <span class="detail-metric-value" id="mPanturrilhaE">—</span>
                            </div>
                        </div>
                    </div>

                    <!-- Observações -->
                    <h6 class="detail-section-title"><i class="bi bi-chat-left-text text-danger"></i> Parecer do Avaliador</h6>
                    <div class="bg-light p-3 rounded">
                        <p class="mb-0 small text-dark" id="modalObservacoes">—</p>
                    </div>
                </div>
                <div class="modal-footer">
                    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
    <script src="<?= PUBLIC_URL ?>js/aluno/avaliacoes.js"></script>
</body>

</html>
