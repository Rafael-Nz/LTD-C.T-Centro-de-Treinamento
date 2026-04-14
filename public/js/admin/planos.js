$(function () {

    if (typeof inicializarTabela === 'undefined') {
        console.error('tabelas.js não foi carregado.');
        return;
    }

    if (!$('#tabelaPlanos').length) return;

    // Inicializa tabela de planos
    const tabelaPlanos = inicializarTabela({
        tableId: 'tabelaPlanos',
        ajaxUrl: '../api/planos/',
        editUrl: 'plano_form.php',
        emptyMessage: 'Nenhum registro encontrado',
        searchInput: '#campoBuscaPlanos',
        searchButton: '#botaoBuscarPlanos',
        statusFilterClass: '.filtro-status',
        statusColumnIndex: 4,
        columns: [
            {
                data: 'nome',
                className: 'fw-semibold text-start',
                render: (data, type, row) => `
                    <div class="fw-semibold">${data}</div>
                    <small class="text-muted">${row.descricao || 'Sem descrição'}</small>
                `
            },
            {
                data: 'periodicidade',
                className: 'text-center',
                render: data => formatarPeriodicidade(data)
            },
            {
                data: 'valor',
                className: 'text-center fw-semibold',
                render: (data, type, row) => {
                    const valorNumerico = Number(data);
                    if (type !== 'display') return valorNumerico;
                    const limite = (!row.limite_treinos_semana || row.limite_treinos_semana === 0)
                        ? 'Ilimitado'
                        : `${row.limite_treinos_semana} treinos/sem`;
                    return `${formatarMoeda(valorNumerico)}<small class="text-muted d-block">${limite}</small>`;
                }
            },
            {
                data: 'total_alunos',
                className: 'text-center',
                render: data => `<span class="badge bg-info text-white px-3 py-1">${data || 0}</span>`
            },
            {
                data: 'status',
                className: 'text-center',
                render: data => formatarStatus(data)
            },
            {
                data: null,
                orderable: false,
                className: 'text-center',
                render: (data, type, row) => {
                    const isAtivo = row.status === 'ativo';
                    return `
                        <button class="btn btn-sm btn-info btn-editar" data-id="${row.id}" title="Editar plano" data-bs-toggle="tooltip">
                            <i class="ph ph-pencil-simple text-white"></i>
                        </button>
                        <button class="btn btn-sm ${isAtivo ? 'btn-danger' : 'btn-success'} ms-2 btn-toggle-status-plano"
                            data-id="${row.id}" data-ativo="${isAtivo ? 1 : 0}" title="${isAtivo ? 'Desativar plano' : 'Reativar plano'}"
                            data-bs-toggle="tooltip">
                            <i class="ph ${isAtivo ? 'ph-x-circle' : 'ph-arrow-counter-clockwise'}"></i>
                        </button>
                    `;
                }
            }
        ],
        ajax: {
            url: '../api/planos/',
            dataSrc: 'data'
        }
    });

    // --------------------------- //
    // Aplicar filtros apenas ao clicar no botão
    // --------------------------- //
    function aplicarFiltrosPlanos() {
        const statusSelecionado = $('.filtro-status:checked').map(function () {
            return this.value.toLowerCase();
        }).get();

        const busca = $('#campoBuscaPlanos').val();

        let url = '../api/planos?';

        if (statusSelecionado.length === 1) url += `status=${statusSelecionado[0]}&`;
        if (busca) url += `search=${encodeURIComponent(busca)}&`;

        if (url.endsWith('&')) url = url.slice(0, -1);

        // Atualiza URL e recarrega
        tabelaPlanos.ajax.url(url).load(null, false);
    }

    $('#aplicarFiltrosPlanos').on('click', aplicarFiltrosPlanos);

    // Permitir apenas um checkbox de status por vez
    $('.filtro-status').on('change', function () {
        $('.filtro-status').not(this).prop('checked', false);
    });

    // Também permitir pesquisa ao apertar Enter
    $('#campoBuscaPlanos').on('keyup', function (e) {
        if (e.key === 'Enter') aplicarFiltrosPlanos();
    });

    $('#botaoBuscarPlanos').on('click', aplicarFiltrosPlanos);

    // --------------------------- //
    // Toggle status
    // --------------------------- //
    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status-plano',
        urlAPI: '../api/planos/',
        metodo: 'PUT',
        tabelaId: 'tabelaPlanos',
        mensagens: {
            desativar: { titulo: 'Confirmar desativação', texto: 'Deseja realmente desativar este plano?' },
            reativar: { titulo: 'Confirmar reativação', texto: 'Deseja realmente reativar este plano?' },
            sucesso: 'Status do plano alterado com sucesso!',
            erro: 'Erro ao alterar status do plano.'
        },
        onSuccess: response => console.log('Toggle status realizado com sucesso:', response)
    });

});
