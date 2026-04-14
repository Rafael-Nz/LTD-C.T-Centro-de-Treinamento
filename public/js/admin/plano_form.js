// plano_form.js - VERSÃO CORRIGIDA
// ================================
// CONFIGURAÇÃO DO TOAST
// ================================
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

// ================================
// VARIÁVEIS GLOBAIS
// ================================
let valorMask = null;

// ================================
// INICIALIZAÇÃO QUANDO DOCUMENTO ESTÁ PRONTO
// ================================
$(document).ready(function() {
    inicializarMascaraValor();
    inicializarSelect2();
    carregarDadosIniciais();
    configurarValidacoes();
});

// ================================
// FUNÇÕES DE INICIALIZAÇÃO
// ================================
function inicializarMascaraValor() {
    const valorInput = document.getElementById('valor');
    if (valorInput) {
        valorMask = IMask(valorInput, {
            mask: Number,
            scale: 2,
            thousandsSeparator: '.',
            padFractionalZeros: true,
            normalizeZeros: true,
            radix: ',',
            min: 0,
            max: 999999.99
        });
    }
}

function inicializarSelect2() {
    // Configuração para selects simples
    const selectsSimples = ['#periodicidade', '#status', '#limite_treinos_semana', '#renovacao_automatica'];
    selectsSimples.forEach(selector => {
        if ($(selector).length) {
            $(selector).select2({
                theme: 'bootstrap-5',
                width: '100%'
            });
        }
    });

    // Configuração especial para modalidades (múltipla seleção)
    if ($('#modalidades').length) {
        $('#modalidades').select2({
            theme: 'bootstrap-5',
            placeholder: 'Selecione as modalidades',
            width: '100%',
            closeOnSelect: false,
            allowClear: true
        });
    }
}

function carregarDadosIniciais() {
    // Carregar modalidades disponíveis
    carregarModalidades();

    // Se estiver em modo de edição, carregar dados do plano
    if (window.isEditMode && window.planoId) {
        carregarDadosPlano(window.planoId);
    }
}

function configurarValidacoes() {
    // Validação do formulário no submit
    $('#formPlano').on('submit', function(e) {
        e.preventDefault();
        
        if (validarFormulario()) {
            enviarFormulario();
        }
    });

    // Validação em tempo real para idade mínima/máxima
    $('#idade_minima, #idade_maxima').on('change', function() {
        validarIdades();
    });
}

// ================================
// FUNÇÕES DE VALIDAÇÃO
// ================================
function validarFormulario() {
    let isValid = true;
    const errors = [];

    // Validação do nome
    const nome = $('#nome').val().trim();
    if (nome.length < 3) {
        errors.push('Nome do plano deve ter pelo menos 3 caracteres');
        $('#nome').addClass('is-invalid');
        isValid = false;
    } else {
        $('#nome').removeClass('is-invalid');
    }

    // Validação do valor
    const valorNumerico = valorMask ? valorMask.typedValue : 0;
    if (valorNumerico <= 0) {
        errors.push('Valor deve ser maior que zero');
        $('#valor').addClass('is-invalid');
        isValid = false;
    } else {
        $('#valor').removeClass('is-invalid');
    }

    // Validação das idades
    if (!validarIdades()) {
        errors.push('Idade mínima não pode ser maior que idade máxima');
        isValid = false;
    }

    // Validação da periodicidade
    const periodicidade = $('#periodicidade').val();
    if (!periodicidade) {
        errors.push('Periodicidade é obrigatória');
        $('#periodicidade').addClass('is-invalid');
        isValid = false;
    } else {
        $('#periodicidade').removeClass('is-invalid');
    }

    // Validação do status
    const status = $('#status').val();
    if (!status) {
        errors.push('Status é obrigatório');
        $('#status').addClass('is-invalid');
        isValid = false;
    } else {
        $('#status').removeClass('is-invalid');
    }

    // Se houver erros, mostrar todos de uma vez
    if (errors.length > 0) {
        mostrarErrosValidacao(errors);
    }

    return isValid;
}

