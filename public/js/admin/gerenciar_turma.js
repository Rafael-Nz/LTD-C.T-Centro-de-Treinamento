document.addEventListener('DOMContentLoaded', function () {
    const mainContent = document.getElementById('mainContent');
    const turmaId = Number(mainContent?.dataset.turmaId || 0);
    const errorAlert = document.getElementById('gerenciarTurmaError');
    const calendarElement = document.getElementById('calendar');
    const alunosTableBody = document.getElementById('alunosTableBody');
    const treinosList = document.getElementById('treinosList');
    const salvarPresencasBtn = document.getElementById('salvarPresencasBtn');
    const presencasTableBody = document.getElementById('presencasTableBody');
    const presencasResumoBadges = document.getElementById('presencasResumoBadges');
    const presencasModalElement = document.getElementById('presencasModal');
    const treinosTabButton = document.getElementById('treinos-tab');
    const abrirAgendarTreinoBtn = document.getElementById('abrirAgendarTreinoBtn');
    const agendarTreinoModalElement = document.getElementById('agendarTreinoModal');
    const agendarTreinoError = document.getElementById('agendarTreinoError');
    const confirmarAgendarTreinoBtn = document.getElementById('confirmarAgendarTreinoBtn');
    const agendarTreinoForm = document.getElementById('agendarTreinoForm');
    const agendarTreinoId = document.getElementById('agendarTreinoId');
    const agendarEspacoId = document.getElementById('agendarEspacoId');
    const agendarInstrutorId = document.getElementById('agendarInstrutorId');
    const agendarInicio = document.getElementById('agendarInicio');
    const agendarFim = document.getElementById('agendarFim');
    const agendarInicioPickerElement = document.getElementById('agendarInicioPicker');
    const agendarFimPickerElement = document.getElementById('agendarFimPicker');
    const agendarObservacoes = document.getElementById('agendarObservacoes');

    const state = {
        payload: null,
        selectedTreinoId: null,
        currentRange: null,
        lastRangeKey: null,
        calendar: null,
        modal: presencasModalElement ? new bootstrap.Modal(presencasModalElement) : null,
        agendarModal: agendarTreinoModalElement ? new bootstrap.Modal(agendarTreinoModalElement) : null,
        agendarInicioPicker: null,
        agendarFimPicker: null,
        catalogs: {
            treinos: [],
            locais: [],
            instrutores: []
        },
        catalogsLoaded: false
    };

    const dateFormat = new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric'
    });

    const dateTimeFormat = new Intl.DateTimeFormat('pt-BR', {
        day: '2-digit',
        month: '2-digit',
        year: 'numeric',
        hour: '2-digit',
        minute: '2-digit'
    });

    const timeFormat = new Intl.DateTimeFormat('pt-BR', {
        hour: '2-digit',
        minute: '2-digit'
    });

    function parseApiItem(payload) {
        if (!payload) return null;
        if (payload.data) return payload.data;
        return payload;
    }

    function parseApiList(payload) {
        if (!payload) return [];
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload.data)) return payload.data;
        if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
        return [];
    }

    function toDate(dateValue) {
        if (!dateValue) return null;
        const normalized = String(dateValue).replace(' ', 'T');
        const parsed = new Date(normalized);
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function showError(message) {
        if (!errorAlert) return;
        errorAlert.textContent = message;
        errorAlert.classList.remove('d-none');
    }

    function clearError() {
        if (!errorAlert) return;
        errorAlert.textContent = '';
        errorAlert.classList.add('d-none');
    }

    function showScheduleError(message) {
        if (!agendarTreinoError) return;
        agendarTreinoError.textContent = message;
        agendarTreinoError.classList.remove('d-none');
    }

    function clearScheduleError() {
        if (!agendarTreinoError) return;
        agendarTreinoError.textContent = '';
        agendarTreinoError.classList.add('d-none');
    }

    function clearScheduleFieldErrors() {
        agendarTreinoForm?.querySelectorAll('.is-invalid').forEach((field) => field.classList.remove('is-invalid'));
    }

    function getTempusDominusNamespace() {
        return window.tempusDominus || window.tempusdominus || null;
    }

    function initializeScheduleSelect2(selectElement, placeholder) {
        if (!selectElement || typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
            return;
        }

        const $select = window.jQuery(selectElement);
        if ($select.hasClass('select2-hidden-accessible')) {
            $select.select2('destroy');
        }

        $select.select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder,
            allowClear: !selectElement.required,
            dropdownParent: window.jQuery(agendarTreinoModalElement)
        });
    }

    function initializeScheduleSelects() {
        initializeScheduleSelect2(agendarTreinoId, 'Selecione um treino...');
        initializeScheduleSelect2(agendarEspacoId, 'Selecione um local...');
        initializeScheduleSelect2(agendarInstrutorId, 'Selecione um instrutor...');
    }

    function syncSelect2Value(selectElement, value) {
        if (!selectElement) return;

        selectElement.value = value ?? '';

        if (typeof window.jQuery !== 'undefined') {
            const $select = window.jQuery(selectElement);
            if ($select.hasClass('select2-hidden-accessible')) {
                $select.trigger('change');
            }
        }
    }

    function buildScheduleDateTimePicker(containerElement) {
        const tempus = getTempusDominusNamespace();
        if (!containerElement || !tempus?.TempusDominus) {
            return null;
        }

        return new tempus.TempusDominus(containerElement, {
            container: agendarTreinoModalElement || document.body,
            useCurrent: false,
            localization: tempus.locales?.pt || {
                locale: 'pt-BR',
                format: 'dd/MM/yyyy HH:mm',
                hourCycle: 'h23'
            },
            display: {
                theme: 'light',
                components: {
                    calendar: true,
                    date: true,
                    month: true,
                    year: true,
                    decades: true,
                    clock: true,
                    hours: true,
                    minutes: true,
                    seconds: false
                },
                buttons: {
                    today: true,
                    clear: true,
                    close: true
                }
            },
            stepping: 5
        });
    }

    function initializeScheduleDateTimePickers() {
        if (!state.agendarInicioPicker) {
            state.agendarInicioPicker = buildScheduleDateTimePicker(agendarInicioPickerElement);
        }

        if (!state.agendarFimPicker) {
            state.agendarFimPicker = buildScheduleDateTimePicker(agendarFimPickerElement);
        }
    }

    function formatPickerDateValue(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
            return '';
        }

        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${day}/${month}/${year} ${hours}:${minutes}`;
    }

    function setScheduleDateTimeValue(inputElement, pickerInstance, date) {
        if (!inputElement) return;

        if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
            inputElement.value = '';

            try {
                if (pickerInstance?.dates?.clear) {
                    pickerInstance.dates.clear();
                }
            } catch (error) {
                inputElement.value = '';
            }

            return;
        }

        const formattedValue = formatPickerDateValue(date);
        inputElement.value = formattedValue;

        if (!pickerInstance?.dates?.setValue) {
            return;
        }

        const tempus = getTempusDominusNamespace();

        try {
            if (tempus?.DateTime?.convert) {
                pickerInstance.dates.setValue(tempus.DateTime.convert(date));
                return;
            }

            pickerInstance.dates.setValue(date);
        } catch (error) {
            inputElement.value = formattedValue;
        }
    }

    function parseScheduleDateTimeValue(value) {
        if (!value) return null;

        const normalizedValue = String(value).trim();
        const match = normalizedValue.match(/^(\d{2})\/(\d{2})\/(\d{4}) (\d{2}):(\d{2})$/);

        if (!match) {
            const fallback = new Date(normalizedValue.replace(' ', 'T'));
            return Number.isNaN(fallback.getTime()) ? null : fallback;
        }

        const [, day, month, year, hours, minutes] = match;
        const parsed = new Date(
            Number(year),
            Number(month) - 1,
            Number(day),
            Number(hours),
            Number(minutes),
            0,
            0
        );

        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    function toApiDateTimeValue(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) {
            return '';
        }

        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        const hours = String(date.getHours()).padStart(2, '0');
        const minutes = String(date.getMinutes()).padStart(2, '0');

        return `${year}-${month}-${day} ${hours}:${minutes}:00`;
    }

    function markInvalid(field) {
        if (field) {
            field.classList.add('is-invalid');
        }
    }

    function formatDate(value) {
        const date = toDate(value);
        return date ? dateFormat.format(date) : '--';
    }

    function formatDateTime(value) {
        const date = toDate(value);
        return date ? dateTimeFormat.format(date) : '--';
    }

    function formatTime(value) {
        const date = toDate(value);
        return date ? timeFormat.format(date) : '--';
    }

    function formatPeriod(start, end) {
        const startDate = toDate(start);
        const endDate = toDate(end);
        if (!startDate || !endDate) return 'Periodo indisponivel';

        return `${dateFormat.format(startDate)} a ${dateFormat.format(endDate)}`;
    }

    function getStatusBadge(status) {
        const map = {
            concluido: 'bg-success-subtle text-success-emphasis',
            agendado: 'bg-info-subtle text-info-emphasis',
            cancelado: 'bg-danger-subtle text-danger-emphasis',
            pendente: 'bg-warning-subtle text-warning-emphasis'
        };

        return map[status] || 'bg-secondary-subtle text-secondary-emphasis';
    }

    function getStatusLabel(status) {
        const map = {
            concluido: 'Concluido',
            agendado: 'Agendado',
            cancelado: 'Cancelado',
            pendente: 'Pendente'
        };

        return map[status] || 'Nao informado';
    }

    function getSituacaoBadge(situacao) {
        const map = {
            presente: 'bg-success-subtle text-success-emphasis',
            ausente: 'bg-danger-subtle text-danger-emphasis',
            justificado: 'bg-warning-subtle text-warning-emphasis'
        };

        return map[situacao] || 'bg-secondary-subtle text-secondary-emphasis';
    }

    function getSituacaoLabel(situacao) {
        const map = {
            presente: 'Presente',
            ausente: 'Ausente',
            justificado: 'Justificado'
        };

        return map[situacao] || 'Nao lancada';
    }

    function getTreinoById(treinoId) {
        return state.payload?.treinos?.find((treino) => Number(treino.id) === Number(treinoId)) || null;
    }

    function getPresencasMap(treino) {
        return new Map((treino?.presenca || []).map((item) => [Number(item.aluno_id), item]));
    }

    function buildCalendarEvents(payload) {
        const treinos = Array.isArray(payload?.treinos) ? payload.treinos : [];

        return treinos.map((treino) => ({
            id: `treino-${treino.id}`,
            title: treino.treino?.nome || 'Treino',
            start: String(treino.data_hora_inicio || '').replace(' ', 'T'),
            end: String(treino.data_hora_fim || '').replace(' ', 'T'),
            classNames: [`fc-event-${treino.status || 'agendado'}`],
            extendedProps: {
                treinoId: Number(treino.id)
            }
        }));
    }

    function renderHeader(payload) {
        const turma = payload?.turma || {};
        const partes = [
            turma.instrutor?.nome || 'Instrutor nao definido',
            turma.horarios_resumo || 'Sem horarios cadastrados',
            `Alunos ativos: ${turma.total_alunos || 0}`
        ];

        setText('pageTitle', turma.nome ? `Gerenciar Turma - ${turma.nome}` : 'Gerenciar Turma');
        setText('pageSubtitle', partes.join(' - '));
        setText('calendarPeriodLabel', `Periodo carregado: ${formatPeriod(payload?.periodo?.start, payload?.periodo?.end)}`);
    }

    function renderMetrics(payload) {
        const metricas = payload?.metricas || {};
        setText('metricTotalTreinos', String(metricas.total_treinos ?? 0));
        setText('metricTreinosConcluidos', String(metricas.treinos_concluidos ?? 0));
        setText('metricTreinosAgendados', String(metricas.treinos_agendados ?? 0));
        setText('metricTaxaPresenca', `${metricas.taxa_presenca ?? 0}%`);
    }

    function renderAlunos(payload) {
        const alunos = Array.isArray(payload?.alunos) ? payload.alunos : [];

        if (!alunosTableBody) return;

        if (alunos.length === 0) {
            alunosTableBody.innerHTML = `
                <tr>
                    <td colspan="4">
                        <div class="empty-state my-3">
                            <i class="ph ph-student fs-2 text-muted"></i>
                            <p class="mb-0">Nenhum aluno encontrado nesta turma.</p>
                        </div>
                    </td>
                </tr>
            `;
            return;
        }

        alunosTableBody.innerHTML = alunos.map((aluno) => `
            <tr>
                <td><strong>${escapeHtml(aluno.aluno_nome || '--')}</strong></td>
                <td>${escapeHtml(aluno.codigo_matricula || '--')}</td>
                <td class="text-center">${escapeHtml(formatDate(aluno.data_inscricao))}</td>
                <td class="text-center">
                    <span class="badge ${Number(aluno.ativo) === 1 ? 'bg-success-subtle text-success-emphasis' : 'bg-secondary-subtle text-secondary-emphasis'}">
                        ${Number(aluno.ativo) === 1 ? 'Ativo' : 'Inativo'}
                    </span>
                </td>
            </tr>
        `).join('');
    }

    function countPresencas(presencas) {
        const resumo = {
            total: 0,
            presente: 0,
            ausente: 0,
            justificado: 0
        };

        presencas.forEach((presenca) => {
            const situacao = presenca?.situacao;
            if (!situacao) return;

            resumo.total += 1;
            if (Object.prototype.hasOwnProperty.call(resumo, situacao)) {
                resumo[situacao] += 1;
            }
        });

        return resumo;
    }

    function renderTreinos(payload) {
        const treinos = Array.isArray(payload?.treinos) ? payload.treinos : [];

        if (!treinosList) return;

        if (treinos.length === 0) {
            treinosList.innerHTML = `
                <div class="empty-state">
                    <i class="ph ph-barbell fs-2 text-muted"></i>
                    <p class="mb-0">Ainda nao existem treinos registrados para esta turma.</p>
                </div>
            `;
            return;
        }

        treinosList.innerHTML = treinos.map((treino) => {
            const resumo = countPresencas(treino.presenca || []);

            return `
                <div class="card border shadow-none treino-card" data-treino-id="${treino.id}">
                    <div class="card-body py-3">
                        <div class="d-flex justify-content-between align-items-center flex-wrap gap-3">
                            <div class="d-flex align-items-center gap-3">
                                <div class="text-center border-end pe-3 treino-card-date">
                                    <span class="d-block h6 mb-0">${escapeHtml(formatDate(treino.data_hora_inicio))}</span>
                                    <small class="text-muted">${escapeHtml(formatTime(treino.data_hora_inicio))}</small>
                                </div>
                                <div>
                                    <span class="badge ${getStatusBadge(treino.status)} mb-1">${getStatusLabel(treino.status)}</span>
                                    <h6 class="mb-1">${escapeHtml(treino.treino?.nome || 'Treino')}</h6>
                                    <p class="text-muted small mb-0">
                                        ${escapeHtml(treino.espaco || 'Espaco nao informado')} - ${escapeHtml(treino.instrutor?.nome || 'Instrutor nao informado')}
                                    </p>
                                </div>
                            </div>
                            <div class="treino-card-actions">
                                <div class="d-flex gap-1 flex-wrap">
                                    <span class="badge bg-success-subtle text-success-emphasis">${resumo.presente} P</span>
                                    <span class="badge bg-danger-subtle text-danger-emphasis">${resumo.ausente} A</span>
                                    <span class="badge bg-warning-subtle text-warning-emphasis">${resumo.justificado} J</span>
                                </div>
                                <button type="button" class="btn btn-sm btn-primary js-open-presencas" data-treino-id="${treino.id}">
                                    <i class="ph ph-users-three me-1"></i>Presencas
                                </button>
                                ${treino.status === 'agendado' ? `
                                    <button type="button" class="btn btn-sm btn-danger js-cancel-treino" data-treino-id="${treino.id}">
                                        <i class="ph ph-x-circle me-1"></i>Cancelar
                                    </button>
                                ` : ''}
                            </div>
                        </div>
                    </div>
                </div>
            `;
        }).join('');

        treinosList.querySelectorAll('.js-open-presencas').forEach((button) => {
            button.addEventListener('click', function () {
                openPresencasModal(Number(this.dataset.treinoId));
            });
        });

        treinosList.querySelectorAll('.js-cancel-treino').forEach((button) => {
            button.addEventListener('click', function () {
                cancelTreino(Number(this.dataset.treinoId));
            });
        });
    }

    function renderCalendar(payload) {
        if (!state.calendar) return;

        state.calendar.removeAllEvents();
        state.calendar.addEventSource(buildCalendarEvents(payload));
    }

    function updateSelectedTreinoCard() {
        document.querySelectorAll('.treino-card').forEach((card) => {
            const isSelected = Number(card.dataset.treinoId) === Number(state.selectedTreinoId);
            card.classList.toggle('is-selected', isSelected);
        });
    }

    function renderPresencasResumoFromTable() {
        if (!presencasTableBody || !presencasResumoBadges) return;

        const selects = Array.from(presencasTableBody.querySelectorAll('select[data-aluno-id]'));
        const resumo = {
            presente: 0,
            ausente: 0,
            justificado: 0
        };

        selects.forEach((select) => {
            const value = select.value;
            if (Object.prototype.hasOwnProperty.call(resumo, value)) {
                resumo[value] += 1;
            }
        });

        presencasResumoBadges.innerHTML = `
            <span class="badge bg-success-subtle text-success-emphasis"><i class="ph ph-check-circle me-1"></i>${resumo.presente} Presente</span>
            <span class="badge bg-danger-subtle text-danger-emphasis"><i class="ph ph-x-circle me-1"></i>${resumo.ausente} Ausente</span>
            <span class="badge bg-warning-subtle text-warning-emphasis"><i class="ph ph-warning-circle me-1"></i>${resumo.justificado} Justificado</span>
        `;
    }

    function renderPresencasModal(treino) {
        const alunos = Array.isArray(state.payload?.alunos) ? state.payload.alunos : [];
        const presencasMap = getPresencasMap(treino);

        setText('presencasModalLabel', `Lista de Presenca - ${formatDateTime(treino?.data_hora_inicio)}`);
        setText('presencasModalMeta', `${treino?.espaco || 'Espaco nao informado'} - ${getStatusLabel(treino?.status)}`);

        if (!presencasTableBody) return;

        if (alunos.length === 0) {
            presencasTableBody.innerHTML = `
                <tr>
                    <td colspan="4">
                        <div class="empty-state my-3">
                            <i class="ph ph-student fs-2 text-muted"></i>
                            <p class="mb-0">Nao ha alunos vinculados a esta turma.</p>
                        </div>
                    </td>
                </tr>
            `;
            renderPresencasResumoFromTable();
            return;
        }

        presencasTableBody.innerHTML = alunos.map((aluno) => {
            const presenca = presencasMap.get(Number(aluno.aluno_id));
            const situacao = presenca?.situacao || '';
            const ativo = Number(aluno.ativo) === 1;
            const disabledAttr = !ativo || treino?.status === 'cancelado' ? 'disabled' : '';

            return `
                <tr>
                    <td>
                        <div class="d-flex align-items-center gap-2">
                            <div class="presenca-avatar rounded-circle bg-danger-subtle text-danger d-flex align-items-center justify-content-center">
                                <i class="ph ph-user presenca-avatar-icon"></i>
                            </div>
                            <div>
                                <div>${escapeHtml(aluno.aluno_nome || '--')}</div>
                                <small class="text-muted">${escapeHtml(aluno.codigo_matricula || '--')}</small>
                            </div>
                        </div>
                    </td>
                    <td class="text-center">
                        <span class="badge ${getSituacaoBadge(situacao)}">${getSituacaoLabel(situacao)}</span>
                    </td>
                    <td class="text-center">
                        <select class="form-select form-select-sm" data-aluno-id="${aluno.aluno_id}" ${disabledAttr}>
                            <option value="">Nao lancada</option>
                            <option value="presente" ${situacao === 'presente' ? 'selected' : ''}>Presente</option>
                            <option value="ausente" ${situacao === 'ausente' ? 'selected' : ''}>Ausente</option>
                            <option value="justificado" ${situacao === 'justificado' ? 'selected' : ''}>Justificado</option>
                        </select>
                    </td>
                    <td class="text-center text-muted">${escapeHtml(formatTime(presenca?.checkin_time))}</td>
                </tr>
            `;
        }).join('');

        presencasTableBody.querySelectorAll('select[data-aluno-id]').forEach((select) => {
            select.addEventListener('change', renderPresencasResumoFromTable);
        });

        if (salvarPresencasBtn) {
            salvarPresencasBtn.disabled = treino?.status === 'cancelado';
        }

        renderPresencasResumoFromTable();
    }

    function openPresencasModal(treinoId) {
        const treino = getTreinoById(treinoId);
        if (!treino || !state.modal) return;

        state.selectedTreinoId = Number(treinoId);
        updateSelectedTreinoCard();
        renderPresencasModal(treino);
        state.modal.show();
    }

    function populateSelect(selectElement, items, placeholder, getLabel, getValue) {
        if (!selectElement) return;

        selectElement.innerHTML = '';
        selectElement.appendChild(new Option(placeholder, '', false, false));

        items.forEach((item) => {
            selectElement.appendChild(new Option(getLabel(item), getValue(item), false, false));
        });
    }

    async function loadCatalogs() {
        if (state.catalogsLoaded) return;

        const [treinosResponse, locaisResponse, instrutoresResponse] = await Promise.all([
            fetch('/ctt/api/treinos?simple=true'),
            fetch('/ctt/api/locais?simple=true'),
            fetch('/ctt/api/funcionarios?cargos=2,3&simple=true')
        ]);

        const [treinosPayload, locaisPayload, instrutoresPayload] = await Promise.all([
            treinosResponse.json(),
            locaisResponse.json(),
            instrutoresResponse.json()
        ]);

        if (!treinosResponse.ok) {
            throw new Error(treinosPayload.message || 'Nao foi possivel carregar os treinos disponiveis.');
        }

        if (!locaisResponse.ok) {
            throw new Error(locaisPayload.message || 'Nao foi possivel carregar os locais disponiveis.');
        }

        if (!instrutoresResponse.ok) {
            throw new Error(instrutoresPayload.message || 'Nao foi possivel carregar os instrutores disponiveis.');
        }

        state.catalogs.treinos = parseApiList(treinosPayload);
        state.catalogs.locais = parseApiList(locaisPayload);
        state.catalogs.instrutores = parseApiList(instrutoresPayload);
        state.catalogsLoaded = true;
    }

    function suggestNextDateTimeSlot(referenceDate) {
        const configHorarios = Array.isArray(state.payload?.turma?.config_horarios) ? state.payload.turma.config_horarios : [];
        if (configHorarios.length === 0) {
            return null;
        }

        const weekDayMap = {
            domingo: 0,
            segunda: 1,
            terca: 2,
            quarta: 3,
            quinta: 4,
            sexta: 5,
            sabado: 6
        };

        const baseDate = referenceDate instanceof Date && !Number.isNaN(referenceDate.getTime())
            ? new Date(referenceDate)
            : new Date();

        for (let offset = 0; offset < 14; offset += 1) {
            const candidate = new Date(baseDate);
            candidate.setDate(baseDate.getDate() + offset);

            const matchingConfig = configHorarios.find((item) => weekDayMap[item.dia_semana] === candidate.getDay());
            if (!matchingConfig) {
                continue;
            }

            const [startHour, startMinute] = String(matchingConfig.hora_inicio || '00:00:00').split(':').map(Number);
            const [endHour, endMinute] = String(matchingConfig.hora_fim || '00:00:00').split(':').map(Number);

            const startDate = new Date(candidate);
            startDate.setHours(startHour || 0, startMinute || 0, 0, 0);

            if (startDate <= new Date()) {
                continue;
            }

            const endDate = new Date(candidate);
            endDate.setHours(endHour || 0, endMinute || 0, 0, 0);

            return {
                start: startDate,
                end: endDate
            };
        }

        return null;
    }

    function resetScheduleForm() {
        clearScheduleError();
        clearScheduleFieldErrors();

        const suggestedSlot = suggestNextDateTimeSlot(state.calendar?.getDate?.());

        syncSelect2Value(agendarTreinoId, '');
        syncSelect2Value(agendarEspacoId, '');
        if (agendarObservacoes) agendarObservacoes.value = '';

        syncSelect2Value(
            agendarInstrutorId,
            state.payload?.turma?.instrutor?.id ? String(state.payload.turma.instrutor.id) : ''
        );

        setScheduleDateTimeValue(agendarInicio, state.agendarInicioPicker, suggestedSlot?.start || null);
        setScheduleDateTimeValue(agendarFim, state.agendarFimPicker, suggestedSlot?.end || null);
    }

    async function openScheduleModal() {
        if (!state.agendarModal) return;

        try {
            clearScheduleError();
            await loadCatalogs();

            populateSelect(
                agendarTreinoId,
                state.catalogs.treinos,
                'Selecione um treino...',
                (item) => item.modalidade_nome ? `${item.nome} - ${item.modalidade_nome}` : item.nome,
                (item) => item.id
            );

            populateSelect(
                agendarEspacoId,
                state.catalogs.locais,
                'Selecione um local...',
                (item) => item.nome,
                (item) => item.id
            );

            populateSelect(
                agendarInstrutorId,
                state.catalogs.instrutores,
                'Selecione um instrutor...',
                (item) => [item.nome, item.sobrenome].filter(Boolean).join(' '),
                (item) => item.id
            );

            initializeScheduleSelects();
            initializeScheduleDateTimePickers();
            resetScheduleForm();
            state.agendarModal.show();
        } catch (error) {
            Swal.fire('Erro', error.message || 'Nao foi possivel preparar o agendamento.', 'error');
        }
    }

    function validateScheduleForm() {
        clearScheduleError();
        clearScheduleFieldErrors();

        let isValid = true;

        if (!agendarTreinoId?.value) {
            markInvalid(agendarTreinoId);
            isValid = false;
        }

        if (!agendarEspacoId?.value) {
            markInvalid(agendarEspacoId);
            isValid = false;
        }

        if (!agendarInicio?.value) {
            markInvalid(agendarInicio);
            isValid = false;
        }

        if (!agendarFim?.value) {
            markInvalid(agendarFim);
            isValid = false;
        }

        if (!isValid) {
            showScheduleError('Preencha os campos obrigatorios para agendar o treino.');
            return false;
        }

        const startDate = parseScheduleDateTimeValue(agendarInicio.value);
        const endDate = parseScheduleDateTimeValue(agendarFim.value);

        if (!startDate || !endDate || startDate >= endDate) {
            markInvalid(agendarInicio);
            markInvalid(agendarFim);
            showScheduleError('A data/hora de inicio deve ser anterior ao termino.');
            return false;
        }

        return true;
    }

    async function saveScheduledTreino() {
        if (!validateScheduleForm()) {
            return;
        }

        const startDate = parseScheduleDateTimeValue(agendarInicio.value);
        const endDate = parseScheduleDateTimeValue(agendarFim.value);

        const payload = {
            treino_id: Number(agendarTreinoId.value),
            espaco_id: Number(agendarEspacoId.value),
            instrutor_id: agendarInstrutorId?.value ? Number(agendarInstrutorId.value) : null,
            data_hora_inicio: toApiDateTimeValue(startDate),
            data_hora_fim: toApiDateTimeValue(endDate),
            observacoes: agendarObservacoes?.value.trim() || null,
            status: 'agendado'
        };

        if (confirmarAgendarTreinoBtn) {
            confirmarAgendarTreinoBtn.disabled = true;
        }

        try {
            const response = await fetch(`/ctt/api/turmas/${turmaId}/treinos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao agendar treino.');
            }

            state.agendarModal?.hide();
            treinosTabButton?.click();
            await Swal.fire('Sucesso', 'Treino agendado com sucesso.', 'success');
            await loadManagementData(state.currentRange?.start, state.currentRange?.end, false);
        } catch (error) {
            showScheduleError(error.message || 'Nao foi possivel concluir o agendamento.');
        } finally {
            if (confirmarAgendarTreinoBtn) {
                confirmarAgendarTreinoBtn.disabled = false;
            }
        }
    }

    async function cancelTreino(treinoId) {
        const treino = getTreinoById(treinoId);
        if (!treino) return;

        const confirmation = await Swal.fire({
            icon: 'warning',
            title: 'Cancelar treino?',
            text: `O treino "${treino.treino?.nome || 'Treino'}" sera marcado como cancelado.`,
            showCancelButton: true,
            confirmButtonText: 'Sim, cancelar',
            cancelButtonText: 'Voltar',
            confirmButtonColor: '#dc3545'
        });

        if (!confirmation.isConfirmed) {
            return;
        }

        try {
            const response = await fetch(`/ctt/api/turmas/${turmaId}/treinos/${treinoId}/cancelar`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' }
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao cancelar treino.');
            }

            await Swal.fire('Sucesso', 'Treino cancelado com sucesso.', 'success');
            await loadManagementData(state.currentRange?.start, state.currentRange?.end, false);
        } catch (error) {
            Swal.fire('Erro', error.message || 'Nao foi possivel cancelar o treino.', 'error');
        }
    }

    async function savePresencas() {
        const treino = getTreinoById(state.selectedTreinoId);
        if (!treino || !presencasTableBody) return;

        const selects = Array.from(presencasTableBody.querySelectorAll('select[data-aluno-id]'));
        const payload = {
            presencas: selects.map((select) => ({
                aluno_id: Number(select.dataset.alunoId),
                situacao: select.value
            }))
        };

        if (salvarPresencasBtn) {
            salvarPresencasBtn.disabled = true;
        }

        try {
            const response = await fetch(`/ctt/api/turmas/${turmaId}/treinos/${treino.id}/presencas`, {
                method: 'PUT',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao salvar presencas.');
            }

            await Swal.fire('Sucesso', 'Presencas salvas com sucesso.', 'success');
            await loadManagementData(state.currentRange?.start, state.currentRange?.end, true);
            openPresencasModal(treino.id);
        } catch (error) {
            Swal.fire('Erro', error.message || 'Nao foi possivel salvar as presencas.', 'error');
        } finally {
            if (salvarPresencasBtn) {
                salvarPresencasBtn.disabled = false;
            }
        }
    }

    async function loadManagementData(start, end, preserveSelection = false) {
        clearError();

        try {
            const params = new URLSearchParams();
            if (start) params.set('start', start);
            if (end) params.set('end', end);

            const response = await fetch(`/ctt/api/turmas/${turmaId}/gerenciar?${params.toString()}`);
            const result = await response.json();
            const payload = parseApiItem(result);

            if (!response.ok || !result.success || !payload) {
                throw new Error(result.message || 'Falha ao carregar os dados da turma.');
            }

            state.payload = payload;
            state.currentRange = {
                start: payload.periodo?.start || start,
                end: payload.periodo?.end || end
            };

            renderHeader(payload);
            renderMetrics(payload);
            renderAlunos(payload);
            renderTreinos(payload);
            renderCalendar(payload);

            if (!preserveSelection) {
                state.selectedTreinoId = null;
            }

            if (preserveSelection && state.selectedTreinoId) {
                updateSelectedTreinoCard();
            }
        } catch (error) {
            showError(error.message || 'Nao foi possivel carregar a gestao da turma.');
        }
    }

    function initCalendar() {
        if (!calendarElement || typeof FullCalendar === 'undefined') return;

        state.calendar = new FullCalendar.Calendar(calendarElement, {
            locale: 'pt-br',
            initialView: window.innerWidth < 768 ? 'listWeek' : 'dayGridMonth',
            height: 'auto',
            headerToolbar: {
                left: 'prev,next today',
                center: 'title',
                right: 'dayGridMonth,timeGridWeek,listWeek'
            },
            buttonText: {
                today: 'Hoje',
                month: 'Mes',
                week: 'Semana',
                list: 'Lista'
            },
            datesSet(info) {
                const start = info.startStr.slice(0, 19).replace('T', ' ');
                const end = info.endStr.slice(0, 19).replace('T', ' ');
                const rangeKey = `${start}|${end}`;

                if (state.lastRangeKey === rangeKey) {
                    return;
                }

                state.lastRangeKey = rangeKey;
                loadManagementData(start, end);
            },
            eventClick(info) {
                treinosTabButton?.click();
                openPresencasModal(Number(info.event.extendedProps.treinoId));
            }
        });

        state.calendar.render();
    }

    salvarPresencasBtn?.addEventListener('click', savePresencas);
    abrirAgendarTreinoBtn?.addEventListener('click', openScheduleModal);
    confirmarAgendarTreinoBtn?.addEventListener('click', saveScheduledTreino);

    if (agendarTreinoModalElement) {
        agendarTreinoModalElement.addEventListener('hidden.bs.modal', function () {
            clearScheduleError();
            clearScheduleFieldErrors();
        });
    }

    if (turmaId < 1) {
        showError('Turma invalida para gerenciamento.');
        return;
    }

    initCalendar();

    if (!state.calendar) {
        loadManagementData(null, null);
    }
});
