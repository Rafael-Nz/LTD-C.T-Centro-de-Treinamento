$(document).ready(function () {
    const tableId = 'tabelaCargos';
    let tabelaInicializada = false;

    function obterFiltroStatus() {
        const selecionados = $('.filtro-status-cargo:checked').map(function () {
            const valor = $(this).val();
            return valor === 'Ativo' ? '1' : valor === 'Inativo' ? '0' : '';
        }).get().filter(Boolean);

        return selecionados.length === 1 ? selecionados[0] : '';
    }

    function esconderDropdown($botaoAplicar) {
        const $toggle = $botaoAplicar.closest('.dropdown-menu').siblings('.dropdown-toggle').first();
        if ($toggle.length) {
            bootstrap.Dropdown.getOrCreateInstance($toggle[0]).hide();
        }
    }

    function ajustarTabela() {
        if (!tabelas[tableId]) return;
        tabelas[tableId].columns.adjust();
        if (tabelas[tableId].responsive) {
            tabelas[tableId].responsive.recalc();
        }
    }

    function inicializarCargos() {
        if (tabelaInicializada || !document.getElementById(tableId)) return;

        inicializarTabela({
            tableId,
            ajaxUrl: '/ctt/api/cargos',
            emptyMessage: 'Nenhum cargo encontrado.',
            searchInput: '#campoBuscaCargos',
            searchButton: '#botaoBuscarCargos',
            getFilters: function () {
                return {
                    status: obterFiltroStatus()
                };
            },
            columns: [
                {
                    data: 'nome'
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: function (data) {
                        return typeof formatarStatus === 'function' ? formatarStatus(data) : data;
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function (data) {
                        if (!data) return '';

                        const isAtivo = data.ativo == 1 || data.ativo === true;
                        const btnStatus = isAtivo
                            ? `<button class="btn btn-sm btn-danger btn-toggle-status-cargo" data-id="${data.id}" data-ativo="1" title="Desativar"><i class="ph ph-x"></i></button>`
                            : `<button class="btn btn-sm btn-success btn-toggle-status-cargo" data-id="${data.id}" data-ativo="0" title="Reativar"><i class="ph ph-check"></i></button>`;

                        return `
                            <div class="d-flex gap-2 justify-content-center">
                                <a href="/ctt/admin/cargos/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar">
                                    <i class="ph ph-pencil"></i>
                                </a>
                                ${btnStatus}
                            </div>
                        `;
                    }
                }
            ]
        });

        $('#aplicarFiltrosCargos').on('click', function (e) {
            e.preventDefault();
            recarregarTabela(tableId);
            esconderDropdown($(this));
        });

        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-cargo',
            urlAPI: '/ctt/api/cargos',
            tabelaId: tableId,
            mensagens: {
                desativar: {
                    titulo: 'Confirmar desativacao',
                    texto: 'Tem certeza que deseja desativar este cargo?'
                },
                reativar: {
                    titulo: 'Confirmar reativacao',
                    texto: 'Tem certeza que deseja reativar este cargo?'
                },
                sucesso: 'Status do cargo alterado com sucesso!',
                erro: 'Erro ao alterar o status do cargo.'
            }
        });

        tabelaInicializada = true;
    }

    document.querySelector('#configTabs [data-bs-target="#cargos"]')?.addEventListener('shown.bs.tab', function () {
        inicializarCargos();
        ajustarTabela();
    });

    if ($('#cargos').hasClass('show') || $('#cargos').hasClass('active')) {
        inicializarCargos();
        ajustarTabela();
    }
});
