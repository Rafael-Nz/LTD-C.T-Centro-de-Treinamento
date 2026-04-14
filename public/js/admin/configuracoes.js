$(function () {

    /* =====================================================
       VERIFICAÇÕES GERAIS
    ===================================================== */
    if (typeof inicializarTabela === 'undefined') {
        console.error('tabelas.js não foi carregado.');
        return;
    }

    /* =====================================================
       MÓDULO: PERFIS
    ===================================================== */
    if ($('#tabelaPerfis').length) {

        const tabelaPerfis = inicializarTabela({
            tableId: 'tabelaPerfis',
            ajaxUrl: '../api/perfis/',
            editUrl: 'perfil_form.php',
            emptyMessage: 'Nenhum registro encontrado',
            searchInput: '#campoBuscaPerfis',
            searchButton: '#botaoBuscarPerfis',
            statusFilterClass: '.filtro-status-perfil',
            statusColumnIndex: 2,
            ajax: {
                url: '../api/perfis/',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'nome',
                    className: 'fw-semibold text-start',
                    render: data => `<div class="fw-semibold">${data}</div>`
                },
                {
                    data: 'descricao',
                    className: 'text-center',
                    render: data => data || '<span class="text-muted">Sem descrição</span>'
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: data => formatarStatus(data)
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => {
                        const isAtivo = row.ativo == 1;
                        return `
                            <a href="perfil_form.php?id=${row.id}" 
                            class="btn btn-sm btn-info"
                            title="Editar perfil"
                            data-bs-toggle="tooltip">
                                <i class="ph ph-pencil-simple text-white"></i>
                            </a>

                            <button class="btn btn-sm ${isAtivo ? 'btn-danger' : 'btn-success'} ms-2 btn-toggle-status-perfil"
                                data-id="${row.id}"
                                data-ativo="${isAtivo ? 1 : 0}"
                                title="${isAtivo ? 'Desativar perfil' : 'Reativar perfil'}"
                                data-bs-toggle="tooltip">
                                <i class="ph ${isAtivo ? 'ph-x-circle' : 'ph-arrow-counter-clockwise'}"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        function aplicarFiltrosPerfis() {
            const statusSelecionado = $('.filtro-status-perfil:checked').map(function () {
                return this.value.toLowerCase();
            }).get();

            const busca = $('#campoBuscaPerfis').val();
            let url = '../api/perfis?';

            if (statusSelecionado.length === 1) url += `ativo=${statusSelecionado[0]}&`;
            if (busca) url += `search=${encodeURIComponent(busca)}&`;

            if (url.endsWith('&')) url = url.slice(0, -1);

            tabelaPerfis.ajax.url(url).load(null, false);
        }

        $('#aplicarFiltrosPerfis').on('click', aplicarFiltrosPerfis);

        $('.filtro-status-perfil').on('change', function () {
            $('.filtro-status-perfil').not(this).prop('checked', false);
        });

        $('#campoBuscaPerfis').on('keyup', e => {
            if (e.key === 'Enter') aplicarFiltrosPerfis();
        });

        $('#botaoBuscarPerfis').on('click', aplicarFiltrosPerfis);

        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-perfil',
            urlAPI: '../api/perfis/',
            metodo: 'PUT',
            tabelaId: 'tabelaPerfis',
            mensagens: {
                desativar: { titulo: 'Confirmar desativação', texto: 'Deseja realmente desativar este perfil?' },
                reativar: { titulo: 'Confirmar reativação', texto: 'Deseja realmente reativar este perfil?' },
                sucesso: 'Status do perfil alterado com sucesso!',
                erro: 'Erro ao alterar status do perfil.'
            }
        });
    }

    /* =====================================================
       MÓDULO: CARGOS
    ===================================================== */
    if ($('#tabelaCargos').length) {

        const tabelaCargos = inicializarTabela({
            tableId: 'tabelaCargos',
            ajaxUrl: '../api/cargos/',
            editUrl: 'cargo_form.php',
            emptyMessage: 'Nenhum registro encontrado',
            searchInput: '#campoBuscaCargos',
            searchButton: '#botaoBuscarCargos',
            statusFilterClass: '.filtro-status-cargo',
            statusColumnIndex: 3,
            ajax: {
                url: '../api/cargos/',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'nome',
                    className: 'fw-semibold text-start',
                    render: data => `<div class="fw-semibold">${data}</div>`
                },
                {
                    data: 'descricao',
                    className: 'text-center',
                    render: data => data || '<span class="text-muted">Sem descrição</span>'
                },
                {
                    data: 'salario_base',
                    className: 'text-center fw-semibold',
                    render: data => formatarMoeda(Number(data))
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: data => formatarStatus(data)
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => {
                        const isAtivo = row.ativo == 1;
                        return `
                            <button class="btn btn-sm btn-info btn-editar"
                                data-id="${row.id}"
                                title="Editar cargo"
                                data-bs-toggle="tooltip">
                                <i class="ph ph-pencil-simple text-white"></i>
                            </button>

                            <button class="btn btn-sm ${isAtivo ? 'btn-danger' : 'btn-success'} ms-2 btn-toggle-status-cargo"
                                data-id="${row.id}"
                                data-ativo="${isAtivo ? 1 : 0}"
                                title="${isAtivo ? 'Desativar cargo' : 'Reativar cargo'}"
                                data-bs-toggle="tooltip">
                                <i class="ph ${isAtivo ? 'ph-x-circle' : 'ph-arrow-counter-clockwise'}"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        function aplicarFiltrosCargos() {
            const statusSelecionado = $('.filtro-status-cargo:checked').map(function () {
                return this.value.toLowerCase();
            }).get();

            const busca = $('#campoBuscaCargos').val();
            let url = '../api/cargos?';

            if (statusSelecionado.length === 1) url += `ativo=${statusSelecionado[0]}&`;
            if (busca) url += `search=${encodeURIComponent(busca)}&`;

            if (url.endsWith('&')) url = url.slice(0, -1);

            tabelaCargos.ajax.url(url).load(null, false);
        }

        $('#aplicarFiltrosCargos').on('click', aplicarFiltrosCargos);

        $('.filtro-status-cargo').on('change', function () {
            $('.filtro-status-cargo').not(this).prop('checked', false);
        });

        $('#campoBuscaCargos').on('keyup', e => {
            if (e.key === 'Enter') aplicarFiltrosCargos();
        });

        $('#botaoBuscarCargos').on('click', aplicarFiltrosCargos);

        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-cargo',
            urlAPI: '../api/cargos/',
            metodo: 'PUT',
            tabelaId: 'tabelaCargos',
            mensagens: {
                desativar: { titulo: 'Confirmar desativação', texto: 'Deseja realmente desativar este cargo?' },
                reativar: { titulo: 'Confirmar reativação', texto: 'Deseja realmente reativar este cargo?' },
                sucesso: 'Status do cargo alterado com sucesso!',
                erro: 'Erro ao alterar status do cargo.'
            }
        });
    }

    /* =====================================================
       MÓDULO: MODALIDADES
    ===================================================== */
    if ($('#tabelaModalidades').length) {

        const tabelaModalidades = inicializarTabela({
            tableId: 'tabelaModalidades',
            ajaxUrl: '../api/modalidades/',
            editUrl: 'modalidade_form.php',
            emptyMessage: 'Nenhuma registro encontrado',
            searchInput: '#campoBuscaModalidades',
            searchButton: '#botaoBuscarModalidades',
            statusFilterClass: '.filtro-status-modalidade',
            statusColumnIndex: 3,
            ajax: {
                url: '../api/modalidades/',
                dataSrc: 'data'
            },
            columns: [
                {
                    data: 'nome',
                    className: 'fw-semibold text-start',
                    render: data => `<div class="fw-semibold">${data}</div>`
                },
                {
                    data: 'descricao',
                    className: 'text-center',
                    render: data => data || '<span class="text-muted">Sem descrição</span>'
                },
                {
                    data: 'data_criacao',
                    className: 'text-center',
                    render: data => formatarData(data)
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: data => formatarStatus(data)
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: (data, type, row) => {
                        const isAtivo = row.ativo == 1;
                        return `
                            <button class="btn btn-sm btn-info btn-editar"
                                data-id="${row.id}"
                                title="Editar modalidade"
                                data-bs-toggle="tooltip">
                                <i class="ph ph-pencil-simple text-white"></i>
                            </button>

                            <button class="btn btn-sm ${isAtivo ? 'btn-danger' : 'btn-success'} ms-2 btn-toggle-status-modalidade"
                                data-id="${row.id}"
                                data-ativo="${isAtivo ? 1 : 0}"
                                title="${isAtivo ? 'Desativar modalidade' : 'Reativar modalidade'}"
                                data-bs-toggle="tooltip">
                                <i class="ph ${isAtivo ? 'ph-x-circle' : 'ph-arrow-counter-clockwise'}"></i>
                            </button>
                        `;
                    }
                }
            ]
        });

        function aplicarFiltrosModalidades() {
            const statusSelecionado = $('.filtro-status-modalidade:checked').map(function () {
                return this.value.toLowerCase();
            }).get();

            const busca = $('#campoBuscaModalidades').val();
            let url = '../api/modalidades?';

            if (statusSelecionado.length === 1) url += `ativo=${statusSelecionado[0]}&`;
            if (busca) url += `search=${encodeURIComponent(busca)}&`;

            if (url.endsWith('&')) url = url.slice(0, -1);

            tabelaModalidades.ajax.url(url).load(null, false);
        }

        $('#aplicarFiltrosModalidades').on('click', aplicarFiltrosModalidades);

        $('.filtro-status-modalidade').on('change', function () {
            $('.filtro-status-modalidade').not(this).prop('checked', false);
        });

        $('#campoBuscaModalidades').on('keyup', e => {
            if (e.key === 'Enter') aplicarFiltrosModalidades();
        });

        $('#botaoBuscarModalidades').on('click', aplicarFiltrosModalidades);

        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-modalidade',
            urlAPI: '../api/modalidades/',
            metodo: 'PUT',
            tabelaId: 'tabelaModalidades',
            mensagens: {
                desativar: { titulo: 'Confirmar desativação', texto: 'Deseja realmente desativar esta modalidade?' },
                reativar: { titulo: 'Confirmar reativação', texto: 'Deseja realmente reativar esta modalidade?' },
                sucesso: 'Status da modalidade alterado com sucesso!',
                erro: 'Erro ao alterar status da modalidade.'
            }
        });
    }

});



/* =====================================================
   MÓDULO: CONTROLE DE ABAS (Bootstrap)
===================================================== */
$(document).ready(function () {
    const abaAtiva = localStorage.getItem('abaConfigAtiva');

    if (abaAtiva) {
        const targetTab = $(`#configTabs button[data-bs-target="#${abaAtiva}"]`);

        if (targetTab.length) {
            const tabTrigger = new bootstrap.Tab(targetTab[0]);
            tabTrigger.show();
        }

        localStorage.removeItem('abaConfigAtiva');
    }
});
