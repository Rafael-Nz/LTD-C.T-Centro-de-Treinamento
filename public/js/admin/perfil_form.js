document.addEventListener("DOMContentLoaded", () => {
    const permissoesContainer = document.querySelector('.permissoes-container');
    const inputBusca = document.getElementById('searchPermissoes');
    const btnLimpar = document.getElementById('clearSearch');
    const formPerfil = document.getElementById('formPerfil');
    const tituloPagina = document.querySelector('h1.h4');
    
    let totalGeralPermissoes = 0;
    let perfilId = null;
    let permissoesSelecionadasCache = []; // Cache das permissões selecionadas

    const Toast = Swal.mixin({
        toast: true,
        position: 'top-end',
        showConfirmButton: false,
        timer: 3000,
        timerProgressBar: true
    });

    // 1. Verificar se está em modo de edição
    function verificarModoEdicao() {
        const urlParams = new URLSearchParams(window.location.search);
        const id = urlParams.get('id');
        
        if (id) {
            perfilId = parseInt(id);
            tituloPagina.textContent = 'Editar Perfil';
            
            btnSalvarPerfil.textContent = 'Salvar Alterações';

            // Mudar ação do formulário para UPDATE
            formPerfil.action = `../api/perfis/${id}`;
            
            // Carregar perfil
            carregarPerfil(perfilId);
        } else {
            // Modo criação: carregar permissões imediatamente
            carregarPermissoes();
        }
    }

    // 2. Carregar dados do perfil para edição
    function carregarPerfil(id) {
        fetch(`../api/perfis/${id}`)
            .then(response => {
                if (!response.ok) throw new Error('Perfil não encontrado');
                return response.json();
            })
            .then(perfil => {
                // Preencher campos do formulário
                document.getElementById('nomePerfil').value = perfil.nome || '';
                document.getElementById('descricaoPerfil').value = perfil.descricao || '';
                document.getElementById('ativoPerfil').value = perfil.ativo || '1';
                
                // Armazenar as permissões selecionadas em cache
                permissoesSelecionadasCache = perfil.permissoes_ids || [];
                
                // Agora carregar as permissões com as selecionadas
                carregarPermissoes();
            })
            .catch(error => {
                console.error('Erro:', error);
                Toast.fire({ icon: 'error', title: 'Erro ao carregar perfil' });
                setTimeout(() => voltar('perfis'), 2000);
            });
    }

    // 3. Carregar Permissões via API
    function carregarPermissoes() {
        fetch('../api/permissoes/?agrupar=true')
            .then(response => response.json())
            .then(result => {
                renderizarPermissoes(result.data);
            })
            .catch(error => {
                console.error('Erro:', error);
                permissoesContainer.innerHTML = '<div class="no-results">Erro ao carregar permissões.</div>';
            });
    }

    // 4. Renderizar HTML (com marcação das permissões selecionadas)
    function renderizarPermissoes(modulos) {
        if (!modulos || modulos.length === 0) return;

        let html = '';
        totalGeralPermissoes = 0;

        modulos.forEach(grupo => {
            html += `<div class="modulo-group"><div class="modulo-header">${grupo.modulo}</div>`;

            grupo.permissoes.forEach(p => {
                totalGeralPermissoes++;
                const labelPrincipal = p.descricao || p.nome;
                const isChecked = permissoesSelecionadasCache.includes(p.id);

                html += `
                    <div class="permissao-item" data-busca="${p.nome.toLowerCase()} ${labelPrincipal.toLowerCase()}">
                        <div class="permissao-info">
                            <span class="fw-bold text-dark">${labelPrincipal}</span>
                            <small class="text-muted d-block" style="font-size: 0.7rem;">Cod: ${p.nome}</small>
                        </div>
                        <div class="form-check form-switch">
                            <input class="form-check-input chkPerm" type="checkbox" 
                                   name="permissoes[]" value="${p.id}" id="perm_${p.id}"
                                   ${isChecked ? 'checked' : ''}>
                        </div>
                    </div>`;
            });
            html += `</div>`;
        });

        permissoesContainer.innerHTML = html;
        document.getElementById('totalPermissoes').textContent = totalGeralPermissoes;
        
        // Atualizar contador de selecionadas
        const selecionadas = permissoesSelecionadasCache.length;
        document.getElementById('permissoesSelecionadas').textContent = selecionadas;

        // Adicionar listener para atualizar contadores
        document.querySelectorAll('.chkPerm').forEach(chk => {
            chk.addEventListener('change', atualizarContadores);
        });
    }

    function atualizarContadores() {
        const selecionadas = document.querySelectorAll('.chkPerm:checked').length;
        document.getElementById('permissoesSelecionadas').textContent = selecionadas;
    }

    // 5. Lógica de Busca e Filtro
    function filtrar(termo) {
        const termoLower = termo.toLowerCase().trim();
        const itens = document.querySelectorAll('.permissao-item');
        
        itens.forEach(item => {
            const matches = item.getAttribute('data-busca').includes(termoLower);
            item.style.setProperty('display', matches ? 'flex' : 'none', 'important');
        });

        document.querySelectorAll('.modulo-group').forEach(grupo => {
            const temVisivel = Array.from(grupo.querySelectorAll('.permissao-item'))
                                    .some(item => item.style.display !== 'none');
            grupo.style.display = temVisivel ? 'block' : 'none';
        });
    }

    // Listener do Input
    inputBusca.addEventListener('input', (e) => filtrar(e.target.value));

    // 6. Lógica do Botão de "X" (Limpar)
    btnLimpar.addEventListener('click', () => {
        inputBusca.value = '';
        inputBusca.focus();
        filtrar('');
    });

    // 7. Enviar Form (JSON) - Suporte para CREATE e UPDATE
    formPerfil.addEventListener('submit', function(e) {
        e.preventDefault();
        
        const permissoesIds = Array.from(document.querySelectorAll('.chkPerm:checked'))
                                   .map(input => parseInt(input.value));

        const payload = {
            nome: document.getElementById('nomePerfil').value,
            descricao: document.getElementById('descricaoPerfil').value,
            ativo: parseInt(document.getElementById('ativoPerfil').value),
            permissoes: permissoesIds
        };

        const method = perfilId ? 'PUT' : 'POST';
        const url = perfilId ? this.action : this.action;

        fetch(url, {
            method: method,
            headers: { 'Content-Type': 'application/json' },
            body: JSON.stringify(payload)
        })
        .then(async res => {
            const data = await res.json();
            if (!res.ok) {
                throw new Error(data.error || 'Erro ao salvar perfil');
            }
            return data;
        })
        .then(data => {
            const mensagem = perfilId ? 'Perfil atualizado com sucesso!' : 'Perfil criado com sucesso!';
            Toast.fire({ icon: 'success', title: mensagem });
            
            // Redirecionar de volta após 1.5 segundos
            setTimeout(() => voltar('perfis'), 1500);
        })
        .catch(err => {
            Toast.fire({ icon: 'error', title: err.message });
        });
    });

    // 8. Função para voltar à lista
    window.voltar = function(aba) {
        localStorage.setItem('abaConfigAtiva', aba);
        window.location.href = 'configuracoes';
    };

    // 9. Inicializar
    verificarModoEdicao();
});