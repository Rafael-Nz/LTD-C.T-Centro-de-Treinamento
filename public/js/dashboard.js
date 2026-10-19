document.addEventListener('DOMContentLoaded', async () => {
    const card = document.getElementById('activation-card');
    const email = document.getElementById('activation-email');
    const button = document.getElementById('resend-activation');
    const feedback = document.getElementById('activation-feedback');
    if (!card || !button) return;

    try {
        const response = await fetch('/ctt/api/usuarios/me', {
            headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
            credentials: 'same-origin',
        });
        const payload = await response.json();
        const usuario = payload.data || payload;
        const contaAtivada = usuario.conta_ativada_aluno ?? usuario.conta_ativada;
        if (!response.ok || !payload.success || Number(contaAtivada) === 1) return;

        email.textContent = usuario.email || '';
        card.classList.remove('d-none');
    } catch (_) {
        return;
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        feedback.textContent = 'Enviando...';
        feedback.className = 'small mt-2 mb-0';

        try {
            const csrfCookie = document.cookie
                .split('; ')
                .find((item) => item.startsWith('CTT_CSRF_TOKEN='));
            const csrfToken = csrfCookie
                ? decodeURIComponent(csrfCookie.split('=').slice(1).join('='))
                : '';
            const response = await fetch('/ctt/api/auth/reenviar-ativacao', {
                method: 'POST',
                headers: {
                    'Accept': 'application/json',
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': csrfToken,
                    'X-Auth-Area': 'aluno',
                },
                credentials: 'same-origin',
                body: JSON.stringify({ perfil: 'aluno' }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Não foi possível enviar o link.');
            feedback.textContent = payload.data?.message || 'Link enviado para seu e-mail.';
            feedback.classList.add('text-success');
        } catch (error) {
            feedback.textContent = error.message;
            feedback.classList.add('text-danger');
            button.disabled = false;
        }
    });
});
