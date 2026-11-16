const csrfHeader = () => { const m = document.cookie.match(/(?:^|; )CTT_CSRF_TOKEN=([^;]*)/); return m ? { 'X-CSRF-Token': decodeURIComponent(m[1]) } : {}; };
document.addEventListener('DOMContentLoaded', () => {
    const form = document.getElementById('account-activation-form');
    const token = form?.dataset.token || '';
    const loginUrl = form?.dataset.loginUrl || '/ctt/login';
    const error = document.getElementById('activation-error');
    const success = document.getElementById('activation-success');
    const button = document.getElementById('activation-submit');
    const novaSenhaInput = document.getElementById('nova_senha');

    const passwordRules = {
        length: (value) => value.length >= 12 && value.length <= 128,
        number: (value) => /[0-9]/.test(value),
        uppercase: (value) => /[A-Z]/.test(value),
        lowercase: (value) => /[a-z]/.test(value),
        symbol: (value) => /[^A-Za-z0-9\s]/.test(value),
    };

    const showError = (message) => {
        error.textContent = message;
        error.classList.remove('d-none');
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

    novaSenhaInput.addEventListener('input', () => updatePasswordRules(novaSenhaInput.value));

    document.querySelectorAll('[data-password-toggle]').forEach((toggle) => {
        toggle.addEventListener('click', () => {
            const passwordInput = document.getElementById(toggle.dataset.passwordToggle);
            const eyeIcon = document.getElementById(toggle.dataset.icon);
            const isPassword = passwordInput.type === 'password';
            passwordInput.type = isPassword ? 'text' : 'password';
            eyeIcon.classList.toggle('ph-eye', isPassword);
            eyeIcon.classList.toggle('ph-eye-slash', !isPassword);
            toggle.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
        });
    });

    if (!token) {
        showError('Link inválido ou expirado. Solicite um novo link de ativação.');
        form.classList.add('d-none');
        return;
    }

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        const novaSenha = novaSenhaInput.value;
        const confirmarSenha = document.getElementById('confirmar_senha').value;
        error.classList.add('d-none');
        success.classList.add('d-none');

        if (!Object.values(passwordRules).every((rule) => rule(novaSenha))) {
            showError('A senha deve ter 12 a 128 caracteres, com número, maiúscula, minúscula e símbolo.');
            return;
        }
        if (novaSenha !== confirmarSenha) {
            showError('As senhas não coincidem.');
            return;
        }

        button.disabled = true;
        button.innerHTML = '<i class="ph ph-spinner fa-spin me-2"></i> Salvando...';
        try {
            const response = await fetch('/ctt/api/auth/ativar-conta', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'Accept': 'application/json', ...csrfHeader() },
                body: JSON.stringify({ token, nova_senha: novaSenha, confirmar_senha: confirmarSenha }),
            });
            const result = await response.json();
            if (!response.ok) throw new Error(result.message || 'Não foi possível ativar a conta.');

            success.textContent = result.data?.message || 'Conta ativada com sucesso!';
            success.classList.remove('d-none');
            form.classList.add('d-none');
            setTimeout(() => { window.location.href = loginUrl; }, 1800);
        } catch (activationError) {
            showError(activationError.message || 'Algo deu errado. Tente novamente.');
            button.disabled = false;
            button.innerHTML = '<i class="ph ph-check-circle me-2"></i> Ativar Conta';
        }
    });
});
