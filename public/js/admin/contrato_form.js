(() => {
    const api = '/ctt/api/financeiro';
    let listaServicos = [];
    let mapaPlanos = new Map();

    const request = async (url, options = {}) => {
        const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(url, {
            credentials: 'same-origin',
            ...options,
            headers: {
                ...(options.headers || {}),
                ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {})
            }
        });
        const text = await response.text();
        let payload;
        try {
            payload = JSON.parse(text);
        } catch {
            throw new Error(`A API retornou uma resposta inválida (HTTP ${response.status}).`);
        }
        if (!response.ok || payload.success === false) {
            throw new Error(payload.message || 'Não foi possível concluir a operação.');
        }
        return payload.data;
    };

    const money = value => Number(value || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const showError = error => window.Swal ? Swal.fire({ icon: 'error', title: 'Atenção', text: error.message }) : window.alert(error.message);

    const atualizarTotaisContrato = () => {
        let totalServicos = 0;
        const linhas = document.querySelectorAll('#itensContratoContainer tr');

        linhas.forEach(linha => {
            const qtd = Number(linha.querySelector('.item-quantidade')?.value || 0);
            const valorUnit = Number(linha.querySelector('.item-valor-unitario')?.value || 0);
            const desconto = Number(linha.querySelector('.item-desconto')?.value || 0);
            const subtotal = Math.max(0, (qtd * valorUnit) - desconto);
            totalServicos += subtotal;

            const subtotalEl = linha.querySelector('.item-subtotal');
            if (subtotalEl) subtotalEl.textContent = money(subtotal);
        });

        const somaEl = document.getElementById('somaItensContrato');
        if (somaEl) somaEl.textContent = money(totalServicos);

        const msgEl = document.getElementById('mensagemItensContrato');
        const btnSoma = document.getElementById('btnUsarSomaContrato');
        const valorSomaSugerido = document.getElementById('valorSomaContratoSugerido');

        if (linhas.length === 0) {
            if (msgEl) msgEl.textContent = 'Nenhum serviço adicionado.';
            if (btnSoma) btnSoma.style.display = 'none';
        } else {
            if (msgEl) msgEl.textContent = `${linhas.length} serviço(s) na composição.`;
            if (btnSoma) {
                btnSoma.style.display = 'inline-block';
                if (valorSomaSugerido) valorSomaSugerido.textContent = money(totalServicos);
            }
        }
    };

    const criarLinhaItemContrato = (item = {}) => {
        const servicoId = item.servico_id ? Number(item.servico_id) : '';
        const quantidade = item.quantidade || 1;
        const valorUnitario = item.valor_unitario !== undefined ? Number(item.valor_unitario).toFixed(2) : '';
        const desconto = item.valor_desconto !== undefined ? Number(item.valor_desconto).toFixed(2) : '0.00';

        const tr = document.createElement('tr');
        const options = listaServicos.map(s => {
            const selected = Number(s.id) === servicoId ? 'selected' : '';
            return `<option value="${s.id}" data-nome="${encodeURIComponent(s.nome)}" data-valor="${s.valor_base}" ${selected}>${s.nome} (${money(s.valor_base)})</option>`;
        }).join('');

        tr.innerHTML = `
            <td>
                <select class="form-select form-select-sm item-servico-id" required>
                    <option value="">Selecione um serviço...</option>
                    ${options}
                </select>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-center item-quantidade" min="1" max="9999" value="${quantidade}" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-end item-valor-unitario" min="0" step="0.01" value="${valorUnitario}" placeholder="0,00" required>
            </td>
            <td>
                <input type="number" class="form-control form-control-sm text-end item-desconto" min="0" step="0.01" value="${desconto}" placeholder="0,00">
            </td>
            <td class="text-end fw-semibold item-subtotal">R$ 0,00</td>
            <td class="text-center">
                <button type="button" class="btn btn-sm btn-outline-danger btn-remover-item" title="Remover item">
                    <i class="ph ph-trash"></i>
                </button>
            </td>
        `;

        const selectServico = tr.querySelector('.item-servico-id');
        const inputQtd = tr.querySelector('.item-quantidade');
        const inputValor = tr.querySelector('.item-valor-unitario');
        const inputDesc = tr.querySelector('.item-desconto');
        const btnRemover = tr.querySelector('.btn-remover-item');

        const onSelectChange = () => {
            const opt = selectServico.selectedOptions[0];
            if (opt && opt.value) {
                inputValor.value = Number(opt.dataset.valor || 0).toFixed(2);
            }
            atualizarTotaisContrato();
        };

        selectServico.addEventListener('change', onSelectChange);

        if (window.jQuery?.fn.select2) {
            const $s = window.jQuery(selectServico);
            $s.select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione um serviço...'
            }).on('change', onSelectChange);
        }

        inputQtd.addEventListener('input', atualizarTotaisContrato);
        inputValor.addEventListener('input', atualizarTotaisContrato);
        inputDesc.addEventListener('input', atualizarTotaisContrato);

        btnRemover.addEventListener('click', () => {
            if (window.jQuery?.fn.select2) {
                const $s = window.jQuery(selectServico);
                if ($s.data('select2')) $s.select2('destroy');
            }
            tr.remove();
            atualizarTotaisContrato();
        });

        return tr;
    };

    async function loadOptions() {
        const [alunosResponse, planos, servicos] = await Promise.all([
            request('/ctt/api/alunos?draw=1&start=0&length=1000'),
            request(`${api}/planos?ativos=true`),
            request(`${api}/servicos?ativos=true`)
        ]);

        const alunos = (Array.isArray(alunosResponse) ? alunosResponse : (alunosResponse?.data || []))
            .filter(item => item && (item.ativo === undefined || Number(item.ativo) === 1));
        listaServicos = Array.isArray(servicos) ? servicos : [];

        mapaPlanos.clear();
        (planos || []).forEach(p => mapaPlanos.set(Number(p.id), p));

        const alunoSelect = document.querySelector('#alunoId');
        alunoSelect.innerHTML = '<option value="">Selecione um aluno...</option>' +
            alunos.map(item => {
                const matricula = item.codigo_matricula ? ` (${item.codigo_matricula})` : '';
                return `<option value="${item.id}">${item.nome} ${item.sobrenome}${matricula}</option>`;
            }).join('');

        const planoSelect = document.querySelector('#planoId');
        planoSelect.innerHTML = '<option value="">Selecione um plano comercial...</option>' +
            (planos || []).map(item => `<option value="${item.id}">${item.nome} (${item.periodicidade}) - ${money(item.valor)}</option>`).join('');

        if (window.jQuery?.fn.select2) {
            $('#alunoId').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione ou busque um aluno...'
            });
            $('#planoId').select2({
                theme: 'bootstrap-5',
                width: '100%',
                placeholder: 'Selecione um plano comercial...'
            });
            $('#periodicidade').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        }
    }

    const aplicarTipoContratacao = (tipo) => {
        const boxPlano = document.getElementById('boxSelecaoPlano');
        const container = document.getElementById('itensContratoContainer');

        if (tipo === 'plano') {
            boxPlano.style.display = 'block';
            document.getElementById('planoId').required = true;
        } else {
            boxPlano.style.display = 'none';
            document.getElementById('planoId').required = false;
            if (window.jQuery?.fn.select2) {
                $('#planoId').val('').trigger('change');
            } else {
                document.getElementById('planoId').value = '';
            }

            if (window.jQuery?.fn.select2) {
                $('#periodicidade').val('avulso').trigger('change');
            } else {
                document.getElementById('periodicidade').value = 'avulso';
            }

            if (container && container.querySelectorAll('tr').length === 0) {
                container.appendChild(criarLinhaItemContrato());
                atualizarTotaisContrato();
            }
        }
    };

    $('input[name="tipoContratacao"]').on('change', function () {
        aplicarTipoContratacao(this.value);
    });

    const onPlanoChange = async (planoId) => {
        const container = document.querySelector('#itensContratoContainer');

        if (!planoId) {
            return;
        }

        try {
            const planoCompleto = await request(`${api}/planos/${planoId}`);
            if (planoCompleto) {
                document.querySelector('#valorContratado').value = Number(planoCompleto.valor || 0).toFixed(2);
                if (window.jQuery?.fn.select2) {
                    $('#periodicidade').val(planoCompleto.periodicidade || 'mensal').trigger('change');
                } else {
                    document.querySelector('#periodicidade').value = planoCompleto.periodicidade || 'mensal';
                }

                if (window.jQuery?.fn.select2) {
                    $(container).find('select.select2-hidden-accessible').select2('destroy');
                }
                container.innerHTML = '';
                if (Array.isArray(planoCompleto.itens) && planoCompleto.itens.length > 0) {
                    planoCompleto.itens.forEach(item => {
                        container.appendChild(criarLinhaItemContrato({
                            servico_id: item.servico_id,
                            quantidade: item.quantidade,
                            valor_unitario: item.valor_unitario,
                            valor_desconto: 0
                        }));
                    });
                }
                atualizarTotaisContrato();
            }
        } catch (error) {
            console.error('Erro ao buscar detalhes do plano:', error);
        }
    };

    $('#planoId').on('change', event => {
        const planoId = event.target.value ? Number(event.target.value) : null;
        if (planoId) {
            onPlanoChange(planoId);
        }
    });

    document.querySelector('#btnAdicionarServicoContrato').addEventListener('click', () => {
        const container = document.querySelector('#itensContratoContainer');
        const novaLinha = criarLinhaItemContrato();
        container.appendChild(novaLinha);
        atualizarTotaisContrato();
    });

    document.querySelector('#btnUsarSomaContrato').addEventListener('click', () => {
        let soma = 0;
        document.querySelectorAll('#itensContratoContainer tr').forEach(linha => {
            const qtd = Number(linha.querySelector('.item-quantidade')?.value || 0);
            const valorUnit = Number(linha.querySelector('.item-valor-unitario')?.value || 0);
            const desconto = Number(linha.querySelector('.item-desconto')?.value || 0);
            soma += Math.max(0, (qtd * valorUnit) - desconto);
        });
        document.querySelector('#valorContratado').value = soma.toFixed(2);
    });

    document.querySelector('#formContrato').addEventListener('submit', async event => {
        event.preventDefault();
        const btnSalvar = document.querySelector('#btnSalvarContrato');

        const alunoId = Number(document.querySelector('#alunoId').value);
        if (!alunoId) {
            showError(new Error('Selecione um aluno para o contrato.'));
            return;
        }

        const tipoContratacao = document.querySelector('input[name="tipoContratacao"]:checked')?.value || 'plano';
        const planoIdVal = document.querySelector('#planoId').value;
        const planoId = (tipoContratacao === 'plano' && planoIdVal) ? Number(planoIdVal) : null;

        if (tipoContratacao === 'plano' && !planoId) {
            showError(new Error('Por favor, selecione um plano comercial ou mude para "Serviços Avulsos".'));
            return;
        }

        const valorContratado = Number(document.querySelector('#valorContratado').value);
        const desconto = Number(document.querySelector('#desconto').value || 0);

        if (isNaN(valorContratado) || valorContratado < 0) {
            showError(new Error('Informe um valor contratado válido.'));
            return;
        }
        if (desconto > valorContratado) {
            showError(new Error('O desconto não pode ser maior que o valor contratado.'));
            return;
        }

        const itens = [];
        let erroItem = null;

        document.querySelectorAll('#itensContratoContainer tr').forEach(row => {
            if (erroItem) return;
            const selectEl = row.querySelector('.item-servico-id');
            const servicoId = selectEl.value ? Number(selectEl.value) : null;
            const opt = selectEl.selectedOptions[0];
            const descricao = opt && opt.value ? decodeURIComponent(opt.dataset.nome || '') : 'Serviço contratado';
            const quantidade = Number(row.querySelector('.item-quantidade').value);
            const valorUnitario = Number(row.querySelector('.item-valor-unitario').value);
            const valorDesconto = Number(row.querySelector('.item-desconto').value || 0);

            if (!servicoId) {
                erroItem = 'Selecione o serviço em todas as linhas da tabela ou remova as linhas vazias.';
                return;
            }
            if (isNaN(quantidade) || quantidade < 1) {
                erroItem = 'A quantidade de cada serviço deve ser no mínimo 1.';
                return;
            }
            if (isNaN(valorUnitario) || valorUnitario < 0) {
                erroItem = 'O valor unitário não pode ser negativo.';
                return;
            }
            if (valorDesconto > (quantidade * valorUnitario)) {
                erroItem = `O desconto do serviço "${descricao}" não pode exceder o seu valor total.`;
                return;
            }

            itens.push({
                servico_id: servicoId,
                descricao: descricao,
                quantidade: quantidade,
                valor_unitario: valorUnitario,
                valor_desconto: valorDesconto
            });
        });

        if (erroItem) {
            showError(new Error(erroItem));
            return;
        }

        if (tipoContratacao === 'avulso' && itens.length === 0) {
            showError(new Error('Adicione ao menos um serviço para a contratação de serviços avulsos.'));
            return;
        }

        const payload = {
            aluno_id: alunoId,
            plano_id: planoId,
            data_inicio: document.querySelector('#dataInicio').value,
            data_fim: document.querySelector('#dataFim').value || null,
            periodicidade: document.querySelector('#periodicidade').value,
            dia_vencimento: Number(document.querySelector('#diaVencimento').value),
            valor_contratado: valorContratado,
            desconto: desconto,
            multa_percentual: Number(document.querySelector('#multa').value || 0),
            juros_percentual: Number(document.querySelector('#juros').value || 0),
            observacoes: document.querySelector('#observacoes').value.trim() || null,
            itens: itens
        };

        btnSalvar.disabled = true;
        try {
            await request(`${api}/contratos`, {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            if (window.Swal) {
                await Swal.fire({
                    icon: 'success',
                    title: 'Contrato Criado!',
                    text: 'O contrato financeiro foi registrado com sucesso.',
                    timer: 1600,
                    showConfirmButton: false
                });
            }
            window.location.href = '/ctt/admin/financeiro';
        } catch (error) {
            showError(error);
            btnSalvar.disabled = false;
        }
    });

    const initTooltips = () => {
        if (window.bootstrap?.Tooltip) {
            document.querySelectorAll('[data-bs-toggle="tooltip"]').forEach(el => new bootstrap.Tooltip(el));
        }
    };

    loadOptions().then(initTooltips).catch(showError);
    initTooltips();
    document.querySelector('#dataInicio').value = new Date().toISOString().slice(0, 10);
})();