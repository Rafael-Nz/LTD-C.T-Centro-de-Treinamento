/**
 * aluno_form.js - Gerenciamento de Alunos com Integração de Anamnese
 */
document.addEventListener('DOMContentLoaded', function () {

    let anamneseEngine = null;
    const form = document.getElementById('formAluno');
    const id   = form?.dataset.id || null;

    // ── Máscaras ─────────────────────────────────────────────────────────────────
    function applyMasks() {
        if (typeof IMask === 'undefined') return;

        const masks = [
            { id: 'cpf',      mask: '000.000.000-00'  },
            { id: 'telefone1', mask: '(00) 00000-0000' },
            { id: 'telefone2', mask: '(00) 0000-0000'  },
            { id: 'cep',      mask: '00000-000'        }
        ];
        masks.forEach(m => {
            const el = document.getElementById(m.id);
            if (el) IMask(el, { mask: m.mask });
        });
    }

    // ── Busca de CEP ─────────────────────────────────────────────────────────────
    function setupCepLookup() {
        const btn = document.getElementById('buscarCep');
        if (!btn) return;

        btn.addEventListener('click', async () => {
            const cep = document.getElementById('cep')?.value.replace(/\D/g, '');
            if (cep?.length !== 8) {
                Swal.fire('Atenção', 'Digite um CEP válido.', 'warning');
                return;
            }

            try {
                const r    = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                const data = await r.json();
                if (data.erro) throw new Error();

                setValue('endereco', data.logradouro);
                setValue('bairro',   data.bairro);
                setValue('cidade',   data.localidade);
                document.getElementById('numero')?.focus();
            } catch {
                Swal.fire('Erro', 'CEP não encontrado.', 'error');
            }
        });
    }

    // ── Validação ────────────────────────────────────────────────────────────────
    function showError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        if (!field) return;
        field.classList.add('is-invalid');
        let fb = field.nextElementSibling;
        if (!fb || !fb.classList.contains('invalid-feedback')) {
            fb = document.createElement('div');
            fb.className = 'invalid-feedback';
            field.parentNode.insertBefore(fb, field.nextSibling);
        }
        fb.textContent = msg;
    }

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function validate() {
        let valid = true;
        clearErrors();

        const required = [
            { id: 'nome',      msg: 'Nome é obrigatório'      },
            { id: 'sobrenome', msg: 'Sobrenome é obrigatório' },
            { id: 'endereco',  msg: 'Endereço é obrigatório'  },
            { id: 'numero',    msg: 'Número é obrigatório'    }
        ];

        required.forEach(f => {
            if (!document.getElementById(f.id)?.value.trim()) {
                showError(f.id, f.msg);
                valid = false;
            }
        });

        const cpf = document.getElementById('cpf')?.value.replace(/\D/g, '');
        if (!cpf || cpf.length !== 11) {
            showError('cpf', 'CPF inválido');
            valid = false;
        }

        return valid;
    }

    // ── Payload ──────────────────────────────────────────────────────────────────
    function buildPayload() {
        const payload = {
            nome:            document.getElementById('nome')?.value.trim(),
            sobrenome:       document.getElementById('sobrenome')?.value.trim(),
            cpf:             document.getElementById('cpf')?.value.replace(/\D/g, ''),
            email:           document.getElementById('email')?.value,
            data_nascimento: document.getElementById('nascimento')?.value,
            genero:          document.getElementById('genero')?.value || 'O',
            data_matricula:  document.getElementById('dataMatricula')?.value,
            cadastrado_por:  parseInt(document.getElementById('cadastrado_por')?.value || 1),
            endereco: {
                logradouro:  document.getElementById('endereco')?.value,
                numero:      document.getElementById('numero')?.value,
                cidade:      document.getElementById('cidade')?.value,
                bairro:      document.getElementById('bairro')?.value,
                cep:         document.getElementById('cep')?.value.replace(/\D/g, ''),
                complemento: document.getElementById('complemento')?.value
            },
            contatos: []
        };

        ['telefone1', 'telefone2'].forEach((fId, idx) => {
            const val = document.getElementById(fId)?.value.replace(/\D/g, '');
            if (val) payload.contatos.push({ tipo: idx === 0 ? 'telefone' : 'whatsapp', valor: val });
        });

        const email2 = document.getElementById('email2')?.value.trim();
        if (email2) {
            payload.contatos.push({ tipo: 'email_secundario', valor: email2 });
        }

        // Integração com AnamneseEngine — enviada separadamente no submit
        // (ver submitAnamnese abaixo)

        return payload;
    }

    // ── Carga dos dados do aluno (edição) ────────────────────────────────────────
    async function loadAlunoData() {
        if (!id) {
            setValue('dataMatricula', new Date().toISOString().split('T')[0]);
            return;
        }

        try {
            const res    = await fetch(`/ctt/api/alunos/${id}`);
            const result = await res.json();
            const aluno  = result.data || result;

            setValue('nome',          aluno.nome);
            setValue('sobrenome',     aluno.sobrenome);
            setValue('cpf',           aluno.cpf);
            setValue('nascimento',    aluno.data_nascimento);
            setValue('genero',        aluno.genero);
            setValue('email',         aluno.email);
            setValue('dataMatricula', aluno.data_matricula);

            if (aluno.contatos) {
                const tel = aluno.contatos.find(c => c.tipo === 'telefone');
                const zap = aluno.contatos.find(c => c.tipo === 'whatsapp');
                const emailSec = aluno.contatos.find(c => c.tipo === 'email_secundario');
                setValue('telefone1', tel?.valor || '');
                setValue('telefone2', zap?.valor || '');
                setValue('email2', emailSec?.valor || '');
            }

            setValue('endereco',    aluno.logradouro);
            setValue('numero',      aluno.numero);
            setValue('cidade',      aluno.cidade);
            setValue('bairro',      aluno.bairro);
            setValue('cep',         aluno.cep);
            setValue('complemento', aluno.complemento);

            // Dispara input para IMask recalcular as máscaras
            ['cpf', 'telefone1', 'telefone2', 'cep'].forEach(f => {
                document.getElementById(f)?.dispatchEvent(new Event('input'));
            });

        } catch {
            Swal.fire('Erro', 'Falha ao carregar dados do aluno.', 'error');
        }
    }

    function setValue(elemId, value) {
        const field = document.getElementById(elemId);
        if (field) field.value = value || '';
    }

    // ── Inicialização ────────────────────────────────────────────────────────────
    async function init() {
        applyMasks();
        setupCepLookup();

        if (document.getElementById('anamnese-container')) {
            anamneseEngine = new AnamneseEngine('anamnese-container', id);
            await anamneseEngine.init();
        }

        await loadAlunoData();
    }

    init();

    // ── Envio da Anamnese (separado do aluno) ────────────────────────────────────
    async function submitAnamnese(alunoId) {
        if (!anamneseEngine) return;

        const respostas = anamneseEngine.getData();

        const perguntasObrigatorias = anamneseEngine.perguntas.filter(p => p.obrigatoria);
        
        const faltamRespostas = perguntasObrigatorias.some(p => {
            const resp = respostas.find(r => r.pergunta_id === p.id);
            return !resp || resp.valor === null || resp.valor === '';
        });

        if (faltamRespostas) {
            throw new Error("Por favor, preencha todas as perguntas obrigatórias da anamnese.");
        }

        const res = await fetch('/ctt/api/anamnese', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify({ aluno_id: Number(alunoId), respostas })
        });

        if (!res.ok) {
            const err = await res.json().catch(() => ({}));
            throw new Error(err.message || 'Erro ao salvar anamnese');
        }
    }

    // ── Submit ───────────────────────────────────────────────────────────────────
    if (form) {
        form.addEventListener('submit', async function (e) {
            e.preventDefault();

            if (!validate()) {
                Swal.fire('Atenção', 'Verifique os campos obrigatórios.', 'warning');
                return;
            }

            const payload = buildPayload();
            const url     = id ? `/ctt/api/alunos/${id}` : '/ctt/api/alunos';
            const method  = id ? 'PUT' : 'POST';
            const btn     = this.querySelector('[type="submit"]');

            if (btn) btn.disabled = true;

            try {
                // 1. Salva o aluno
                const res  = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload)
                });
                const data = await res.json();

                if (!res.ok || (!data.success && !data.id)) {
                    throw new Error(data.message || 'Erro no servidor');
                }

                // 2. Salva a anamnese com o ID real do aluno
                const alunoId = data.id ?? parseInt(id);
                await submitAnamnese(alunoId);

                await Swal.fire({
                    icon:  'success',
                    title: id ? 'Atualizado!' : 'Cadastrado!',
                    text:  'Dados salvos com sucesso.'
                });
                window.location.href = '/ctt/admin/alunos';

            } catch (err) {
                Swal.fire('Erro', err.message, 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    }
});