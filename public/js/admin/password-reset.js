document.addEventListener('DOMContentLoaded', () => {
    const passwordResetForm = document.getElementById('password-reset-form');
    const resetSubmit = document.getElementById('reset-submit');
    const resetError = document.getElementById('reset-error');
    const resetSuccess = document.getElementById('reset-success');
    const resetToken = passwordResetForm.dataset.token;
    const novaSenhaInput = document.getElementById('nova_senha');

    const passwordRules = {
        length: (value) => value.length >= 12 && value.length <= 128,
        number: (value) => /[0-9]/.test(value),
        uppercase: (value) => /[A-Z]/.test(value),
        lowercase: (value) => /[a-z]/.test(value),
        symbol: (value) => /[^A-Za-z0-9\s]/.test(value)
    };

    const updatePasswordRules = (value) => {
        Object.entries(passwordRules).forEach(([rule, isValid]) => {
            const ruleElement = document.querySelector(`[data-password-rule="${rule}"]`);
            const icon = ruleElement.querySelector('i');
            const valid = isValid(value);

            ruleElement.classList.toggle('valid', valid);
            icon.classList.toggle('ph-check-circle', valid);
            icon.classList.toggle('ph-x-circle', !valid);
        });
    };

    novaSenhaInput.addEventListener('input', () => {
        updatePasswordRules(novaSenhaInput.value);
    });

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
        const novaSenha = document.getElementById('nova_senha').value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        if (novaSenha.length < 12 || novaSenha.length > 128) {
            resetError.textContent = 'A senha deve ter entre 12 e 128 caracteres.';
            resetError.classList.remove('d-none');
            return;
        }
        if (!/[0-9]/.test(novaSenha) || !/[A-Z]/.test(novaSenha) || !/[a-z]/.test(novaSenha) || !/[^A-Za-z0-9\s]/.test(novaSenha)) {
            resetError.textContent = 'A senha deve conter numero, letra maiuscula, letra minuscula e caractere especial.';
            resetError.classList.remove('d-none');
            return;
        }
        if (novaSenha !== confirmarSenha) {
            resetError.textContent = 'As senhas não coincidem.';
            resetError.classList.remove('d-none');
            return;
        }

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
                    nova_senha: novaSenha,
                    confirmar_senha: confirmarSenha
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
