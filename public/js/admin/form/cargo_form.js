document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCargo');
    const id = form?.dataset.id || null;
    const redirectUrl = '/ctt/admin/cargos';

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
        const salarioBaseRaw = document.getElementById('salario_base')?.value.trim() || '';
        const salarioBase = salarioBaseRaw === '' ? 0 : Number(salarioBaseRaw);
        const descricao = document.getElementById('descricao')?.value.trim() || '';

        if (!nome) {
            showError('nome', 'Nome do cargo e obrigatorio.');
            valid = false;
        } else if (nome.length > 100) {
            showError('nome', 'Nome do cargo nao pode exceder 100 caracteres.');
            valid = false;
        }

        if (!Number.isFinite(salarioBase) || salarioBase < 0) {
            showError('salario_base', 'Salario base deve ser um valor numerico maior ou igual a zero.');
            valid = false;
        }

        if (descricao.length > 255) {
            showError('descricao', 'Descricao nao pode exceder 255 caracteres.');
            valid = false;
        }

        return valid;
    }

    async function loadCargo() {
        if (!id) return;

        const response = await fetch(`/ctt/api/cargos/${id}`);
        const payload = await response.json();
        const cargo = payload.data || null;

        if (!response.ok || !payload.success || !cargo) {
            throw new Error(payload.message || 'Falha ao carregar cargo.');
        }

        setFieldValue('nome', cargo.nome);
        setFieldValue('salario_base', cargo.salario_base ?? 0);
        setFieldValue('descricao', cargo.descricao || '');
    }

    loadCargo().catch(error => {
        Swal.fire('Erro', error.message || 'Falha ao carregar cargo.', 'error');
    });

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!validate()) {
            Swal.fire('Atencao', 'Corrija os campos obrigatorios.', 'warning');
            return;
        }

        const salarioBaseRaw = document.getElementById('salario_base').value.trim();
        const payload = {
            nome: document.getElementById('nome').value.trim(),
            salario_base: salarioBaseRaw === '' ? 0 : Number(salarioBaseRaw),
            descricao: document.getElementById('descricao').value.trim() || null,
            ativo: true
        };

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(id ? `/ctt/api/cargos/${id}` : '/ctt/api/cargos', {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao salvar cargo.');
            }

            await Swal.fire(
                'Sucesso',
                id ? 'Cargo atualizado com sucesso.' : 'Cargo cadastrado com sucesso.',
                'success'
            );

            window.location.href = redirectUrl;
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao salvar cargo.', 'error');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
});
