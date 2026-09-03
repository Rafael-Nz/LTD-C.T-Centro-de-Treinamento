document.addEventListener('DOMContentLoaded', () => {
    const passwordResetForm = document.getElementById('password-reset-form');
    const resetSubmit = document.getElementById('reset-submit');
    const resetError = document.getElementById('reset-error');
    const resetSuccess = document.getElementById('reset-success');
    const resetToken = passwordResetForm.dataset.token;

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const passwordInput = document.getElementById(toggle.dataset.passwordToggle);
            const eyeIcon = document.getElementById(toggle.dataset.icon);
            const isPassword = passwordInput.type === 'password';

            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('ph-eye', isPassword);
            eyeIcon.classList.toggle('ph-eye-slash', !isPassword);
        });
    });

    if (!resetToken) {
        resetError.textContent = 'Link inválido ou expirado. Solicite uma nova recuperação de senha.';
        resetError.classList.remove('d-none');
        passwordResetForm.classList.add('d-none');
        return;
    }

    passwordResetForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        resetError.classList.add('d-none');
        resetSuccess.classList.add('d-none');
        resetSubmit.disabled = true;
        resetSubmit.innerHTML = '<i class="ph ph-spinner fa-spin me-2"></i> Salvando...';

        try {
            const response = await fetch('/ctt/api/auth/redefinir-senha', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    token: resetToken,
                    nova_senha: document.getElementById('nova_senha').value,
                    confirmar_senha: document.getElementById('confirmar_senha').value
                })
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Não foi possível redefinir a senha.');
            }

            resetSuccess.textContent = result.data?.message || 'Senha redefinida com sucesso!';
            resetSuccess.classList.remove('d-none');
            passwordResetForm.classList.add('d-none');
        } catch (error) {
            resetError.textContent = error.message || 'Algo deu errado. Tente novamente.';
            resetError.classList.remove('d-none');
            resetSubmit.disabled = false;
            resetSubmit.innerHTML = '<i class="ph ph-check-circle me-2"></i> Redefinir Senha';
        }
    });
});
