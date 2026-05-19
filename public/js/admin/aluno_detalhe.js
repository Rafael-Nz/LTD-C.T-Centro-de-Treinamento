document.addEventListener('DOMContentLoaded', function () {
    const mainContent = document.getElementById('mainContent');
    const alunoId = Number(mainContent?.dataset.alunoId || 0);
    const errorAlert = document.getElementById('alunoDetalheError');
    const alunoDadosGrid = document.getElementById('alunoDadosGrid');
    const avaliacoesList = document.getElementById('avaliacoesList');
    const anamneseList = document.getElementById('anamneseList');

    function parseApiData(payload) {
        if (!payload) return null;
        return payload.data ?? payload;
    }

    function parseApiList(payload) {
        const parsed = parseApiData(payload);
        return Array.isArray(parsed) ? parsed : [];
    }

    function showError(message) {
        if (!errorAlert) return;
        errorAlert.textContent = message;
        errorAlert.classList.remove('d-none');
    }

    function escapeHtml(value) {
        return String(value ?? '')
            .replace(/&/g, '&amp;')
            .replace(/</g, '&lt;')
            .replace(/>/g, '&gt;')
            .replace(/"/g, '&quot;')
            .replace(/'/g, '&#039;');
    }

    function formatDate(value) {
        if (!value) return '--';
        const date = new Date(String(value).replace(' ', 'T'));
        return Number.isNaN(date.getTime()) ? '--' : date.toLocaleDateString('pt-BR');
    }

    function formatNumber(value, digits = 1) {
        if (value === null || value === undefined || value === '') return '--';
        const number = Number(value);
        if (Number.isNaN(number)) return '--';
        return number.toLocaleString('pt-BR', {
            minimumFractionDigits: digits,
            maximumFractionDigits: digits
        });
    }

    function ageFromBirthDate(value) {
        if (!value) return '--';
        const birthDate = new Date(value);
        if (Number.isNaN(birthDate.getTime())) return '--';

        const today = new Date();
        let age = today.getFullYear() - birthDate.getFullYear();
        const monthDiff = today.getMonth() - birthDate.getMonth();
        if (monthDiff < 0 || (monthDiff === 0 && today.getDate() < birthDate.getDate())) {
            age -= 1;
        }

        return `${age} anos`;
    }

    function genderLabel(value) {
        const map = {
            M: 'Masculino',
            F: 'Feminino',
            O: 'Outro'
        };

        return map[value] || '--';
    }

    function setText(id, value) {
        const element = document.getElementById(id);
        if (element) {
            element.textContent = value;
        }
    }

    function renderAluno(aluno) {
        setText('alunoNome', `${aluno.nome || ''} ${aluno.sobrenome || ''}`.trim() || 'Perfil do Aluno');
        setText('alunoMeta', `Matricula ${aluno.codigo_matricula || '--'} • ${aluno.email || 'sem email'}`);
        setText('cardMatricula', aluno.codigo_matricula || '--');
        setText('cardIdade', ageFromBirthDate(aluno.data_nascimento));
        setText('cardSexo', genderLabel(aluno.genero));
        setText('cardTurmaAtual', aluno.turmas?.[0]?.nome || 'Sem turma');

        if (!alunoDadosGrid) return;

        const fields = [
            ['CPF', aluno.cpf || '--'],
            ['Nascimento', formatDate(aluno.data_nascimento)],
            ['E-mail', aluno.email || '--'],
            ['Data da matricula', formatDate(aluno.data_matricula)],
            ['Telefone', aluno.contatos?.find((item) => item.tipo === 'telefone')?.valor || '--'],
            ['WhatsApp', aluno.contatos?.find((item) => item.tipo === 'whatsapp')?.valor || '--'],
            ['Cidade', aluno.cidade || '--'],
            ['Bairro', aluno.bairro || '--']
        ];

        alunoDadosGrid.innerHTML = fields.map(([label, value]) => `
            <div class="detail-item">
                <span class="detail-item-label">${escapeHtml(label)}</span>
                <strong>${escapeHtml(value)}</strong>
            </div>
        `).join('');
    }

    function renderAvaliacoes(avaliacoes) {
        setText('avaliacoesCount', `${avaliacoes.length} registro(s)`);

        if (!avaliacoesList) return;

        if (avaliacoes.length === 0) {
            avaliacoesList.innerHTML = `
                <div class="empty-panel">
                    <i class="ph ph-clipboard-text fs-2 mb-2"></i>
                    <p class="mb-0">Nenhuma avaliacao fisica cadastrada para este aluno.</p>
                </div>
            `;
            return;
        }

        avaliacoesList.innerHTML = avaliacoes.map((avaliacao) => `
            <div class="card avaliacao-card shadow-none">
                <div class="card-body">
                    <div class="d-flex justify-content-between align-items-start flex-wrap gap-3">
                        <div>
                            <div class="d-flex align-items-center gap-2 mb-2">
                                <span class="badge text-bg-light border">${escapeHtml(formatDate(avaliacao.data_avaliacao))}</span>
                                <span class="text-muted small">${escapeHtml(avaliacao.avaliador?.nome || '--')}</span>
                            </div>
                            <h6 class="mb-2">Resultados principais</h6>
                            <div class="d-flex gap-2 flex-wrap">
                                <span class="metric-chip">IMC ${escapeHtml(formatNumber(avaliacao.imc, 2))}</span>
                                <span class="metric-chip">% Gordura ${escapeHtml(formatNumber(avaliacao.percentual_gordura, 1))}</span>
                                <span class="metric-chip">% Musculo ${escapeHtml(formatNumber(avaliacao.percentual_musculo, 1))}</span>
                                <span class="metric-chip">Visceral ${escapeHtml(formatNumber(avaliacao.gordura_visceral, 1))}</span>
                            </div>
                        </div>
                        <a href="/ctt/admin/alunos/${alunoId}/avaliacoes/editar/${avaliacao.id}" class="btn btn-sm btn-primary">
                            <i class="ph ph-pencil-simple me-1"></i>Editar
                        </a>
                    </div>
                    <hr>
                    <div class="row g-3">
                        <div class="col-md-3"><strong>Classificacao IMC:</strong> ${escapeHtml(avaliacao.classificacoes?.imc?.label || '--')}</div>
                        <div class="col-md-3"><strong>Body Fat:</strong> ${escapeHtml(avaliacao.classificacoes?.percentual_gordura?.label || '--')}</div>
                        <div class="col-md-3"><strong>Muscle:</strong> ${escapeHtml(avaliacao.classificacoes?.percentual_musculo?.label || '--')}</div>
                        <div class="col-md-3"><strong>Visceral:</strong> ${escapeHtml(avaliacao.classificacoes?.gordura_visceral?.label || '--')}</div>
                        <div class="col-md-3"><strong>Peso:</strong> ${escapeHtml(formatNumber(avaliacao.peso, 1))} kg</div>
                        <div class="col-md-3"><strong>Altura:</strong> ${escapeHtml(formatNumber(avaliacao.altura, 2))} m</div>
                        <div class="col-md-3"><strong>Cintura:</strong> ${escapeHtml(formatNumber(avaliacao.cintura, 1))} cm</div>
                        <div class="col-md-3"><strong>Torax:</strong> ${escapeHtml(formatNumber(avaliacao.torax, 1))} cm</div>
                    </div>
                </div>
            </div>
        `).join('');
    }

    function formatAnamneseValue(value) {
        if (Array.isArray(value)) {
            return value.join(', ');
        }

        if (value === true) return 'Sim';
        if (value === false) return 'Nao';
        if (value === null || value === undefined || value === '') return '--';
        return String(value);
    }

    function renderAnamnese(perguntas, respostas) {
        if (!anamneseList) return;

        const respostasMap = new Map(respostas.map((resposta) => [Number(resposta.pergunta_id), resposta]));

        if (perguntas.length === 0) {
            anamneseList.innerHTML = `
                <div class="empty-panel">
                    <p class="mb-0">Nenhum formulario de anamnese encontrado.</p>
                </div>
            `;
            return;
        }

        anamneseList.innerHTML = perguntas.map((pergunta) => {
            const resposta = respostasMap.get(Number(pergunta.id));
            return `
                <div class="anamnese-item">
                    <div class="fw-semibold mb-2">${escapeHtml(pergunta.pergunta || 'Pergunta')}</div>
                    <div>${escapeHtml(formatAnamneseValue(resposta?.valor))}</div>
                    ${resposta?.observacao ? `<small class="text-muted d-block mt-2">Obs: ${escapeHtml(resposta.observacao)}</small>` : ''}
                </div>
            `;
        }).join('');
    }

    async function loadData() {
        if (alunoId < 1) {
            showError('Aluno invalido.');
            return;
        }

        try {
            const [alunoResponse, avaliacoesResponse, perguntasResponse, respostasResponse] = await Promise.all([
                fetch(`/ctt/api/alunos/${alunoId}`),
                fetch(`/ctt/api/alunos/${alunoId}/avaliacoes`),
                fetch('/ctt/api/anamnese/formularios/1/perguntas'),
                fetch(`/ctt/api/anamnese/respostas/${alunoId}`)
            ]);

            const [alunoPayload, avaliacoesPayload, perguntasPayload, respostasPayload] = await Promise.all([
                alunoResponse.json(),
                avaliacoesResponse.json(),
                perguntasResponse.json(),
                respostasResponse.json()
            ]);

            if (!alunoResponse.ok) {
                throw new Error(alunoPayload.message || 'Falha ao carregar o aluno.');
            }

            if (!avaliacoesResponse.ok) {
                throw new Error(avaliacoesPayload.message || 'Falha ao carregar avaliacoes.');
            }

            renderAluno(parseApiData(alunoPayload));
            renderAvaliacoes(parseApiList(avaliacoesPayload));
            renderAnamnese(
                parseApiList(perguntasPayload),
                parseApiList(respostasPayload)
            );
        } catch (error) {
            showError(error.message || 'Nao foi possivel carregar o perfil do aluno.');
        }
    }

    loadData();
});
