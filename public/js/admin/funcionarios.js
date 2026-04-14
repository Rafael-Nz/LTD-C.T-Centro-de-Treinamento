// funcionarios.js

$(function () {
    // Verificar dependências
    if (typeof inicializarTabela === 'undefined') {
        console.error('tabelas.js não foi carregado.');
        return;
    }

    if (!$('#tabelaFuncionarios').length) return;

    // URLs da API
    const API_FUNCIONARIOS = '../api/funcionarios/';
    const API_CARGOS = '../api/cargos/';

    // Inicializar componentes
    inicializarComponentes();

    // Inicializar tabela de funcionários
    const tabelaFuncionarios = inicializarTabelaFuncionarios();

    // Configurar eventos
    configurarEventos();

    // Configurar botões de status
    configurarToggleStatusFuncionarios();

    /* =====================================================
       INICIALIZAR COMPONENTES
    ===================================================== */
    function inicializarComponentes() {
        // Inicializar Select2 apenas se estiver disponível
        if ($.fn.select2 && $('#filtroCargosSelect').length) {
            inicializarSelect2();
            carregarCargosParaFiltro();
        } else {
            // Fallback: usar select nativo
            console.warn('Select2 não disponível, usando select nativo');
            carregarCargosParaFiltroNativo();
        }
    }

    /* =====================================================
       INICIALIZAR SELECT2
    ===================================================== */
    function inicializarSelect2() {
        $('#filtroCargosSelect').select2({
            theme: 'bootstrap-5',
            placeholder: 'Selecione um cargo',
            allowClear: true,
            width: '100%',
            dropdownParent: $('#filtroCargosSelect').parent()
        });
    }

    /* =====================================================
       CARREGAR CARGOS PARA FILTRO (COM SELECT2)
    ===================================================== */
    function carregarCargosParaFiltro() {
        $.ajax({
            url: API_CARGOS,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.data && Array.isArray(response.data)) {
                    $('#filtroCargosSelect').empty();
                    
                    // Adicionar opção vazia
                    $('#filtroCargosSelect').append(
                        $('<option>', {
                            value: '',
                            text: 'Selecione um cargo',
                            selected: true
                        })
                    );
                    
                    // Adicionar cargos ativos
                    response.data.forEach(cargo => {
                        if (cargo.ativo == 1) {
                            $('#filtroCargosSelect').append(
                                $('<option>', {
                                    value: cargo.id,
                                    text: cargo.nome
                                })
                            );
                        }
                    });
                    
                    // Atualizar Select2
                    $('#filtroCargosSelect').trigger('change');
                    
                    // Atualizar ao mudar seleção
                    $('#filtroCargosSelect').on('change', function() {
                        aplicarFiltros();
                    });
                }
            },
            error: function(xhr) {
                console.error('Erro ao carregar cargos:', xhr);
            }
        });
    }

    /* =====================================================
       CARREGAR CARGOS PARA FILTRO (SEM SELECT2 - FALLBACK)
    ===================================================== */
    function carregarCargosParaFiltroNativo() {
        $.ajax({
            url: API_CARGOS,
            method: 'GET',
            dataType: 'json',
            success: function(response) {
                if (response.data && Array.isArray(response.data)) {
                    const select = $('#filtroCargosSelect')[0];
                    
                    // Adicionar opção vazia
                    const optionVazia = document.createElement('option');
                    optionVazia.value = '';
                    optionVazia.textContent = 'Selecione um cargo';
                    optionVazia.selected = true;
                    select.appendChild(optionVazia);
                    
                    // Adicionar cargos ativos
                    response.data.forEach(cargo => {
                        if (cargo.ativo == 1) {
                            const option = document.createElement('option');
                            option.value = cargo.id;
                            option.textContent = cargo.nome;
                            select.appendChild(option);
                        }
                    });
                    
                    // Atualizar ao mudar seleção
                    $(select).on('change', function() {
                        aplicarFiltros();
                    });
                }
            },
            error: function(xhr) {
                console.error('Erro ao carregar cargos:', xhr);
            }
        });
    }

    /* =====================================================
       INICIALIZAR TABELA DE FUNCIONÁRIOS
    ===================================================== */
    function inicializarTabelaFuncionarios() {
        return inicializarTabela({
            tableId: 'tabelaFuncionarios',
            ajaxUrl: API_FUNCIONARIOS,
            editUrl: 'funcionario_form.php',
            emptyMessage: 'Nenhum funcionário encontrado',
            searchInput: '#campoBusca',
            searchButton: '#botaoBuscar',
            statusFilterClass: '.filtro-status',
            statusColumnIndex: 4,
            columns: [
                {
                    data: null,
                    className: 'text-start',
                    render: function(data, type, row) {
                        return `
                            <div class="fw-semibold">${data.nome} ${data.sobrenome}</div>
                            <small class="text-muted">${data.cpf || 'CPF não informado'}</small>
                        `;
                    }
                },
                {
                    data: 'cargo_nome',
                    className: 'text-start',
                    render: function(data) {
                        return data || '<span class="text-body-secondary">Não informado</span>';
                    }
                },
                {
                    data: 'email',
                    className: 'text-start',
                    render: function(data) {
                        if (!data) return '<span class="text-body-secondary">Não informado</span>';
                        return `<a href="mailto:${data}" class="text-decoration-none">${data}</a>`;
                    }
                },
                {
                    data: 'data_criacao',
                    className: 'text-center',
                    render: function(data) {
                        if (!data) return '—';
                        try {
                            const dataObj = new Date(data);
                            return dataObj.toLocaleDateString('pt-BR');
                        } catch (e) {
                            return 'Data inválida';
                        }
                    }
                },
                {
                    data: 'ativo',
                    className: 'text-center',
                    render: function(data) {
                        return formatarStatus(data);
                    }
                },
                {
                    data: null,
                    orderable: false,
                    className: 'text-center',
                    render: function(data, type, row) {
                        const isAtivo = row.ativo == 1;
                        const statusIcon = isAtivo ? 'ph-x-circle' : 'ph-arrow-counter-clockwise';
                        const statusTitle = isAtivo ? 'Desativar funcionário' : 'Reativar funcionário';
                        const statusClass = isAtivo ? 'btn-danger' : 'btn-success';
                        
                        return `
                            <div class="d-flex justify-content-center gap-2">
                                <a href="funcionario_form.php?id=${row.id}" 
                                   class="btn btn-sm btn-info text-white" 
                                   title="Editar funcionário"
                                   data-bs-toggle="tooltip">
                                    <i class="ph ph-pencil-simple"></i>
                                </a>
                                <button class="btn btn-sm ${statusClass} btn-toggle-status-funcionario"
                                        data-id="${row.id}" 
                                        data-ativo="${isAtivo ? 1 : 0}" 
                                        title="${statusTitle}"
                                        data-bs-toggle="tooltip">
                                    <i class="ph ${statusIcon}"></i>
                                </button>
                            </div>
                        `;
                    }
                }
            ],
            ajax: {
                url: API_FUNCIONARIOS,
                dataSrc: 'data',
                error: function(xhr, error, thrown) {
                    console.error('Erro ao carregar dados da tabela:', error);
                    
                    // Mostrar mensagem de erro na tabela
                    const tabela = $('#tabelaFuncionarios');
                    tabela.find('tbody').html(`
                        <tr>
                            <td colspan="6" class="text-center text-danger">
                                <i class="ph ph-warning-circle me-2"></i>
                                Erro ao carregar dados dos funcionários. 
                                Tente recarregar a página.
                            </td>
                        </tr>
                    `);
                }
            }
        });
    }

    /* =====================================================
       CONFIGURAR EVENTOS
    ===================================================== */
    function configurarEventos() {
        // Aplicar filtros
        $('#aplicarFiltros').on('click', aplicarFiltros);
        
        // Permitir apenas um checkbox de status por vez
        $('.filtro-status').on('change', function() {
            $('.filtro-status').not(this).prop('checked', false);
            aplicarFiltros();
        });
        
        // Buscar ao pressionar Enter
        $('#campoBusca').on('keyup', function(e) {
            if (e.key === 'Enter') {
                aplicarFiltros();
            }
        });
        
        // Buscar ao clicar no botão
        $('#botaoBuscar').on('click', aplicarFiltros);
    }

    /* =====================================================
       APLICAR FILTROS
    ===================================================== */
    function aplicarFiltros() {
        const statusSelecionado = $('.filtro-status:checked').map(function() {
            return $(this).val();
        }).get();
        
        const busca = $('#campoBusca').val();
        const cargosSelecionados = $('#filtroCargosSelect').val();
        
        // Construir parâmetros
        const params = new URLSearchParams();
        
        if (statusSelecionado.length === 1) {
            params.append('ativo', statusSelecionado[0]);
        }
        
        if (busca) {
            params.append('search', busca);
        }
        
        if (cargosSelecionados && cargosSelecionados.length > 0) {
            params.append('cargo_id', cargosSelecionados.join(','));
        }
        
        // Construir URL
        let url = API_FUNCIONARIOS;
        const queryString = params.toString();
        if (queryString) {
            url += '?' + queryString;
        }
        
        // Atualizar URL da tabela e recarregar
        tabelaFuncionarios.ajax.url(url).load(null, false);
        
        // Fechar dropdown
        const dropdown = $('.dropdown-menu.show');
        if (dropdown.length) {
            const dropdownInstance = bootstrap.Dropdown.getInstance(dropdown.closest('.dropdown')[0]);
            if (dropdownInstance) {
                dropdownInstance.hide();
            }
        }
    }

    /* =====================================================
       CONFIGURAR TOGGLE STATUS
    ===================================================== */
    function configurarToggleStatusFuncionarios() {
        configurarToggleStatus({
            botaoSeletor: '.btn-toggle-status-funcionario',
            urlAPI: API_FUNCIONARIOS,
            metodo: 'PUT',
            tabelaId: 'tabelaFuncionarios',
            mensagens: {
                desativar: { 
                    titulo: 'Desativar Funcionário', 
                    texto: 'Tem certeza que deseja desativar este funcionário? Ele não poderá acessar o sistema.' 
                },
                reativar: { 
                    titulo: 'Reativar Funcionário', 
                    texto: 'Tem certeza que deseja reativar este funcionário?' 
                },
                sucesso: 'Status do funcionário alterado com sucesso!',
                erro: 'Erro ao alterar status do funcionário.'
            },
            onSuccess: function(response) {
                console.log('Status do funcionário alterado:', response);
            }
        });
    }

    // Inicializar tooltips quando a tabela é desenhada
    tabelaFuncionarios.on('draw', function() {
        $('[data-bs-toggle="tooltip"]').tooltip();
    });
});