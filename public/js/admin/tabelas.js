/* =====================================================
   REGISTRO GLOBAL DAS TABELAS
===================================================== */
const tabelas = {};

/* =====================================================
   FUNÇÃO GENÉRICA DE DATATABLE (Padrão Configurações)
===================================================== */
function inicializarTabela(config) {
    const {
        tableId,
        ajaxUrl,
        emptyMessage,
        columns,
        searchInput,
        searchButton,
        editUrl,
        customFilter
    } = config;

    // Destruir tabela existente
    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
        $(`#${tableId}`).DataTable().destroy();
        $(`#${tableId} tbody`).empty(); 
    }

    // Inicializar DataTable
    const tabela = new DataTable(`#${tableId}`, {
        responsive: true,
        ordering: false,
        pageLength: 10,
        processing: true,
        serverSide: true,

        ajax: {
            url: ajaxUrl,
            type: 'GET',
            data: function (d) {
                if (typeof config.getFilters === 'function') {
                    const filtrosExtras = config.getFilters();
                    return $.extend({}, d, filtrosExtras);
                }
                return d;
            },
            dataSrc: function (json) {
                if (json.success && json.data.data) {
                    json.draw = json.data.draw;
                    json.recordsTotal = json.data.recordsTotal;
                    json.recordsFiltered = json.data.recordsFiltered;
                    return json.data.data;
                }
                return json.data || json;
            },
            error: function (xhr) {
                console.error("Erro ao carregar dados:", xhr);
                Swal.fire('Erro!', 'Não foi possível carregar os dados da tabela.', 'error');
            }
        },

        "drawCallback": function(settings) {
            // log para debug
            //console.log('Dados recebidos:', settings.json);
        },
        "recordsTotal": function(json) {
            return json.data.recordsTotal;
        },
        "recordsFiltered": function(json) {
            return json.data.recordsFiltered;
        },

        columns,

        language: {
            emptyTable: emptyMessage,
            info: "Mostrando _START_ até _END_ de _TOTAL_ registros",
            infoEmpty: "Mostrando 0 até 0 de 0 registros",
            infoFiltered: "(filtrado de _MAX_ registros)",
            lengthMenu: "Mostrar _MENU_ registros",
            loadingRecords: "Carregando...",
            processing: "Processando...",
            zeroRecords: "Nenhum registro encontrado",
            paginate: {
                first: "«",
                last: "»",
                next: "›",
                previous: "‹"
            }
        },

        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        }
    });

    // Registrar tabela globalmente
    tabelas[tableId] = tabela;

    /* ===================== BUSCA ===================== */
    if (searchInput) {
        $(searchInput).on('keypress', function (e) {
            if (e.which === 13) {
                tabela.search(this.value).draw();
            }
        });
    }

    if (searchButton) {
        $(searchButton).on('click', function () {
            tabela.search($(searchInput).val()).draw();
        });
    }

    if (searchInput) {
        $(searchInput).on('search', function () {
            if ($(this).val() === "") {
                tabela.search('').draw();
            }
        });
    }

    /* ===================== FILTRO PERSONALIZADO ===================== */
    if (customFilter) {
        $.fn.dataTable.ext.search.pop(); // Remove filtro anterior, se existir
        $.fn.dataTable.ext.search.push(customFilter);
    }

    /* ===================== EDITAR ===================== */
    if (editUrl) {
        $(`#${tableId}`).on('click', '.btn-editar', function () {
            const id = $(this).data('id');
            window.location.href = `${editUrl}?id=${id}`;
        });
    }

    // Re-inicializar tooltips após carregar dados
    tabela.on('draw', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(t => new bootstrap.Tooltip(t));
    });

    return tabela;
}

/* =====================================================
   FUNÇÕES AUXILIARES
===================================================== */
function formatarData(dataString) {
    if (!dataString) return '—';
    const data = new Date(dataString);
    return data.toLocaleDateString('pt-BR');
}

function formatarMoeda(valor) {
    if (!valor && valor !== 0) return '—';
    const num = parseFloat(valor);
    if (isNaN(num)) return '—';
    return `R$ ${num.toLocaleString('pt-BR', { minimumFractionDigits: 2, maximumFractionDigits: 2 })}`;
}

