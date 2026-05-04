document.addEventListener("DOMContentLoaded", () => {
    const formLogin = document.getElementById('formLogin');
    const emailInput = document.getElementById('email');
    const senhaInput = document.getElementById('senha');
    const btnSubmit = formLogin.querySelector('button[type="submit"]');
    const togglePassword = document.getElementById("togglePassword");
    const eyeIcon = document.getElementById("eyeIcon");
    
    const errorContainer = document.getElementById('error-container');

    togglePassword.addEventListener("click", () => {
        const isPassword = senhaInput.type === "password";
        senhaInput.type = isPassword ? "text" : "password";

        // Alterna o ícone do Phosphor Icons
        eyeIcon.classList.toggle("ph-eye");
        eyeIcon.classList.toggle("ph-eye-slash");
    });

    formLogin.addEventListener('submit', async (e) => {
        e.preventDefault(); 

        if (errorContainer) errorContainer.innerHTML = '';

        // Feedback visual no botão
        const textoOriginal = btnSubmit.innerHTML;
        btnSubmit.innerHTML = '<i class="ph ph-spinner fa-spin me-2"></i> Entrando...';
        btnSubmit.disabled = true;

        try {
            const response = await fetch('/ctt/api/auth/login', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json'
                },
                body: JSON.stringify({
                    login: emailInput.value,
                    senha: senhaInput.value
                })
            });

            const data = await response.json();

            if (response.ok) {
                window.location.href = '/ctt/admin/inicio'; 
            } else {
                if (errorContainer) {
                    errorContainer.innerHTML = `
                        <div class="alert alert-danger alert-custom border-0 fade show" role="alert" style="background-color: #dc3545; color: white;">
                            <div class="d-flex align-items-center small">
                                <i class="ph ph-warning-circle me-2" style="font-size: 1rem;"></i>
                                <div>${data.message || 'As informações de login que você inseriu estão incorretas.'}</div>
                            </div>
                        </div>
                    `;
                } else {
                    alert(data.message || 'As informações de login que você inseriu estão incorretas.');
                }
                
                // Restaura o botão
                btnSubmit.innerHTML = textoOriginal;
                btnSubmit.disabled = false;
            }
      } catch (error) {
          console.error('Erro na requisição:', error);
          if (errorContainer) {
              errorContainer.innerHTML = `
                  <div class="alert alert-danger alert-custom border-0 fade show" role="alert" style="background-color: #dc3545; color: white;">
                      <div class="d-flex align-items-center small">
                          <i class="ph ph-warning-circle me-2" style="font-size: 1rem;"></i>
                          <div>Algo deu errado. Por favor, tente novamente em instantes.</div>
                      </div>
                  </div>
              `;
          }
          btnSubmit.innerHTML = textoOriginal;
          btnSubmit.disabled = false;
      }
    });
});