$(function () {
    const esc = value => $('<span>').text(value ?? '').html();
    const tabela = inicializarTabela({
        tableId: 'tabelaCobrancas', ajaxUrl: '/ctt/api/financeiro/cobrancas',
        emptyMessage: 'Nenhuma cobrança encontrada.',
        searchInput: '#campoBuscaCobrancas', searchButton: '#botaoBuscarCobrancas',
        getFilters: () => ({ status: $('.filtro-status-cobranca:checked').map((_, el) => el.value).get().join(',') }),
        columns: [
            { data: 'aluno_nome', render: esc },
            { data: 'descricao', render: esc },
            { data: 'competencia', render: value => esc(value || '—') },
            { data: 'data_vencimento', render: value => value ? esc(value.split('-').reverse().join('/')) : '—' },
            { data: 'status', className: 'text-center', render: value => {
                const labels = { aberta: 'Aberta', paga: 'Paga', vencida: 'Vencida', cancelada: 'Cancelada' };
                const color = { aberta: 'warning', paga: 'success', vencida: 'danger', cancelada: 'secondary' }[value] || 'secondary';
                return `<span class="badge bg-${color}-subtle text-${color}-emphasis">${esc(labels[value] || value)}</span>`;
            } },
            { data: null, className: 'text-end', render: row => `${formatarMoeda(row.valor_final)}<small class="d-block text-muted">Recebido: ${formatarMoeda(row.total_pago)}</small><strong class="d-block">Saldo: ${formatarMoeda(row.saldo)}</strong>` },
            { data: null, className: 'text-center', render: row => {
                const id = Number(row.id);
                let buttons = `<button type="button" class="btn btn-sm btn-secondary btn-detalhes" data-id="${id}" title="Histórico" aria-label="Histórico"><i class="ph ph-eye" aria-hidden="true"></i></button>`;
                if (row.status !== 'cancelada') {
                    if (Number(row.saldo) > 0 || sessionStorage.getItem(`ctt-pagamento-${id}`)) buttons += ` <button type="button" class="btn btn-sm btn-success btn-pagamento" data-id="${id}" title="Receber" aria-label="Receber"><i class="ph ph-arrow-down" aria-hidden="true"></i></button>`;
                    buttons += ` <button type="button" class="btn btn-sm btn-danger btn-cancelar" data-id="${id}" title="Cancelar" aria-label="Cancelar"><i class="ph ph-x" aria-hidden="true"></i></button>`;
                }
                return `<div class="d-flex gap-1 justify-content-center flex-wrap">${buttons}</div>`;
            } }
        ]
    });
    const reload = () => { tabela.ajax.reload(); bootstrap.Dropdown.getInstance(document.getElementById('filtrosCobrancasBtn'))?.hide(); };
    $('#aplicarFiltrosCobrancas').on('click', reload);
    $('#limparFiltrosCobrancas').on('click', () => {
        $('.filtro-status-cobranca').prop('checked', false);
        $('#campoBuscaCobrancas').val('');
        tabela.search('');
        reload();
    });
});
