$(document).ready(function () {
    const tableId = 'tabelaPerfis';
    let tabelaInicializada = false;

    function obterFiltroStatus() {
        const selecionados = $('.filtro-status-perfil:checked').map(function () {
            return $(this).val();
        }).get();

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

    function inicializarPerfis() {
        if (tabelaInicializada || !document.getElementById(tableId)) return;

        inicializarTabela({
            tableId,
            ajaxUrl: '/ctt/api/usuarios',
            emptyMessage: 'Nenhum perfil encontrado.',
            searchInput: '#campoBuscaPerfis',
            searchButton: '#botaoBuscarPerfis',
            getFilters: function () {
                return {
                    status: obterFiltroStatus()
                };
            },
            columns: [
                {
                    data: null,
                    render: function (data) {
                        const tipoMap = {
                            admin: 'Administrador',
                            funcionario: 'Funcionario',
                            aluno: 'Aluno'
                        };
                        const nomeCompleto = [data.nome, data.sobrenome].filter(Boolean).join(' ').trim() || '—';
                        const tipoUsuario = tipoMap[data.tipo_usuario] || data.tipo_usuario || '—';

                        return `
                            <div class="d-flex flex-column">
                                <span>${nomeCompleto}</span>
                                <small class="text-muted">${tipoUsuario}</small>
                            </div>
                        `;
                    }
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: function (data) {
                        return typeof formatarStatus === 'function' ? formatarStatus(data) : data;
                    }
                },
                {
                    data: 'data_criacao',
                    className: 'text-center',
                    render: function (data) {
                        return typeof formatarData === 'function' ? formatarData(data) : data;
                    }
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: function (data) {
                        if (!data) return '';

                        const isAtivo = data.ativo == 1 || data.ativo === true;
                        return isAtivo
                            ? `<button class="btn btn-sm btn-danger btn-toggle-status-perfil" data-id="${data.id}" data-ativo="1" title="Desativar"><i class="ph ph-x"></i></button>`
                            : `<button class="btn btn-sm btn-success btn-toggle-status-perfil" data-id="${data.id}" data-ativo="0" title="Reativar"><i class="ph ph-check"></i></button>`;
                    }
                }
            ]
        });

        $('#aplicarFiltrosPerfis').on('click', function (e) {
            e.preventDefault();
            recarregarTabela(tableId);
            esconderDropdown($(this));
        });

        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-perfil',
            urlAPI: '/ctt/api/usuarios',
            tabelaId: tableId,
            mensagens: {
                desativar: {
                    titulo: 'Confirmar desativacao',
                    texto: 'Tem certeza que deseja desativar este usuario?'
                },
                reativar: {
                    titulo: 'Confirmar reativacao',
                    texto: 'Tem certeza que deseja reativar este usuario?'
                },
                sucesso: 'Status do usuario alterado com sucesso!',
                erro: 'Erro ao alterar o status do usuario.'
            }
        });

        tabelaInicializada = true;
    }

    inicializarPerfis();

    document.querySelector('#configTabs [data-bs-target="#perfis"]')?.addEventListener('shown.bs.tab', function () {
        inicializarPerfis();
        ajustarTabela();
    });

    if ($('#perfis').hasClass('show') || $('#perfis').hasClass('active')) {
        ajustarTabela();
    }
});
