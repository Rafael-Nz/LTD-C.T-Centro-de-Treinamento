document.addEventListener('DOMContentLoaded', () => {
    const resetRequestForm = document.getElementById('password-reset-request-form');
    const resetSubmit = document.getElementById('reset-submit');
    const resetError = document.getElementById('reset-error');
    const resetSuccess = document.getElementById('reset-success');
    const emailInput = document.getElementById('email');

    resetRequestForm.addEventListener('submit', async (event) => {
        event.preventDefault();
        resetError.classList.add('d-none');
        resetSuccess.classList.add('d-none');
        resetSubmit.disabled = true;
        resetSubmit.innerHTML = '<i class="ph ph-spinner fa-spin me-2"></i> Enviando...';

        try {
            const response = await fetch('/ctt/api/auth/recuperar-senha', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({ email: emailInput.value.trim() })
            });
            const result = await response.json();

            if (!response.ok) {
                throw new Error(result.message || 'Não foi possível solicitar a recuperação.');
            }

            resetSuccess.textContent = result.data?.message || 'Se o e-mail estiver cadastrado, um link de recuperação será enviado.';
            resetSuccess.classList.remove('d-none');
            resetRequestForm.classList.add('d-none');
        } catch (error) {
            resetError.textContent = error.message || 'Algo deu errado. Tente novamente.';
            resetError.classList.remove('d-none');
            resetSubmit.disabled = false;
            resetSubmit.innerHTML = '<i class="ph ph-paper-plane-tilt me-2"></i> Enviar Link de Recuperação';
        }
    });
});
