document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formLocal');
    const id = form?.dataset.id || null;

    function clearErrors() {
        document.querySelectorAll('.invalid-feedback').forEach(el => el.remove());
        document.querySelectorAll('.is-invalid').forEach(el => el.classList.remove('is-invalid'));
    }

    function showError(fieldId, msg) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.classList.add('is-invalid');

        let feedback = field.parentNode.querySelector('.invalid-feedback');
        if (!feedback) {
            feedback = document.createElement('div');
            feedback.className = 'invalid-feedback';
            field.parentNode.appendChild(feedback);
        }

        feedback.textContent = msg;
    }

    function validate() {
        clearErrors();
        let valid = true;

        const nome = document.getElementById('nome').value.trim();
        const capMin = parseInt(document.getElementById('capacidade_minima').value || 0, 10);
        const capMax = parseInt(document.getElementById('capacidade_maxima').value || 0, 10);

        if (!nome) {
            showError('nome', 'Nome do local e obrigatorio.');
            valid = false;
        } else if (nome.length > 50) {
            showError('nome', 'Nome nao pode exceder 50 caracteres.');
            valid = false;
        }

        if (!capMin || capMin < 1) {
            showError('capacidade_minima', 'Capacidade minima deve ser maior que zero.');
            valid = false;
        }

        if (!capMax || capMax < 1) {
            showError('capacidade_maxima', 'Capacidade maxima deve ser maior que zero.');
            valid = false;
        }

        if (capMin >= capMax) {
            showError('capacidade_minima', 'Capacidade minima deve ser menor que a maxima.');
            valid = false;
        }

        return valid;
    }

    async function loadLocal() {
        if (!id) return;

        const response = await fetch(`/ctt/api/locais/${id}`);
        const payload = await response.json();
        const local = payload.data || null;

        if (!response.ok || !payload.success || !local) {
            throw new Error(payload.message || 'Falha ao carregar local.');
        }

        document.getElementById('nome').value = local.nome || '';
        document.getElementById('capacidade_minima').value = local.capacidade_minima ?? '';
        document.getElementById('capacidade_maxima').value = local.capacidade_maxima ?? '';
        document.getElementById('equipamentos').value = local.equipamentos || '';
    }

    loadLocal().catch(error => {
        Swal.fire('Erro', error.message || 'Falha ao carregar local.', 'error');
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!validate()) {
            Swal.fire('Atencao', 'Corrija os campos obrigatorios.', 'warning');
            return;
        }

        const payload = {
            nome: document.getElementById('nome').value.trim(),
            capacidade_minima: parseInt(document.getElementById('capacidade_minima').value, 10),
            capacidade_maxima: parseInt(document.getElementById('capacidade_maxima').value, 10),
            equipamentos: document.getElementById('equipamentos').value.trim() || null
        };

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(id ? `/ctt/api/locais/${id}` : '/ctt/api/locais', {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao salvar local.');
            }

            await Swal.fire(
                'Sucesso',
                id ? 'Local atualizado com sucesso.' : 'Local cadastrado com sucesso.',
                'success'
            );

            window.location.href = '/ctt/admin/locais';
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao salvar local.', 'error');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
});
