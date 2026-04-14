/**
 * cargo-form.js
 * Gerenciamento do formulário de cargos com suporte à nova API JSON
 */

document.addEventListener('DOMContentLoaded', function() {
    const form = document.getElementById('formCargo');
    const cargoIdInput = document.getElementById('cargoId');
    const cargoId = cargoIdInput ? cargoIdInput.value : null;
    const isEditMode = !!cargoId;

    // Configuração do Toast do SweetAlert
    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // Inicialização do Select2
    $('#perfilCargo').select2({
        theme: 'bootstrap-5',
        placeholder: 'Selecione um perfil'
    });

    // 1. Carregar Perfis e então carregar dados se for edição
    carregarPerfis().then(() => {
        if (isEditMode) {
            carregarDadosCargo(cargoId);
        }
    });

    // 2. Manipulação do envio do formulário
    form.addEventListener('submit', async function(e) {
        e.preventDefault();

        const formData = new FormData(form);
        const payload = {
            nome: formData.get('nome'),
            descricao: formData.get('descricao'),
            salario_base: parseFloat(formData.get('salario_base')) || 0,
            ativo: parseInt(formData.get('ativo')),
            perfil_id: formData.get('perfil_cargo') // Alinhado com CargoRepository.php
        };

        const url = isEditMode ? `../api/cargos/${cargoId}` : '../api/cargos/';
        const method = isEditMode ? 'PUT' : 'POST';

        try {
            const response = await fetch(url, {
                method: method,
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify(payload)
            });

            const result = await response.json();

            if (!response.ok) throw new Error(result.error || 'Erro na requisição');

            Toast.fire({
                icon: 'success',
                title: isEditMode ? 'Cargo atualizado!' : 'Cargo cadastrado!'
            });

            // Se for novo cadastro, limpa o formulário em vez de voltar
            if (!isEditMode) {
                form.reset();
                $('#perfilCargo').val(null).trigger('change');
            }

        } catch (error) {
            Toast.fire({
                icon: 'error',
                title: error.message
            });
        }
    });
});

async function carregarPerfis() {
    try {
        const response = await fetch('../api/perfis/');
        const result = await response.json();
        const perfis = Array.isArray(result) ? result : result.data;

        const select = $('#perfilCargo');
        select.empty().append('<option value="">Nenhum perfil</option>');

        if (perfis) {
            perfis.forEach(p => select.append(new Option(p.nome, p.id)));
        }
        select.trigger('change');
    } catch (e) { console.error("Erro perfis:", e); }
}

async function carregarDadosCargo(id) {
    try {
        const response = await fetch(`../api/cargos/${id}`);
        const cargo = await response.json();

        document.getElementById('nomeCargo').value = cargo.nome || '';
        document.getElementById('descricaoCargo').value = cargo.descricao || '';
        document.getElementById('salarioBase').value = cargo.salario_base || '0.00';
        document.getElementById('ativoCargo').value = cargo.ativo;
        
        if (cargo.perfil_id) {
            $('#perfilCargo').val(cargo.perfil_id).trigger('change');
        }
    } catch (e) { console.error("Erro carregar cargo:", e); }
}

// Função voltar global
window.voltar = function(aba) {
    localStorage.setItem('abaConfigAtiva', aba);
    window.location.href = 'configuracoes';
};