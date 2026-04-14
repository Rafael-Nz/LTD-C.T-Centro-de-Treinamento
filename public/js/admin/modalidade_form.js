const Toast = Swal.mixin({
    toast: true,
    position: 'top-end',
    showConfirmButton: false,
    timer: 3000,
    timerProgressBar: true,
    didOpen: (toast) => {
        toast.addEventListener('mouseenter', Swal.stopTimer);
        toast.addEventListener('mouseleave', Swal.resumeTimer);
    }
});

function voltar(aba) {
    // Salva a aba desejada no localStorage
    if (aba) {
        localStorage.setItem('abaConfigAtiva', aba);
    }
    // Redireciona para configurações
    window.location.href = 'configuracoes.php';
}

function carregarDadosModalidade(id) {
    $.ajax({
        url: `../api/modalidades/${id}`,
        method: 'GET',
        dataType: 'json',
        beforeSend: function() {
            // Mostra indicador de carregamento
            $('#nome').prop('disabled', true);
            $('#descricao').prop('disabled', true);
            $('#ativo').prop('disabled', true);
        },
        success: function(response) {
            // Verifica se a resposta é um objeto direto ou tem estrutura {data: ...}
            const modalidade = response.data || response;
            
            // Preenche os campos do formulário
            $('#nome').val(modalidade.nome || '');
            $('#descricao').val(modalidade.descricao || '');
            $('#ativo').val(modalidade.ativo?.toString() || '1');
            
            // Habilita os campos novamente
            $('#nome').prop('disabled', false);
            $('#descricao').prop('disabled', false);
            $('#ativo').prop('disabled', false);
        },
        error: function(xhr) {
            let errorMessage = 'Erro ao carregar dados da modalidade';
            
            if (xhr.responseJSON?.error) {
                errorMessage = xhr.responseJSON.error;
            } else if (xhr.status === 404) {
                errorMessage = 'Modalidade não encontrada';
            }
            
            Toast.fire({
                icon: 'error',
                title: errorMessage
            });
            
            console.error('Erro:', xhr.responseText);
            
            // Redireciona após erro
            setTimeout(function() {
                voltar('modalidades');
            }, 2000);
        }
    });
}

function validarFormulario() {
    const nome = $('#nome').val().trim();
    
    if (!nome) {
        Toast.fire({
            icon: 'warning',
            title: 'Nome da modalidade é obrigatório'
        });
        $('#nome').focus();
        return false;
    }
    
    return true;
}

function prepararDadosEnvio() {
    const id = $('#modalidadeId').val();
    const dados = {
        nome: $('#nome').val().trim(),
        descricao: $('#descricao').val().trim(),
        ativo: $('#ativo').val()
    };
    
    // Se for edição, adiciona o ID
    if (id) {
        dados.id = parseInt(id);
    }
    
    return dados;
}

function enviarDadosModalidade(dados, isEditMode) {
    const id = $('#modalidadeId').val();
    const method = isEditMode ? 'PUT' : 'POST';
    let url = '../api/modalidades/';
    
    // Para edição, adiciona o ID à URL
    if (isEditMode) {
        url += id;
    }

    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(dados),
        contentType: 'application/json; charset=UTF-8',
        dataType: 'json',
        beforeSend: function() {
            // Desabilita o botão de submit durante o envio
            $('#formModalidade button[type="submit"]').prop('disabled', true).html(
                '<span class="spinner-border spinner-border-sm me-2" role="status" aria-hidden="true"></span>Processando...'
            );
        },
        success: function(response) {
            const mensagemSucesso = isEditMode ? 
                'Modalidade atualizada com sucesso!' : 
                'Modalidade cadastrada com sucesso!';
            
            Toast.fire({
                icon: 'success',
                title: mensagemSucesso
            });

            // Redireciona após 2 segundos
            setTimeout(function() {
                voltar('modalidades');
            }, 2000);
        },
        error: function(xhr) {
            let errorMsg = 'Erro inesperado ao salvar modalidade';
            
            if (xhr.responseJSON?.error) {
                errorMsg = xhr.responseJSON.error;
            } else if (xhr.status === 409) {
                errorMsg = 'Já existe uma modalidade com este nome';
            } else if (xhr.status === 422) {
                errorMsg = 'Dados inválidos. Verifique os campos obrigatórios';
            } else if (xhr.status === 404 && isEditMode) {
                errorMsg = 'Modalidade não encontrada para edição';
            }
            
            Toast.fire({
                icon: 'error',
                title: errorMsg
            });
            
            console.error('Erro detalhado:', xhr.responseText);
        },
        complete: function() {
            // Reabilita o botão de submit
            const textoBotao = isEditMode ? 'Salvar Alterações' : 'Cadastrar';
            $('#formModalidade button[type="submit"]').prop('disabled', false).html(textoBotao);
        }
    });
}

function inicializarFormulario() {
    const id = $('#modalidadeId').val();
    const isEditMode = !!id;
    
    // Se estiver em modo de edição, carrega os dados da modalidade
    if (isEditMode) {
        carregarDadosModalidade(id);
    }
    
    // Configura o evento de submit do formulário
    $('#formModalidade').on('submit', function(e) {
        e.preventDefault();
        
        // Valida o formulário
        if (!validarFormulario()) {
            return;
        }
        
        // Prepara e envia os dados
        const dados = prepararDadosEnvio();
        enviarDadosModalidade(dados, isEditMode);
    });
    
    // Configura o evento de click no botão voltar
    $('.btn-voltar').on('click', function() {
        voltar('modalidades');
    });
    
    // Configura evento para limpar mensagens de erro ao editar campos
    $('#nome, #descricao, #ativo').on('input', function() {
        $(this).removeClass('is-invalid');
    });
}

// Inicializa quando o documento estiver pronto
$(document).ready(function() {
    inicializarFormulario();
});