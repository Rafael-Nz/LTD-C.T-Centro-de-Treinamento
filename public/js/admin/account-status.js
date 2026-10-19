document.addEventListener('DOMContentLoaded', async () => {
    const card = document.getElementById('admin-activation-card');
    const email = document.getElementById('admin-activation-email');
    const button = document.getElementById('admin-resend-activation');
    const feedback = document.getElementById('admin-activation-feedback');
    if (!card || !button) return;

    try {
        const response = await fetch('/ctt/api/usuarios/me', { credentials: 'same-origin' });
        const payload = await response.json();
        const usuario = payload?.data ?? payload;
        const isFuncionario = usuario?.tipo_usuario === 'funcionario';
        const contaAtivada = Number(usuario?.conta_ativada_funcionario) === 1;

        if (!response.ok || payload?.success === false || !isFuncionario || contaAtivada) return;

        email.textContent = usuario.email || '';
        card.classList.remove('d-none');
    } catch (_) {
        return;
    }

    button.addEventListener('click', async () => {
        button.disabled = true;
        feedback.textContent = 'Enviando...';
        feedback.className = 'small mt-1';

        try {
            const response = await fetch('/ctt/api/auth/reenviar-ativacao', {
                method: 'POST',
                headers: { 'Accept': 'application/json', 'Content-Type': 'application/json' },
                credentials: 'same-origin',
                body: JSON.stringify({ perfil: 'funcionario' }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) {
                throw new Error(payload.message || 'Não foi possível enviar o link.');
            }

            feedback.textContent = payload.data?.message || 'Link enviado para seu e-mail.';
            feedback.classList.add('text-success');
        } catch (error) {
            feedback.textContent = error.message || 'Não foi possível enviar o link.';
            feedback.classList.add('text-danger');
            button.disabled = false;
        }
    });
});
