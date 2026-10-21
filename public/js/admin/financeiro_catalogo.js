(() => {
    const catalogo = document.getElementById('mainContent')?.dataset.catalogo;
    if (!['servicos', 'planos'].includes(catalogo)) return;
    const servico = catalogo === 'servicos';
    const endpoint = `/ctt/api/financeiro/${catalogo}`;
    const form = document.getElementById('formCatalogo');
    async function request(url, method = 'GET', body) {
        const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
        const response = await fetch(url, { method, credentials: 'same-origin', headers: { 'Content-Type': 'application/json', ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {}) }, ...(body ? { body: JSON.stringify(body) } : {}) });
        const result = await response.json();
        if (!response.ok || result.success === false) throw new Error(result.message || 'Não foi possível concluir a operação.');
        return result.data;
    }
    if (form) {
        const id = Number(form.dataset.id);
        let loaded = !id;
        let listaServicos = [];

        const formatarMoedaLocal = valor => Number(valor || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });

        const atualizarTotaisItens = () => {
            if (servico) return;
            let soma = 0;
            const linhas = document.querySelectorAll('#itensPlanoContainer tr');
            linhas.forEach(linha => {
                const qtd = Number(linha.querySelector('.item-quantidade')?.value || 0);
                const valor = Number(linha.querySelector('.item-valor-unitario')?.value || 0);
                const subtotal = qtd * valor;
                soma += subtotal;
                const subtotalEl = linha.querySelector('.item-subtotal');
                if (subtotalEl) subtotalEl.textContent = formatarMoedaLocal(subtotal);
            });

            const somaEl = document.getElementById('somaServicosPlano');
            if (somaEl) somaEl.textContent = formatarMoedaLocal(soma);

            const msgEl = document.getElementById('mensagemItensPlano');
            const btnSoma = document.getElementById('btnUsarSomaItens');
            const valorSomaSugerido = document.getElementById('valorSomaSugerido');

            if (linhas.length === 0) {
                if (msgEl) msgEl.textContent = 'Nenhum serviço adicionado ainda. É recomendado incluir ao menos um serviço.';
                if (btnSoma) btnSoma.style.display = 'none';
            } else {
                if (msgEl) msgEl.textContent = `${linhas.length} serviço(s) na composição.`;
                if (btnSoma) {
                    btnSoma.style.display = 'inline-block';
                    if (valorSomaSugerido) valorSomaSugerido.textContent = formatarMoedaLocal(soma);
                }
            }
        };

        const criarLinhaItemPlano = (servicoId = '', quantidade = 1, valorUnitario = '') => {
            const tr = document.createElement('tr');
            const options = listaServicos.map(s => {
                const selected = Number(s.id) === Number(servicoId) ? 'selected' : '';
                return `<option value="${s.id}" data-valor="${s.valor_base}" ${selected}>${s.nome} (${formatarMoedaLocal(s.valor_base)})</option>`;
            }).join('');

            tr.innerHTML = `
                <td>
                    <select class="form-select form-select-sm item-servico-id" required>
                        <option value="">Selecione um serviço...</option>
                        ${options}
                    </select>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-quantidade" min="1" max="9999" value="${quantidade}" required>
                </td>
                <td>
                    <input type="number" class="form-control form-control-sm item-valor-unitario" min="0" step="0.01" value="${valorUnitario !== '' ? valorUnitario : ''}" placeholder="0,00" required>
                </td>
                <td class="text-end fw-semibold item-subtotal">${formatarMoedaLocal(Number(quantidade) * Number(valorUnitario || 0))}</td>
                <td class="text-center">
                    <button type="button" class="btn btn-sm btn-outline-danger btn-remover-item" title="Remover"><i class="ph ph-trash"></i></button>
                </td>
            `;

            const selectServico = tr.querySelector('.item-servico-id');
            const inputQtd = tr.querySelector('.item-quantidade');
            const inputValor = tr.querySelector('.item-valor-unitario');
            const btnRemover = tr.querySelector('.btn-remover-item');

            const onSelectChange = () => {
                const selectedOpt = selectServico.selectedOptions[0];
                if (selectedOpt && selectedOpt.dataset.valor !== undefined) {
                    inputValor.value = Number(selectedOpt.dataset.valor).toFixed(2);
                }
                atualizarTotaisItens();
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

            inputQtd.addEventListener('input', atualizarTotaisItens);
            inputValor.addEventListener('input', atualizarTotaisItens);

            btnRemover.addEventListener('click', () => {
                if (window.jQuery?.fn.select2) {
                    const $s = window.jQuery(selectServico);
                    if ($s.data('select2')) $s.select2('destroy');
                }
                tr.remove();
                atualizarTotaisItens();
            });

            return tr;
        };

        const carregarDadosForm = async () => {
            if (!servico) {
                try {
                    const servicos = await request('/ctt/api/financeiro/servicos?ativos=true');
                    listaServicos = Array.isArray(servicos) ? servicos : [];
                } catch (e) {
                    console.error('Erro ao carregar lista de serviços:', e);
                }
            }

            if (id) {
                document.getElementById('salvarCatalogo').disabled = true;
                try {
                    const item = await request(`${endpoint}/${id}`);
                    document.getElementById('catalogoNome').value = item.nome;
                    document.getElementById('catalogoDescricao').value = item.descricao || '';
                    document.getElementById('catalogoValor').value = item[servico ? 'valor_base' : 'valor'];
                    $('#catalogoCategoria').val(item[servico ? 'tipo' : 'periodicidade']).trigger('change');
                    if (servico) $('#catalogoRecorrente').val(Number(item.recorrente) ? '1' : '0').trigger('change');

                    if (!servico && Array.isArray(item.itens)) {
                        const container = document.getElementById('itensPlanoContainer');
                        if (container) {
                            container.innerHTML = '';
                            item.itens.forEach(it => {
                                container.appendChild(criarLinhaItemPlano(it.servico_id, it.quantidade, it.valor_unitario));
                            });
                            atualizarTotaisItens();
                        }
                    }

                    loaded = true;
                    document.getElementById('salvarCatalogo').disabled = false;
                } catch (error) {
                    Swal.fire('Erro', error.message, 'error');
                }
            } else {
                loaded = true;
            }
        };

        carregarDadosForm();

        const btnAdicionarServico = document.getElementById('btnAdicionarServicoPlano');
        if (btnAdicionarServico) {
            btnAdicionarServico.addEventListener('click', () => {
                const container = document.getElementById('itensPlanoContainer');
                if (container) {
                    container.appendChild(criarLinhaItemPlano());
                    atualizarTotaisItens();
                }
            });
        }

        const btnUsarSoma = document.getElementById('btnUsarSomaItens');
        if (btnUsarSoma) {
            btnUsarSoma.addEventListener('click', () => {
                let soma = 0;
                document.querySelectorAll('#itensPlanoContainer tr').forEach(linha => {
                    const qtd = Number(linha.querySelector('.item-quantidade')?.value || 0);
                    const valor = Number(linha.querySelector('.item-valor-unitario')?.value || 0);
                    soma += qtd * valor;
                });
                document.getElementById('catalogoValor').value = soma.toFixed(2);
            });
        }

        if (window.jQuery?.fn.select2) {
            window.jQuery(form).find('select:not(.item-servico-id)').select2({
                theme: 'bootstrap-5',
                width: '100%',
                minimumResultsForSearch: Infinity
            });
        }

        form.addEventListener('submit', async event => {
            event.preventDefault();
            const button = document.getElementById('salvarCatalogo');
            if (!loaded || !form.reportValidity() || button.disabled) return;

            const payload = {
                nome: document.getElementById('catalogoNome').value.trim(),
                descricao: document.getElementById('catalogoDescricao').value.trim() || null,
                ativo: true,
                [servico ? 'valor_base' : 'valor']: document.getElementById('catalogoValor').value,
                [servico ? 'tipo' : 'periodicidade']: document.getElementById('catalogoCategoria').value
            };
            if (servico) payload.recorrente = document.getElementById('catalogoRecorrente').value === '1';

            if (!servico) {
                const itens = [];
                const servicosVistos = new Set();
                let erro = null;

                document.querySelectorAll('#itensPlanoContainer tr').forEach(row => {
                    if (erro) return;
                    const servicoId = Number(row.querySelector('.item-servico-id')?.value);
                    const quantidade = Number(row.querySelector('.item-quantidade')?.value);
                    const valorUnitario = Number(row.querySelector('.item-valor-unitario')?.value);

                    if (!servicoId) {
                        erro = 'Por favor, selecione o serviço em todas as linhas ou remova as linhas em branco.';
                        return;
                    }
                    if (servicosVistos.has(servicoId)) {
                        erro = 'O mesmo serviço foi adicionado mais de uma vez. Ajuste a quantidade na linha correspondente.';
                        return;
                    }
                    servicosVistos.add(servicoId);

                    if (isNaN(quantidade) || quantidade < 1) {
                        erro = 'A quantidade de cada serviço deve ser no mínimo 1.';
                        return;
                    }
                    if (isNaN(valorUnitario) || valorUnitario < 0) {
                        erro = 'O valor unitário de cada serviço não pode ser negativo.';
                        return;
                    }

                    itens.push({
                        servico_id: servicoId,
                        quantidade: quantidade,
                        valor_unitario: valorUnitario
                    });
                });

                if (erro) {
                    Swal.fire('Composição do Plano', erro, 'warning');
                    return;
                }

                payload.itens = itens;
            }

            button.disabled = true;
            try {
                const csrf = document.cookie.split('; ').find(item => item.startsWith('CTT_CSRF_TOKEN='))?.split('=').slice(1).join('=');
                const response = await fetch(id ? `${endpoint}/${id}` : endpoint, {
                    method: id ? 'PUT' : 'POST', credentials: 'same-origin',
                    headers: { 'Content-Type': 'application/json', ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {}) },
                    body: JSON.stringify(payload)
                });
                const result = await response.json();
                if (!response.ok || result.success === false) throw new Error(result.message || 'Não foi possível salvar o cadastro.');
                window.location.href = `/ctt/admin/financeiro/${catalogo}`;
            } catch (error) {
                Swal.fire('Erro', error.message, 'error');
                button.disabled = false;
            }
        });
        return;
    }
    $(function () {
        const labels = { mensalidade: 'Mensalidade', avaliacao: 'Avaliação', personal: 'Personal', taxa: 'Taxa', outro: 'Outro', mensal: 'Mensal', trimestral: 'Trimestral', semestral: 'Semestral', anual: 'Anual', avulso: 'Avulso' };
        const escape = value => $('<span>').text(value ?? '').html();
        const $categoria = $('#filtroCategoria');
        if ($categoria.length) {
            $categoria.select2({
                theme: 'bootstrap-5',
                placeholder: servico ? 'Todos os tipos' : 'Todas as periodicidades',
                allowClear: true,
                width: '100%',
                dropdownParent: $categoria.closest('.dropdown-menu')
            });
        }

        const tabela = inicializarTabela({
            tableId: 'tabelaCatalogo', ajaxUrl: endpoint,
            emptyMessage: servico ? 'Nenhum serviço encontrado.' : 'Nenhum plano encontrado.',
            searchInput: '#campoBuscaCatalogo', searchButton: '#botaoBuscarCatalogo',
            getFilters: () => {
                const checked = $('.filtro-status-catalogo:checked').map((_, el) => el.value).get();
                return {
                    categoria: $categoria.val() || '',
                    ativo: checked.length === 1 ? checked[0] : ''
                };
            },
            columns: [
                { data: 'nome', render: escape },
                { data: servico ? 'tipo' : 'periodicidade', render: value => escape(labels[value] || value) },
                { data: servico ? 'valor_base' : 'valor', className: 'text-end', render: formatarMoeda },
                { data: 'ativo', className: 'text-center', render: formatarStatus },
                { data: null, className: 'text-center', render: row => {
                    const id = Number(row.id), ativo = Number(row.ativo) === 1;
                    const canManage = typeof window.can === 'function' ? window.can('financeiro.gerenciar') : true;
                    if (!canManage) {
                        return '<span class="text-muted">—</span>';
                    }
                    const nome = servico ? 'serviço' : 'plano';
                    const acao = `${ativo ? 'Desativar' : 'Ativar'} ${nome}`;
                    return `<div class="d-flex gap-1 justify-content-center flex-wrap"><a class="btn btn-sm btn-primary" href="/ctt/admin/financeiro/${catalogo}/editar/${id}" title="Editar ${nome}" aria-label="Editar ${nome}"><i class="ph ph-pencil" aria-hidden="true"></i></a><button type="button" class="btn btn-sm btn-${ativo ? 'danger' : 'success'} btn-status-catalogo" data-id="${id}" data-ativo="${ativo ? 1 : 0}" title="${acao}" aria-label="${acao}"><i class="ph ph-${ativo ? 'x' : 'check'}" aria-hidden="true"></i></button></div>`;
                } }
            ]
        });
        $(document).on('click', '.btn-status-catalogo', async function () {
            const button = this;
            button.disabled = true;
            try {
                const ativo = button.dataset.ativo !== '1';
                const answer = await Swal.fire({ title: `${ativo ? 'Ativar' : 'Desativar'} ${servico ? 'serviço' : 'plano'}?`, text: 'Contratos, cobranças e pagamentos existentes serão preservados.', icon: 'question', showCancelButton: true, confirmButtonText: 'Confirmar', cancelButtonText: 'Voltar' });
                if (answer.isConfirmed) { await request(`${endpoint}/${button.dataset.id}/status`, 'PUT', { ativo }); tabela.ajax.reload(null, false); }
            } catch (error) { Swal.fire('Erro', error.message, 'error'); }
            finally { button.disabled = false; }
        });
        const closeFilters = () => bootstrap.Dropdown.getOrCreateInstance(document.getElementById('filtrosCatalogoBtn')).hide();
        $('#aplicarFiltrosCatalogo').on('click', () => { tabela.ajax.reload(); closeFilters(); });
        $('#limparFiltrosCatalogo').on('click', () => {
            $categoria.val(null).trigger('change');
            $('.filtro-status-catalogo').prop('checked', false);
            $('#campoBuscaCatalogo').val('');
            tabela.search('').draw();
            closeFilters();
        });
    });
})();
