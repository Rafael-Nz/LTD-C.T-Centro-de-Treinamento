document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formTreino');
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

    function setFieldValue(fieldId, value) {
        const field = document.getElementById(fieldId);
        if (!field) return;

        field.value = value ?? '';
        if ($(field).hasClass('select2-hidden-accessible')) {
            $(field).trigger('change');
        }
    }

    function parseApiList(payload) {
        if (!payload) return [];
        if (Array.isArray(payload)) return payload;
        if (Array.isArray(payload.data)) return payload.data;
        if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
        return [];
    }

    function validate() {
        clearErrors();
        let valid = true;

        const nome = document.getElementById('nome')?.value.trim() || '';
        if (!nome) {
            showError('nome', 'Nome e obrigatorio.');
            valid = false;
        } else if (nome.length > 100) {
            showError('nome', 'Nome nao pode exceder 100 caracteres.');
            valid = false;
        }

        if (!document.getElementById('modalidade_id')?.value) {
            showError('modalidade_id', 'Modalidade e obrigatoria.');
            valid = false;
        }

        return valid;
    }

    async function loadModalidades() {
        const field = document.getElementById('modalidade_id');
        if (!field) return;

        const response = await fetch('/ctt/api/modalidades');
        const payload = await response.json();
        const items = parseApiList(payload);

        if (!response.ok) {
            throw new Error(payload.message || 'Falha ao carregar modalidades.');
        }

        field.innerHTML = '';
        field.appendChild(new Option('Selecione a modalidade...', '', false, false));

        items.forEach(item => {
            field.appendChild(new Option(item.nome, item.id, false, false));
        });

        if ($.fn.select2) {
            $(field).select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione a modalidade...',
                allowClear: false
            });
        }
    }

    async function loadTreino() {
        if (!id) return;

        const response = await fetch(`/ctt/api/treinos/${id}`);
        const payload = await response.json();
        const treino = payload.data || null;

        if (!response.ok || !payload.success || !treino) {
            throw new Error(payload.message || 'Falha ao carregar treino.');
        }

        setFieldValue('nome', treino.nome);
        setFieldValue('modalidade_id', treino.modalidade_id);
        setFieldValue('descricao', treino.descricao || '');
    }

    async function init() {
        try {
            await loadModalidades();

            await loadTreino();
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao carregar formulario.', 'error');
        }
    }

    init();

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!validate()) {
            Swal.fire('Atencao', 'Corrija os campos obrigatorios.', 'warning');
            return;
        }

        const payload = {
            nome: document.getElementById('nome').value.trim(),
            modalidade_id: parseInt(document.getElementById('modalidade_id').value, 10),
            descricao: document.getElementById('descricao').value.trim() || null,
            ativo: true
        };

        const submitButton = form.querySelector('[type="submit"]');
        if (submitButton) submitButton.disabled = true;

        try {
            const response = await fetch(id ? `/ctt/api/treinos/${id}` : '/ctt/api/treinos', {
                method: id ? 'PUT' : 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });
            const result = await response.json();

            if (!response.ok || !result.success) {
                throw new Error(result.message || 'Falha ao salvar treino.');
            }

            await Swal.fire(
                'Sucesso',
                id ? 'Treino atualizado com sucesso.' : 'Treino cadastrado com sucesso.',
                'success'
            );

            window.location.href = '/ctt/admin/treinos';
        } catch (error) {
            Swal.fire('Erro', error.message || 'Falha ao salvar treino.', 'error');
        } finally {
            if (submitButton) submitButton.disabled = false;
        }
    });
});
