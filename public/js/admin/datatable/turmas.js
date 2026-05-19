$(document).ready(function () {
    const configTurmas = {
        tableId: 'tabelaTurmas',
        ajaxUrl: '/ctt/api/turmas',
        emptyMessage: 'Nenhuma turma encontrada.',
        searchInput: '#campoBusca',
        searchButton: '#botaoBuscar',
        getFilters: function () {
            const statusSelecionados = [];
            $('.filtro-status:checked').each(function () {
                const valor = $(this).val();
                if (valor === 'ativo') {
                    statusSelecionados.push(1);
                } else if (valor === 'inativo') {
                    statusSelecionados.push(0);
                }
            });

            const filters = {};

            if (statusSelecionados.length > 0) {
                filters.ativo = statusSelecionados.join(',');
            }

            return filters;
        },
        columns: [
            {
                data: 'nome',
                render: data => data || '—'
            },
            {
                data: 'instrutor_nome',
                render: data => data || '—'
            },
            {
                data: 'total_alunos',
                className: 'text-center',
                render: data => Number.isFinite(Number(data)) ? data : '0'
            },
            {
                data: null,
                className: 'text-center',
                render: data => {
                    if (!data || !data.capacidade_minima || !data.capacidade_maxima) {
                        return '—';
                    }
                    return `${data.capacidade_minima}/${data.capacidade_maxima}`;
                }
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
                    if (!data) return '';
                    const isAtivo = data.ativo == 1 || data.ativo === true;
                    const btnStatus = isAtivo
                        ? `<button class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar"><i class="ph ph-x"></i></button>`
                        : `<button class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar"><i class="ph ph-check"></i></button>`;

                    return `
                        <div class="d-flex gap-2 justify-content-center">
                            <a href="/ctt/admin/turmas/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar Turma">
                                <i class="ph ph-pencil"></i>
                            </a>
                            <a href="/ctt/admin/turmas/${data.id}/gerenciar" class="btn btn-sm btn-info text-white" title="Gerenciar Turma">
                                <i class="ph ph-users"></i>
                            </a>
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configTurmas);

    $('#aplicarFiltros').on('click', function (e) {
        e.preventDefault();
        tabelas[configTurmas.tableId].ajax.reload();
        const dropdownButton = $('.dropdown-toggle[data-bs-toggle="dropdown"]');
        if (dropdownButton.length) {
            bootstrap.Dropdown.getInstance(dropdownButton[0])?.hide();
        }
    });

    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status',
        urlAPI: '/ctt/api/turmas',
        tabelaId: 'tabelaTurmas',
        rotaDesativar: '/desativar',
        rotaReativar: '/reativar',
        mensagens: {
            desativar: {
                titulo: 'Confirmar desativacao',
                texto: 'Esta turma sera desativada e nao aceitara mais alunos.'
            },
            reativar: {
                titulo: 'Confirmar reativacao',
                texto: 'A turma sera reativada normalmente.'
            },
            sucesso: 'Status da turma alterado com sucesso!',
            erro: 'Erro ao alterar o status da turma.'
        }
    });
});
