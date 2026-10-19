document.addEventListener('DOMContentLoaded', function () {
    const form = document.getElementById('formCargo');
    const id = form?.dataset.id || null;
    const redirectUrl = '/ctt/admin/cargos';

    function escapeHtml(value) {
        if (value === null || value === undefined) return '';
        return String(value)
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

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

    const containerPermissoes = document.getElementById('containerPermissoes');
    let permissoesCarregadas = [];

    function renderPermissoes(permissoes, selecionadas = []) {
        if (!containerPermissoes) return;
        if (!Array.isArray(permissoes) || permissoes.length === 0) {
            containerPermissoes.innerHTML = '<div class="col-12 text-muted small">Nenhuma permissão disponível no sistema.</div>';
            return;
        }

        // Agrupa por módulo
        const modulos = {};
        permissoes.forEach(p => {
            const mod = p.modulo || 'Outros';
            if (!modulos[mod]) modulos[mod] = [];
            modulos[mod].push(p);
        });

        const selecionadasSet = new Set(selecionadas.map(Number));
        let html = '';

        for (const [mod, lista] of Object.entries(modulos)) {
            const modSlug = mod.toLowerCase().replace(/[^a-z0-9]/g, '_');
            html += `
                <div class="col-md-6 col-lg-4">
                    <div class="card h-100 border shadow-none bg-body-tertiary">
                        <div class="card-header bg-transparent py-2 d-flex justify-content-between align-items-center">
                            <strong class="small mb-0">${escapeHtml(mod)}</strong>
                            <button type="button" class="btn btn-link btn-sm p-0 text-decoration-none btn-toggle-modulo" data-target="mod_${modSlug}">
                                Alternar
                            </button>
                        </div>
                        <div class="card-body p-2">
            `;

            lista.forEach(p => {
                const checked = selecionadasSet.has(Number(p.id)) ? 'checked' : '';
                html += `
                    <div class="form-check form-switch mb-2">
                        <input class="form-check-input perm-check mod_${modSlug}" type="checkbox" role="switch" value="${p.id}" id="perm_${p.id}" ${checked}>
                        <label class="form-check-label small" for="perm_${p.id}">
                            <strong>${escapeHtml(p.nome)}</strong>
                            <span class="d-block text-muted" style="font-size: 0.75rem;">${escapeHtml(p.descricao || '')}</span>
                        </label>
                    </div>
                `;
            });

            html += `
                        </div>
                    </div>
                </div>
            `;
        }

        containerPermissoes.innerHTML = html;

        // Listener para alternar módulo
        containerPermissoes.querySelectorAll('.btn-toggle-modulo').forEach(btn => {
            btn.addEventListener('click', () => {
                const targetClass = btn.dataset.target;
                const checks = containerPermissoes.querySelectorAll(`.${targetClass}`);
                const someUnchecked = Array.from(checks).some(c => !c.checked);
                checks.forEach(c => c.checked = someUnchecked);
            });
        });
    }

    document.getElementById('btnMarcarTodas')?.addEventListener('click', () => {
        containerPermissoes?.querySelectorAll('.perm-check').forEach(c => c.checked = true);
    });

    document.getElementById('btnDesmarcarTodas')?.addEventListener('click', () => {
        containerPermissoes?.querySelectorAll('.perm-check').forEach(c => c.checked = false);
    });

    async function loadData() {
        // Carrega catálogo de permissões
        try {
            const respPerms = await fetch('/ctt/api/permissoes');
            const dataPerms = await respPerms.json();
            permissoesCarregadas = dataPerms.data || dataPerms || [];
        } catch (e) {
            console.error('Erro ao buscar catálogo de permissões:', e);
        }

        let permissoesCargo = [];

        if (id) {
            try {
                const response = await fetch(`/ctt/api/cargos/${id}`);
                const payload = await response.json();
                const cargo = payload.data || null;

                if (!response.ok || !payload.success || !cargo) {
                    throw new Error(payload.message || 'Falha ao carregar cargo.');
                }

                setFieldValue('nome', cargo.nome);
                setFieldValue('salario_base', cargo.salario_base ?? 0);
                setFieldValue('descricao', cargo.descricao || '');

                if (Array.isArray(cargo.permissoes)) {
                    permissoesCargo = cargo.permissoes;
                }
            } catch (error) {
                Swal.fire('Erro', error.message || 'Falha ao carregar cargo.', 'error');
            }
        }

        renderPermissoes(permissoesCarregadas, permissoesCargo);
    }

    loadData();

    form?.addEventListener('submit', async function (event) {
        event.preventDefault();

        if (!validate()) {
            Swal.fire('Atencao', 'Corrija os campos obrigatorios.', 'warning');
            return;
        }

        const permissoesSelecionadas = Array.from(
            containerPermissoes.querySelectorAll('.perm-check:checked')
        ).map(c => Number(c.value));

        const salarioBaseRaw = document.getElementById('salario_base').value.trim();
        const payload = {
            nome: document.getElementById('nome').value.trim(),
            salario_base: salarioBaseRaw === '' ? 0 : Number(salarioBaseRaw),
            descricao: document.getElementById('descricao').value.trim() || null,
            permissoes: permissoesSelecionadas,
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
