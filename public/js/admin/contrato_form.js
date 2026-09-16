(() => {
    const api = '/ctt/api/financeiro';
    const request = async (url, options = {}) => {
        const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(url, { credentials: 'same-origin', ...options, headers: { ...(options.headers || {}), ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {}) } });
        const text = await response.text();
        let payload;
        try {
            payload = JSON.parse(text);
        } catch {
            throw new Error(`A API retornou uma resposta inválida (HTTP ${response.status}).`);
        }
        if (!response.ok || payload.success === false) throw new Error(payload.message || 'Não foi possível concluir a operação.');
        return payload.data;
    };
    const money = value => Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const showError = error => window.Swal ? Swal.fire({ icon: 'error', title: 'Contrato', text: error.message }) : window.alert(error.message);

    async function loadOptions() {
        const [alunosResponse, planos] = await Promise.all([request('/ctt/api/alunos?draw=1&start=0&length=1000'), request(`${api}/planos`)]);
        const alunos = alunosResponse.data || [];
        document.querySelector('#alunoId').innerHTML = '<option value="">Selecione um aluno</option>' + alunos.map(item => `<option value="${item.id}">${item.nome} ${item.sobrenome} - ${item.codigo_matricula || ''}</option>`).join('');
        document.querySelector('#planoId').innerHTML += planos.map(item => `<option value="${item.id}" data-valor="${item.valor}">${item.nome} - ${money(item.valor)}</option>`).join('');
    }

    document.querySelector('#planoId').addEventListener('change', event => {
        const value = event.target.selectedOptions[0]?.dataset.valor;
        if (value !== undefined) document.querySelector('#valorContratado').value = value;
    });

    document.querySelector('#formContrato').addEventListener('submit', async event => {
        event.preventDefault();
        const payload = {
            aluno_id: Number(document.querySelector('#alunoId').value),
            plano_id: document.querySelector('#planoId').value ? Number(document.querySelector('#planoId').value) : null,
            data_inicio: document.querySelector('#dataInicio').value,
            data_fim: document.querySelector('#dataFim').value || null,
            periodicidade: document.querySelector('#periodicidade').value,
            dia_vencimento: Number(document.querySelector('#diaVencimento').value),
            valor_contratado: Number(document.querySelector('#valorContratado').value),
            desconto: Number(document.querySelector('#desconto').value || 0),
            multa_percentual: Number(document.querySelector('#multa').value || 0),
            juros_percentual: Number(document.querySelector('#juros').value || 0),
            observacoes: document.querySelector('#observacoes').value || null
        };
        try {
            await request(`${api}/contratos`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(payload) });
            window.location.href = '/ctt/admin/financeiro';
        } catch (error) { showError(error); }
    });

    loadOptions().catch(showError);
    document.querySelector('#dataInicio').value = new Date().toISOString().slice(0, 10);
})();
