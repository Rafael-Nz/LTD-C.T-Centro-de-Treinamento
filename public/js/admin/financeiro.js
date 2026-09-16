(() => {
    const api = '/ctt/api/financeiro';
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
    const post = (url, body) => request(url, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify(body) });

    const money = value => Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const formaPagamento = value => ({ pix: 'Pix', dinheiro: 'Dinheiro', cartao: 'Cartão', boleto: 'Boleto', transferencia: 'Transferência', outro: 'Outro' }[value] || value);
    const dataPagamento = value => {
        if (!value) return 'Não informada';
        const [dia, hora] = String(value).split(/[T ]/);
        return dia.split('-').reverse().join('/') + (hora ? ` às ${hora.slice(0, 5)}` : '');
    };
    const iniciarSelectModal = popup => {
        if (window.jQuery?.fn.select2) $(popup).find('select').select2({ theme: 'bootstrap-5', width: '100%', dropdownParent: $(popup), minimumResultsForSearch: Infinity });
    };
    const destruirSelectModal = () => {
        if (window.jQuery?.fn.select2) $(Swal.getPopup()).find('select.select2-hidden-accessible').select2('destroy');
    };
    const date = value => value ? new Date(`${value}T00:00:00`).toLocaleDateString('pt-BR') : '-';
    const status = value => {
        const type =
            value === 'paga' || value === 'ativo'
                ? 'success'
                : value === 'vencida' || value === 'cancelado'
                    ? 'danger'
                    : 'secondary';

        return `<span class="badge bg-${type}-subtle text-${type}-emphasis">${value}</span>`;
    };

    async function request(url, options = {}) {
        const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(url, { credentials: 'same-origin', ...options, headers: { ...(options.headers || {}), ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {}) } });
        const text = await response.text();
        let payload;
        try {
            payload = JSON.parse(text);
        } catch {
            throw new Error(`A API retornou uma resposta inválida (HTTP ${response.status}).`);
        }
        if (!response.ok || payload.success === false) { const error = new Error(payload.message || 'Não foi possível concluir a operação.'); error.status = response.status; throw error; }
        return payload.data;
    }

    function showError(error) {
        if (window.Swal) Swal.fire({ icon: 'error', title: 'Financeiro', text: error.message });
        else window.alert(error.message);
    }

    async function loadServicos() {
        const table = document.querySelector('#tabelaServicos');
        if (!table) return;
        try {
            const rows = await request(`${api}/servicos`);
            table.innerHTML = rows.length ? rows.map(item => `<tr><td>${item.nome}</td><td>${item.tipo}</td><td class="text-end">${money(item.valor_base)}</td><td>${status(item.ativo == 1 ? 'ativo' : 'inativo')}</td></tr>`).join('') : '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum serviço cadastrado.</td></tr>';
        } catch (error) { table.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${error.message}</td></tr>`; }
    }

    async function loadPlanos() {
        const table = document.querySelector('#tabelaPlanos');
        if (!table) return;
        try {
            const rows = await request(`${api}/planos`);
            table.innerHTML = rows.length ? rows.map(item => `<tr><td>${item.nome}</td><td>${item.periodicidade}</td><td class="text-end">${money(item.valor)}</td><td>${status(item.ativo == 1 ? 'ativo' : 'inativo')}</td></tr>`).join('') : '<tr><td colspan="4" class="text-center text-muted py-4">Nenhum plano cadastrado.</td></tr>';
        } catch (error) { table.innerHTML = `<tr><td colspan="4" class="text-center text-danger py-4">${error.message}</td></tr>`; }
    }

    async function loadContratos() {
        if (typeof recarregarTabela === 'function') recarregarTabela('tabelaContratos');
    }

    async function loadCobrancas() {
        if (document.querySelector('table#tabelaCobrancas')) {
            recarregarTabela('tabelaCobrancas');
            return;
        }
        const rows = await request(`${api}/cobrancas`);
        const table = document.querySelector('#tabelaCobrancas');
        const summary = document.querySelector('#tabelaResumo');
        const html = rows.map(item => `<tr><td>${esc(item.aluno_nome)}</td><td>${esc(item.descricao)}</td><td>${esc(item.competencia || '-')}</td><td>${date(item.data_vencimento)}</td><td>${status(item.status)}</td><td class="text-end">${money(item.valor_final)}<small class="d-block">Recebido: ${money(item.total_pago)}</small><strong class="d-block">Saldo: ${money(item.saldo)}</strong></td>${table ? `<td class="text-end"><button class="btn btn-sm btn-outline-secondary btn-detalhes" data-id="${item.id}">Histórico</button> ${item.status !== 'cancelada' ? `${Number(item.saldo) > 0 || sessionStorage.getItem(`ctt-pagamento-${item.id}`) ? `<button class="btn btn-sm btn-outline-success btn-pagamento" data-id="${item.id}">Receber</button>` : ''} <button class="btn btn-sm btn-outline-danger btn-cancelar" data-id="${item.id}">Cancelar</button>` : ''}</td>` : ''}</tr>`).join('');
        if (table) table.innerHTML = html || '<tr><td colspan="7" class="text-center text-muted py-4">Nenhuma cobrança cadastrada.</td></tr>';
        if (summary) summary.innerHTML = rows.slice(0, 8).map(item => `<tr><td>${item.aluno_nome}</td><td>${item.descricao}</td><td>${date(item.data_vencimento)}</td><td>${status(item.status)}</td><td class="text-end">${money(item.valor_final)}</td></tr>`).join('') || '<tr><td colspan="5" class="text-center text-muted py-4">Nenhuma cobrança cadastrada.</td></tr>';
        const open = rows.filter(item => item.status === 'aberta').reduce((sum, item) => sum + Number(item.saldo), 0);
        const overdue = rows.filter(item => item.status === 'vencida').reduce((sum, item) => sum + Number(item.saldo), 0);
        document.querySelector('#totalAberto')?.replaceChildren(document.createTextNode(money(open)));
        document.querySelector('#totalPago')?.replaceChildren(document.createTextNode(money(rows.reduce((sum, item) => sum + Number(item.total_pago || 0), 0))));
        document.querySelector('#totalVencido')?.replaceChildren(document.createTextNode(money(overdue)));
        document.querySelector('#totalCobrancas')?.replaceChildren(document.createTextNode(String(rows.length)));
    }

    document.querySelector('#formServico')?.addEventListener('submit', async event => {
        event.preventDefault();
        try {
            await request(`${api}/servicos`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nome: document.querySelector('#servicoNome').value, tipo: document.querySelector('#servicoTipo').value, valor_base: Number(document.querySelector('#servicoValor').value), recorrente: true }) });
            event.target.reset();
            await loadServicos();
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Serviço cadastrado', timer: 1400, showConfirmButton: false });
        } catch (error) { showError(error); }
    });

    document.querySelector('#formPlano')?.addEventListener('submit', async event => {
        event.preventDefault();
        try {
            await request(`${api}/planos`, { method: 'POST', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ nome: document.querySelector('#planoNome').value, periodicidade: document.querySelector('#planoPeriodicidade').value, valor: Number(document.querySelector('#planoValor').value), descricao: document.querySelector('#planoDescricao').value || null }) });
            event.target.reset();
            await loadPlanos();
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Plano cadastrado', timer: 1400, showConfirmButton: false });
        } catch (error) { showError(error); }
    });

    document.querySelector('#atualizarVencidas')?.addEventListener('click', async () => {
        try { await request(`${api}/cobrancas/atualizar-vencidas`, { method: 'POST' }); await loadCobrancas(); } catch (error) { showError(error); }
    });

    document.addEventListener('click', async event => {
        const statusButton = event.target.closest('.btn-status-contrato');
        if (statusButton) {
            statusButton.disabled = true;
            try {
                const options = statusButton.dataset.status === 'ativo' ? { pausado: 'Pausar', encerrado: 'Encerrar', cancelado: 'Cancelar' } : { ativo: 'Retomar', encerrado: 'Encerrar', cancelado: 'Cancelar' };
                const answer = await Swal.fire({ title: 'Alterar status do contrato', text: 'As cobranças já emitidas e os pagamentos registrados serão preservados. Enquanto o contrato estiver pausado, nenhuma nova cobrança será gerada. Ao retomar, o sistema poderá gerar cobranças pendentes, inclusive referentes ao período de pausa. Se encerrar ou cancelar, não será possível reativar este contrato; será necessário cadastrar um novo.', input: 'select', inputOptions: options, inputPlaceholder: 'Selecione o novo status', inputValidator: value => !value && 'Selecione o status.', showCancelButton: true, confirmButtonText: 'Confirmar alteração', cancelButtonText: 'Voltar' });
                if (answer.isConfirmed && Object.prototype.hasOwnProperty.call(options, answer.value)) {
                    await request(`${api}/contratos/${statusButton.dataset.id}/status`, { method: 'PUT', headers: { 'Content-Type': 'application/json' }, body: JSON.stringify({ status: answer.value }) });
                    await loadContratos();
                }
            } catch (error) { showError(error); } finally { statusButton.disabled = false; }
            return;
        }
        const button = event.target.closest('.btn-pagamento, .btn-cancelar, .btn-detalhes');
        if (!button) return;
        button.disabled = true;
        const id = button.dataset.id;
        try {

            if (button.classList.contains('btn-cancelar')) {
                const answer = await motivo('Cancelar cobrança', 'Pagamentos precisam estar estornados. A cobrança continuará no histórico.');
                if (answer) { await post(`${api}/cobrancas/${id}/cancelar`, { justificativa: answer }); await loadCobrancas(); }
                return;
            }
            const item = await request(`${api}/cobrancas/${id}`);
            if (button.classList.contains('btn-detalhes')) {
                const pagamentos = item.pagamentos || [];
                const disponiveis = pagamentos.filter(p => !p.estornado_em);
                const options = Object.fromEntries(disponiveis.map(p => [String(p.id), `${dataPagamento(p.data_pagamento)} · ${money(p.valor_pago)} · ${formaPagamento(p.forma_pagamento)}`]));
                const linhas = pagamentos.map(p => `<tr><td>${esc(dataPagamento(p.data_pagamento))}</td><td class="text-end text-nowrap">${money(p.valor_pago)}</td><td>${esc(formaPagamento(p.forma_pagamento))}</td><td>${esc(p.registrado_por_nome || 'Não informado')}</td><td>${p.estornado_em ? `<span class="badge bg-secondary">Estornado</span><small class="d-block mt-1">${esc(dataPagamento(p.estornado_em))}<br>Por: ${esc(p.estornado_por_nome || 'Não informado')}<br>Motivo: ${esc(p.motivo_estorno || 'Não informado')}</small>` : '<span class="badge bg-success">Confirmado</span>'}</td></tr>`).join('');
                const answer = await Swal.fire({
                    title: 'Histórico da cobrança', width: 850,
                    html: `<div class="text-start"><p class="mb-1"><strong>${esc(item.aluno_nome)}</strong></p><p class="text-muted">${esc(item.descricao)}</p><p>Total recebido: <strong>${money(item.total_pago)}</strong> · Saldo a receber: <strong>${money(item.saldo)}</strong></p>${item.motivo_cancelamento ? `<p>Motivo do cancelamento: ${esc(item.motivo_cancelamento)}</p>` : ''}<h3 class="h6">Pagamentos desta cobrança</h3>${pagamentos.length ? `<div class="table-responsive"><table class="table table-hover align-middle small"><thead><tr><th scope="col">Data</th><th scope="col" class="text-end">Valor</th><th scope="col">Forma de pagamento</th><th scope="col">Registrado por</th><th scope="col">Situação</th></tr></thead><tbody>${linhas}</tbody></table></div>` : '<p class="text-muted">Nenhum pagamento registrado para esta cobrança.</p>'}</div>`,
                    ...(disponiveis.length ? {
                        input: 'select', inputOptions: options, inputLabel: 'Pagamento que deseja estornar', inputPlaceholder: 'Selecione um pagamento',
                        confirmButtonText: 'Estornar pagamento', showCancelButton: true, cancelButtonText: 'Fechar',
                        inputValidator: value => !Object.prototype.hasOwnProperty.call(options, value) && 'Selecione um pagamento desta cobrança.'
                    } : { showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Fechar' }),
                    didOpen: iniciarSelectModal, willClose: destruirSelectModal
                });
                if (answer.isConfirmed && typeof answer.value === 'string' && Object.prototype.hasOwnProperty.call(options, answer.value)) {
                    const reason = await motivo('Justificativa do estorno', 'O valor integral será retirado do total recebido. Este registro não devolve dinheiro pelo banco.');
                    if (reason) { await post(`${api}/cobrancas/${id}/pagamentos/${answer.value}/estornar`, { justificativa: reason }); await loadCobrancas(); }
                }
                return;
            }
            const storageKey = `ctt-pagamento-${id}`;
            let pending = JSON.parse(sessionStorage.getItem(storageKey) || 'null');
            if (!pending) {
                const answer = await Swal.fire({ title: 'Registrar recebimento', html: `<p>Saldo: ${money(item.saldo)}</p><label for="recebimentoValor">Valor recebido *</label><input id="recebimentoValor" class="swal2-input" type="number" min="0.01" step="0.01" max="${Number(item.saldo)}" value="${Number(item.saldo)}"><label for="recebimentoForma">Forma de pagamento *</label><select id="recebimentoForma" class="form-select"><option value="pix">Pix</option><option value="dinheiro">Dinheiro</option><option value="cartao">Cartão</option><option value="boleto">Boleto</option><option value="transferencia">Transferência</option><option value="outro">Outro</option></select><label for="recebimentoTransacao">Identificação bancária (opcional)</label><input id="recebimentoTransacao" class="swal2-input" maxlength="120">`, showCancelButton: true, confirmButtonText: 'Registrar', cancelButtonText: 'Voltar', didOpen: iniciarSelectModal, willClose: destruirSelectModal, preConfirm: () => {
                    const value = document.querySelector('#recebimentoValor').value;
                    if (!/^\d+(\.\d{1,2})?$/.test(value) || Number(value) <= 0 || Number(value) > Number(item.saldo)) { Swal.showValidationMessage('Informe um valor válido, até o saldo disponível.'); return false; }
                    return { valor_pago: value, forma_pagamento: document.querySelector('#recebimentoForma').value, transacao_id: document.querySelector('#recebimentoTransacao').value.trim() || null, chave_idempotencia: crypto.randomUUID() };
                } });
                if (!answer.isConfirmed) return;
                pending = answer.value;
                sessionStorage.setItem(storageKey, JSON.stringify(pending));
            } else {
                const answer = await Swal.fire({ title: 'Conferir recebimento anterior', text: `Existe uma operação de ${money(pending.valor_pago)} aguardando confirmação. Reenviar usará a mesma identificação e não duplicará o pagamento.`, showCancelButton: true, confirmButtonText: 'Conferir / reenviar', cancelButtonText: 'Voltar' });
                if (!answer.isConfirmed) return;
            }
            try { await post(`${api}/cobrancas/${id}/pagamentos`, pending); }
            catch (error) { if (error.status === 422) sessionStorage.removeItem(storageKey); throw error; }
            sessionStorage.removeItem(storageKey);
            await loadCobrancas();
            await Swal.fire({ icon: 'success', title: 'Pagamento registrado' });
        } catch (error) { showError(error); } finally { button.disabled = false; }
    });

    async function motivo(title, text) {
        const result = await Swal.fire({ title, text, input: 'textarea', inputLabel: 'Justificativa obrigatória', inputAttributes: { maxlength: 500 }, showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Voltar', inputValidator: value => (!value || value.trim().length < 5) ? 'Escreva pelo menos 5 caracteres.' : undefined });
        return result.isConfirmed ? result.value.trim() : null;
    }

    if (document.querySelector('#tabelaServicos')) loadServicos();
    if (document.querySelector('#tabelaPlanos')) loadPlanos();

    if (document.querySelector('#tabelaCobrancas') || document.querySelector('#financeiroResumo')) {
        loadCobrancas().catch(showError);
    }
})();
