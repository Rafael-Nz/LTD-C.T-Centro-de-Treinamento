$(document).ready(function () {
// 1. Inicializar a tabela
    const configAlunos = {
        tableId: 'tabelaAlunos',
        ajaxUrl: '/ctt/api/alunos',
        getFilters: function() {
            // Criamos um array para armazenar os status selecionados
            let statusSelecionados = [];
            
            $('.filtro-status:checked').each(function() {
                // Se o valor no HTML for "Ativo", enviamos 1. Se "Inativo", enviamos 0.
                statusSelecionados.push($(this).val() === 'Ativo' ? 1 : 0);
            });

            return {
                // Enviamos como string separada por vírgula para o PHP (ex: "1,0")
                status: statusSelecionados.join(',')
            };
        },
        emptyMessage: 'Nenhum aluno encontrado.',
        searchInput: '#campoBusca',
        columns: [
            { 
                data: null,
                render: data => `${data.nome} ${data.sobrenome}`
            },
            { 
                data: 'codigo_matricula',
                className: 'text-center',
            },
            { 
                data: 'data_matricula',
                className: 'text-center',
                render: data => typeof formatarData === 'function' ? formatarData(data) : data
            },
            { 
                data: 'ativo',
                className: 'text-center',
                render: (data) => typeof formatarStatus === 'function' ? formatarStatus(data) : data
            },
            {
                data: null,
                className: 'text-center',
                orderable: false,
                render: function (data) {
                    if (!data) return '';
                    const isAtivo = (data.ativo == 1 || data.ativo === true);
                    const btnStatus = isAtivo 
                        ? `<button class=\"btn btn-sm btn-danger btn-toggle-status\" data-id=\"${data.id}\" data-ativo=\"1\" title=\"Desativar\"><i class=\"ph ph-x\"></i></button>`
                        : `<button class=\"btn btn-sm btn-success btn-toggle-status\" data-id=\"${data.id}\" data-ativo=\"0\" title=\"Reativar\"><i class=\"ph ph-check\"></i></button>`;

                    return `
                        <div class=\"d-flex gap-2 justify-content-center\">
                            <a href=\"/ctt/admin/alunos/editar/${data.id}\" class=\"btn btn-sm btn-primary\">
                                <i class=\"ph ph-pencil\"></i>
                            </a>
                            ${btnStatus}
                        </div>
                    `;
                }
            }
        ]
    };

    inicializarTabela(configAlunos);

    // Ação do Botão "Aplicar Filtros"
    $('#aplicarFiltros').on('click', function (e) {
        e.preventDefault();
        // Recarrega os dados da tabela chamando o getFilters() novamente
        tabelas[configAlunos.tableId].ajax.reload();
        
        // Opcional: fechar o dropdown após clicar
        bootstrap.Dropdown.getInstance($('.dropdown-toggle')).hide();
    });
    
    $('#campoBusca').on('keyup', function () {
        tabelas[configAlunos.tableId].search(this.value).draw();
    });

    configurarToggleStatus({
        botaoSeletor: '.btn-toggle-status',
        urlAPI: '/ctt/api/alunos',
        tabelaId: 'tabelaAlunos',
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