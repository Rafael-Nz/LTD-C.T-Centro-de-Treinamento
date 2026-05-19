document.addEventListener('DOMContentLoaded', function () {
    const mainContent = document.getElementById('mainContent');
    let currentAlunoId = Number(mainContent?.dataset.alunoId || 0);
    const avaliacaoId = Number(mainContent?.dataset.avaliacaoId || 0);
    const acao = mainContent?.dataset.acao || 'cadastrar';
    const form = document.getElementById('formAvaliacao');
    const errorAlert = document.getElementById('avaliacaoFormError');
    const dataAvaliacao = document.getElementById('dataAvaliacao');
    const dataAvaliacaoPickerElement = document.getElementById('dataAvaliacaoPicker');

    const fields = {
        peso: document.getElementById('peso'),
        altura: document.getElementById('altura'),
        cintura: document.getElementById('cintura'),
        torax: document.getElementById('torax'),
        braco_dc: document.getElementById('bracoDC'),
        braco_d: document.getElementById('bracoD'),
        braco_ec: document.getElementById('bracoEC'),
        braco_e: document.getElementById('bracoE'),
        coxa_d: document.getElementById('coxaD'),
        coxa_e: document.getElementById('coxaE'),
        panturrilha_d: document.getElementById('panturrilhaD'),
        panturrilha_e: document.getElementById('panturrilhaE'),
        percentual_gordura: document.getElementById('bodyFat'),
        percentual_musculo: document.getElementById('muscle'),
        metabolismo_repouso: document.getElementById('rm'),
        idade_biologica: document.getElementById('bodyAge'),
        gordura_visceral: document.getElementById('visceralFat'),
        observacoes: document.getElementById('observacoes')
    };

    const computedFields = {
        imc: document.getElementById('imc'),
        imcClassificacao: document.getElementById('imcClassificacao'),
        bodyFatClassificacao: document.getElementById('bodyFatClassificacao'),
        muscleClassificacao: document.getElementById('muscleClassificacao'),
        visceralFatClassificacao: document.getElementById('visceralFatClassificacao')
    };

    const alunoContext = {
        genero: null,
        dataNascimento: null
    };

    let datePicker = null;

    function updateNavigationLinks() {
        const fallback = currentAlunoId > 0 ? `/ctt/admin/alunos/visualizar/${currentAlunoId}` : '/ctt/admin/alunos';
        document.querySelectorAll('a[href="/ctt/admin/alunos"], a[href^="/ctt/admin/alunos/visualizar/"]').forEach((link) => {
            if (link.textContent.includes('Voltar') || link.textContent.includes('Cancelar')) {
                link.href = fallback;
            }
        });
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

    function getTempus() {
        return window.tempusDominus || window.tempusdominus || null;
    }

    function initDatePicker() {
        const tempus = getTempus();
        if (!tempus?.TempusDominus || !dataAvaliacaoPickerElement) {
            if (dataAvaliacao && !dataAvaliacao.value) {
                dataAvaliacao.value = formatDateForDisplay(new Date());
            }
            return;
        }

        datePicker = new tempus.TempusDominus(dataAvaliacaoPickerElement, {
            localization: tempus.locales?.pt || { locale: 'pt-BR', format: 'dd/MM/yyyy' },
            display: {
                components: {
                    calendar: true,
                    date: true,
                    month: true,
                    year: true,
                    decades: true,
                    clock: false,
                    hours: false,
                    minutes: false,
                    seconds: false
                },
                buttons: {
                    today: true,
                    clear: true,
                    close: true
                }
            }
        });

        if (!dataAvaliacao.value) {
            setDateInputValue(new Date());
        }
    }

    function formatDateForDisplay(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const day = String(date.getDate()).padStart(2, '0');
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const year = date.getFullYear();
        return `${day}/${month}/${year}`;
    }

    function parseDisplayDate(value) {
        if (!value) return null;
        const match = String(value).trim().match(/^(\d{2})\/(\d{2})\/(\d{4})$/);
        if (!match) return null;
        const [, day, month, year] = match;
        const parsed = new Date(Number(year), Number(month) - 1, Number(day));
        return Number.isNaN(parsed.getTime()) ? null : parsed;
    }

    function formatDateForApi(date) {
        if (!(date instanceof Date) || Number.isNaN(date.getTime())) return '';
        const year = date.getFullYear();
        const month = String(date.getMonth() + 1).padStart(2, '0');
        const day = String(date.getDate()).padStart(2, '0');
        return `${year}-${month}-${day}`;
    }

    function setDateInputValue(date) {
        const formatted = formatDateForDisplay(date);
        if (dataAvaliacao) {
            dataAvaliacao.value = formatted;
        }

        try {
            const tempus = getTempus();
            if (datePicker?.dates?.setValue && tempus?.DateTime?.convert) {
                datePicker.dates.setValue(tempus.DateTime.convert(date));
            }
        } catch (_) {
            // fallback silencioso para input simples
        }
    }

    function setValue(id, value) {
        const field = document.getElementById(id);
        if (field) {
            field.value = value ?? '';
        }
    }

    function formatNumber(value, digits = 2) {
        if (value === null || value === undefined || value === '') return '';
        const number = Number(value);
        if (Number.isNaN(number)) return '';
        return number.toFixed(digits);
    }

    function calculateAgeAtEvaluation() {
        if (!alunoContext.dataNascimento || !dataAvaliacao?.value) return null;

        const birthDate = new Date(alunoContext.dataNascimento);
        const evaluationDate = parseDisplayDate(dataAvaliacao.value);
        if (Number.isNaN(birthDate.getTime()) || !evaluationDate) return null;

        let age = evaluationDate.getFullYear() - birthDate.getFullYear();
        const monthDiff = evaluationDate.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && evaluationDate.getDate() < birthDate.getDate())) {
            age -= 1;
        }

        return age;
    }

    function calculateImc() {
        const peso = Number(fields.peso?.value);
        const altura = Number(fields.altura?.value);
        if (!peso || !altura) return null;
        return Number((peso / (altura * altura)).toFixed(2));
    }

    function classifyImc(imc) {
        if (imc === null || Number.isNaN(imc)) return '';
        if (imc < 18.5) return 'Abaixo do peso';
        if (imc < 25) return 'Normal';
        if (imc < 30) return 'Sobrepeso';
        return 'Obeso';
    }

    function getBodyFatRange(genero, idade) {
        if (genero === 'F') {
            if (idade >= 20 && idade <= 39) return [21.0, 32.9, 38.9];
            if (idade >= 40 && idade <= 59) return [23.0, 33.9, 39.9];
            if (idade >= 60 && idade <= 79) return [24.0, 35.9, 41.9];
        }

        if (genero === 'M') {
            if (idade >= 20 && idade <= 39) return [8.0, 19.9, 24.9];
            if (idade >= 40 && idade <= 59) return [11.0, 21.9, 27.9];
            if (idade >= 60 && idade <= 79) return [13.0, 24.9, 29.9];
        }

        return null;
    }

    function getMuscleRange(genero, idade) {
        if (genero === 'F') {
            if (idade >= 18 && idade <= 39) return [24.3, 30.3, 35.3];
            if (idade >= 40 && idade <= 59) return [24.1, 30.1, 35.1];
            if (idade >= 60 && idade <= 80) return [23.9, 29.9, 34.9];
        }

        if (genero === 'M') {
            if (idade >= 18 && idade <= 39) return [33.3, 39.3, 44.0];
            if (idade >= 40 && idade <= 59) return [33.1, 39.1, 43.8];
            if (idade >= 60 && idade <= 80) return [32.9, 38.9, 43.6];
        }

        return null;
    }

    function classifyByRange(value, range) {
        if (value === null || Number.isNaN(value) || !range) return '';
        if (value < range[0]) return 'Baixo';
        if (value <= range[1]) return 'Normal';
        if (value <= range[2]) return 'Alto';
        return 'Muito Alto';
    }

    function classifyVisceral(value) {
        if (value === null || Number.isNaN(value)) return '';
        if (value <= 9) return 'Nivel Normal';
        if (value <= 14) return 'Nivel Alto';
        return 'Nivel Muito Alto';
    }

    function updateComputedFields() {
        const idade = calculateAgeAtEvaluation();
        const imc = calculateImc();
        const bodyFat = Number(fields.percentual_gordura?.value);
        const muscle = Number(fields.percentual_musculo?.value);
        const visceral = Number(fields.gordura_visceral?.value);

        computedFields.imc.value = imc !== null ? formatNumber(imc, 2) : '';
        computedFields.imcClassificacao.value = classifyImc(imc);
        computedFields.bodyFatClassificacao.value = classifyByRange(bodyFat, getBodyFatRange(alunoContext.genero, idade));
        computedFields.muscleClassificacao.value = classifyByRange(muscle, getMuscleRange(alunoContext.genero, idade));
        computedFields.visceralFatClassificacao.value = classifyVisceral(visceral);
        setValue('alunoIdade', idade !== null ? `${idade} anos` : '');
    }

    function buildPayload() {
        const date = parseDisplayDate(dataAvaliacao?.value);
        return {
            data_avaliacao: formatDateForApi(date),
            peso: fields.peso.value || null,
            altura: fields.altura.value || null,
            cintura: fields.cintura.value || null,
            torax: fields.torax.value || null,
            braco_dc: fields.braco_dc.value || null,
            braco_d: fields.braco_d.value || null,
            braco_ec: fields.braco_ec.value || null,
            braco_e: fields.braco_e.value || null,
            coxa_d: fields.coxa_d.value || null,
            coxa_e: fields.coxa_e.value || null,
            panturrilha_d: fields.panturrilha_d.value || null,
            panturrilha_e: fields.panturrilha_e.value || null,
            percentual_gordura: fields.percentual_gordura.value || null,
            percentual_musculo: fields.percentual_musculo.value || null,
            metabolismo_repouso: fields.metabolismo_repouso.value || null,
            idade_biologica: fields.idade_biologica.value || null,
            gordura_visceral: fields.gordura_visceral.value || null,
            observacoes: fields.observacoes.value.trim() || null
        };
    }

    function validateForm() {
        clearError();

        if (currentAlunoId < 1) {
            showError('Aluno invalido para esta avaliacao.');
            return false;
        }

        if (!parseDisplayDate(dataAvaliacao?.value)) {
            showError('Informe uma data de avaliacao valida.');
            return false;
        }

        return true;
    }

    async function loadAluno() {
        const response = await fetch(`/ctt/api/alunos/${currentAlunoId}`);
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Nao foi possivel carregar o aluno.');
        }

        const aluno = result.data || result;
        alunoContext.genero = aluno.genero || 'O';
        alunoContext.dataNascimento = aluno.data_nascimento || null;

        setValue('alunoNome', `${aluno.nome || ''} ${aluno.sobrenome || ''}`.trim());
        setValue('alunoSexo', aluno.genero === 'M' ? 'Masculino' : aluno.genero === 'F' ? 'Feminino' : 'Outro');
        updateNavigationLinks();
        updateComputedFields();
    }

    async function loadAvaliacao() {
        if (!avaliacaoId) return;

        const response = await fetch(`/ctt/api/avaliacoes/${avaliacaoId}`);
        const result = await response.json();
        if (!response.ok) {
            throw new Error(result.message || 'Nao foi possivel carregar a avaliacao.');
        }

        const avaliacao = result.data || result;
        if (avaliacao.aluno?.id && currentAlunoId < 1) {
            currentAlunoId = Number(avaliacao.aluno.id);
            mainContent.dataset.alunoId = String(currentAlunoId);
        }
        const date = avaliacao.data_avaliacao ? new Date(`${avaliacao.data_avaliacao}T00:00:00`) : null;
        if (date && !Number.isNaN(date.getTime())) {
            setDateInputValue(date);
        }

        setValue('avaliadorNome', avaliacao.avaliador?.nome || '');
        setValue('alunoNome', avaliacao.aluno?.nome || '');
        fields.peso.value = formatNumber(avaliacao.peso, 2);
        fields.altura.value = formatNumber(avaliacao.altura, 2);
        fields.cintura.value = formatNumber(avaliacao.cintura, 2);
        fields.torax.value = formatNumber(avaliacao.torax, 2);
        fields.braco_dc.value = formatNumber(avaliacao.braco_dc, 2);
        fields.braco_d.value = formatNumber(avaliacao.braco_d, 2);
        fields.braco_ec.value = formatNumber(avaliacao.braco_ec, 2);
        fields.braco_e.value = formatNumber(avaliacao.braco_e, 2);
        fields.coxa_d.value = formatNumber(avaliacao.coxa_d, 2);
        fields.coxa_e.value = formatNumber(avaliacao.coxa_e, 2);
        fields.panturrilha_d.value = formatNumber(avaliacao.panturrilha_d, 2);
        fields.panturrilha_e.value = formatNumber(avaliacao.panturrilha_e, 2);
        fields.percentual_gordura.value = formatNumber(avaliacao.percentual_gordura, 2);
        fields.percentual_musculo.value = formatNumber(avaliacao.percentual_musculo, 2);
        fields.metabolismo_repouso.value = avaliacao.metabolismo_repouso ?? '';
        fields.idade_biologica.value = avaliacao.idade_biologica ?? '';
        fields.gordura_visceral.value = formatNumber(avaliacao.gordura_visceral, 2);
        fields.observacoes.value = avaliacao.observacoes || '';
        updateNavigationLinks();
        updateComputedFields();
    }

    async function submitForm(event) {
        event.preventDefault();
        if (!validateForm()) return;

        const payload = buildPayload();
        const url = avaliacaoId ? `/ctt/api/avaliacoes/${avaliacaoId}` : `/ctt/api/alunos/${currentAlunoId}/avaliacoes`;
        const method = avaliacaoId ? 'PUT' : 'POST';
        const submitButton = form?.querySelector('[type="submit"]');

        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(url, {
                method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();
            if (!response.ok) {
                throw new Error(result.message || 'Falha ao salvar avaliacao.');
            }

            await Swal.fire('Sucesso', avaliacaoId ? 'Avaliacao atualizada com sucesso.' : 'Avaliacao criada com sucesso.', 'success');
            window.location.href = `/ctt/admin/alunos/visualizar/${currentAlunoId}`;
        } catch (error) {
            showError(error.message || 'Nao foi possivel salvar a avaliacao.');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    }

    Object.values(fields).forEach((field) => {
        field?.addEventListener('input', updateComputedFields);
    });
    dataAvaliacao?.addEventListener('change', updateComputedFields);
    form?.addEventListener('submit', submitForm);

    initDatePicker();

    (async function init() {
        try {
            if (!dataAvaliacao?.value) {
                setDateInputValue(new Date());
            }
            if (acao === 'editar' && avaliacaoId > 0) {
                await loadAvaliacao();
            }
            if (currentAlunoId > 0) {
                await loadAluno();
            }
        } catch (error) {
            showError(error.message || 'Nao foi possivel preparar o formulario.');
        }
    }());
});
