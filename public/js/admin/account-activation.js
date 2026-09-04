document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('account-activation-form');
    const token = form?.dataset.token || '';
    const error = document.getElementById('activation-error');
    const success = document.getElementById('activation-success');
    const button = document.getElementById('activation-submit');

    const showError = (message) => {
        error.textContent = message;
        error.classList.remove('d-none');
    };

    if (!token) {
        form?.classList.add('d-none');
        showError('Convite invalido ou expirado.');
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        const requisitos = /[0-9]/.test(novaSenha)
            && /[A-Z]/.test(novaSenha)
            && /[a-z]/.test(novaSenha)
            && /[^A-Za-z0-9\s]/.test(novaSenha);

        error.classList.add('d-none');
        if (novaSenha.length < 12 || novaSenha.length > 128 || !requisitos) {
            showError('A senha deve ter 12 a 128 caracteres, com numero, maiuscula, minuscula e simbolo.');
            return;
        }
        if (novaSenha !== confirmarSenha) {
            showError('As senhas nao coincidem.');
            return;
        }

        button.disabled = true;
        try {
            const response = await fetch('/ctt/api/auth/ativar-conta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
                body: JSON.stringify({ token, nova_senha: novaSenha, confirmar_senha: confirmarSenha })
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Nao foi possivel ativar a conta.');
            form.classList.add('d-none');
            success.textContent = result.data?.message || 'Conta ativada com sucesso.';
            success.classList.remove('d-none');
        } catch (activationError) {
            showError(activationError.message || 'Nao foi possivel ativar a conta.');
            button.disabled = false;
        }
    });
});
