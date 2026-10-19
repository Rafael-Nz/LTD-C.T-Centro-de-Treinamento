(() => {
    const api = '/ctt/api/financeiro';
    const money = value => Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const esc = value => String(value ?? '').replace(/[&<>"']/g, char => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[char]));
    const date = value => value ? value.split('-').reverse().join('/') : '-';

    async function request(url, options = {}) {
        const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(url, { credentials: 'same-origin', ...options, headers: { ...(options.headers || {}), ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {}) } });
        const text = await response.text();
        let payload;
        try {
            payload = JSON.parse(text);
        } catch {
            throw new Error(`Resposta inválida (HTTP ${response.status}).`);
        }
        if (!response.ok || payload.success === false) {
            const error = new Error(payload.message || 'Não foi possível carregar os dados financeiros.');
            error.status = response.status;
            throw error;
        }
        return payload.data;
    }

    function formatCompetencia(comp) {
        if (!comp || !/^\d{4}-\d{2}$/.test(comp)) return comp || 'Mensalidade';
        const [ano, mes] = comp.split('-');
        const nomes = ['', 'Janeiro', 'Fevereiro', 'Março', 'Abril', 'Maio', 'Junho', 'Julho', 'Agosto', 'Setembro', 'Outubro', 'Novembro', 'Dezembro'];
        return `${nomes[parseInt(mes, 10)]} ${ano}`;
    }

    async function loadContrato() {
        try {
            const contratos = await request(`${api}/contratos`);
            const planoEl = document.querySelector('#alunoPlanoNome');
            const valorEl = document.querySelector('#alunoPlanoValor');
            const vencEl = document.querySelector('#alunoPlanoVencimento');
            const statusEl = document.querySelector('#alunoPlanoStatus');

            if (!contratos || !contratos.length) {
                if (planoEl) planoEl.textContent = 'Nenhum contrato ativo';
                if (valorEl) valorEl.textContent = 'R$ 0,00';
                if (vencEl) vencEl.textContent = '—';
                if (statusEl) {
                    statusEl.className = 'status-badge status-badge--yellow';
                    statusEl.textContent = 'Sem plano';
                }
                return;
            }

            const ativo = contratos.find(c => c.status === 'ativo') || contratos[0];
            if (planoEl) planoEl.textContent = ativo.plano_nome ? `${ativo.plano_nome} (${ativo.periodicidade})` : 'Plano Avulso';
            if (valorEl) valorEl.textContent = money(ativo.valor_contratado);
            if (vencEl) vencEl.textContent = `Todo dia ${ativo.dia_vencimento || 10}`;

            if (statusEl) {
                if (ativo.status === 'ativo') {
                    statusEl.className = 'status-badge status-badge--green';
                    statusEl.textContent = 'Ativo';
                } else if (ativo.status === 'pausado') {
                    statusEl.className = 'status-badge status-badge--yellow';
                    statusEl.textContent = 'Pausado';
                } else {
                    statusEl.className = 'status-badge status-badge--red';
                    statusEl.textContent = ativo.status;
                }
            }
        } catch (error) {
            console.error('Falha ao carregar contrato:', error);
        }
    }

    async function loadCobrancas() {
        try {
            const cobrancas = await request(`${api}/cobrancas`);
            const lista = document.querySelector('#historicoPagamentos');
            const cardPagar = document.querySelector('#cardPagarMensalidade');

            if (!lista) return;

            if (!cobrancas || !cobrancas.length) {
                lista.innerHTML = '<p class="text-muted small my-3">Nenhuma cobrança registrada para sua conta.</p>';
                if (cardPagar) {
                    cardPagar.innerHTML = `
                        <div class="text-center py-4">
                            <i class="bi bi-check-circle text-success fs-1"></i>
                            <h4 class="h6 mt-2 mb-1">Nenhuma fatura pendente</h4>
                            <p class="text-muted small mb-0">Você não possui pagamentos em aberto no momento.</p>
                        </div>
                    `;
                }
                return;
            }

            const pendentes = cobrancas.filter(c => (c.status === 'aberta' || c.status === 'vencida') && Number(c.saldo) > 0);
            const primeiraPendente = pendentes[0];

            if (primeiraPendente && cardPagar) {
                const isVencida = primeiraPendente.status === 'vencida';
                cardPagar.innerHTML = `
                    <h3 class="payment-title">Pagar Mensalidade</h3>
                    <p class="payment-ref mb-1"><strong>${esc(primeiraPendente.descricao)}</strong></p>
                    <p class="text-muted small mb-3">Vencimento: ${date(primeiraPendente.data_vencimento)} · Saldo: <strong class="text-dark">${money(primeiraPendente.saldo)}</strong> ${isVencida ? '<span class="badge bg-danger ms-1">Vencida</span>' : ''}</p>
                    <p class="payment-choose">Escolha a forma de pagamento</p>

                    <div class="payment-option">
                        <h4 class="payment-method">PIX</h4>
                        <p class="payment-desc">Chave Pix copia e cola · Identificação imediata</p>
                        <button class="btn-pay btn-pay--pix" id="btnPagarPix">Pagar com PIX</button>
                    </div>

                    <div class="payment-option">
                        <h4 class="payment-method">Cartão ou Balcão</h4>
                        <p class="payment-desc">Pague na recepção do Centro de Treinamento</p>
                        <button class="btn-pay btn-pay--card" id="btnPagarBalcao">Instruções de Pagamento</button>
                    </div>
                `;

                document.querySelector('#btnPagarPix')?.addEventListener('click', () => {
                    const chaveAleatoria = 'pix@crossct.com.br';
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Pagamento via PIX',
                            html: `
                                <div class="text-start">
                                    <p class="mb-2">Utilize a chave Pix abaixo no app do seu banco:</p>
                                    <div class="input-group mb-3">
                                        <input type="text" class="form-control" id="inputChavePix" value="${chaveAleatoria}" readonly>
                                        <button class="btn btn-outline-secondary" type="button" id="btnCopiarPix"><i class="bi bi-clipboard"></i> Copiar</button>
                                    </div>
                                    <div class="card bg-light border-0 p-2 small mb-2">
                                        <div><strong>Beneficiário:</strong> Cross C.T - Centro de Treinamento</div>
                                        <div><strong>Valor da Fatura:</strong> ${money(primeiraPendente.saldo)}</div>
                                        <div><strong>Referência:</strong> ${esc(primeiraPendente.descricao)}</div>
                                    </div>
                                    <small class="text-muted d-block">Após o pagamento, o atendente confirmará a quitação no sistema e você receberá seu comprovante.</small>
                                </div>
                            `,
                            showCloseButton: true,
                            confirmButtonText: 'Entendido',
                            confirmButtonColor: '#c53030',
                            didOpen: popup => {
                                popup.querySelector('#btnCopiarPix')?.addEventListener('click', () => {
                                    navigator.clipboard.writeText(chaveAleatoria);
                                    Swal.showValidationMessage('Chave Pix copiada com sucesso!');
                                    setTimeout(() => Swal.resetValidationMessage(), 2000);
                                });
                            }
                        });
                    } else {
                        alert(`Chave Pix: ${chaveAleatoria}\nValor: ${money(primeiraPendente.saldo)}`);
                    }
                });

                document.querySelector('#btnPagarBalcao')?.addEventListener('click', () => {
                    if (window.Swal) {
                        Swal.fire({
                            title: 'Pagamento na Recepção',
                            text: 'Dirija-se à recepção do Cross C.T para efetuar o pagamento via Cartão de Débito, Crédito ou Dinheiro informando seu nome ou CPF.',
                            icon: 'info',
                            confirmButtonText: 'Ok, entendi',
                            confirmButtonColor: '#c53030'
                        });
                    } else {
                        alert('Efetue o pagamento diretamente na recepção.');
                    }
                });
            } else if (cardPagar) {
                cardPagar.innerHTML = `
                    <div class="text-center py-4">
                        <i class="bi bi-check-circle text-success fs-1"></i>
                        <h4 class="h6 mt-2 mb-1">Parabéns! Mensalidades em dia</h4>
                        <p class="text-muted small mb-0">Você não possui nenhuma fatura em aberto.</p>
                    </div>
                `;
            }

            lista.innerHTML = cobrancas.map(c => {
                let badgeClass = 'status-badge--yellow';
                let statusLabel = 'A vencer';
                if (c.status === 'paga') {
                    badgeClass = 'status-badge--green';
                    statusLabel = 'Pago';
                } else if (c.status === 'vencida') {
                    badgeClass = 'status-badge--red';
                    statusLabel = 'Vencido';
                } else if (c.status === 'cancelada') {
                    badgeClass = 'status-badge';
                    statusLabel = 'Cancelado';
                }

                return `
                    <div class="history-item">
                        <div>
                            <span class="history-month">${esc(formatCompetencia(c.competencia))}</span>
                            <span class="history-info">${date(c.data_vencimento)} · ${money(c.valor_final)}</span>
                        </div>
                        <span class="status-badge ${badgeClass}">${statusLabel}</span>
                    </div>
                `;
            }).join('');

        } catch (error) {
            console.error('Falha ao carregar cobranças:', error);
        }
    }

    loadContrato();
    loadCobrancas();
})();
