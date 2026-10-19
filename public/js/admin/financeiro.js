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

    async function loadMetricas(competencia = null) {
        if (!document.querySelector('#financeiroResumo')) return;
        try {
            const comp = competencia ?? document.querySelector('#filtroCompetenciaMetricas')?.value ?? 'all';
            const url = `${api}/metricas${comp && comp !== 'all' ? `?competencia=${comp}` : ''}`;
            const res = await request(url);
            const m = res.metricas || {};

            document.querySelector('#totalAberto')?.replaceChildren(document.createTextNode(money(m.total_aberto)));
            document.querySelector('#totalVencido')?.replaceChildren(document.createTextNode(money(m.total_vencido)));
            document.querySelector('#totalPago')?.replaceChildren(document.createTextNode(money(m.total_pago)));
            document.querySelector('#totalCobrancas')?.replaceChildren(document.createTextNode(String(m.total_cobrancas || 0)));

            const detAberto = document.querySelector('#detalheAberto');
            if (detAberto) detAberto.textContent = `${m.qtd_abertas || 0} cobrança(s)`;
            const detVencido = document.querySelector('#detalheVencido');
            if (detVencido) detVencido.textContent = `${m.qtd_vencidas || 0} vencida(s)`;
            const badgeInad = document.querySelector('#badgeInadimplencia');
            if (badgeInad) badgeInad.textContent = `${m.taxa_inadimplencia || 0}% inadimplência`;
            const detPago = document.querySelector('#detalhePago');
            if (detPago) detPago.textContent = `${m.qtd_pagas || 0} quitada(s)`;

            const selectComp = document.querySelector('#filtroCompetenciaMetricas');
            if (selectComp && selectComp.options.length <= 1 && Array.isArray(res.competencias)) {
                res.competencias.forEach(c => {
                    const opt = document.createElement('option');
                    opt.value = c;
                    const [ano, mes] = c.split('-');
                    opt.textContent = `${mes}/${ano}`;
                    selectComp.appendChild(opt);
                });
                if (res.competencia_atual && res.competencia_atual !== 'all') {
                    selectComp.value = res.competencia_atual;
                }
            }

            const btnExport = document.querySelector('#btnExportarCobrancasGeral');
            if (btnExport) {
                btnExport.href = `/ctt/api/financeiro/exportar${comp && comp !== 'all' ? `?competencia=${comp}` : ''}`;
            }
        } catch (error) {
            console.error('Falha ao carregar métricas:', error);
        }
    }

    async function loadCobrancas() {
        if (document.querySelector('table#tabelaCobrancas')) {
            if (typeof recarregarTabela === 'function') recarregarTabela('tabelaCobrancas');
            return;
        }
        await loadMetricas();
    }

    async function imprimirRecibo(cobrancaId, pagamentoId) {
        try {
            const data = await request(`${api}/cobrancas/${cobrancaId}/pagamentos/${pagamentoId}/recibo`);
            const estornoBadge = data.estornado_em
                ? `<div class="alert alert-danger text-center fw-bold py-2 mb-3">PAGAMENTO ESTORNADO EM ${dataPagamento(data.estornado_em)}</div>`
                : '';

            const printHtml = `
                <div class="text-start p-2" id="conteudoReciboImpressao">
                    ${estornoBadge}
                    <div class="d-flex justify-content-between align-items-center border-bottom pb-3 mb-3">
                        <div>
                            <h4 class="h5 mb-0 fw-bold">${esc(data.estabelecimento?.nome || 'Cross C.T')}</h4>
                            <small class="text-muted d-block">${esc(data.estabelecimento?.endereco || '')}</small>
                            <small class="text-muted d-block">CNPJ: ${esc(data.estabelecimento?.documento || '')} · Tel: ${esc(data.estabelecimento?.telefone || '')}</small>
                        </div>
                        <div class="text-end">
                            <span class="badge bg-dark fs-6 px-3 py-2">RECIBO Nº ${data.id}</span>
                            <small class="d-block text-muted mt-1">Data: ${dataPagamento(data.data_pagamento)}</small>
                        </div>
                    </div>

                    <div class="card bg-light border-0 p-3 mb-3">
                        <div class="row g-2 small">
                            <div class="col-sm-6"><strong>Aluno:</strong> ${esc(data.aluno_nome)} ${esc(data.aluno_sobrenome || '')}</div>
                            <div class="col-sm-6"><strong>CPF:</strong> ${esc(data.aluno_cpf || 'Não informado')}</div>
                            <div class="col-sm-6"><strong>Referência:</strong> ${esc(data.cobranca_descricao)} (${esc(data.competencia || 'Avulso')})</div>
                            <div class="col-sm-6"><strong>Vencimento:</strong> ${date(data.data_vencimento)}</div>
                        </div>
                    </div>

                    <table class="table table-bordered align-middle small mb-3">
                        <tbody>
                            <tr>
                                <td><strong>Valor Pago:</strong></td>
                                <td class="text-end h5 mb-0 text-success fw-bold">${money(data.valor_pago)}</td>
                            </tr>
                            <tr>
                                <td><strong>Forma de Pagamento:</strong></td>
                                <td class="text-end">${esc(formaPagamento(data.forma_pagamento))}</td>
                            </tr>
                            ${data.transacao_id ? `<tr><td><strong>Identificação / Transação:</strong></td><td class="text-end">${esc(data.transacao_id)}</td></tr>` : ''}
                            <tr>
                                <td><strong>Saldo Restante da Fatura:</strong></td>
                                <td class="text-end">${money(data.saldo_restante)}</td>
                            </tr>
                            <tr>
                                <td><strong>Operador Responsável:</strong></td>
                                <td class="text-end">${esc(data.registrado_por_nome || 'Sistema')}</td>
                            </tr>
                        </tbody>
                    </table>

                    <div class="border-top pt-2 text-center text-muted small">
                        <small>Chave de autenticação: <code>${esc(data.chave_idempotencia || 'N/A')}</code></small><br>
                        <small>Este recibo comprova a quitação do valor especificado acima junto ao centro de treinamento.</small>
                    </div>
                </div>
            `;

            await Swal.fire({
                title: 'Comprovante de Pagamento',
                html: printHtml,
                width: 650,
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-printer me-1"></i> Imprimir',
                cancelButtonText: 'Fechar',
                confirmButtonColor: '#c53030',
                preConfirm: () => {
                    const printWindow = window.open('', '_blank', 'width=800,height=600');
                    if (printWindow) {
                        printWindow.document.write(`
                            <!DOCTYPE html>
                            <html>
                            <head>
                                <title>Recibo de Pagamento - Cross C.T</title>
                                <link href="/ctt/plugins/bootstrap/css/bootstrap.min.css" rel="stylesheet">
                                <style>
                                    body { font-family: sans-serif; padding: 20px; color: #111; }
                                    @media print { .no-print { display: none; } }
                                </style>
                            </head>
                            <body>
                                ${document.querySelector('#conteudoReciboImpressao').innerHTML}
                                <script>
                                    window.onload = function() { window.print(); };
                                </script>
                            </body>
                            </html>
                        `);
                        printWindow.document.close();
                    } else {
                        window.print();
                    }
                }
            });
        } catch (err) {
            showError(err);
        }
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
        try {
            const res = await request(`${api}/cobrancas/atualizar-vencidas`, { method: 'POST' });
            await loadCobrancas();
            await loadMetricas();
            if (window.Swal) Swal.fire({ icon: 'success', title: 'Atualização concluída', text: `${res.atualizadas || 0} cobrança(s) atualizada(s) para vencidas.`, timer: 1600, showConfirmButton: false });
        } catch (error) { showError(error); }
    });

    document.querySelector('#filtroCompetenciaMetricas')?.addEventListener('change', event => {
        loadMetricas(event.target.value);
    });

    document.querySelector('#btnAtualizarMetricas')?.addEventListener('click', () => {
        loadMetricas();
    });

    document.addEventListener('click', async event => {
        const btnRecibo = event.target.closest('.btn-ver-recibo');
        if (btnRecibo) {
            imprimirRecibo(btnRecibo.dataset.cobranca, btnRecibo.dataset.pagamento);
            return;
        }

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
                if (answer) {
                    await post(`${api}/cobrancas/${id}/cancelar`, { justificativa: answer });
                    await loadCobrancas();
                    await loadMetricas();
                }
                return;
            }

            const item = await request(`${api}/cobrancas/${id}`);

            if (button.classList.contains('btn-detalhes')) {
                const pagamentos = item.pagamentos || [];
                const disponiveis = pagamentos.filter(p => !p.estornado_em);
                const options = Object.fromEntries(disponiveis.map(p => [String(p.id), `${dataPagamento(p.data_pagamento)} · ${money(p.valor_pago)} · ${formaPagamento(p.forma_pagamento)}`]));
                const linhas = pagamentos.map(p => `
                    <tr>
                        <td>${esc(dataPagamento(p.data_pagamento))}</td>
                        <td class="text-end text-nowrap">${money(p.valor_pago)}</td>
                        <td>${esc(formaPagamento(p.forma_pagamento))}</td>
                        <td>${esc(p.registrado_por_nome || 'Não informado')}</td>
                        <td>
                            ${p.estornado_em
                                ? `<span class="badge bg-secondary">Estornado</span><small class="d-block mt-1">${esc(dataPagamento(p.estornado_em))}<br>Por: ${esc(p.estornado_por_nome || 'Não informado')}<br>Motivo: ${esc(p.motivo_estorno || 'Não informado')}</small>`
                                : `<span class="badge bg-success">Confirmado</span> <button type="button" class="btn btn-sm btn-link text-decoration-none p-0 ms-1 btn-ver-recibo" data-cobranca="${item.id}" data-pagamento="${p.id}" title="Imprimir Recibo"><i class="ph ph-printer"></i> Recibo</button>`}
                        </td>
                    </tr>
                `).join('');

                const answer = await Swal.fire({
                    title: 'Histórico da cobrança', width: 850,
                    confirmButtonColor: '#c53030',
                    cancelButtonColor: '#6c757d',
                    html: `<div class="text-start"><p class="mb-1"><strong>${esc(item.aluno_nome)}</strong></p><p class="text-muted">${esc(item.descricao)}</p><p>Total recebido: <strong>${money(item.total_pago)}</strong> · Saldo a receber: <strong>${money(item.saldo)}</strong></p>${item.motivo_cancelamento ? `<p>Motivo do cancelamento: ${esc(item.motivo_cancelamento)}</p>` : ''}<h3 class="h6">Pagamentos desta cobrança</h3>${pagamentos.length ? `<div class="table-responsive"><table class="table table-hover align-middle small"><thead><tr><th scope="col">Data</th><th scope="col" class="text-end">Valor</th><th scope="col">Forma de pagamento</th><th scope="col">Registrado por</th><th scope="col">Situação</th></tr></thead><tbody>${linhas}</tbody></table></div>` : '<p class="text-muted">Nenhum pagamento registrado para esta cobrança.</p>'}</div>`,
                    ...(disponiveis.length ? {
                        input: 'select', inputOptions: options, inputLabel: 'Pagamento que deseja estornar', inputPlaceholder: 'Selecione um pagamento',
                        confirmButtonText: 'Estornar pagamento', showCancelButton: true, cancelButtonText: 'Fechar',
                        inputValidator: value => !Object.prototype.hasOwnProperty.call(options, value) && 'Selecione um pagamento desta cobrança.'
                    } : { showConfirmButton: false, showCancelButton: true, cancelButtonText: 'Fechar' })
                });

                if (answer.isConfirmed && typeof answer.value === 'string' && Object.prototype.hasOwnProperty.call(options, answer.value)) {
                    const reason = await motivo('Justificativa do estorno', 'O valor integral será retirado do total recebido. Este registro não devolve dinheiro pelo banco.');
                    if (reason) {
                        await post(`${api}/cobrancas/${id}/pagamentos/${answer.value}/estornar`, { justificativa: reason });
                        await loadCobrancas();
                        await loadMetricas();
                    }
                }
                return;
            }

            const storageKey = `ctt-pagamento-${id}`;
            let pending = JSON.parse(sessionStorage.getItem(storageKey) || 'null');

            if (!pending) {
                let encargosHtml = '';
                let valorSugerido = Number(item.saldo);
                let encargosCalculados = null;

                if (item.status === 'vencida') {
                    try {
                        encargosCalculados = await request(`${api}/cobrancas/${id}/encargos`);
                        if (encargosCalculados && encargosCalculados.dias_atraso > 0 && encargosCalculados.valor_encargos > 0) {
                            valorSugerido = Number(encargosCalculados.valor_total_atualizado);
                            encargosHtml = `
                                <div class="card border-warning-subtle bg-warning-subtle p-3 mb-3 text-start">
                                    <div class="d-flex align-items-center gap-2 mb-2">
                                        <i class="ph ph-warning-circle text-warning-emphasis fs-5"></i>
                                        <strong class="text-warning-emphasis">Cobrança em atraso (${encargosCalculados.dias_atraso} dia${encargosCalculados.dias_atraso > 1 ? 's' : ''})</strong>
                                    </div>
                                    <div class="small text-dark mb-1 d-flex justify-content-between">
                                        <span>Multa (${item.multa_percentual || 0}%):</span>
                                        <strong>${money(encargosCalculados.multa)}</strong>
                                    </div>
                                    <div class="small text-dark mb-2 d-flex justify-content-between">
                                        <span>Juros (${item.juros_percentual || 0}%/mês):</span>
                                        <strong>${money(encargosCalculados.juros)}</strong>
                                    </div>
                                    <div class="border-top border-warning-subtle pt-2 d-flex justify-content-between align-items-center mb-2">
                                        <span class="small fw-bold">Total atualizado:</span>
                                        <span class="h6 mb-0 text-danger fw-bold">${money(encargosCalculados.valor_total_atualizado)}</span>
                                    </div>
                                    <div class="form-check mt-1">
                                        <input class="form-check-input" type="checkbox" id="checkAplicarEncargos" checked>
                                        <label class="form-check-label small fw-semibold user-select-none" for="checkAplicarEncargos">Aplicar multa e juros contratuais</label>
                                    </div>
                                </div>
                            `;
                        }
                    } catch (e) {
                        console.warn('Não foi possível carregar encargos:', e);
                    }
                }

                const answer = await Swal.fire({
                    title: 'Registrar recebimento',
                    width: 520,
                    showCancelButton: true,
                    confirmButtonText: 'Registrar recebimento',
                    cancelButtonText: 'Voltar',
                    confirmButtonColor: '#c53030',
                    cancelButtonColor: '#6c757d',
                    focusConfirm: false,
                    html: `
                        <div class="text-start">
                            <div class="card bg-body-tertiary border-0 p-3 mb-3">
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Aluno:</span>
                                    <strong class="text-dark small">${esc(item.aluno_nome)}</strong>
                                </div>
                                <div class="d-flex justify-content-between align-items-center mb-1">
                                    <span class="text-muted small">Cobrança:</span>
                                    <span class="small">${esc(item.descricao)}</span>
                                </div>
                                <div class="d-flex justify-content-between align-items-center">
                                    <span class="text-muted small">Vencimento:</span>
                                    <span class="small ${item.status === 'vencida' ? 'text-danger fw-bold' : ''}">${date(item.data_vencimento)}</span>
                                </div>
                            </div>

                            ${encargosHtml}

                            <div class="d-flex justify-content-between align-items-center mb-3 px-1">
                                <span class="text-muted small">Saldo nominal em aberto:</span>
                                <span class="h5 mb-0 fw-bold text-dark">${money(item.saldo)}</span>
                            </div>

                            <div class="mb-3">
                                <label for="recebimentoValor" class="form-label small fw-bold mb-1">Valor recebido (R$) *</label>
                                <div class="input-group">
                                    <span class="input-group-text">R$</span>
                                    <input id="recebimentoValor" class="form-control" type="number" min="0.01" step="0.01" value="${valorSugerido.toFixed(2)}">
                                </div>
                            </div>

                            <div class="mb-3">
                                <label for="recebimentoForma" class="form-label small fw-bold mb-1">Forma de pagamento *</label>
                                <select id="recebimentoForma" class="form-select">
                                    <option value="pix">Pix</option>
                                    <option value="dinheiro">Dinheiro</option>
                                    <option value="cartao">Cartão de Crédito / Débito</option>
                                    <option value="boleto">Boleto Bancário</option>
                                    <option value="transferencia">Transferência</option>
                                    <option value="outro">Outro</option>
                                </select>
                            </div>

                            <div class="mb-1">
                                <label for="recebimentoTransacao" class="form-label small fw-bold mb-1">Identificação bancária / Código (opcional)</label>
                                <input id="recebimentoTransacao" class="form-control" maxlength="120" placeholder="Ex.: E2E PIX, NSU Cartão, etc.">
                            </div>
                        </div>
                    `,
                    didOpen: popup => {
                        const checkEnc = popup.querySelector('#checkAplicarEncargos');
                        const inputVal = popup.querySelector('#recebimentoValor');
                        if (checkEnc && inputVal && encargosCalculados) {
                            checkEnc.addEventListener('change', () => {
                                const novo = checkEnc.checked ? Number(encargosCalculados.valor_total_atualizado) : Number(item.saldo);
                                inputVal.value = novo.toFixed(2);
                            });
                        }
                        inputVal?.focus();
                    },
                    preConfirm: () => {
                        const inputVal = document.querySelector('#recebimentoValor');
                        const rawValue = inputVal ? inputVal.value.replace(',', '.') : '';
                        const aplicarEncargos = document.querySelector('#checkAplicarEncargos')?.checked ?? false;
                        const maxPermitido = aplicarEncargos && encargosCalculados ? Number(encargosCalculados.valor_total_atualizado) : Number(item.saldo);

                        if (!/^\d+(\.\d{1,2})?$/.test(rawValue) || Number(rawValue) <= 0 || Number(rawValue) > maxPermitido + 0.009) {
                            Swal.showValidationMessage(`Informe um valor válido entre R$ 0,01 e ${money(maxPermitido)}.`);
                            return false;
                        }
                        return {
                            valor_pago: Number(rawValue).toFixed(2),
                            forma_pagamento: document.querySelector('#recebimentoForma').value,
                            transacao_id: document.querySelector('#recebimentoTransacao').value.trim() || null,
                            chave_idempotencia: crypto.randomUUID(),
                            aplicar_encargos: aplicarEncargos
                        };
                    }
                });

                if (!answer.isConfirmed) return;
                pending = answer.value;
                sessionStorage.setItem(storageKey, JSON.stringify(pending));
            } else {
                const answer = await Swal.fire({
                    title: 'Conferir recebimento anterior',
                    text: `Existe uma operação de ${money(pending.valor_pago)} aguardando confirmação. Reenviar usará a mesma identificação e não duplicará o pagamento.`,
                    showCancelButton: true,
                    confirmButtonText: 'Conferir / reenviar',
                    cancelButtonText: 'Voltar'
                });
                if (!answer.isConfirmed) return;
            }

            let paymentResult;
            try {
                paymentResult = await post(`${api}/cobrancas/${id}/pagamentos`, pending);
            } catch (error) {
                if (error.status === 422) sessionStorage.removeItem(storageKey);
                throw error;
            }

            sessionStorage.removeItem(storageKey);
            await loadCobrancas();
            await loadMetricas();

            const postAction = await Swal.fire({
                icon: 'success',
                title: 'Pagamento registrado com sucesso!',
                text: 'O saldo foi recalculado e registrado na auditoria.',
                showCancelButton: true,
                confirmButtonText: '<i class="ph ph-printer me-1"></i> Imprimir Recibo',
                cancelButtonText: 'Concluir',
                confirmButtonColor: '#c53030'
            });

            if (postAction.isConfirmed && paymentResult?.id) {
                imprimirRecibo(id, paymentResult.id);
            }
        } catch (error) {
            showError(error);
        } finally {
            button.disabled = false;
        }
    });

    async function motivo(title, text) {
        const result = await Swal.fire({
            title,
            text,
            input: 'textarea',
            inputLabel: 'Justificativa obrigatória',
            inputAttributes: { maxlength: 500 },
            showCancelButton: true,
            confirmButtonText: 'Confirmar',
            cancelButtonText: 'Voltar',
            inputValidator: value => (!value || value.trim().length < 5) ? 'Escreva pelo menos 5 caracteres.' : undefined
        });
        return result.isConfirmed ? result.value.trim() : null;
    }

    if (document.querySelector('#tabelaServicos')) loadServicos();
    if (document.querySelector('#tabelaPlanos')) loadPlanos();
    if (document.querySelector('#financeiroResumo')) loadMetricas();
    if (document.querySelector('#tabelaCobrancas')) loadCobrancas().catch(showError);
})();
