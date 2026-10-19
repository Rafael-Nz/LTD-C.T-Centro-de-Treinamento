(function () {
    const form = document.getElementById('student-login-form');
    const errorBox = document.getElementById('login-error');
    if (!form) return;

    const senhaInput = document.getElementById('uSenha');
    const togglePassword = document.getElementById('toggleStudentPassword');
    const eyeIcon = document.getElementById('studentEyeIcon');

    togglePassword?.addEventListener('click', () => {
        const isPassword = senhaInput.type === 'password';
        senhaInput.type = isPassword ? 'text' : 'password';
        eyeIcon.classList.toggle('ph-eye', isPassword);
        eyeIcon.classList.toggle('ph-eye-slash', !isPassword);
        togglePassword.setAttribute('aria-label', isPassword ? 'Ocultar senha' : 'Mostrar senha');
    });

    const csrfToken = () => {
        const item = document.cookie.split('; ').find((row) => row.startsWith('CTT_CSRF_TOKEN='));
        return item ? decodeURIComponent(item.split('=').slice(1).join('=')) : '';
    };

    form.addEventListener('submit', async (event) => {
        event.preventDefault();
        errorBox.classList.add('d-none');
        const button = form.querySelector('button[type="submit"]');
        button.disabled = true;
        button.textContent = 'Entrando...';

        try {
            const response = await fetch(form.action, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json', 'X-CSRF-Token': csrfToken() },
                credentials: 'same-origin',
                body: JSON.stringify({ area: 'aluno', login: form.login.value.trim(), senha: form.senha.value }),
            });
            const payload = await response.json();
            if (!response.ok || !payload.success) throw new Error(payload.message || 'Não foi possível entrar.');
            window.location.href = '/ctt/dashboard';
        } catch (error) {
            errorBox.textContent = error.message;
            errorBox.classList.remove('d-none');
            button.disabled = false;
            button.textContent = 'Entrar';
        }
    });
})();
