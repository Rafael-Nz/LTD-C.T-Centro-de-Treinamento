$(document).ready(function () {
    const $modalidadeSelect = $('#filtroModalidadeSelect');

    function initSelect2() {
        if ($modalidadeSelect.length) {
            $modalidadeSelect.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $modalidadeSelect.closest('.dropdown-menu'),
                allowClear: true,
                placeholder: 'Todas as modalidades'
            });
        }
    }

    async function loadModalidades() {
        try {
            const response = await fetch('/ctt/api/modalidades?draw=1&start=0&length=1000');
            const payload = await response.json();
            const items = extrairListaApi(payload);

            $modalidadeSelect.empty().append(new Option('Todas as modalidades', '', false, false));

            items.forEach(item => {
                $modalidadeSelect.append(new Option(item.nome, item.id, false, false));
            });

            $modalidadeSelect.trigger('change');
        } catch (error) {
            console.error('Erro ao carregar modalidades:', error);
        }
    }

    initSelect2();
    loadModalidades();

    const configTreinos = {
        tableId: 'tabelaTreinos',
        ajaxUrl: '/ctt/api/treinos',
        emptyMessage: 'Nenhum treino encontrado.',
        searchInput: '#campoBusca',
        searchButton: '#botaoBuscar',
        getFilters: function () {
            const statusSelecionados = $('.filtro-status-treino:checked').map((_, el) => el.value).get();
            return {
                modalidade_id: $modalidadeSelect.val() || '',
                ativo: statusSelecionados.length ? statusSelecionados.join(',') : ''
            };
        },
        columns: [
            {
                data: 'nome',
                render: data => data ? escapeHtml(data) : '—'
            },
            {
                data: 'modalidade_nome',
                render: data => data ? escapeHtml(data) : '—'
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
                    const canEdit = typeof window.can === 'function' ? window.can('treinos.editar') : true;
                    const canStatus = typeof window.can === 'function' ? window.can('treinos.desativar') : true;

                    const isAtivo = data.ativo == 1 || data.ativo === true;
                    const btnStatus = canStatus
                        ? (isAtivo
                            ? `<button type="button" class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar treino" aria-label="Desativar treino"><i class="ph ph-x" aria-hidden="true"></i></button>`
                            : `<button type="button" class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar treino" aria-label="Reativar treino"><i class="ph ph-check" aria-hidden="true"></i></button>`)
                        : '';

                    const btnEdit = canEdit
                        ? `<a href="/ctt/admin/treinos/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar treino" aria-label="Editar treino"><i class="ph ph-pencil" aria-hidden="true"></i></a>`
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

    inicializarTabela(configTreinos);

    const reloadTreinos = () => {
        tabelas[configTreinos.tableId].ajax.reload();
        bootstrap.Dropdown.getInstance(document.getElementById('filtrosTreinosBtn'))?.hide();
    };

    $('#aplicarFiltrosTreinos').on('click', function (e) {
        e.preventDefault();
        reloadTreinos();
    });

    $('#limparFiltrosTreinos').on('click', function (e) {
        e.preventDefault();
        $modalidadeSelect.val('').trigger('change');
        $('.filtro-status-treino').prop('checked', false);
        $('#campoBusca').val('');
        tabelas[configTreinos.tableId].search('');
        reloadTreinos();
    });

    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status',
        urlAPI: '/ctt/api/treinos',
        tabelaId: 'tabelaTreinos',
        rotaDesativar: '',
        rotaReativar: '/reativar',
        metodoDesativar: 'DELETE',
        metodoReativar: 'PUT',
        mensagens: {
            desativar: {
                titulo: 'Confirmar desativacao',
                texto: 'Este treino ficara inativo e nao podera ser usado em novos agendamentos.'
            },
            reativar: {
                titulo: 'Confirmar reativacao',
                texto: 'Este treino voltara a ficar disponivel para uso.'
            },
            sucesso: 'Status do treino alterado com sucesso!',
            erro: 'Erro ao alterar o status do treino.'
        }
    });
});
