document.addEventListener('DOMContentLoaded', function () {
    const mainContent = document.getElementById('mainContent');
    const alunoId = Number(mainContent?.dataset.alunoId || 0);
    const errorAlert = document.getElementById('alunoDetalheError');
    const alunoDadosGrid = document.getElementById('alunoDadosGrid');
    const anamneseList = document.getElementById('anamneseList');
    let tabelaAvaliacoes = null;

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

    function maskCpf(value) {
        const digits = String(value ?? '').replace(/\D/g, '');
        if (!digits) return '--';
        return `*******${digits.slice(-4).padStart(4, '0')}`;
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

        if (!alunoDadosGrid) return;

        const fields = [
            ['Matricula', aluno.codigo_matricula || '--'],
            ['Idade', ageFromBirthDate(aluno.data_nascimento)],
            ['Sexo', genderLabel(aluno.genero)],
            ['Turma atual', aluno.turmas?.[0]?.nome || 'Sem turma'],
            ['CPF', maskCpf(aluno.cpf)],
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
        const tableElement = document.getElementById('tabelaAvaliacoes');
        if (!tableElement || typeof DataTable === 'undefined') return;

        if (tabelaAvaliacoes) {
            tabelaAvaliacoes.destroy();
            $('#tabelaAvaliacoes tbody').empty();
        }

        tabelaAvaliacoes = new DataTable('#tabelaAvaliacoes', {
            data: avaliacoes,
            responsive: true,
            ordering: false,
            pageLength: 10,
            language: {
                emptyTable: 'Nenhuma avaliacao fisica cadastrada para este aluno.',
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
            },
            columns: [
                {
                    data: 'data_avaliacao',
                    render: (_, __, row) => `
                        <div class="fw-semibold">${escapeHtml(formatDate(row.data_avaliacao))}</div>
                        <small class="text-muted">${escapeHtml(row.classificacoes?.imc?.label || '--')}</small>
                    `
                },
                {
                    data: null,
                    render: row => escapeHtml(row.avaliador?.nome || '--')
                },
                {
                    data: 'imc',
                    className: 'text-center',
                    render: data => escapeHtml(formatNumber(data, 2))
                },
                {
                    data: null,
                    className: 'text-center',
                    render: row => `
                        ${escapeHtml(formatNumber(row.percentual_gordura, 1))}%
                        <small class="d-block text-muted">${escapeHtml(row.classificacoes?.percentual_gordura?.label || '--')}</small>
                    `
                },
                {
                    data: null,
                    className: 'text-center',
                    render: row => `
                        ${escapeHtml(formatNumber(row.percentual_musculo, 1))}%
                        <small class="d-block text-muted">${escapeHtml(row.classificacoes?.percentual_musculo?.label || '--')}</small>
                    `
                },
                {
                    data: null,
                    className: 'text-center',
                    render: row => `
                        ${escapeHtml(formatNumber(row.gordura_visceral, 1))}
                        <small class="d-block text-muted">${escapeHtml(row.classificacoes?.gordura_visceral?.label || '--')}</small>
                    `
                },
                {
                    data: 'peso',
                    className: 'text-center',
                    render: data => `${escapeHtml(formatNumber(data, 1))} kg`
                },
                {
                    data: 'altura',
                    className: 'text-center',
                    render: data => `${escapeHtml(formatNumber(data, 2))} m`
                },
                {
                    data: null,
                    className: 'text-center',
                    orderable: false,
                    render: row => `
                        <a href="/ctt/admin/alunos/${alunoId}/avaliacoes/editar/${row.id}" class="btn btn-sm btn-primary" title="Editar Avaliacao">
                            <i class="ph ph-pencil"></i>
                        </a>
                    `
                }
            ]
        });
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
