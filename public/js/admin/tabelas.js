const tabelas = {};

function extrairListaApi(payload) {
    if (!payload) return [];
    if (Array.isArray(payload)) return payload;
    if (Array.isArray(payload.data)) return payload.data;
    if (payload.data && Array.isArray(payload.data.data)) return payload.data.data;
    return [];
}

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

    if ($.fn.DataTable.isDataTable(`#${tableId}`)) {
        $(`#${tableId}`).DataTable().destroy();
        $(`#${tableId} tbody`).empty();
    }

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
                    return $.extend({}, d, config.getFilters());
                }
                return d;
            },
            dataSrc: function (json) {
                if (Array.isArray(json?.data)) {
                    return json.data;
                }

                if (json && Array.isArray(json.data?.data)) {
                    json.draw = json.data.draw ?? json.draw;
                    json.recordsTotal = json.data.recordsTotal ?? json.recordsTotal;
                    json.recordsFiltered = json.data.recordsFiltered ?? json.recordsFiltered;
                    return json.data.data;
                }

                if (Array.isArray(json)) {
                    return json;
                }

                return [];
            },
            error: function (xhr) {
                console.error('Erro ao carregar dados:', xhr);
                Swal.fire('Erro!', 'Nao foi possivel carregar os dados da tabela.', 'error');
            }
        },

        columns,

        language: {
            emptyTable: emptyMessage,
            info: 'Mostrando _START_ ate _END_ de _TOTAL_ registros',
            infoEmpty: 'Mostrando 0 ate 0 de 0 registros',
            infoFiltered: '(filtrado de _MAX_ registros)',
            lengthMenu: 'Mostrar _MENU_ registros',
            loadingRecords: 'Carregando...',
            processing: 'Processando...',
            zeroRecords: 'Nenhum registro encontrado',
            paginate: {
                first: '«',
                last: '»',
                next: '›',
                previous: '‹'
            }
        },

        layout: {
            topStart: null,
            topEnd: null,
            bottomStart: 'info',
            bottomEnd: 'paging'
        }
    });

    tabelas[tableId] = tabela;

    if (searchInput) {
        $(searchInput).on('keypress', function (e) {
            if (e.which === 13) {
                tabela.search(this.value).draw();
            }
        });

        $(searchInput).on('search', function () {
            if ($(this).val() === '') {
                tabela.search('').draw();
            }
        });
    }

    if (searchButton) {
        $(searchButton).on('click', function () {
            tabela.search($(searchInput).val()).draw();
        });
    }

    if (customFilter) {
        $.fn.dataTable.ext.search.pop();
        $.fn.dataTable.ext.search.push(customFilter);
    }

    if (editUrl) {
        $(`#${tableId}`).on('click', '.btn-editar', function () {
            const id = $(this).data('id');
            window.location.href = `${editUrl}?id=${id}`;
        });
    }

    tabela.on('draw', function () {
        const tooltipTriggerList = [].slice.call(document.querySelectorAll('[data-bs-toggle="tooltip"]'));
        tooltipTriggerList.map(t => new bootstrap.Tooltip(t));
    });

    return tabela;
}

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
    const isAtivo = ativo == 1 || ativo === true || ativo === '1';
    return isAtivo
        ? '<span class="badge bg-success-subtle text-success-emphasis">Ativo</span>'
        : '<span class="badge bg-danger-subtle text-danger-emphasis">Inativo</span>';
}

function formatarPeriodicidade(periodicidade) {
    const periodicidades = {
        semanal: 'Semanal',
        quinzenal: 'Quinzenal',
        mensal: 'Mensal',
        bimestral: 'Bimestral',
        trimestral: 'Trimestral',
        semestral: 'Semestral',
        anual: 'Anual'
    };
    return periodicidades[periodicidade] || periodicidade.charAt(0).toUpperCase() + periodicidade.slice(1);
}

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
        rotaDesativar = '/desativar',
        rotaReativar = '/reativar',
        metodoDesativar = 'PUT',
        metodoReativar = 'PUT',
        mensagens = {
            desativar: { titulo: 'Confirmar desativacao', texto: 'Tem certeza que deseja desativar este registro?' },
            reativar: { titulo: 'Confirmar reativacao', texto: 'Tem certeza que deseja reativar este registro?' },
            sucesso: 'Status alterado com sucesso!',
            erro: 'Erro ao alterar status do registro.'
        },
        onSuccess = null
    } = config;

    $(document).on('click', botaoSeletor, function (e) {
        e.preventDefault();

        const $btn = $(this);
        const id = $btn.data('id');
        const ativo = parseInt($btn.data('ativo'), 10);
        const isDesativando = ativo === 1;
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

            const url = isDesativando
                ? `${urlAPI}/${id}${rotaDesativar}`
                : `${urlAPI}/${id}${rotaReativar}`;
            const method = isDesativando ? metodoDesativar : metodoReativar;

            $.ajax({
                url,
                method,
                contentType: 'application/json; charset=UTF-8',
                dataType: 'json',
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
                        errorMsg = 'Rota nao encontrada. Verifique a URL.';
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
