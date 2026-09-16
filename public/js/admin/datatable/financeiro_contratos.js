$(function () {
    const esc = value => $('<span>').text(value ?? '').html();
    const $plano = $('#filtroPlanoContrato');
    $plano.select2({ theme: 'bootstrap-5', placeholder: 'Todos os planos', allowClear: true, width: '100%', dropdownParent: $plano.closest('.dropdown-menu') });
    $.getJSON('/ctt/api/financeiro/planos?ativos=false').done(response => {
        extrairListaApi(response).forEach(plano => $plano.append(new Option(plano.nome, plano.id)));
    }).fail(() => Swal.fire('Erro', 'Não foi possível carregar os planos do filtro.', 'error'));

    const tabela = inicializarTabela({
        tableId: 'tabelaContratos', ajaxUrl: '/ctt/api/financeiro/contratos',
        emptyMessage: 'Nenhum contrato encontrado.', searchInput: '#campoBuscaContratos', searchButton: '#botaoBuscarContratos',
        getFilters: () => ({
            plano_id: $plano.val() || '',
            status: $('.filtro-status-contrato:checked').map((_, el) => el.value).get().join(','),
        }),
        columns: [
            { data: 'aluno_nome', render: data => esc(data) },
            { data: null, render: row => `${esc(row.plano_nome || 'Serviços avulsos')}<small class="d-block text-muted">${esc(formatarPeriodicidade(row.periodicidade || 'avulso'))}</small>` },
            { data: 'data_inicio', render: value => value ? value.split('-').reverse().join('/') : '—' },
            { data: 'dia_vencimento', render: value => `Dia ${Number(value)}` },
            { data: 'status', className: 'text-center', render: value => {
                const labels = { ativo: 'Ativo', pausado: 'Pausado', encerrado: 'Encerrado', cancelado: 'Cancelado' };
                const color = { ativo: 'success', pausado: 'warning', encerrado: 'secondary', cancelado: 'danger' }[value] || 'secondary';
                return `<span class="badge bg-${color}-subtle text-${color}-emphasis">${esc(labels[value] || value)}</span>`;
            } },
            { data: 'valor_contratado', className: 'text-end', render: formatarMoeda },
            { data: null, className: 'text-center', render: row => ['ativo', 'pausado'].includes(row.status) ? `<button type="button" class="btn btn-sm btn-primary btn-status-contrato" data-id="${Number(row.id)}" data-status="${esc(row.status)}" title="Alterar status do contrato" aria-label="Alterar status do contrato"><i class="ph ph-arrows-clockwise" aria-hidden="true"></i></button>` : '<span class="text-muted" title="Contrato finalizado">—</span>' },
        ]
    });
    const reload = () => { tabela.ajax.reload(); bootstrap.Dropdown.getInstance(document.querySelector('#filtrosContratosBtn'))?.hide(); };
    $('#aplicarFiltrosContratos').on('click', reload);
    $('#limparFiltrosContratos').on('click', () => {
        $plano.val(null).trigger('change');
        $('.filtro-status-contrato').prop('checked', false);
        $('#campoBuscaContratos').val('');
        tabela.search('');
        reload();
    });
});
