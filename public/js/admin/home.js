document.addEventListener('DOMContentLoaded', () => {
    const calendarElement = document.getElementById('calendar');
    const eventList = document.getElementById('eventList');
    const statusBadge = document.getElementById('calendarStatus');
    if (!calendarElement || typeof FullCalendar === 'undefined') return;

    const timeFormatter = new Intl.DateTimeFormat('pt-BR', { hour: '2-digit', minute: '2-digit' });
    const dayFormatter = new Intl.DateTimeFormat('pt-BR', { weekday: 'short', day: '2-digit', month: '2-digit' });
    
    const toDate = (value) => { 
        const date = new Date(String(value || '').replace(' ', 'T')); 
        return Number.isNaN(date.getTime()) ? null : date; 
    };

    const escapeHtml = (value) => String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');

    const parseEvents = (payload) => { 
        const data = payload?.data ?? payload; 
        return Array.isArray(data) ? data : (Array.isArray(data?.registros) ? data.registros : []); 
    };

    // Renderização moderna e refinada dos próximos treinos
    const renderUpcoming = (events) => {
        const now = new Date();
        const upcoming = events
            .filter((event) => event.status !== 'cancelado' && toDate(event.data_hora_inicio) >= now)
            .sort((a, b) => toDate(a.data_hora_inicio) - toDate(b.data_hora_inicio))
            .slice(0, 6);

        if (!upcoming.length) {
            eventList.innerHTML = '<div class="text-center py-4 text-body-secondary small"><i class="ph ph-calendar-x fs-3 d-block mb-2 text-muted"></i>Nenhum próximo treino agendado para o período.</div>';
            return;
        }

        eventList.innerHTML = upcoming.map((event) => {
            const date = toDate(event.data_hora_inicio);
            const isToday = date && date.toDateString() === now.toDateString();
            const timeStr = date ? timeFormatter.format(date) : '--:--';
            const dayStr = isToday ? 'Hoje' : (date ? dayFormatter.format(date) : '--');

            return `
                <button type="button" class="list-group-item list-group-item-action home-upcoming-item" data-turma-id="${Number(event.turma_id) || ''}">
                    <div class="upcoming-time-box">
                        <span class="time-val">${escapeHtml(timeStr)}</span>
                        <span class="time-day">${escapeHtml(dayStr)}</span>
                    </div>
                    <div class="upcoming-info">
                        <div class="upcoming-title">${escapeHtml(event.treino || 'Treino')}</div>
                        <div class="upcoming-meta">
                            <span><i class="ph ph-users"></i> ${escapeHtml(event.turma || 'Turma não informada')}</span>
                            <span>•</span>
                            <span><i class="ph ph-map-pin"></i> ${escapeHtml(event.espaco || 'CT')}</span>
                        </div>
                    </div>
                    <div class="upcoming-action-icon" title="Gerenciar turma">
                        <i class="ph ph-caret-right"></i>
                    </div>
                </button>
            `;
        }).join('');

        eventList.querySelectorAll('[data-turma-id]').forEach((item) => {
            item.addEventListener('click', () => { 
                const turmaId = Number(item.dataset.turmaId); 
                if (turmaId > 0) window.location.href = `/ctt/admin/turmas/${turmaId}/gerenciar`; 
            });
        });
    };

    // Inicialização do FullCalendar
    const calendar = new FullCalendar.Calendar(calendarElement, {
        locale: 'pt-br',
        initialView: window.innerWidth < 768 ? 'listWeek' : 'timeGridWeek',
        height: 'auto',
        nowIndicator: true,
        allDaySlot: false,
        slotMinTime: '06:00:00',
        slotMaxTime: '23:00:00',
        expandRows: true,
        noEventsContent: 'Nenhum treino agendado para este período.',
        headerToolbar: {
            left: 'prev,next today',
            center: 'title',
            right: 'timeGridDay,timeGridWeek,listWeek'
        },
        buttonText: {
            today: 'Hoje',
            day: 'Dia',
            week: 'Semana',
            list: 'Lista'
        },
        eventClick(info) { 
            const turmaId = Number(info.event.extendedProps.turmaId); 
            if (turmaId > 0) window.location.href = `/ctt/admin/turmas/${turmaId}/gerenciar`; 
        },
        datesSet: async (info) => {
            statusBadge.textContent = 'Carregando...';
            try {
                const query = new URLSearchParams({ start: info.startStr, end: info.endStr });
                const response = await fetch(`/ctt/api/agenda/treinos?${query}`, { credentials: 'same-origin' });
                const payload = await response.json();
                if (!response.ok) throw new Error(payload.message || 'Não foi possível carregar a agenda.');
                const events = parseEvents(payload);
                calendar.removeAllEvents();
                calendar.addEventSource(events.map((event) => {
                    const status = event.status || 'agendado';
                    let bg = '#dbeafe', border = '#2563eb', text = '#0f172a';
                    if (status === 'concluido') {
                        bg = '#dcfce7'; border = '#16a34a'; text = '#064e3b';
                    } else if (status === 'cancelado') {
                        bg = '#fee2e2'; border = '#dc2626'; text = '#7f1d1d';
                    }
                    return {
                        id: `treino-${event.id}`,
                        title: event.treino || 'Treino',
                        start: String(event.data_hora_inicio).replace(' ', 'T'),
                        end: String(event.data_hora_fim).replace(' ', 'T'),
                        backgroundColor: bg,
                        borderColor: border,
                        textColor: text,
                        classNames: [`fc-event-${status}`],
                        extendedProps: { turmaId: event.turma_id }
                    };
                }));
                renderUpcoming(events);
                statusBadge.textContent = `${events.length} treino${events.length === 1 ? '' : 's'}`;
            } catch (error) {
                statusBadge.textContent = 'Indisponível';
                eventList.innerHTML = `<div class="alert alert-light border small mb-0">${escapeHtml(error.message)}</div>`;
            }
        }
    });

    calendar.render();



    // =========================================================
    // Carregamento dinâmico das métricas dos cards da Home
    // =========================================================
    const loadHomeStats = async () => {
        const moneyFormatter = new Intl.NumberFormat('pt-BR', { style: 'currency', currency: 'BRL' });

        // Alunos Ativos
        try {
            const res = await fetch('/ctt/api/relatorios/metricas', { credentials: 'same-origin' });
            if (res.ok) {
                const data = await res.json();
                const metricas = data?.data ?? data;
                const totalAlunos = metricas?.alunos_ativos !== undefined ? metricas.alunos_ativos : (metricas?.total_alunos ?? 0);
                
                document.querySelectorAll('.stat-alunos-ativos').forEach(el => {
                    el.textContent = totalAlunos;
                });
            }
        } catch (_) {}

        // Métricas financeiras (Receita do Mês, Atrasados, Pendentes)
        try {
            const res = await fetch('/ctt/api/financeiro/cobrancas', { credentials: 'same-origin' });
            if (res.ok) {
                const data = await res.json();
                const cobrancas = Array.isArray(data?.data) ? data.data : (Array.isArray(data) ? data : []);
                
                const now = new Date();
                const currentMonth = now.getMonth();
                const currentYear = now.getFullYear();

                let receitaMes = 0;
                let atrasadosCount = 0;
                let pendentesCount = 0;

                cobrancas.forEach(c => {
                    if (c.status === 'vencida') atrasadosCount++;
                    if (c.status === 'aberta') pendentesCount++;

                    if (Array.isArray(c.pagamentos)) {
                        c.pagamentos.forEach(p => {
                            if (!p.estornado_em && p.data_pagamento) {
                                const dt = new Date(String(p.data_pagamento).replace(' ', 'T'));
                                if (dt.getMonth() === currentMonth && dt.getFullYear() === currentYear) {
                                    receitaMes += Number(p.valor_pago || 0);
                                }
                            }
                        });
                    } else if (c.status === 'paga' && c.data_pagamento) {
                        const dt = new Date(String(c.data_pagamento).replace(' ', 'T'));
                        if (dt.getMonth() === currentMonth && dt.getFullYear() === currentYear) {
                            receitaMes += Number(c.total_pago || c.valor_final || 0);
                        }
                    }
                });

                document.querySelectorAll('.stat-receita-mes').forEach(el => {
                    el.textContent = moneyFormatter.format(receitaMes);
                });
                document.querySelectorAll('.stat-pagamentos-atrasados').forEach(el => {
                    el.textContent = atrasadosCount;
                });
                document.querySelectorAll('.stat-pagamentos-pendentes').forEach(el => {
                    el.textContent = pendentesCount;
                });
            }
        } catch (_) {}
    };

    loadHomeStats();
});
