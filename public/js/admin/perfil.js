document.addEventListener('DOMContentLoaded', () => {
    const errorAlert = document.getElementById('profileError');

    function parseApiData(payload) {
        if (!payload) return null;
        return payload.data ?? payload;
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

    function formatFallback(value, fallback = 'Não informado') {
        const text = String(value ?? '').trim();
        return text !== '' ? text : fallback;
    }

    function formatPhone(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        if (digits.length === 11) {
            return digits.replace(/(\d{2})(\d{5})(\d{4})/, '($1) $2-$3');
        }
        if (digits.length === 10) {
            return digits.replace(/(\d{2})(\d{4})(\d{4})/, '($1) $2-$3');
        }
        return formatFallback(value);
    }

    function getInitials(name) {
        const parts = String(name || '').trim().split(/\s+/).filter(Boolean);
        if (!parts.length) return 'US';
        const first = parts[0][0] || '';
        const last = parts.length > 1 ? parts[parts.length - 1][0] || '' : '';
        return `${first}${last}`.toUpperCase();
    }

    function getContactValue(contatos, tipo, formatter = formatFallback) {
        const contato = Array.isArray(contatos)
            ? contatos.find((item) => item?.tipo === tipo && String(item?.valor ?? '').trim() !== '')
            : null;

        return formatter(contato?.valor ?? '');
    }

    function populateProfile(usuario) {
        const nomeCompleto = `${usuario.nome || ''} ${usuario.sobrenome || ''}`.trim() || 'Usuario';
        const emailPrincipal = formatFallback(usuario.email);
        const logradouro = formatFallback(
            [usuario.logradouro, usuario.numero].filter((item) => String(item ?? '').trim() !== '').join(', ')
        );
        const bairroCidade = formatFallback(
            [usuario.bairro, usuario.cidade].filter((item) => String(item ?? '').trim() !== '').join(' - ')
        );
        const cargoAtual = formatFallback(
            usuario.cargo_nome ?? (usuario.tipo_usuario === 'admin' ? 'Administrador' : 'Funcionario')
        );

        setText('profileAvatar', getInitials(nomeCompleto));
        setText('profileName', nomeCompleto);
        setText('profileEmail', emailPrincipal);
        setText('profileNomeCompleto', nomeCompleto);
        setText('profileEmailPrincipal', emailPrincipal);
        setText('profileTelefone', getContactValue(usuario.contatos, 'telefone', formatPhone));
        setText('profileWhatsapp', getContactValue(usuario.contatos, 'whatsapp', formatPhone));
        setText('profileEmailSecundario', getContactValue(usuario.contatos, 'email_secundario'));
        setText('profileLogradouro', logradouro);
        setText('profileBairroCidade', bairroCidade);
        setText('profileCep', formatFallback(usuario.cep));
        setText('profileComplemento', formatFallback(usuario.complemento));
        setText('profileCargoAtual', cargoAtual);
        setText('profileRegistroProfissional', formatFallback(usuario.registro_profissional));
    }

    async function loadProfile() {
        try {
            const response = await fetch('/ctt/api/usuarios/me', {
                credentials: 'same-origin'
            });

            const payload = await response.json();

            if (!response.ok || payload?.success === false) {
                throw new Error(payload?.message || 'Não foi possivel carregar os dados do perfil.');
            }

            const usuario = parseApiData(payload);
            if (!usuario) {
                throw new Error('Resposta invalida ao carregar o perfil.');
            }

            populateProfile(usuario);
        } catch (error) {
            console.error('[perfil] erro ao carregar perfil', error);
            showError(error.message || 'Não foi possivel carregar os dados do perfil.');
        }
    }

    loadProfile();
});
