// /ctt/js/admin/datatable/funcionarios.js

$(document).ready(function () {
    // ========================================
    // 1. CARREGAR CARGOS NO SELECT2
    // ========================================
    const $cargoSelect = $('#filtroCargosSelect');
    
    $cargoSelect.select2({
        theme: 'bootstrap-5',
        placeholder: 'Todos os cargos',
        allowClear: true,
        width: '100%',
        dropdownParent: $cargoSelect.closest('.dropdown-menu')
    });

    $.ajax({
        url: '/ctt/api/cargos',
        method: 'GET',
        dataType: 'json',
        success: function (response) {
            let cargos = [];
            if (response.success && Array.isArray(response.data)) {
                cargos = response.data;
            } else if (Array.isArray(response)) {
                cargos = response;
            } else if (response.data && Array.isArray(response.data)) {
                cargos = response.data;
            }

            cargos.forEach(cargo => {
                const option = new Option(cargo.nome, cargo.id, false, false);
                $cargoSelect.append(option);
            });
            $cargoSelect.trigger('change');
        },
        error: function (xhr) {
            console.error('Erro ao carregar cargos:', xhr);
        }
    });

    // ========================================
    // 2. CONFIGURAÇÃO DA TABELA (sem data_contratacao)
    // ========================================
    const configFuncionarios = {
        tableId: 'tabelaFuncionarios',
        ajaxUrl: '/ctt/api/funcionarios',
        emptyMessage: 'Nenhum funcionário encontrado.',
        searchInput: '#campoBusca',
        searchButton: '#botaoBuscar',
        
        getFilters: function () {
            let statusSelecionados = [];
            $('.filtro-status:checked').each(function () {
                const valor = $(this).val();
                if (valor === 'ativo') {
                    statusSelecionados.push(1);
                } else if (valor === 'inativo') {
                    statusSelecionados.push(0);
                }
            });

            let cargoId = $cargoSelect.val();

            const filters = {};
            if (statusSelecionados.length > 0) {
                filters.status = statusSelecionados.join(',');
            }
            if (cargoId && cargoId !== '') {
                filters.cargo_id = cargoId;
            }
            return filters;
        },

        // AGORA COM 5 COLUNAS (índice 0 a 4)
        columns: [
            { 
                data: null,
                render: data => `${escapeHtml(data.nome)} ${escapeHtml(data.sobrenome)}`
            },
            { 
                data: null,
                render: data => escapeHtml(data.cargo_nome || (data.cargo ? data.cargo.nome : '—'))
            },
            { 
                data: 'email',
                render: data => escapeHtml(data)
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
                    const canManage = typeof window.can === 'function' ? window.can('funcionarios.gerenciar') : true;

                    const isAtivo = (data.ativo == 1 || data.ativo === true);
                    const btnStatus = canManage
                        ? (isAtivo 
                            ? `<button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar funcionário" aria-label="Desativar funcionário"><i class="ph ph-x" aria-hidden="true"></i></button>`
                            : `<button type="button" class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar funcionário" aria-label="Reativar funcionário"><i class="ph ph-check" aria-hidden="true"></i></button>`)
                        : '';

                    const btnEdit = canManage
                        ? `<a href="/ctt/admin/funcionarios/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar funcionário" aria-label="Editar funcionário"><i class="ph ph-pencil" aria-hidden="true"></i></a>`
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

    // Inicializa a DataTable
    inicializarTabela(configFuncionarios);

    // ========================================
    // 3. FILTROS (APLICAR E LIMPAR)
    // ========================================
    const reloadFuncionarios = () => {
        tabelas[configFuncionarios.tableId].ajax.reload();
        bootstrap.Dropdown.getInstance(document.getElementById('filtrosFuncionariosBtn'))?.hide();
    };

    $('#aplicarFiltros').on('click', function (e) {
        e.preventDefault();
        reloadFuncionarios();
    });

    $('#limparFiltros').on('click', function (e) {
        e.preventDefault();
        $cargoSelect.val(null).trigger('change');
        $('.filtro-status').prop('checked', false);
        $('#campoBusca').val('');
        tabelas[configFuncionarios.tableId].search('');
        reloadFuncionarios();
    });

    // ========================================
    // 4. TOGGLE DE STATUS
    // ========================================
    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status',
        urlAPI: '/ctt/api/funcionarios',
        tabelaId: 'tabelaFuncionarios',
        rotaDesativar: '/desativar',  // Usa a nova rota PUT
        rotaReativar: '/reativar',    // Usa a rota PUT existente
        mensagens: {
            desativar: { 
                titulo: 'Confirmar desativação', 
                texto: 'Este funcionário será desativado e não poderá mais acessar o sistema.' 
            },
            reativar: { 
                titulo: 'Confirmar reativação', 
                texto: 'O funcionário será reativado normalmente.' 
            },
            sucesso: 'Status do funcionário alterado com sucesso!',
            erro: 'Erro ao alterar o status do funcionário.'
        }
    });
});