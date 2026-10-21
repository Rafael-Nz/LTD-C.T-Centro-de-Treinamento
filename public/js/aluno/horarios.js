(() => {
    let calendar = null;
    let alunoTurmaIds = new Set();
    let todasTurmas = [];
    let agendaTreinos = [];
    let alunoPresencas = new Map(); // Map de chave (data_turma) -> situacao
    let filtroTipo = 'minhas'; // 'minhas' ou 'todas'
    let currentModalInstance = null;

    const diasNomes = {
        'segunda': 'Segunda-feira',
        'terca': 'Terça-feira',
        'quarta': 'Quarta-feira',
        'quinta': 'Quinta-feira',
        'sexta': 'Sexta-feira',
        'sabado': 'Sábado',
        'domingo': 'Domingo'
    };

    const diasOrdem = ['segunda', 'terca', 'quarta', 'quinta', 'sexta', 'sabado', 'domingo'];

    async function init() {
        try {
            const alunoId = window.AUTH_USER_ID;

            // Disparar requisições em paralelo
            const promises = [
                // 1. Turmas do aluno
                alunoId ? fetch(`/ctt/api/alunos/${alunoId}`, {
                    headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                    credentials: 'same-origin',
                }).then(r => r.ok ? r.json() : null).catch(() => null) : Promise.resolve(null),

                // 2. Turmas gerais do CT
                fetch('/ctt/api/turmas?simple=false', {
                    headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                    credentials: 'same-origin',
                }).then(r => r.ok ? r.json() : null).catch(() => null),

                // 3. Agenda de treinos
                fetch('/ctt/api/agenda/treinos', {
                    headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                    credentials: 'same-origin',
                }).then(r => r.ok ? r.json() : null).catch(() => null),

                // 4. Presenças do aluno (via relatório de presença)
                alunoId ? fetch(`/ctt/api/relatorios/gerar?tipo=presenca&aluno=${alunoId}`, {
                    headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                    credentials: 'same-origin',
                }).then(r => r.ok ? r.json() : null).catch(() => null) : Promise.resolve(null)
            ];

            const [alunoRes, turmasRes, agendaRes, presencaRes] = await Promise.allSettled(promises);

            // Processar turmas do aluno
            if (alunoRes.status === 'fulfilled' && alunoRes.value?.data?.turmas) {
                alunoRes.value.data.turmas.forEach(t => alunoTurmaIds.add(Number(t.id)));
            }

            // Processar turmas gerais
            if (turmasRes.status === 'fulfilled' && turmasRes.value) {
                todasTurmas = turmasRes.value.data?.data || turmasRes.value.data || [];
            }

            // Processar agenda
            if (agendaRes.status === 'fulfilled' && agendaRes.value) {
                agendaTreinos = agendaRes.value.data || agendaRes.value || [];
            }

            // Processar presenças
            if (presencaRes.status === 'fulfilled' && presencaRes.value?.data?.registros) {
                const registros = presencaRes.value.data.registros || [];
                registros.forEach(p => {
                    const key = `${p.data_treino}_${p.turma}`;
                    alunoPresencas.set(key, {
                        situacao: p.situacao,
                        checkin: p.checkin_time
                    });
                });
            }

            configurarEventos();
            renderProximoTreino();
            initFullCalendar();
        } catch (error) {
            console.error('Erro ao inicializar horários:', error);
        }
    }

    function initFullCalendar() {
        const calendarEl = document.getElementById('fullCalendarAluno');
        if (!calendarEl || typeof FullCalendar === 'undefined') return;

        // Calcular data inicial relevante
        let initialDate = new Date();
        if (agendaTreinos && agendaTreinos.length > 0) {
            const treinosFuturos = agendaTreinos.filter(t => new Date(t.data_hora_inicio.replace(' ', 'T')) >= initialDate);
            if (treinosFuturos.length === 0 && agendaTreinos.length > 0) {
                const ultimo = agendaTreinos[agendaTreinos.length - 1];
                initialDate = new Date(ultimo.data_hora_inicio.replace(' ', 'T'));
            }
        }

        calendar = new FullCalendar.Calendar(calendarEl, {
            initialView: window.innerWidth < 768 ? 'timeGridDay' : 'timeGridWeek',
            initialDate: initialDate,
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,timeGridDay'
            },
            locale: 'pt-br',
            buttonText: {
                today: 'Hoje',
                month: 'Mês',
                week: 'Semana',
                day: 'Dia'
            },
            allDaySlot: false,
            slotMinTime: '05:00:00',
            slotMaxTime: '22:00:00',
            slotDuration: '00:30:00',
            expandRows: true,
            nowIndicator: true,
            navLinks: true,
            dayHeaderFormat: { weekday: 'short', day: 'numeric', month: 'numeric' },
            events: (info, successCallback) => {
                successCallback(gerarEventosCalendario());
            },
            eventClick: (info) => {
                info.jsEvent.preventDefault();
                abrirModalTreino(info.event.extendedProps);
            },
            windowResize: () => {
                if (window.innerWidth < 768 && calendar.view.type === 'timeGridWeek') {
                    calendar.changeView('timeGridDay');
                }
            }
        });

        calendar.render();
    }

    function gerarEventosCalendario() {
        const eventos = [];
        const somenteMinhas = filtroTipo === 'minhas';

        if (Array.isArray(agendaTreinos) && agendaTreinos.length > 0) {
            agendaTreinos.forEach(item => {
                const isMinha = item.turma_id ? alunoTurmaIds.has(Number(item.turma_id)) : false;
                if (somenteMinhas && !isMinha) return;

                const startIso = item.data_hora_inicio.replace(' ', 'T');
                const endIso = item.data_hora_fim.replace(' ', 'T');
                const status = (item.status || 'agendado').toLowerCase();

                let className = 'fc-event-other-turma';
                let bgColor = '#475569';
                let borderColor = '#334155';

                if (status === 'cancelado') {
                    className = 'fc-event-cancelled';
                    bgColor = '#94a3b8';
                    borderColor = '#64748b';
                } else if (status === 'concluido') {
                    className = 'fc-event-completed';
                    bgColor = '#059669';
                    borderColor = '#047857';
                } else if (isMinha) {
                    className = 'fc-event-my-turma';
                    bgColor = '#dc3545';
                    borderColor = '#b91c1c';
                }

                // Verificar presença do aluno se concluído
                const dataTreino = item.data_hora_inicio.split(' ')[0];
                const keyPresenca = `${dataTreino}_${item.turma}`;
                const presenca = alunoPresencas.get(keyPresenca);

                eventos.push({
                    id: String(item.id),
                    title: `${item.treino || item.turma} (${item.turma || 'CT'})`,
                    start: startIso,
                    end: endIso,
                    backgroundColor: bgColor,
                    borderColor: borderColor,
                    classNames: [className],
                    extendedProps: {
                        treinoId: item.id,
                        treinoNome: item.treino,
                        treinoDescricao: item.treino_descricao || '',
                        modalidade: item.modalidade || 'Cross Training',
                        turmaId: item.turma_id,
                        turmaNome: item.turma,
                        instrutor: item.instrutor || 'Instrutor Cross C.T',
                        espaco: item.espaco || 'Box Principal',
                        status: status,
                        isMinha: isMinha,
                        dataHoraInicio: item.data_hora_inicio,
                        dataHoraFim: item.data_hora_fim,
                        presenca: presenca
                    }
                });
            });
        }

        return eventos;
    }

    function abrirModalTreino(props) {
        if (!props) return;

        document.getElementById('modalTreinoNome').textContent = props.treinoNome || props.turmaNome || 'Treino';
        document.getElementById('modalTreinoModalidade').textContent = props.modalidade || 'Treino';

        const dataInicio = new Date(props.dataHoraInicio.replace(' ', 'T'));
        const dataFim = new Date(props.dataHoraFim.replace(' ', 'T'));

        const horaInicio = dataInicio.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        const horaFim = dataFim.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
        const dataFormatada = dataInicio.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: '2-digit', year: 'numeric' });

        document.getElementById('modalTreinoHorario').textContent = `${horaInicio} - ${horaFim}`;
        document.getElementById('modalTreinoData').textContent = dataFormatada.charAt(0).toUpperCase() + dataFormatada.slice(1);
        document.getElementById('modalTreinoTurma').textContent = props.turmaNome || 'Geral';
        document.getElementById('modalTreinoInstrutor').textContent = props.instrutor || 'Cross C.T';
        document.getElementById('modalTreinoEspaco').textContent = props.espaco || 'Espaço Principal';

        const isMinhaBadge = document.getElementById('modalTreinoIsMinha');
        if (props.isMinha) {
            isMinhaBadge.className = 'badge bg-danger-subtle text-danger border border-danger-subtle ms-auto';
            isMinhaBadge.innerHTML = '<i class="bi bi-star-fill me-1"></i>Sua Turma';
        } else {
            isMinhaBadge.className = 'badge bg-light text-muted border ms-auto';
            isMinhaBadge.textContent = 'Outra Turma';
        }

        // Status Badge
        const statusBadge = document.getElementById('modalTreinoStatusBadge');
        if (props.status === 'concluido') {
            statusBadge.className = 'badge bg-success';
            statusBadge.textContent = 'Concluído';
        } else if (props.status === 'cancelado') {
            statusBadge.className = 'badge bg-secondary';
            statusBadge.textContent = 'Cancelado';
        } else {
            statusBadge.className = 'badge bg-primary';
            statusBadge.textContent = 'Agendado';
        }

        // Descrição
        const descWrap = document.getElementById('modalTreinoDescricaoWrapper');
        const descText = document.getElementById('modalTreinoDescricao');
        if (props.treinoDescricao && props.treinoDescricao.trim() !== '') {
            descText.textContent = props.treinoDescricao;
            descWrap.classList.remove('d-none');
        } else {
            descWrap.classList.add('d-none');
        }

        // Informações de Presença do Aluno
        const presencaWrap = document.getElementById('modalTreinoPresencaWrapper');
        const presencaBadge = document.getElementById('modalPresencaBadge');
        const presencaIcon = document.getElementById('modalPresencaIcon');
        const presencaTitulo = document.getElementById('modalPresencaTitulo');
        const presencaSub = document.getElementById('modalPresencaSubtitulo');

        if (props.presenca) {
            presencaWrap.classList.remove('d-none');
            const sit = props.presenca.situacao;

            if (sit === 'presente') {
                presencaWrap.className = 'p-3 rounded-3 mb-0 bg-success-subtle border border-success-subtle text-success-emphasis';
                presencaIcon.className = 'bi bi-check-circle-fill fs-5 text-success';
                presencaTitulo.textContent = 'Presença Confirmada';
                presencaSub.textContent = props.presenca.checkin ? `Check-in realizado às ${props.presenca.checkin.slice(11, 16)}` : 'Check-in registrado pelo instrutor';
                presencaBadge.className = 'badge bg-success';
                presencaBadge.textContent = 'Presente';
            } else if (sit === 'justificado') {
                presencaWrap.className = 'p-3 rounded-3 mb-0 bg-warning-subtle border border-warning-subtle text-warning-emphasis';
                presencaIcon.className = 'bi bi-exclamation-circle-fill fs-5 text-warning';
                presencaTitulo.textContent = 'Falta Justificada';
                presencaSub.textContent = 'Ausência prévia informada ao CT';
                presencaBadge.className = 'badge bg-warning text-dark';
                presencaBadge.textContent = 'Justificado';
            } else {
                presencaWrap.className = 'p-3 rounded-3 mb-0 bg-danger-subtle border border-danger-subtle text-danger-emphasis';
                presencaIcon.className = 'bi bi-x-circle-fill fs-5 text-danger';
                presencaTitulo.textContent = 'Falta Registrada';
                presencaSub.textContent = 'Você não compareceu a este treino';
                presencaBadge.className = 'badge bg-danger';
                presencaBadge.textContent = 'Ausente';
            }
        } else if (props.isMinha && props.status === 'agendado') {
            presencaWrap.classList.remove('d-none');
            presencaWrap.className = 'p-3 rounded-3 mb-0 bg-light border text-muted';
            presencaIcon.className = 'bi bi-clock-history fs-5 text-secondary';
            presencaTitulo.textContent = 'Treino Futuro';
            presencaSub.textContent = 'O check-in de presença será aberto no horário da aula';
            presencaBadge.className = 'badge bg-secondary';
            presencaBadge.textContent = 'Aguardando';
        } else {
            presencaWrap.classList.add('d-none');
        }

        const modalEl = document.getElementById('modalTreinoDetalhe');
        if (!currentModalInstance) {
            currentModalInstance = new bootstrap.Modal(modalEl);
        }
        currentModalInstance.show();
    }

    function renderProximoTreino() {
        const card = document.getElementById('nextWorkoutCard');
        if (!card) return;

        let proximo = null;
        const agora = new Date();

        if (Array.isArray(agendaTreinos) && agendaTreinos.length > 0) {
            proximo = agendaTreinos.find(t => {
                const inicio = new Date(t.data_hora_inicio.replace(' ', 'T'));
                const ehMinhaTurma = t.turma_id ? alunoTurmaIds.has(Number(t.turma_id)) : false;
                const statusValido = (t.status || 'agendado') === 'agendado';
                return inicio >= agora && (alunoTurmaIds.size === 0 || ehMinhaTurma) && statusValido;
            });

            if (!proximo) {
                proximo = agendaTreinos.find(t => {
                    const ehMinhaTurma = t.turma_id ? alunoTurmaIds.has(Number(t.turma_id)) : false;
                    return (alunoTurmaIds.size === 0 || ehMinhaTurma) && t.status === 'agendado';
                });
            }
        }

        if (proximo) {
            const dataInicio = new Date(proximo.data_hora_inicio.replace(' ', 'T'));
            const horaStr = dataInicio.toLocaleTimeString('pt-BR', { hour: '2-digit', minute: '2-digit' });
            const diaSemanaTexto = dataInicio.toLocaleDateString('pt-BR', { weekday: 'long', day: '2-digit', month: '2-digit' });

            document.getElementById('nextHour').textContent = horaStr;
            document.getElementById('nextDay').textContent = diaSemanaTexto.charAt(0).toUpperCase() + diaSemanaTexto.slice(1);
            document.getElementById('nextTitle').textContent = proximo.treino || proximo.turma || 'Cross Training';
            document.getElementById('nextTurma').textContent = proximo.turma ? `Turma: ${proximo.turma}` : 'Treino Geral';
            document.getElementById('nextInstrutor').textContent = proximo.instrutor ? `Instrutor: ${proximo.instrutor}` : 'Cross C.T';
            document.getElementById('nextEspaco').textContent = proximo.espaco ? `Local: ${proximo.espaco}` : 'Espaço Principal';
            card.classList.remove('d-none');
            return;
        }

        // Fallback: verificar slots configurados das turmas do aluno
        const slotsMinhas = extrairSlots(true);
        if (slotsMinhas.length > 0) {
            const slot = slotsMinhas[0];
            document.getElementById('nextHour').textContent = slot.hora_inicio.slice(0, 5);
            document.getElementById('nextDay').textContent = diasNomes[slot.dia_semana] || slot.dia_semana;
            document.getElementById('nextTitle').textContent = slot.turma_nome;
            document.getElementById('nextTurma').textContent = `Capacidade: ${slot.capacidade_maxima || 20} vagas`;
            document.getElementById('nextInstrutor').textContent = slot.instrutor_nome ? `Instrutor: ${slot.instrutor_nome}` : 'Cross C.T';
            document.getElementById('nextEspaco').textContent = 'Espaço Cross C.T';
            card.classList.remove('d-none');
        } else {
            card.classList.add('d-none');
        }
    }

    function extrairSlots(somenteMinhas = false) {
        const slots = [];
        todasTurmas.forEach(turma => {
            const isMinha = alunoTurmaIds.has(Number(turma.id));
            if (somenteMinhas && !isMinha) return;

            const configs = turma.config_horarios || [];
            configs.forEach(cfg => {
                slots.push({
                    turma_id: Number(turma.id),
                    turma_nome: turma.nome,
                    instrutor_nome: turma.instrutor_nome,
                    capacidade_maxima: turma.capacidade_maxima,
                    dia_semana: (cfg.dia_semana || '').toLowerCase(),
                    hora_inicio: cfg.hora_inicio || '00:00',
                    hora_fim: cfg.hora_fim || '00:00',
                    is_minha: isMinha
                });
            });
        });

        slots.sort((a, b) => {
            const idxA = diasOrdem.indexOf(a.dia_semana);
            const idxB = diasOrdem.indexOf(b.dia_semana);
            if (idxA !== idxB) return idxA - idxB;
            return a.hora_inicio.localeCompare(b.hora_inicio);
        });

        return slots;
    }

    function configurarEventos() {
        // Alternância Minhas Turmas vs Grade Geral
        document.querySelectorAll('.schedule-filter-btn').forEach(btn => {
            btn.addEventListener('click', () => {
                document.querySelectorAll('.schedule-filter-btn').forEach(b => b.classList.remove('active'));
                btn.classList.add('active');
                filtroTipo = btn.getAttribute('data-tipo');

                const subtitleEl = document.getElementById('calendarSubtitle');
                if (subtitleEl) {
                    subtitleEl.textContent = filtroTipo === 'minhas' ? 'Treinos das Minhas Turmas' : 'Grade Geral de Treinos do CT';
                }

                if (calendar) {
                    calendar.refetchEvents();
                }
            });
        });
    }

    document.addEventListener('DOMContentLoaded', init);
})();
