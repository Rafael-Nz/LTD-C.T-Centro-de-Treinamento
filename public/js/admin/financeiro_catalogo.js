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
        if (id) {
            document.getElementById('salvarCatalogo').disabled = true;
            request(`${endpoint}/${id}`).then(item => {
                document.getElementById('catalogoNome').value = item.nome;
                document.getElementById('catalogoDescricao').value = item.descricao || '';
                document.getElementById('catalogoValor').value = item[servico ? 'valor_base' : 'valor'];
                $('#catalogoCategoria').val(item[servico ? 'tipo' : 'periodicidade']).trigger('change');
                if (servico) $('#catalogoRecorrente').val(Number(item.recorrente) ? '1' : '0').trigger('change');
                loaded = true;
                document.getElementById('salvarCatalogo').disabled = false;
            }).catch(error => Swal.fire('Erro', error.message, 'error'));
        }
        if (window.jQuery?.fn.select2) {
            window.jQuery(form).find('select').select2({
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
        const tabela = inicializarTabela({
            tableId: 'tabelaCatalogo', ajaxUrl: endpoint,
            emptyMessage: servico ? 'Nenhum serviço encontrado.' : 'Nenhum plano encontrado.',
            searchInput: '#campoBuscaCatalogo', searchButton: '#botaoBuscarCatalogo',
            getFilters: () => ({ categoria: $('#filtroCategoria').val(), ativo: $('#filtroAtivo').val() }),
            columns: [
                { data: 'nome', render: escape },
                { data: servico ? 'tipo' : 'periodicidade', render: value => escape(labels[value] || value) },
                { data: servico ? 'valor_base' : 'valor', className: 'text-end', render: formatarMoeda },
                { data: 'ativo', className: 'text-center', render: formatarStatus },
                { data: null, className: 'text-center', render: row => {
                    const id = Number(row.id), ativo = Number(row.ativo) === 1;
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
            $('#filtroCategoria, #filtroAtivo, #campoBuscaCatalogo').val('');
            tabela.search('').draw();
            closeFilters();
        });
    });
})();
