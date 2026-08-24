document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formModalidade');
    const id = form?.dataset.id || null;
    const redirectUrl = '/ctt/admin/modalidades';

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

    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (field) {
            field.value = value ?? '';
        }
    }

    function validate() {
        clearErrors();
        let valid = true;

        const nome = document.getElementById('nome')?.value.trim() || '';

        if (!nome) {
            showError('nome', 'Nome da modalidade e obrigatorio.');
            valid = false;
        } else if (nome.length > 50) {
            showError('nome', 'Nome da modalidade nao pode exceder 50 caracteres.');
            valid = false;
        }

        return valid;
    }

    async function loadModalidade() {
        if (!id) return;

        const response = await fetch(`/ctt/api/modalidades/${id}`);
        const payload = await response.json();
        const modalidade = payload.data || null;

        if (!response.ok || !payload.success || !modalidade) {
            throw new Error(payload.message || 'Falha ao carregar modalidade.');
        }

        setFieldValue('nome', modalidade.nome);
        setFieldValue('descricao', modalidade.descricao || '');
    }

    loadModalidade().catch(error => {
        Swal.fire('Erro', error.message || 'Falha ao carregar modalidade.', 'error');
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!validate()) {
            Swal.fire('Atencao', 'Corrija os campos obrigatorios.', 'warning');
            return;
        }

        const payload = {
            nome: document.getElementById('nome').value.trim(),
            descricao: document.getElementById('descricao').value.trim() || null,
            ativo: true
        };

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(id ? `/ctt/api/modalidades/${id}` : '/ctt/api/modalidades', {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao salvar modalidade.');
            }

            await Swal.fire(
                'Sucesso',
                id ? 'Modalidade atualizada com sucesso.' : 'Modalidade cadastrada com sucesso.',
                'success'
            );

            window.location.href = redirectUrl;
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao salvar modalidade.', 'error');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
});
