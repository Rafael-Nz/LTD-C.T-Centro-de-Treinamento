$(document).ready(function () {
    const configLocais = {
        tableId: 'tabelaLocais',
        ajaxUrl: '/ctt/api/locais',
        emptyMessage: 'Nenhum local encontrado.',
        searchInput: '#campoBusca',
        searchButton: '#botaoBuscar',
        getFilters: function () {
            const statusSelecionados = [];

            $('.filtro-status:checked').each(function () {
                statusSelecionados.push($(this).val());
            });

            return {
                status: statusSelecionados.length ? statusSelecionados.join(',') : ''
            };
        },
        columns: [
            {
                data: 'nome',
                render: data => data ? escapeHtml(data) : '—'
            },
            {
                data: null,
                className: 'text-center',
                render: data => `${data.capacidade_minima ?? 0}/${data.capacidade_maxima ?? 0}`
            },
            {
                data: 'ativo',
                className: 'text-center',
                render: data => typeof formatarStatus === 'function' ? formatarStatus(data) : data
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data) {
                    const canManage = typeof window.can === 'function' ? window.can('locais.gerenciar') : true;

                    const isAtivo = (data.ativo == 1 || data.ativo === true);
                    const btnStatus = canManage
                        ? (isAtivo
                            ? `<button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar local" aria-label="Desativar local"><i class="ph ph-x" aria-hidden="true"></i></button>`
                            : `<button type="button" class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar local" aria-label="Reativar local"><i class="ph ph-check" aria-hidden="true"></i></button>`)
                        : '';

                    const btnEdit = canManage
                        ? `<a href="/ctt/admin/locais/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar local" aria-label="Editar local"><i class="ph ph-pencil" aria-hidden="true"></i></a>`
                        : '';

                    return `
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            ${btnEdit}
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configLocais);

    const reloadLocais = () => {
        tabelas[configLocais.tableId].ajax.reload();
        bootstrap.Dropdown.getInstance(document.getElementById('filtrosLocaisBtn'))?.hide();
    };

    $('#aplicarFiltrosLocais').on('click', function (e) {
        e.preventDefault();
        reloadLocais();
    });

    $('#limparFiltrosLocais').on('click', function (e) {
        e.preventDefault();
        $('.filtro-status').prop('checked', false);
        $('#campoBusca').val('');
        tabelas[configLocais.tableId].search('');
        reloadLocais();
    });

    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status',
        urlAPI: '/ctt/api/locais',
        tabelaId: 'tabelaLocais',
        rotaDesativar: '/desativar',
        rotaReativar: '/reativar',
        mensagens: {
            desativar: {
                titulo: 'Confirmar desativacao',
                texto: 'Este local nao podera ser escolhido em novos treinos.'
            },
            reativar: {
                titulo: 'Confirmar reativacao',
                texto: 'O local voltara a ficar disponivel para novos treinos.'
            },
            sucesso: 'Status do local alterado com sucesso!',
            erro: 'Erro ao alterar o status do local.'
        }
    });
});
