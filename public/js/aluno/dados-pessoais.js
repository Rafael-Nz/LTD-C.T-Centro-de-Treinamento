(() => {
    let usuarioAtual = null;
    let maskWpp = null;
    let maskTel = null;
    let maskCep = null;

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));

    const formatCpf = (cpf) => {
        if (!cpf) return '-';
        const clean = String(cpf).replace(/\D/g, '');
        if (clean.length !== 11) return cpf;
        return clean.replace(/(\d{3})(\d{3})(\d{3})(\d{2})/, '$1.$2.$3-$4');
    };

    const formatDate = (dateStr) => {
        if (!dateStr) return '-';
        const parts = dateStr.split('-');
        if (parts.length === 3) return `${parts[2]}/${parts[1]}/${parts[0]}`;
        return dateStr;
    };

    const calcIdade = (dataNasc) => {
        if (!dataNasc) return '';
        const hoje = new Date();
        const nasc = new Date(dataNasc);
        let idade = hoje.getFullYear() - nasc.getFullYear();
        const m = hoje.getMonth() - nasc.getMonth();
        if (m < 0 || (m === 0 && hoje.getDate() < nasc.getDate())) idade--;
        return idade >= 0 ? ` (${idade} anos)` : '';
    };

    function getCsrfToken() {
        const cookie = document.cookie.split('; ').find((item) => item.startsWith('CTT_CSRF_TOKEN='));
        return cookie ? decodeURIComponent(cookie.split('=').slice(1).join('=')) : '';
    }

    async function carregarDados() {
        try {
            const resp = await fetch('/ctt/api/usuarios/me', {
                headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                credentials: 'same-origin',
            });
            const res = await resp.json();
            if (!resp.ok || !res.success) {
                throw new Error(res.message || 'Erro ao carregar dados pessoais.');
            }

            usuarioAtual = res.data;
            renderDados(usuarioAtual);
        } catch (error) {
            console.error('Falha ao carregar perfil:', error);
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: error.message || 'Não foi possível carregar suas informações.',
            });
        }
    }

    function renderDados(user) {
        if (!user) return;

        // Avatar e Saudação
        const nomeCompleto = `${user.nome || ''} ${user.sobrenome || ''}`.trim() || 'Aluno';
        const iniciais = (user.nome ? user.nome[0] : 'A') + (user.sobrenome ? user.sobrenome[0] : '');

        const avatarEl = document.getElementById('profileAvatar');
        if (avatarEl) avatarEl.textContent = iniciais.toUpperCase();

        const nameEl = document.getElementById('profileName');
        if (nameEl) nameEl.textContent = nomeCompleto;

        const emailSubEl = document.getElementById('profileEmailSub');
        if (emailSubEl) emailSubEl.textContent = user.email || '';

        // Dados Cadastrais
        const nomeEl = document.getElementById('infoNome');
        if (nomeEl) nomeEl.textContent = user.nome || '-';

        const sobrenomeEl = document.getElementById('infoSobrenome');
        if (sobrenomeEl) sobrenomeEl.textContent = user.sobrenome || '-';

        const cpfEl = document.getElementById('infoCpf');
        if (cpfEl) cpfEl.textContent = formatCpf(user.cpf);

        const emailEl = document.getElementById('infoEmail');
        if (emailEl) emailEl.textContent = user.email || '-';

        const nascEl = document.getElementById('infoNascimento');
        if (nascEl) nascEl.textContent = formatDate(user.data_nascimento) + calcIdade(user.data_nascimento);

        const generoMap = { 'M': 'Masculino', 'F': 'Feminino', 'O': 'Outro' };
        const generoEl = document.getElementById('infoGenero');
        if (generoEl) generoEl.textContent = generoMap[user.genero] || user.genero || 'Não informado';

        const statusEl = document.getElementById('infoStatus');
        if (statusEl) {
            const ativo = Number(user.ativo) === 1;
            statusEl.className = `badge-status ${ativo ? 'badge-status--green' : 'badge-status--red'}`;
            statusEl.textContent = ativo ? 'Ativo' : 'Inativo';
        }

        // Contatos
        const contatosList = document.getElementById('infoContatosList');
        if (contatosList) {
            const contatos = user.contatos || [];
            const cWhatsapp = contatos.find(c => c.tipo === 'whatsapp')?.valor || '';
            const cTelefone = contatos.find(c => c.tipo === 'telefone')?.valor || '';
            const cEmailSec = contatos.find(c => c.tipo === 'email_secundario')?.valor || '';

            const cleanWpp = cWhatsapp.replace(/\D/g, '');

            contatosList.innerHTML = `
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-whatsapp text-success me-1"></i>Contato 1 (WhatsApp)</span>
                    <span class="info-value d-flex align-items-center justify-content-between flex-wrap gap-2">
                        <span>${cWhatsapp ? esc(cWhatsapp) : '<span class="text-muted fw-normal">Não informado</span>'}</span>
                        ${cleanWpp.length >= 10 ? `<a href="https://wa.me/55${cleanWpp}" target="_blank" class="btn btn-sm btn-outline-success py-0 px-2" style="font-size: 11px;"><i class="bi bi-whatsapp"></i> Conversar</a>` : ''}
                    </span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-telephone text-primary me-1"></i>Contato 2 (Telefone/Celular)</span>
                    <span class="info-value">${cTelefone ? esc(cTelefone) : '<span class="text-muted fw-normal">Não informado</span>'}</span>
                </div>
                <div class="info-item">
                    <span class="info-label"><i class="bi bi-envelope-at text-info me-1"></i>E-mail Secundário</span>
                    <span class="info-value">${cEmailSec ? esc(cEmailSec) : '<span class="text-muted fw-normal">Não informado</span>'}</span>
                </div>
            `;
        }

        // Endereço
        const logradouroEl = document.getElementById('infoLogradouro');
        if (logradouroEl) logradouroEl.textContent = user.logradouro ? `${user.logradouro}, ${user.numero || 'S/N'}` : '-';

        const bairroEl = document.getElementById('infoBairro');
        if (bairroEl) bairroEl.textContent = user.bairro || '-';

        const cidadeEl = document.getElementById('infoCidade');
        if (cidadeEl) cidadeEl.textContent = user.cidade || '-';

        const cepEl = document.getElementById('infoCep');
        if (cepEl) cepEl.textContent = user.cep || '-';

        const complEl = document.getElementById('infoComplemento');
        if (complEl) complEl.textContent = user.complemento || '-';
    }

    // Modal de Edição de Dados Pessoais e Endereço
    const btnEditar = document.getElementById('btnEditarPerfil');
    const formEditar = document.getElementById('formEditarPerfil');
    const modalEl = document.getElementById('modalEditarPerfil');
    let modalInstance = null;

    if (btnEditar && modalEl) {
        modalInstance = new bootstrap.Modal(modalEl);

        btnEditar.addEventListener('click', () => {
            if (!usuarioAtual) return;

            document.getElementById('editNome').value = usuarioAtual.nome || '';
            document.getElementById('editSobrenome').value = usuarioAtual.sobrenome || '';
            document.getElementById('editGenero').value = usuarioAtual.genero || 'M';

            if (window.jQuery && $.fn.select2) {
                $('#editGenero').val(usuarioAtual.genero || 'M').trigger('change');
            }

            // Contatos: WhatsApp, Telefone Secundário e E-mail Secundário
            const contatos = usuarioAtual.contatos || [];
            const wpp = contatos.find(c => c.tipo === 'whatsapp')?.valor || '';
            const tel = contatos.find(c => c.tipo === 'telefone')?.valor || '';
            const emailSec = contatos.find(c => c.tipo === 'email_secundario')?.valor || '';

            if (maskWpp) maskWpp.value = wpp; else document.getElementById('editWhatsapp').value = wpp;
            if (maskTel) maskTel.value = tel; else document.getElementById('editTelefoneSecundario').value = tel;
            document.getElementById('editEmailSecundario').value = emailSec;

            // Endereço
            if (maskCep) maskCep.value = usuarioAtual.cep || ''; else document.getElementById('editCep').value = usuarioAtual.cep || '';
            document.getElementById('editLogradouro').value = usuarioAtual.logradouro || '';
            document.getElementById('editNumero').value = usuarioAtual.numero || '';
            document.getElementById('editBairro').value = usuarioAtual.bairro || '';
            document.getElementById('editCidade').value = usuarioAtual.cidade || '';
            document.getElementById('editComplemento').value = usuarioAtual.complemento || '';

            modalInstance.show();
        });
    }

    // Busca de CEP automática no modal
    const cepInput = document.getElementById('editCep');
    if (cepInput) {
        cepInput.addEventListener('blur', async () => {
            const cep = cepInput.value.replace(/\D/g, '');
            if (cep.length === 8) {
                try {
                    const res = await fetch(`https://viacep.com.br/ws/${cep}/json/`);
                    const data = await res.json();
                    if (!data.erro) {
                        const logradouroEl = document.getElementById('editLogradouro');
                        const bairroEl = document.getElementById('editBairro');
                        const cidadeEl = document.getElementById('editCidade');
                        if (logradouroEl && !logradouroEl.value) logradouroEl.value = data.logradouro || '';
                        if (bairroEl && !bairroEl.value) bairroEl.value = data.bairro || '';
                        if (cidadeEl && !cidadeEl.value) cidadeEl.value = data.localidade || '';
                        document.getElementById('editNumero')?.focus();
                    }
                } catch (_) {}
            }
        });
    }

    // Salvar Dados
    if (formEditar) {
        formEditar.addEventListener('submit', async (e) => {
            e.preventDefault();
            if (!usuarioAtual) return;

            const btnSalvar = document.getElementById('btnSalvarPerfil');
            if (btnSalvar) {
                btnSalvar.disabled = true;
                btnSalvar.innerHTML = '<span class="spinner-border spinner-border-sm me-1"></span> Salvando...';
            }

            const wppVal = maskWpp ? maskWpp.unmaskedValue : document.getElementById('editWhatsapp').value.replace(/\D/g, '');
            const telVal = maskTel ? maskTel.unmaskedValue : document.getElementById('editTelefoneSecundario').value.replace(/\D/g, '');
            const emailSecVal = document.getElementById('editEmailSecundario').value.trim();
            const cepVal = maskCep ? maskCep.unmaskedValue : document.getElementById('editCep').value.replace(/\D/g, '');

            const payload = {
                nome: document.getElementById('editNome').value.trim(),
                sobrenome: document.getElementById('editSobrenome').value.trim(),
                genero: document.getElementById('editGenero').value,
                endereco: {
                    cep: cepVal,
                    logradouro: document.getElementById('editLogradouro').value.trim(),
                    numero: document.getElementById('editNumero').value.trim(),
                    bairro: document.getElementById('editBairro').value.trim(),
                    cidade: document.getElementById('editCidade').value.trim(),
                    complemento: document.getElementById('editComplemento').value.trim(),
                },
                contatos: [
                    {
                        tipo: 'whatsapp',
                        valor: wppVal,
                    },
                    {
                        tipo: 'telefone',
                        valor: telVal,
                    },
                    {
                        tipo: 'email_secundario',
                        valor: emailSecVal,
                    }
                ]
            };

            try {
                const response = await fetch(`/ctt/api/usuarios/${usuarioAtual.id}`, {
                    method: 'PUT',
                    headers: {
                        'Accept': 'application/json',
                        'Content-Type': 'application/json',
                        'X-CSRF-Token': getCsrfToken(),
                        'X-Auth-Area': 'aluno',
                    },
                    credentials: 'same-origin',
                    body: JSON.stringify(payload),
                });

                const result = await response.json();
                if (!response.ok || !result.success) {
                    throw new Error(result.message || 'Falha ao salvar dados.');
                }

                if (modalInstance) modalInstance.hide();

                Swal.fire({
                    icon: 'success',
                    title: 'Atualizado!',
                    text: 'Seus dados pessoais e contatos foram atualizados com sucesso.',
                    timer: 2000,
                    showConfirmButton: false,
                });

                await carregarDados();
            } catch (error) {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: error.message || 'Não foi possível salvar as alterações.',
                });
            } finally {
                if (btnSalvar) {
                    btnSalvar.disabled = false;
                    btnSalvar.innerHTML = 'Salvar Alterações';
                }
            }
        });
    }

    document.addEventListener('DOMContentLoaded', () => {
        carregarDados();

        // Inicializar Select2 com tema Bootstrap 5
        if (window.jQuery && $.fn.select2) {
            $('#editGenero').select2({
                theme: 'bootstrap-5',
                dropdownParent: $('#modalEditarPerfil'),
                minimumResultsForSearch: Infinity,
                width: '100%'
            });
        }

        // Inicializar Máscaras via IMask
        if (window.IMask) {
            const wppInput = document.getElementById('editWhatsapp');
            if (wppInput) {
                maskWpp = IMask(wppInput, { mask: '(00) 00000-0000' });
            }

            const telInput = document.getElementById('editTelefoneSecundario');
            if (telInput) {
                maskTel = IMask(telInput, {
                    mask: [
                        { mask: '(00) 0000-0000' },
                        { mask: '(00) 00000-0000' }
                    ]
                });
            }

            const cepEl = document.getElementById('editCep');
            if (cepEl) {
                maskCep = IMask(cepEl, { mask: '00000-000' });
            }
        }
    });
})();
