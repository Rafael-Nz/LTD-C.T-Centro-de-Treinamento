(() => {
    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const money = (v) => Number(v || 0).toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
    const formatDate = (v) => v ? v.split('-').reverse().join('/') : '-';

    function calcTempoMatricula(dataStr) {
        if (!dataStr) return 'Recente';
        const inicio = new Date(dataStr);
        const agora = new Date();
        const diffMeses = (agora.getFullYear() - inicio.getFullYear()) * 12 + (agora.getMonth() - inicio.getMonth());
        if (diffMeses <= 0) return 'Menos de 1 mês';
        if (diffMeses === 1) return '1 mês';
        if (diffMeses < 12) return `${diffMeses} meses`;
        const anos = Math.floor(diffMeses / 12);
        const restos = diffMeses % 12;
        return `${anos} ano${anos > 1 ? 's' : ''}${restos > 0 ? ` e ${restos} m` : ''}`;
    }

    async function request(url) {
        const response = await fetch(url, {
            headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
            credentials: 'same-origin',
        });
        const res = await response.json();
        if (!response.ok || !res.success) {
            throw new Error(res.message || 'Falha ao buscar dados.');
        }
        return res.data;
    }

    async function init() {
        try {
            // 1. Obter usuário logado
            const user = await request('/ctt/api/usuarios/me');
            if (!user) return;

            const alunoId = user.id;

            // 2. Obter dados específicos de Aluno (código de matrícula, turmas)
            let alunoData = null;
            try {
                alunoData = await request(`/ctt/api/alunos/${alunoId}`);
            } catch (err) {
                console.warn('Não foi possível carregar dados complementares do aluno:', err);
            }

            // 3. Obter dados de Contrato / Plano
            let contratos = [];
            try {
                contratos = await request('/ctt/api/financeiro/contratos');
            } catch (err) {
                console.warn('Não foi possível carregar contratos:', err);
            }

            // 4. Obter catálogo de turmas com horários para cruzar
            let todasTurmas = [];
            try {
                todasTurmas = await request('/ctt/api/turmas?simple=false');
                if (todasTurmas && todasTurmas.data) todasTurmas = todasTurmas.data;
            } catch (err) {
                console.warn('Não foi possível carregar horários das turmas:', err);
            }

            // 5. Verificar Anamnese
            let anamneseRespostas = [];
            try {
                anamneseRespostas = await request(`/ctt/api/anamnese/respostas/${alunoId}`);
            } catch (err) {
                // Normal se ainda não tiver respostas
            }

            renderMatricula(user, alunoData);
            renderPlano(contratos);
            renderTurmas(alunoData ? (alunoData.turmas || []) : [], todasTurmas);
            renderAnamnese(anamneseRespostas);
        } catch (error) {
            console.error('Erro ao inicializar página de matrícula:', error);
        }
    }

    function renderMatricula(user, aluno) {
        const codigoEl = document.getElementById('matriculaCodigo');
        const dataEl = document.getElementById('matriculaData');
        const tempoEl = document.getElementById('matriculaTempo');
        const statusEl = document.getElementById('matriculaStatus');
        const alunoNomeEl = document.getElementById('matriculaAlunoNome');

        const codigo = aluno?.codigo_matricula || `MAT-${String(user.id).padStart(6, '0')}`;
        const dataMatricula = aluno?.data_matricula || user.data_criacao?.split(' ')[0] || user.data_criacao;

        if (codigoEl) codigoEl.textContent = codigo;
        if (dataEl) dataEl.textContent = formatDate(dataMatricula);
        if (tempoEl) tempoEl.textContent = calcTempoMatricula(dataMatricula);
        if (alunoNomeEl) alunoNomeEl.textContent = `${user.nome || ''} ${user.sobrenome || ''}`.trim();

        if (statusEl) {
            const ativo = Number(user.ativo) === 1;
            statusEl.className = `status-pill ${ativo ? 'status-pill--active' : 'status-pill--inactive'}`;
            statusEl.textContent = ativo ? 'Matrícula Ativa' : 'Matrícula Inativa';
        }
    }

    function renderPlano(contratos) {
        const nomeEl = document.getElementById('planoNome');
        const valorEl = document.getElementById('planoValor');
        const periodicidadeEl = document.getElementById('planoPeriodicidade');
        const vencimentoEl = document.getElementById('planoVencimento');
        const statusEl = document.getElementById('planoStatus');

        if (!contratos || !contratos.length) {
            if (nomeEl) nomeEl.textContent = 'Nenhum plano ativo';
            if (valorEl) valorEl.textContent = 'R$ 0,00';
            if (periodicidadeEl) periodicidadeEl.textContent = '—';
            if (vencimentoEl) vencimentoEl.textContent = '—';
            if (statusEl) {
                statusEl.className = 'status-pill status-pill--pending';
                statusEl.textContent = 'Sem contrato';
            }
            return;
        }

        const ativo = contratos.find(c => c.status === 'ativo') || contratos[0];
        if (nomeEl) nomeEl.textContent = ativo.plano_nome || 'Plano de Treinamento';
        if (valorEl) valorEl.textContent = money(ativo.valor_contratado);
        if (periodicidadeEl) periodicidadeEl.textContent = ativo.periodicidade ? ativo.periodicidade.toUpperCase() : 'MENSAL';
        if (vencimentoEl) vencimentoEl.textContent = `Todo dia ${ativo.dia_vencimento || 10}`;

        if (statusEl) {
            const isAtivo = ativo.status === 'ativo';
            statusEl.className = `status-pill ${isAtivo ? 'status-pill--active' : 'status-pill--pending'}`;
            statusEl.textContent = isAtivo ? 'Plano Ativo' : ativo.status;
        }
    }

    function renderTurmas(turmasAluno, todasTurmas) {
        const container = document.getElementById('listaTurmasMatricula');
        if (!container) return;

        if (!turmasAluno || !turmasAluno.length) {
            container.innerHTML = `
                <div class="text-center py-4">
                    <i class="bi bi-calendar-x text-muted fs-1 mb-2"></i>
                    <h5 class="h6 text-muted mb-1">Nenhuma turma vinculada</h5>
                    <p class="small text-muted mb-0">Você ainda não está inscrito em nenhuma turma regular. Converse com a recepção para escolher seus horários.</p>
                </div>
            `;
            return;
        }

        // Mapear dados detalhados da turma se disponíveis
        const turmasMap = new Map();
        if (Array.isArray(todasTurmas)) {
            todasTurmas.forEach(t => turmasMap.set(Number(t.id), t));
        }

        container.innerHTML = turmasAluno.map(t => {
            const detalhe = turmasMap.get(Number(t.id));
            const horarios = detalhe?.horarios_resumo || detalhe?.horario_resumo || 'Consulte os horários';
            const instrutor = detalhe?.instrutor_nome || 'Instrutor Cross C.T';

            return `
                <div class="turma-item">
                    <div class="turma-info">
                        <div class="d-flex align-items-center gap-2">
                            <h4 class="turma-name">${esc(t.nome)}</h4>
                            <span class="status-pill status-pill--active">Inscrito</span>
                        </div>
                        <div class="turma-details mt-1">
                            <span><i class="bi bi-person text-danger"></i> ${esc(instrutor)}</span>
                            <span><i class="bi bi-clock text-primary"></i> <strong class="text-dark">${esc(horarios)}</strong></span>
                            ${t.data_inscricao ? `<span><i class="bi bi-calendar3"></i> Desde ${formatDate(t.data_inscricao)}</span>` : ''}
                        </div>
                    </div>
                    <a href="/ctt/horarios" class="btn btn-sm btn-outline-danger">
                        Ver Grade <i class="bi bi-arrow-right"></i>
                    </a>
                </div>
            `;
        }).join('');
    }

    function renderAnamnese(respostas) {
        const statusEl = document.getElementById('anamneseStatus');
        const descEl = document.getElementById('anamneseDesc');
        const iconEl = document.getElementById('anamneseIcon');

        const preenchida = Array.isArray(respostas) && respostas.length > 0;

        if (statusEl) {
            statusEl.className = `status-pill ${preenchida ? 'status-pill--active' : 'status-pill--pending'}`;
            statusEl.textContent = preenchida ? 'Preenchida' : 'Pendente';
        }

        if (iconEl) {
            iconEl.className = `bi ${preenchida ? 'bi-check-circle-fill text-success' : 'bi-exclamation-triangle-fill text-warning'} fs-2`;
        }

        if (descEl) {
            if (preenchida) {
                descEl.textContent = 'Sua ficha médica/anamnese está devidamente preenchida e arquivada pela equipe técnica.';
            } else {
                descEl.textContent = 'Sua ficha médica/anamnese ainda não foi respondida. Agende uma conversa com nosso avaliador ou instrutor.';
            }
        }
    }

    document.addEventListener('DOMContentLoaded', init);
})();
