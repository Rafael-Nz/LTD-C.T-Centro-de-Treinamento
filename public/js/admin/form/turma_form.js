document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTurma');
    const id = form?.dataset.id || null;
    const horariosContainer = document.getElementById('horariosContainer');
    const horarioTemplate = document.getElementById('horarioRowTemplate');
    const addHorarioBtn = document.getElementById('addHorarioBtn');
    let horarioRowCounter = 0;

    function showError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.add('is-invalid');
        let feedback = field.nextElementSibling;

        if (!feedback || !feedback.classList.contains('invalid-feedback')) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.insertBefore(feedback, field.nextSibling);
        }

        feedback.textContent = msg;
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function clearHorarioErrors() {
        document.querySelectorAll('.horario-row .is-invalid').forEach(el => el.classList.remove('is-invalid'));
        document.querySelectorAll('.horario-feedback').forEach(el => el.remove());
    }

    function showHorarioError(field, msg) {
        if (!field) return;

        field.classList.add('is-invalid');
        let feedback = field.parentNode.querySelector('.horario-feedback');

        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback horario-feedback';
            field.parentNode.appendChild(feedback);
        }

        feedback.textContent = msg;
    }

    function normalizeTime(value) {
        if (!value) return '';
        return value.slice(0, 5);
    }

    function parseFuncionarioList(payload) {
        if (!payload) return [];
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload.data)) return payload.data;
        if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
        if (payload.data && payload.data.data && Array.isArray(payload.data.data)) return payload.data.data;
        return [];
    }

    function initializeHorarioSelect2(selectElement) {
        if (!selectElement || !$.fn.select2) return;

        const row = selectElement.closest('.horario-row');

        $(selectElement).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Selecione...',
            allowClear: true,
            dropdownParent: row
        });
    }

    function buildTimePicker(containerElement) {
        if (!containerElement || typeof tempusDominus === 'undefined') {
            return null;
        }

        return new tempusDominus.TempusDominus(containerElement, {
            container: document.body,
            useCurrent: false,
            localization: {
                locale: 'pt-BR',
                format: 'HH:mm',
                hourCycle: 'h23'
            },
            display: {
                viewMode: 'clock',
                theme: 'light',
                components: {
                    calendar: false,
                    date: false,
                    month: false,
                    year: false,
                    decades: false,
                    clock: true,
                    hours: true,
                    minutes: true,
                    seconds: false
                },
                buttons: {
                    today: false,
                    clear: true,
                    close: true
                }
            }
        });
    }

    function initializeHorarioPickers(row) {
        const diaField = row.querySelector('.horario-dia');
        const inicioPickerElement = row.querySelector('.horario-inicio-picker');
        const fimPickerElement = row.querySelector('.horario-fim-picker');

        initializeHorarioSelect2(diaField);

        const inicioPicker = buildTimePicker(inicioPickerElement);
        const fimPicker = buildTimePicker(fimPickerElement);

        row._inicioPicker = inicioPicker;
        row._fimPicker = fimPicker;
    }

    function destroyHorarioWidgets(row) {
        const diaField = row.querySelector('.horario-dia');

        if (diaField && $(diaField).hasClass('select2-hidden-accessible')) {
            $(diaField).select2('destroy');
        }

        if (row._inicioPicker && typeof row._inicioPicker.dispose === 'function') {
            row._inicioPicker.dispose();
        }

        if (row._fimPicker && typeof row._fimPicker.dispose === 'function') {
            row._fimPicker.dispose();
        }
    }

    function setValue(elemId, value) {
        const field = document.getElementById(elemId);
        if (!field) return;

        field.value = value ?? '';

        if ($(field).hasClass('select2-hidden-accessible')) {
            $(field).trigger('change');
        }
    }

    function addHorarioRow(horario = {}) {
        if (!horarioTemplate || !horariosContainer) return null;

        const fragment = horarioTemplate.content.cloneNode(true);
        const row = fragment.querySelector('.horario-row');
        const diaField = row.querySelector('.horario-dia');
        const inicioField = row.querySelector('.horario-inicio');
        const fimField = row.querySelector('.horario-fim');
        const removeBtn = row.querySelector('.remove-horario-btn');
        const rowId = `horario-row-${horarioRowCounter += 1}`;

        row.dataset.rowId = rowId;
        diaField.value = horario.dia_semana || '';
        inicioField.value = normalizeTime(horario.hora_inicio || '');
        fimField.value = normalizeTime(horario.hora_fim || '');

        removeBtn.addEventListener('click', function () {
            destroyHorarioWidgets(row);
            row.remove();
        });

        horariosContainer.appendChild(row);
        initializeHorarioPickers(row);
        return row;
    }

    function getHorarios() {
        return Array.from(document.querySelectorAll('.horario-row'))
            .map(row => {
                const dia = row.querySelector('.horario-dia')?.value || '';
                const inicio = row.querySelector('.horario-inicio')?.value || '';
                const fim = row.querySelector('.horario-fim')?.value || '';

                if (!dia && !inicio && !fim) {
                    return null;
                }

                return {
                    dia_semana: dia,
                    hora_inicio: inicio,
                    hora_fim: fim
                };
            })
            .filter(Boolean);
    }

    function validateHorarios() {
        let valid = true;
        clearHorarioErrors();

        const horarios = [];

        document.querySelectorAll('.horario-row').forEach(row => {
            const diaField = row.querySelector('.horario-dia');
            const inicioField = row.querySelector('.horario-inicio');
            const fimField = row.querySelector('.horario-fim');

            const dia = diaField?.value || '';
            const inicio = inicioField?.value || '';
            const fim = fimField?.value || '';

            if (!dia && !inicio && !fim) {
                return;
            }

            if (!dia) {
                showHorarioError(diaField, 'Selecione o dia da semana.');
                valid = false;
            }

            if (!inicio) {
                showHorarioError(inicioField, 'Informe a hora de inicio.');
                valid = false;
            }

            if (!fim) {
                showHorarioError(fimField, 'Informe a hora de fim.');
                valid = false;
            }

            if (inicio && fim && inicio >= fim) {
                showHorarioError(inicioField, 'A hora de inicio deve ser menor que a hora de fim.');
                valid = false;
            }

            if (dia && inicio && fim) {
                horarios.push({ dia, inicio, fim, inicioField });
            }
        });

        const grouped = {};
        horarios.forEach(horario => {
            grouped[horario.dia] = grouped[horario.dia] || [];
            grouped[horario.dia].push(horario);
        });

        Object.values(grouped).forEach(lista => {
            lista.sort((a, b) => a.inicio.localeCompare(b.inicio));

            for (let i = 1; i < lista.length; i += 1) {
                if (lista[i].inicio < lista[i - 1].fim) {
                    showHorarioError(lista[i].inicioField, 'Existe sobreposicao de horarios neste dia.');
                    valid = false;
                }
            }
        });

        return valid;
    }

    function validate() {
        let valid = true;
        clearErrors();

        const nome = document.getElementById('nome')?.value.trim();
        if (!nome) {
            showError('nome', 'Nome da turma e obrigatorio');
            valid = false;
        } else if (nome.length > 100) {
            showError('nome', 'Nome nao pode exceder 100 caracteres');
            valid = false;
        }

        const capMin = parseInt(document.getElementById('capacidade_minima')?.value || 0, 10);
        if (!capMin || capMin < 1) {
            showError('capacidade_minima', 'Capacidade minima deve ser um numero positivo');
            valid = false;
        }

        const capMax = parseInt(document.getElementById('capacidade_maxima')?.value || 0, 10);
        if (!capMax || capMax < 1) {
            showError('capacidade_maxima', 'Capacidade maxima deve ser um numero positivo');
            valid = false;
        }

        if (capMin >= capMax) {
            showError('capacidade_minima', 'Capacidade minima deve ser menor que a maxima');
            valid = false;
        }

        if (!validateHorarios()) {
            valid = false;
        }

        return valid;
    }

    function buildPayload() {
        return {
            nome: document.getElementById('nome')?.value.trim(),
            instrutor_id: document.getElementById('instrutor_id')?.value
                ? parseInt(document.getElementById('instrutor_id')?.value, 10)
                : null,
            capacidade_minima: parseInt(document.getElementById('capacidade_minima')?.value || 0, 10),
            capacidade_maxima: parseInt(document.getElementById('capacidade_maxima')?.value || 0, 10),
            config_horarios: getHorarios()
        };
    }

    async function loadInstrutores() {
        const instrutorSelect = document.getElementById('instrutor_id');
        if (!instrutorSelect) return;

        try {
            const response = await fetch('/ctt/api/funcionarios?cargos=2,3&simple=true');
            const result = await response.json();
            const listaFuncionarios = parseFuncionarioList(result);

            instrutorSelect.innerHTML = '<option value="">Selecione um instrutor...</option>';

            listaFuncionarios.forEach(funcionario => {
                const option = document.createElement('option');
                option.value = funcionario.id;
                option.textContent = [funcionario.nome, funcionario.sobrenome].filter(Boolean).join(' ');
                instrutorSelect.appendChild(option);
            });

            if ($.fn.select2) {
                $(instrutorSelect).select2({
                    theme: 'bootstrap-5',
                    width: '100%',
                    placeholder: 'Selecione um instrutor...',
                    allowClear: true
                });
            }
        } catch (error) {
            console.error('Erro ao carregar instrutores:', error);
            Swal.fire('Aviso', 'Nao foi possivel carregar os instrutores.', 'warning');
        }
    }

    async function loadTurmaData() {
        if (!id) return;

        try {
            const res = await fetch(`/ctt/api/turmas/${id}`);
            const result = await res.json();
            const turma = result.data || null;

            if (!res.ok || !result.success || !turma) {
                throw new Error(result.message || 'Erro ao carregar turma');
            }

            setValue('nome', turma.nome);
            setValue('instrutor_id', turma.instrutor_id);
            setValue('capacidade_minima', turma.capacidade_minima);
            setValue('capacidade_maxima', turma.capacidade_maxima);

            horariosContainer.innerHTML = '';

            if (Array.isArray(turma.config_horarios) && turma.config_horarios.length > 0) {
                turma.config_horarios.forEach(addHorarioRow);
            } else {
                addHorarioRow();
            }
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao carregar dados da turma.', 'error');
        }
    }

    async function init() {
        await loadInstrutores();

        if (id) {
            await loadTurmaData();
        } else {
            addHorarioRow();
        }
    }

    if (addHorarioBtn) {
        addHorarioBtn.addEventListener('click', function () {
            addHorarioRow();
        });
    }

    init();

    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!validate()) {
                Swal.fire('Atencao', 'Verifique os campos obrigatorios.', 'warning');
                return;
            }

            const payload = buildPayload();
            const url = id ? `/ctt/api/turmas/${id}` : '/ctt/api/turmas';
            const method = id ? 'PUT' : 'POST';
            const btn = this.querySelector('[type="submit"]');

            if (btn) btn.disabled = true;

            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });

                const data = await res.json();

                if (!res.ok || !data.success) {
                    throw new Error(data.message || 'Erro ao processar turma');
                }

                await Swal.fire({
                    icon: 'success',
                    title: id ? 'Atualizada!' : 'Cadastrada!',
                    text: id ? 'Turma atualizada com sucesso.' : 'Turma cadastrada com sucesso.'
                });

                window.location.href = '/ctt/admin/turmas';
            } catch (error) {
                Swal.fire('Erro', error.message || 'Falha ao salvar turma.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    }
});
