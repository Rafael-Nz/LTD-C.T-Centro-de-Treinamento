<?php
$pageTitle = 'Cross C.T - Horários';
$currentPage = 'horarios';
$pageCss = 'horarios.css';

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
                    <h2 class="section-title">Horários de Treino</h2>
                    <p class="section-subtitle">Consulte os horários das suas aulas e a grade semanal completa de treinos</p>
                </div>

                <!-- Próximo Treino em Destaque -->
                <div class="next-workout-card mb-4 d-none" id="nextWorkoutCard">
                    <div class="next-workout-time">
                        <span class="next-workout-badge">Próximo Treino</span>
                        <span class="next-workout-hour" id="nextHour">07:00</span>
                        <span class="next-workout-day" id="nextDay">Segunda-feira</span>
                    </div>
                    <div class="next-workout-info flex-grow-1">
                        <h3 class="next-workout-title" id="nextTitle">Cross Training</h3>
                        <div class="next-workout-sub">
                            <span><i class="bi bi-collection text-danger"></i> <span id="nextTurma">Turma Matutina</span></span>
                            <span><i class="bi bi-person text-danger"></i> <span id="nextInstrutor">Prof. Rafael</span></span>
                            <span><i class="bi bi-geo-alt text-danger"></i> <span id="nextEspaco">Box Principal</span></span>
                        </div>
                    </div>
                    <div class="d-none d-md-block">
                        <a href="https://wa.me/5500000000000" target="_blank" class="btn btn-sm btn-outline-light">
                            <i class="bi bi-whatsapp me-1"></i> Confirmar Presença
                        </a>
                    </div>
                </div>

                <!-- Filtros Principais -->
                <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 mb-3">
                    <div class="schedule-filter-tabs">
                        <button class="schedule-filter-btn active" data-tipo="minhas" id="btnFiltroMinhas">
                            <i class="bi bi-star me-1"></i> Minhas Turmas
                        </button>
                        <button class="schedule-filter-btn" data-tipo="todas" id="btnFiltroTodas">
                            <i class="bi bi-grid me-1"></i> Grade Geral do CT
                        </button>
                    </div>
                </div>

                <!-- Container do FullCalendar -->
                <div class="calendar-card mb-4" id="calendarCard">
                    <div class="d-flex justify-content-between align-items-center flex-wrap gap-2 p-3 border-bottom bg-light">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-calendar-check-fill text-danger fs-5"></i>
                            <span class="fw-bold text-dark" id="calendarSubtitle">Treinos das Minhas Turmas</span>
                        </div>
                        <div class="d-flex align-items-center gap-3 calendar-legend">
                            <span class="legend-item"><span class="legend-dot dot-my-turma"></span> Minhas Turmas</span>
                            <span class="legend-item"><span class="legend-dot dot-other-turma"></span> Outras Turmas</span>
                            <span class="legend-item"><span class="legend-dot dot-completed"></span> Concluído</span>
                            <span class="legend-item"><span class="legend-dot dot-cancelled"></span> Cancelado</span>
                        </div>
                    </div>
                    <div class="p-3">
                        <div id="fullCalendarAluno"></div>
                    </div>
                </div>
            </main>
        </div>
    </div>

    <!-- Modal Detalhes do Treino -->
    <div class="modal fade" id="modalTreinoDetalhe" tabindex="-1" aria-labelledby="modalTreinoLabel" aria-hidden="true">
        <div class="modal-dialog modal-dialog-centered">
            <div class="modal-content border-0 shadow">
                <div class="modal-header bg-dark text-white border-0 py-3">
                    <div class="d-flex align-items-center gap-2">
                        <div class="modal-header-icon">
                            <i class="bi bi-lightning-charge-fill text-danger"></i>
                        </div>
                        <div>
                            <h5 class="modal-title fs-6 fw-bold mb-0" id="modalTreinoLabel">Detalhes do Treino</h5>
                            <small class="text-white-50" id="modalTreinoStatusBadge">Agendado</small>
                        </div>
                    </div>
                    <button type="button" class="btn-close btn-close-white" data-bs-dismiss="modal" aria-label="Fechar"></button>
                </div>
                <div class="modal-body p-4">
                    <div class="d-flex align-items-center justify-content-between mb-3 pb-3 border-bottom">
                        <div>
                            <h4 class="fw-bold text-dark mb-0 fs-5" id="modalTreinoNome">-</h4>
                            <span class="badge bg-danger-subtle text-danger border border-danger-subtle mt-1" id="modalTreinoModalidade">-</span>
                        </div>
                        <div class="text-end">
                            <div class="fs-5 fw-extrabold text-danger" id="modalTreinoHorario">00:00 - 00:00</div>
                            <small class="text-muted" id="modalTreinoData">00/00/0000</small>
                        </div>
                    </div>

                    <div class="d-flex flex-column gap-2 mb-3">
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-collection text-danger"></i>
                            <span class="text-muted small">Turma:</span>
                            <strong class="text-dark small" id="modalTreinoTurma">-</strong>
                            <span class="badge bg-light text-muted border ms-auto" id="modalTreinoIsMinha"></span>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-person text-danger"></i>
                            <span class="text-muted small">Instrutor:</span>
                            <strong class="text-dark small" id="modalTreinoInstrutor">-</strong>
                        </div>
                        <div class="d-flex align-items-center gap-2">
                            <i class="bi bi-geo-alt text-danger"></i>
                            <span class="text-muted small">Local / Box:</span>
                            <strong class="text-dark small" id="modalTreinoEspaco">-</strong>
                        </div>
                    </div>

                    <div id="modalTreinoDescricaoWrapper" class="p-3 bg-light rounded-3 mb-3 d-none">
                        <div class="small fw-bold text-dark mb-1"><i class="bi bi-info-circle me-1 text-danger"></i> Descrição do Treino</div>
                        <p class="small text-muted mb-0" id="modalTreinoDescricao"></p>
                    </div>

                    <div id="modalTreinoPresencaWrapper" class="p-3 rounded-3 mb-0 d-none">
                        <div class="d-flex align-items-center justify-content-between">
                            <div class="d-flex align-items-center gap-2">
                                <i class="bi bi-person-check-fill fs-5" id="modalPresencaIcon"></i>
                                <div>
                                    <span class="fw-bold small d-block" id="modalPresencaTitulo">Sua Presença</span>
                                    <small id="modalPresencaSubtitulo" class="text-muted"></small>
                                </div>
                            </div>
                            <span class="badge" id="modalPresencaBadge"></span>
                        </div>
                    </div>
                </div>
                <div class="modal-footer bg-light border-0 py-2 px-4 d-flex justify-content-between">
                    <span class="small text-muted"><i class="bi bi-building me-1"></i> Cross C.T</span>
                    <button type="button" class="btn btn-sm btn-secondary" data-bs-dismiss="modal">Fechar</button>
                </div>
            </div>
        </div>
    </div>

    <script src="/ctt/plugins/fullcalendar/index.global.min.js"></script>
    <script src="/ctt/plugins/bootstrap/js/bootstrap.bundle.min.js"></script>
    <script src="<?= PUBLIC_URL ?>js/sidebar.js"></script>
    <script src="<?= PUBLIC_URL ?>js/aluno/horarios.js"></script>
</body>

</html>