function validarIdades() {
    const idadeMinima = parseInt($('#idade_minima').val()) || 0;
    const idadeMaxima = parseInt($('#idade_maxima').val()) || 0;
    
    let isValid = true;
    
    if (idadeMinima > 0 && idadeMaxima > 0 && idadeMinima > idadeMaxima) {
        $('#idade_minima').addClass('is-invalid');
        $('#idade_maxima').addClass('is-invalid');
        isValid = false;
    } else {
        $('#idade_minima').removeClass('is-invalid');
        $('#idade_maxima').removeClass('is-invalid');
    }
    
    return isValid;
}

function mostrarErrosValidacao(errors) {
    let mensagem = '<ul class="text-start">';
    errors.forEach(error => {
        mensagem += `<li>${error}</li>`;
    });
    mensagem += '</ul>';

    Swal.fire({
        icon: 'warning',
        title: 'Corrija os seguintes erros:',
        html: mensagem,
        confirmButtonColor: '#d33'
    });
}

// ================================
// FUNÇÕES DE CARREGAMENTO DE DADOS
// ================================
function carregarModalidades() {
    $.ajax({
        url: '../api/modalidades/',
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Resposta da API de modalidades:', response);
            
            let modalidadesArray = [];
            
            // Extrair array de modalidades da resposta
            if (response && response.data && Array.isArray(response.data)) {
                modalidadesArray = response.data;
            } else if (Array.isArray(response)) {
                modalidadesArray = response;
            } else {
                console.error('Formato de resposta inesperado:', response);
                Toast.fire({
                    icon: 'error',
                    title: 'Formato de dados inválido'
                });
                return;
            }
            
            if (modalidadesArray.length > 0) {
                popularSelectModalidades(modalidadesArray);
            } else {
                popularSelectModalidades([]);
                Toast.fire({
                    icon: 'info',
                    title: 'Nenhuma modalidade encontrada'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar modalidades:', error);
            console.error('Detalhes:', xhr.responseText);
            
            Toast.fire({
                icon: 'error',
                title: 'Erro ao carregar modalidades',
                text: 'Verifique a conexão com o servidor'
            });
            
            // Popular com array vazio para evitar erros
            popularSelectModalidades([]);
        }
    });
}

function popularSelectModalidades(modalidadesArray) {
    const select = $('#modalidades');
    select.empty();
    
    if (!Array.isArray(modalidadesArray)) {
        console.error('modalidadesArray não é um array:', modalidadesArray);
        select.append('<option value="">Erro ao carregar modalidades</option>');
        return;
    }
    
    // Filtrar apenas modalidades ativas
    const modalidadesAtivas = modalidadesArray.filter(m => {
        return m && (m.ativo == 1 || m.ativo === true || m.status === 'ativo');
    });
    
    if (modalidadesAtivas.length === 0) {
        select.append('<option value="">Nenhuma modalidade disponível</option>');
        select.prop('disabled', true);
    } else {
        // Não adicionar opção vazia para select múltiplo
        
        // Adicionar modalidades
        modalidadesAtivas.forEach(modalidade => {
            if (modalidade && modalidade.id && modalidade.nome) {
                select.append(new Option(modalidade.nome, modalidade.id));
            }
        });
        select.prop('disabled', false);
    }
    
    // Reaplicar Select2
    select.select2({
        theme: 'bootstrap-5',
        placeholder: 'Selecione as modalidades',
        width: '100%',
        closeOnSelect: false,
        allowClear: true
    });
}

function carregarDadosPlano(id) {
    $.ajax({
        url: '../api/planos/' + id,
        method: 'GET',
        dataType: 'json',
        success: function(response) {
            console.log('Resposta da API de planos:', response);
            
            // Extrair dados do plano
            let plano = null;
            if (response && response.success && response.data) {
                plano = response.data;
            } else if (response && !response.success) {
                Toast.fire({
                    icon: 'error',
                    title: response.error || 'Erro ao carregar plano'
                });
                return;
            } else {
                plano = response;
            }
            
            if (!plano) {
                Toast.fire({
                    icon: 'error',
                    title: 'Dados do plano não encontrados'
                });
                return;
            }
            
            preencherCamposFormulario(plano);
        },
        error: function(xhr, status, error) {
            console.error('Erro ao carregar plano:', error);
            console.error('Detalhes:', xhr.responseText);
            
            if (xhr.status === 404) {
                Toast.fire({
                    icon: 'error',
                    title: 'Plano não encontrado'
                });
                setTimeout(() => {
                    window.location.href = 'planos';
                }, 2000);
            } else {
                Toast.fire({
                    icon: 'error',
                    title: 'Erro ao carregar dados do plano',
                    text: 'Tente novamente mais tarde'
                });
            }
        }
    });
}

function preencherCamposFormulario(plano) {
    console.log('Preenchendo formulário com:', plano);
    
    // Campos básicos
    $('#nome').val(plano.nome || '');
    $('#descricao').val(plano.descricao || '');
    
    // Selects
    if (plano.periodicidade) {
        $('#periodicidade').val(plano.periodicidade).trigger('change');
    }
    
    if (plano.status) {
        $('#status').val(plano.status).trigger('change');
    }
    
    // Valor
    if (plano.valor !== null && plano.valor !== undefined) {
        const valorNumerico = parseFloat(plano.valor);
        if (!isNaN(valorNumerico) && valorMask) {
            valorMask.typedValue = valorNumerico;
        }
    }
    
    // Outros selects
    if (plano.limite_treinos_semana !== undefined) {
        $('#limite_treinos_semana').val(plano.limite_treinos_semana.toString()).trigger('change');
    }
    
    if (plano.renovacao_automatica !== undefined) {
        const renovacaoValue = plano.renovacao_automatica ? '1' : '0';
        $('#renovacao_automatica').val(renovacaoValue).trigger('change');
    }
    
    // Idades
    $('#idade_minima').val(plano.idade_minima || 0);
    $('#idade_maxima').val(plano.idade_maxima || 99);
    
    // Textareas
    $('#restricoes').val(plano.restricoes || '');
    $('#observacoes').val(plano.observacoes || '');
    
    // Configurações de renovação
    $('#dias_aviso_vencimento').val(plano.dias_aviso_vencimento || 7);
    $('#multa_atraso').val(plano.multa_atraso || 2);
    $('#tolerancia_atraso').val(plano.tolerancia_atraso || 5);
    
    // Modalidades - IMPORTANTE: Preencher após as modalidades serem carregadas
    if (plano.modalidades_ids && Array.isArray(plano.modalidades_ids) && plano.modalidades_ids.length > 0) {
        // Esperar que as modalidades sejam carregadas
        const waitForModalidades = setInterval(() => {
            if ($('#modalidades option').length > 0) {
                clearInterval(waitForModalidades);
                
                // Converter para strings (Select2 espera strings)
                const idsStrings = plano.modalidades_ids.map(id => id.toString());
                
                // Verificar quais IDs existem no select
                const availableIds = [];
                $('#modalidades option').each(function() {
                    const optionValue = $(this).val();
                    if (idsStrings.includes(optionValue)) {
                        availableIds.push(optionValue);
                    }
                });
                
                if (availableIds.length > 0) {
                    $('#modalidades').val(availableIds).trigger('change');
                    console.log('Modalidades selecionadas:', availableIds);
                }
            }
        }, 100);
        
        // Timeout de segurança
        setTimeout(() => clearInterval(waitForModalidades), 3000);
    }
}

// ================================
// FUNÇÃO DE ENVIO DO FORMULÁRIO
// ================================
function enviarFormulario() {
    const id = $('#planoId').val();
    const method = id ? 'PUT' : 'POST';
    const url = id ? '../api/planos/' + id : '../api/planos/';
    
    const dadosFormulario = obterDadosFormulario();
    
    console.log('Enviando dados para:', url);
    console.log('Método:', method);
    console.log('Dados:', dadosFormulario);
    
    // Mostrar indicador de carregamento
    const submitButton = $('#formPlano button[type="submit"]');
    const originalText = submitButton.html();
    submitButton.html('<span class="spinner-border spinner-border-sm" role="status" aria-hidden="true"></span> Processando...');
    submitButton.prop('disabled', true);
    
    $.ajax({
        url: url,
        method: method,
        data: JSON.stringify(dadosFormulario),
        contentType: 'application/json; charset=UTF-8',
        dataType: 'json',
        success: function(response) {
            console.log('Resposta do servidor:', response);
            
            if (response && response.success) {
                Toast.fire({
                    icon: 'success',
                    title: id ? 'Plano atualizado com sucesso!' : 'Plano cadastrado com sucesso!'
                });
                
                setTimeout(() => {
                    window.location.href = 'planos';
                }, 1500);
            } else {
                Swal.fire({
                    icon: 'error',
                    title: 'Erro',
                    text: response.error || 'Erro ao salvar plano',
                    confirmButtonColor: '#d33'
                });
            }
        },
        error: function(xhr, status, error) {
            console.error('Erro ao salvar plano:', error);
            console.error('Status:', xhr.status);
            console.error('Resposta:', xhr.responseText);
            
            let mensagemErro = 'Erro ao salvar plano';
            
            try {
                const respostaJSON = JSON.parse(xhr.responseText);
                if (respostaJSON && respostaJSON.error) {
                    mensagemErro = respostaJSON.error;
                }
            } catch (e) {
                // Não conseguiu parsear JSON
            }
            
            Swal.fire({
                icon: 'error',
                title: 'Erro',
                text: mensagemErro,
                confirmButtonColor: '#d33'
            });
        },
        complete: function() {
            // Restaurar botão
            submitButton.html(originalText);
            submitButton.prop('disabled', false);
        }
    });
}

function obterDadosFormulario() {
    const dados = {
        nome: $('#nome').val().trim(),
        descricao: $('#descricao').val().trim() || null,
        periodicidade: $('#periodicidade').val(),
        valor: valorMask ? valorMask.typedValue : 0,
        limite_treinos_semana: parseInt($('#limite_treinos_semana').val()) || 0,
        status: $('#status').val(),
        idade_minima: parseInt($('#idade_minima').val()) || 0,
        idade_maxima: parseInt($('#idade_maxima').val()) || 99,
        restricoes: $('#restricoes').val().trim() || null,
        observacoes: $('#observacoes').val().trim() || null,
        dias_aviso_vencimento: parseInt($('#dias_aviso_vencimento').val()) || 7,
        multa_atraso: parseFloat($('#multa_atraso').val()) || 2,
        tolerancia_atraso: parseInt($('#tolerancia_atraso').val()) || 5
    };
    
    // Renovação automática
    const renovacaoValue = $('#renovacao_automatica').val();
    dados.renovacao_automatica = renovacaoValue === '1';
    
    // Modalidades - OBTER DIRETAMENTE DO SELECT2
    const modalidadesSelecionadas = $('#modalidades').val();
    if (modalidadesSelecionadas && modalidadesSelecionadas.length > 0) {
        // Converter para números
        dados.modalidades = modalidadesSelecionadas.map(id => {
            const numId = parseInt(id);
            return !isNaN(numId) && numId > 0 ? numId : null;
        }).filter(id => id !== null);
    } else {
        dados.modalidades = [];
    }
    
    console.log('Dados preparados para envio:', dados);
    return dados;
}

// ================================
// FUNÇÕES AUXILIARES
// ================================
function voltarParaPlanos() {
    window.location.href = 'planos';
}

// ================================
// VALIDAÇÃO EM TEMPO REAL
// ================================
$(document).on('input', 'input[required], select[required], textarea[required]', function() {
    const $this = $(this);
    const value = $this.val();
    
    if (!value || value.trim() === '') {
        $this.addClass('is-invalid');
    } else {
        $this.removeClass('is-invalid');
    }
});

// ================================
// ADICIONAR EVENT LISTENER PARA O BOTÃO VOLTAR
// ================================
$(document).on('click', 'a[href="planos"]', function(e) {
    e.preventDefault();
    voltarParaPlanos();
});