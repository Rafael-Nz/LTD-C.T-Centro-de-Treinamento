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
                render: data => data ? escapeHtml(data) : '—'
            },
            {
                data: 'instrutor_nome',
                render: data => data ? escapeHtml(data) : '—'
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
                    const canEdit = typeof window.can === 'function' ? window.can('turmas.editar') : true;
                    const canManage = typeof window.can === 'function' ? (window.can('turmas.gerenciar') || window.can('turmas.visualizar')) : true;
                    const canStatus = typeof window.can === 'function' ? window.can('turmas.desativar') : true;

                    const isAtivo = data.ativo == 1 || data.ativo === true;
                    const btnStatus = canStatus
                        ? (isAtivo
                            ? `<button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar turma" aria-label="Desativar turma"><i class="ph ph-x" aria-hidden="true"></i></button>`
                            : `<button type="button" class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar turma" aria-label="Reativar turma"><i class="ph ph-check" aria-hidden="true"></i></button>`)
                        : '';

                    const btnEdit = canEdit
                        ? `<a href="/ctt/admin/turmas/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar turma" aria-label="Editar turma"><i class="ph ph-pencil" aria-hidden="true"></i></a>`
                        : '';

                    const btnManage = canManage
                        ? `<a href="/ctt/admin/turmas/${data.id}/gerenciar" class="btn btn-sm btn-secondary" title="Gerenciar turma" aria-label="Gerenciar turma"><i class="ph ph-users" aria-hidden="true"></i></a>`
                        : '';

                    return `
                        <div class="d-flex gap-1 justify-content-center flex-wrap">
                            ${btnEdit}
                            ${btnManage}
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configTurmas);

    const reloadTurmas = () => {
        tabelas[configTurmas.tableId].ajax.reload();
        bootstrap.Dropdown.getInstance(document.getElementById('filtrosTurmasBtn'))?.hide();
    };

    $('#aplicarFiltros').on('click', function (e) {
        e.preventDefault();
        reloadTurmas();
    });

    $('#limparFiltros').on('click', function (e) {
        e.preventDefault();
        $('.filtro-status').prop('checked', false);
        $('#campoBusca').val('');
        tabelas[configTurmas.tableId].search('');
        reloadTurmas();
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
