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
                render: data => data || '—'
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
                    const isAtivo = (data.ativo == 1 || data.ativo === true);
                    const btnStatus = isAtivo
                        ? `<button class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar"><i class="ph ph-x"></i></button>`
                        : `<button class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar"><i class="ph ph-check"></i></button>`;

                    return `
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="/ctt/admin/locais/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar local">
                                <i class="ph ph-pencil"></i>
                            </a>
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configLocais);

    $('#aplicarFiltrosLocais').on('click', function (e) {
        e.preventDefault();
        tabelas[configLocais.tableId].ajax.reload();
        const dropdownButton = $('.dropdown-toggle[data-bs-toggle="dropdown"]');
        if (dropdownButton.length) {
            bootstrap.Dropdown.getInstance(dropdownButton[0])?.hide();
        }
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