function formatarStatus(ativo) {
    const isAtivo = (ativo == 1 || ativo === true || ativo === '1');
    return isAtivo
        ? '<span class="badge bg-success-subtle text-success-emphasis">Ativo</span>'
        : '<span class="badge bg-danger-subtle text-danger-emphasis">Inativo</span>';
}

function formatarPeriodicidade(periodicidade) {
    const periodicidades = {
        'semanal': 'Semanal',
        'quinzenal': 'Quinzenal',
        'mensal': 'Mensal',
        'bimestral': 'Bimestral',
        'trimestral': 'Trimestral',
        'semestral': 'Semestral',
        'anual': 'Anual'
    };
    return periodicidades[periodicidade] || periodicidade.charAt(0).toUpperCase() + periodicidade.slice(1);
}

/* =====================================================
   RECARREGAR TABELA
===================================================== */
function recarregarTabela(tableId) {
    if (tabelas[tableId]) {
        tabelas[tableId].ajax.reload(null, false);
    }
}

function configurarToggleStatus(config) {
    const {
        botaoSeletor,
        urlAPI,
        tabelaId,
        rotaDesativar = '/desativar',  // Nova opção: rota para desativar
        rotaReativar = '/reativar',    // Nova opção: rota para reativar
        mensagens = {
            desativar: { titulo: 'Confirmar desativação', texto: 'Tem certeza que deseja desativar este registro?' },
            reativar: { titulo: 'Confirmar reativação', texto: 'Tem certeza que deseja reativar este registro?' },
            sucesso: 'Status alterado com sucesso!',
            erro: 'Erro ao alterar status do registro.'
        },
        onSuccess = null
    } = config;

    $(document).on('click', botaoSeletor, function (e) {
        e.preventDefault(); // Prevenir comportamento padrão
        
        const $btn = $(this);
        const id = $btn.data('id');
        
        // IMPORTANTE: data-ativo pode ser string, converter corretamente
        const ativo = parseInt($btn.data('ativo'), 10);
        const isDesativando = (ativo === 1);

        const acao = isDesativando ? 'desativar' : 'reativar';
        const mensagem = isDesativando ? mensagens.desativar : mensagens.reativar;

        Swal.fire({
            title: mensagem.titulo,
            text: mensagem.texto,
            icon: 'question',
            showCancelButton: true,
            confirmButtonText: `Sim, ${acao}`,
            cancelButtonText: 'Cancelar',
            confirmButtonColor: isDesativando ? '#dc3545' : '#28a745'
        }).then(res => {
            if (!res.isConfirmed) return;

            // URLs usando PUT para ambas as ações
            let url, method;
            
            if (isDesativando) {
                url = `${urlAPI}/${id}${rotaDesativar}`;
                method = 'PUT';
            } else {
                url = `${urlAPI}/${id}${rotaReativar}`;
                method = 'PUT';
            }

            $.ajax({
                url: url,
                method: method,
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
                
                // Adicionar headers para evitar cache
                cache: false,
                
                success: (response) => {
                    Swal.fire({
                        title: 'Sucesso!',
                        text: mensagens.sucesso,
                        icon: 'success',
                        timer: 2000,
                        showConfirmButton: false
                    });

                    if (tabelas[tabelaId]) {
                        tabelas[tabelaId].ajax.reload(null, false);
                    }

                    if (typeof onSuccess === 'function') {
                        onSuccess(response);
                    }
                },

                error: (xhr) => {
                    let errorMsg = mensagens.erro;
                    
                    if (xhr.responseJSON?.message) {
                        errorMsg = xhr.responseJSON.message;
                    } else if (xhr.responseJSON?.error) {
                        errorMsg = xhr.responseJSON.error;
                    } else if (xhr.status === 404) {
                        errorMsg = 'Rota não encontrada. Verifique a URL.';
                    } else if (xhr.status === 500) {
                        errorMsg = 'Erro interno do servidor.';
                    }
                    
                    Swal.fire('Erro!', errorMsg, 'error');
                    console.error('Erro detalhado:', xhr);
                }
            });
        });
    });
}

