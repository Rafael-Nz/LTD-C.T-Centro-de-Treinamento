$(document).ready(function () {
    const $modalidadeSelect = $('#filtroModalidadeSelect');
    const $statusSelect = $('#filtroStatusSelect');

    function initSelect2() {
        [$modalidadeSelect, $statusSelect].forEach($select => {
            if (!$select.length) return;

            $select.select2({
                theme: 'bootstrap-5',
                width: '100%',
                dropdownParent: $select.closest('.dropdown-menu'),
                allowClear: true
            });
        });
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
            return {
                modalidade_id: $('#filtroModalidadeSelect').val() || '',
                ativo: $('#filtroStatusSelect').val() || ''
            };
        },
        columns: [
            {
                data: 'nome',
                render: data => data || '—'
            },
            {
                data: 'modalidade_nome',
                render: data => data || '—'
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
                    const isAtivo = data.ativo == 1 || data.ativo === true;
                    const btnStatus = isAtivo
                        ? `<button class="btn btn-sm btn-danger btn-toggle-status" data-id="${data.id}" data-ativo="1" title="Desativar treino"><i class="ph ph-x"></i></button>`
                        : `<button class="btn btn-sm btn-success btn-toggle-status" data-id="${data.id}" data-ativo="0" title="Reativar treino"><i class="ph ph-check"></i></button>`;

                    return `
                        <div class="d-flex gap-2 justify-content-center flex-wrap">
                            <a href="/ctt/admin/treinos/editar/${data.id}" class="btn btn-sm btn-primary" title="Editar treino">
                                <i class="ph ph-pencil"></i>
                            </a>
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configTreinos);

    $('#aplicarFiltrosTreinos').on('click', function (e) {
        e.preventDefault();
        tabelas[configTreinos.tableId].ajax.reload();
        const dropdownButton = $('.dropdown-toggle[data-bs-toggle="dropdown"]');
        if (dropdownButton.length) {
            bootstrap.Dropdown.getInstance(dropdownButton[0])?.hide();
        }
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
