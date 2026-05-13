/**
 * aluno_form.js - Gerenciamento de Alunos com Integracao de Anamnese
 */
document.addEventListener('DOMContentLoaded', function () {
    let anamneseEngine = null;
    const form = document.getElementById('formAluno');
    const id = form?.dataset.id || null;
    const turmaSelect = document.getElementById('turma');

    function initTurmaSelect2() {
        if (!turmaSelect || typeof window.jQuery === 'undefined' || typeof window.jQuery.fn.select2 === 'undefined') {
            return;
        }

        window.jQuery(turmaSelect).select2({
            theme: 'bootstrap-5',
            width: '100%',
            placeholder: 'Selecione uma turma',
            allowClear: true
        });
    }

    function formatTurmaOptionLabel(turma) {
        return turma.nome || '';
    }

    function applyMasks() {
        if (typeof IMask === 'undefined') return;

        const masks = [
            { id: 'cpf', mask: '000.000.000-00' },
            { id: 'telefone1', mask: '(00) 00000-0000' },
            { id: 'telefone2', mask: '(00) 0000-0000' },
            { id: 'cep', mask: '00000-000' }
        ];

        masks.forEach((maskConfig) => {
            const el = document.getElementById(maskConfig.id);
            if (el) IMask(el, { mask: maskConfig.mask });
        });
    }

    function setupCepLookup() {
        const btn = document.getElementById('buscarCep');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const cep = document.getElementById('cep')?.value.replace(/\D/g, '');
            if (cep?.length !== 8) {
                Swal.fire('Atencao', 'Digite um CEP valido.', 'warning');
                return;
            }

            try {
                const response = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await response.json();
                if (data.erro) throw new Error();

                setValue('endereco', data.logradouro);
                setValue('bairro', data.bairro);
                setValue('cidade', data.localidade);
                document.getElementById('numero')?.focus();
            } catch {
                Swal.fire('Erro', 'CEP nao encontrado.', 'error');
            }
        });
    }

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
        document.querySelectorAll('.invalid-feedback').forEach((el) => el.remove());
        document.querySelectorAll('.is-invalid').forEach((el) => el.classList.remove('is-invalid'));
    }

    function validate() {
        let valid = true;
        clearErrors();

        const required = [
            { id: 'nome', msg: 'Nome e obrigatorio' },
            { id: 'sobrenome', msg: 'Sobrenome e obrigatorio' },
            { id: 'endereco', msg: 'Endereco e obrigatorio' },
            { id: 'numero', msg: 'Numero e obrigatorio' }
        ];

        required.forEach((fieldConfig) => {
            if (!document.getElementById(fieldConfig.id)?.value.trim()) {
                showError(fieldConfig.id, fieldConfig.msg);
                valid = false;
            }
        });

        const cpf = document.getElementById('cpf')?.value.replace(/\D/g, '');
        if (!cpf || cpf.length !== 11) {
            showError('cpf', 'CPF invalido');
            valid = false;
        }

        if (!turmaSelect?.value) {
            showError('turma', 'Turma e obrigatoria');
            valid = false;
        }

        return valid;
    }

    function buildPayload() {
        const payload = {
            nome: document.getElementById('nome')?.value.trim(),
            sobrenome: document.getElementById('sobrenome')?.value.trim(),
            cpf: document.getElementById('cpf')?.value.replace(/\D/g, ''),
            email: document.getElementById('email')?.value,
            data_nascimento: document.getElementById('nascimento')?.value,
            genero: document.getElementById('genero')?.value || 'O',
            data_matricula: document.getElementById('dataMatricula')?.value,
            cadastrado_por: parseInt(document.getElementById('cadastrado_por')?.value || 1, 10),
            turma_ids: turmaSelect?.value ? [Number(turmaSelect.value)] : [],
            endereco: {
                logradouro: document.getElementById('endereco')?.value,
                numero: document.getElementById('numero')?.value,
                cidade: document.getElementById('cidade')?.value,
                bairro: document.getElementById('bairro')?.value,
                cep: document.getElementById('cep')?.value.replace(/\D/g, ''),
                complemento: document.getElementById('complemento')?.value
            },
            contatos: []
        };

        ['telefone1', 'telefone2'].forEach((fieldId, index) => {
            const value = document.getElementById(fieldId)?.value.replace(/\D/g, '');
            if (value) {
                payload.contatos.push({
                    tipo: index === 0 ? 'telefone' : 'whatsapp',
                    valor: value
                });
            }
        });

        const email2 = document.getElementById('email2')?.value.trim();
        if (email2) {
            payload.contatos.push({ tipo: 'email_secundario', valor: email2 });
        }

        return payload;
    }

    async function loadTurmas(selectedTurmaId = null) {
        if (!turmaSelect) return;

        try {
            const response = await fetch('/ctt/api/turmas?simple=true');
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Nao foi possivel carregar as turmas.');
            }

            const turmas = Array.isArray(result.data) ? result.data : [];
            turmaSelect.innerHTML = '<option value="" selected disabled>Selecione uma turma</option>';

            turmas.forEach((turma) => {
                const option = document.createElement('option');
                option.value = turma.id;
                option.textContent = formatTurmaOptionLabel(turma);
                turmaSelect.appendChild(option);
            });

            if (selectedTurmaId) {
                turmaSelect.value = String(selectedTurmaId);
            }

            if (typeof window.jQuery !== 'undefined' && typeof window.jQuery.fn.select2 !== 'undefined') {
                window.jQuery(turmaSelect).trigger('change.select2');
            }
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao carregar as turmas disponiveis.', 'error');
        }
    }

    async function loadAlunoData() {
        if (!id) {
            setValue('dataMatricula', new Date().toISOString().split('T')[0]);
            await loadTurmas();
            return;
        }

        try {
            const response = await fetch(`/ctt/api/alunos/${id}`);
            const result = await response.json();
            const aluno = result.data || result;

            setValue('nome', aluno.nome);
            setValue('sobrenome', aluno.sobrenome);
            setValue('cpf', aluno.cpf);
            setValue('nascimento', aluno.data_nascimento);
            setValue('genero', aluno.genero);
            setValue('email', aluno.email);
            setValue('dataMatricula', aluno.data_matricula);

            if (aluno.contatos) {
                const telefone = aluno.contatos.find((contato) => contato.tipo === 'telefone');
                const whatsapp = aluno.contatos.find((contato) => contato.tipo === 'whatsapp');
                const emailSecundario = aluno.contatos.find((contato) => contato.tipo === 'email_secundario');

                setValue('telefone1', telefone?.valor || '');
                setValue('telefone2', whatsapp?.valor || '');
                setValue('email2', emailSecundario?.valor || '');
            }

            setValue('endereco', aluno.logradouro);
            setValue('numero', aluno.numero);
            setValue('cidade', aluno.cidade);
            setValue('bairro', aluno.bairro);
            setValue('cep', aluno.cep);
            setValue('complemento', aluno.complemento);

            await loadTurmas(aluno.turmas?.[0]?.id ?? null);

            ['cpf', 'telefone1', 'telefone2', 'cep'].forEach((fieldId) => {
                document.getElementById(fieldId)?.dispatchEvent(new Event('input'));
            });
        } catch {
            Swal.fire('Erro', 'Falha ao carregar dados do aluno.', 'error');
        }
    }

    function setValue(elemId, value) {
        const field = document.getElementById(elemId);
        if (field) field.value = value || '';
    }

    async function init() {
        applyMasks();
        setupCepLookup();
        initTurmaSelect2();

        if (document.getElementById('anamnese-container')) {
            anamneseEngine = new AnamneseEngine({
                containerId: 'anamnese-container',
                alunoId: id ? Number(id) : null,
                formularioId: 1,
                basePath: '/ctt/api'
            });
            await anamneseEngine.init();
        }

        await loadAlunoData();
    }

    init();

    async function submitAnamnese(alunoId) {
        if (!anamneseEngine) return;

        const respostas = anamneseEngine.getData();
        const perguntasObrigatorias = anamneseEngine.perguntas.filter((pergunta) => pergunta.obrigatoria);

        const faltamRespostas = perguntasObrigatorias.some((pergunta) => {
            const resposta = respostas.find((item) => item.pergunta_id === pergunta.id);
            return !resposta || resposta.valor === null || resposta.valor === '';
        });

        if (faltamRespostas) {
            throw new Error('Por favor, preencha todas as perguntas obrigatorias da anamnese.');
        }

        const response = await fetch('/ctt/api/anamnese', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ aluno_id: Number(alunoId), respostas })
        });

        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Erro ao salvar anamnese');
        }
    }

    if (form) {
        form.addEventListener('submit', async function (event) {
            event.preventDefault();

            if (!validate()) {
                Swal.fire('Atencao', 'Verifique os campos obrigatorios.', 'warning');
                return;
            }

            const payload = buildPayload();
            const url = id ? `/ctt/api/alunos/${id}` : '/ctt/api/alunos';
            const method = id ? 'PUT' : 'POST';
            const submitButton = this.querySelector('[type="submit"]');

            if (submitButton) submitButton.disabled = true;

            try {
                const response = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await response.json();

                if (!response.ok) {
                    throw new Error(data.message || 'Erro no servidor');
                }

                const alunoId = data.id ?? parseInt(id, 10);
                await submitAnamnese(alunoId);

                await Swal.fire({
                    icon: 'success',
                    title: id ? 'Atualizado!' : 'Cadastrado!',
                    text: 'Dados salvos com sucesso.'
                });

                window.location.href = '/ctt/admin/alunos';
            } catch (error) {
                Swal.fire('Erro', error.message, 'error');
            } finally {
                if (submitButton) submitButton.disabled = false;
            }
        });
    }
});
