document.addEventListener('DOMContentLoaded', function() {
    // Máscaras
    function applyMasks() {
        if (typeof IMask !== 'undefined') {
            const cpfField = document.getElementById('cpf');
            const tel1Field = document.getElementById('telefone1');
            const tel2Field = document.getElementById('telefone2');
            const cepField = document.getElementById('cep');
            if (cpfField) IMask(cpfField, { mask: '000.000.000-00' });
            if (tel1Field) IMask(tel1Field, { mask: '(00) 00000-0000' });
            if (tel2Field) IMask(tel2Field, { mask: '(00) 00000-0000' });
            if (cepField) IMask(cepField, { mask: '00000-000' });
        } else {
            const cpfField = document.getElementById('cpf');
            if (cpfField) {
                cpfField.addEventListener('input', function(e) {
                    let v = e.target.value.replace(/\D/g, '');
                    if (v.length <= 11) {
                        v = v.replace(/(\d{3})(\d)/, '$1.$2');
                        v = v.replace(/(\d{3})(\d)/, '$1.$2');
                        v = v.replace(/(\d{3})(\d{1,2})$/, '$1-$2');
                        e.target.value = v;
                    }
                });
            }
        }
    }

    // Buscar CEP
    const buscarCepBtn = document.getElementById('buscarCep');
    if (buscarCepBtn) {
        buscarCepBtn.addEventListener('click', function() {
            const cepInput = document.getElementById('cep');
            const cep = cepInput ? cepInput.value.replace(/\D/g, '') : '';
            if (cep.length !== 8) {
                Swal.fire('Atenção', 'Digite um CEP válido com 8 dígitos.', 'warning');
                return;
            }
            fetch(`https://viacep.com.br/ws/${cep}/json/`)
                .then(r => r.json())
                .then(data => {
                    if (data.erro) {
                        Swal.fire('Atenção', 'CEP não encontrado.', 'warning');
                        return;
                    }
                    const logradouroField = document.getElementById('logradouro');
                    const bairroField = document.getElementById('bairro');
                    const cidadeField = document.getElementById('cidade');
                    const numeroField = document.getElementById('numero');
                    if (logradouroField) logradouroField.value = data.logradouro || '';
                    if (bairroField) bairroField.value = data.bairro || '';
                    if (cidadeField) cidadeField.value = data.localidade || '';
                    if (numeroField) numeroField.focus();
                })
                .catch(() => Swal.fire('Erro', 'Não foi possível buscar o CEP.', 'error'));
        });
    }

    // Validação
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

        const nome = document.getElementById('nome')?.value.trim();
        if (!nome) { showError('nome', 'Nome é obrigatório'); valid = false; }

        const sobrenome = document.getElementById('sobrenome')?.value.trim();
        if (!sobrenome) { showError('sobrenome', 'Sobrenome é obrigatório'); valid = false; }

        const cpf = document.getElementById('cpf')?.value.replace(/\D/g, '');
        if (!cpf || cpf.length !== 11) { showError('cpf', 'CPF inválido'); valid = false; }

        const nasc = new Date(document.getElementById('nascimento')?.value);
        if (isNaN(nasc.getTime()) || nasc >= new Date()) {
            showError('nascimento', 'Data de nascimento inválida');
            valid = false;
        }

        const email = document.getElementById('email')?.value;
        if (email && !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
            showError('email', 'E-mail inválido');
            valid = false;
        }

        const telefone1 = document.getElementById('telefone1')?.value.replace(/\D/g, '');
        if (!telefone1 || telefone1.length < 10) {
            showError('telefone1', 'Telefone celular é obrigatório');
            valid = false;
        }

        const cargo = document.getElementById('cargo')?.value;
        if (!cargo) {
            showError('cargo', 'Selecione um cargo');
            valid = false;
        }

        return valid;
    }

    function buildPayload() {
        const senha = document.getElementById('senha')?.value || document.getElementById('cpf')?.value.replace(/\D/g, '');
        const payload = {
            nome: document.getElementById('nome')?.value.trim(),
            sobrenome: document.getElementById('sobrenome')?.value.trim(),
            cpf: document.getElementById('cpf')?.value.replace(/\D/g, ''),
            email: document.getElementById('email')?.value,
            senha: senha,
            data_nascimento: document.getElementById('nascimento')?.value,
            genero: document.getElementById('genero')?.value || 'O',
            cargo_id: parseInt(document.getElementById('cargo')?.value),
            registro_profissional: document.getElementById('registro_profissional')?.value,
            observacoes: document.getElementById('observacoes')?.value,
            endereco: {
                logradouro: document.getElementById('logradouro')?.value,
                numero: document.getElementById('numero')?.value,
                cidade: document.getElementById('cidade')?.value,
                bairro: document.getElementById('bairro')?.value,
                cep: document.getElementById('cep')?.value.replace(/\D/g, ''),
                complemento: document.getElementById('complemento')?.value
            },
            contatos: []
        };
        const tel1 = document.getElementById('telefone1')?.value.replace(/\D/g, '');
        if (tel1) payload.contatos.push({ tipo: 'telefone', valor: tel1 });

        const tel2 = document.getElementById('telefone2')?.value.replace(/\D/g, '');
        if (tel2) payload.contatos.push({ tipo: 'whatsapp', valor: tel2 });

        const email2 = document.getElementById('email2')?.value.trim();
        if (email2) payload.contatos.push({ tipo: 'email_secundario', valor: email2 });
        return payload;
    }

    function setValue(id, value) {
        const field = document.getElementById(id);
        if (!field) return;
        field.value = (value !== null && value !== undefined) ? value : '';
        if ($(field).hasClass('select2-hidden-accessible')) {
            $(field).trigger('change');
        }
    }

    const id = document.getElementById('formFuncionario')?.dataset.id || null;

    async function loadFuncionarioData() {
        if (!id) return;
        try {
            const response = await fetch(`/ctt/api/funcionarios/${id}`);
            if (!response.ok) throw new Error('Erro ao carregar dados');
            const result = await response.json();
            const funcionario = result.data || result;

            setValue('nome', funcionario.nome);
            setValue('sobrenome', funcionario.sobrenome);
            setValue('cpf', funcionario.cpf);
            setValue('nascimento', funcionario.data_nascimento);
            setValue('genero', funcionario.genero);
            setValue('email', funcionario.email);
            setValue('registro_profissional', funcionario.registro_profissional);
            setValue('observacoes', funcionario.observacoes);

            if (funcionario.contatos && Array.isArray(funcionario.contatos)) {
                const tel1 = funcionario.contatos.find(c => c.tipo === 'telefone');
                const tel2 = funcionario.contatos.find(c => c.tipo === 'whatsapp');
                const mail2 = funcionario.contatos.find(c => c.tipo === 'email_secundario');
                setValue('telefone1', tel1 ? tel1.valor : '');
                setValue('telefone2', tel2 ? tel2.valor : '');
                setValue('email2', mail2 ? mail2.valor : '');
            }

            setValue('logradouro', funcionario.logradouro);
            setValue('numero', funcionario.numero);
            setValue('cidade', funcionario.cidade);
            setValue('bairro', funcionario.bairro);
            setValue('cep', funcionario.cep);
            setValue('complemento', funcionario.complemento);

            if (funcionario.cargo_id) {
                $('#cargo').val(funcionario.cargo_id).trigger('change');
            }

            ['cpf', 'telefone1', 'telefone2', 'cep'].forEach(fieldId => {
                const el = document.getElementById(fieldId);
                if (el) el.dispatchEvent(new Event('input'));
            });
        } catch (error) {
            Swal.fire('Erro', 'Não foi possível carregar os dados do funcionário.', 'error');
        }
    }

    async function loadCargos() {
        const cargoSelect = document.getElementById('cargo');
        if (!cargoSelect) return;
        try {
            const response = await fetch('/ctt/api/cargos');
            const result = await response.json();
            const listaCargos = result.data || [];
            cargoSelect.innerHTML = '<option value="">Selecione</option>';
            listaCargos.forEach(cargo => {
                const option = document.createElement('option');
                option.value = cargo.id;
                option.textContent = cargo.nome;
                cargoSelect.appendChild(option);
            });
        } catch (error) {
            console.error('Erro ao carregar cargos:', error);
        }
    }

    async function init() {
        applyMasks();
        await loadCargos();
        if ($.fn.select2) {
            $('#cargo').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione um cargo',
                allowClear: true
            });
        }
        if (id) {
            await loadFuncionarioData();
        }
    }

    init();

    const form = document.getElementById('formFuncionario');
    if (form) {
        form.addEventListener('submit', async function(e) {
            e.preventDefault();
            if (!validate()) {
                Swal.fire('Atenção', 'Por favor, corrija os erros no formulário.', 'warning');
                return;
            }
            const payload = buildPayload();
            const url = id ? `/ctt/api/funcionarios/${id}` : '/ctt/api/funcionarios';
            const method = id ? 'PUT' : 'POST';
            const btn = this.querySelector('[type="submit"]');
            if (btn) btn.disabled = true;
            try {
                const res = await fetch(url, {
                    method,
                    headers: { 'Content-Type': 'application/json' },
                    body: JSON.stringify(payload),
                });
                if (!res.ok) throw new Error(`HTTP ${res.status}`);
                const data = await res.json();
                if (data.success !== false) {
                    await Swal.fire({
                        icon: 'success',
                        title: id ? 'Funcionário atualizado!' : 'Funcionário cadastrado!',
                        text: data.message || (id ? 'Atualizado com sucesso.' : 'Cadastrado com sucesso.'),
                        confirmButtonText: 'OK'
                    });
                    window.location.href = '/ctt/admin/funcionarios';
                } else {
                    Swal.fire('Erro', data.message || 'Erro ao processar solicitação.', 'error');
                }
            } catch (err) {
                Swal.fire('Erro', 'Falha na comunicação com o servidor.', 'error');
            } finally {
                if (btn) btn.disabled = false;
            }
        });
    }
});