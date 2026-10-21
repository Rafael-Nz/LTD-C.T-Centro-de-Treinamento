(() => {
    let avaliacoes = [];
    let modalDetalheInstance = null;

    const esc = (value) => String(value ?? '').replace(/[&<>"']/g, (c) => ({ '&': '&amp;', '<': '&lt;', '>': '&gt;', '"': '&quot;', "'": '&#39;' }[c]));
    const formatDate = (v) => v ? v.split('-').reverse().join('/') : '-';

    function classificarImc(imc) {
        const val = parseFloat(imc);
        if (isNaN(val) || val <= 0) return { label: '—', cls: '' };
        if (val < 18.5) return { label: 'Abaixo do peso', cls: 'badge-imc--baixo' };
        if (val < 25.0) return { label: 'Normal', cls: 'badge-imc--normal' };
        if (val < 30.0) return { label: 'Sobrepeso', cls: 'badge-imc--sobrepeso' };
        return { label: 'Obesidade', cls: 'badge-imc--obesidade' };
    }

    async function init() {
        const modalEl = document.getElementById('modalDetalheAvaliacao');
        if (modalEl) {
            modalDetalheInstance = new bootstrap.Modal(modalEl);
        }

        try {
            const userResp = await fetch('/ctt/api/usuarios/me', {
                headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                credentials: 'same-origin',
            });
            const userJson = await userResp.json();
            if (!userResp.ok || !userJson.success) {
                throw new Error(userJson.message || 'Erro ao consultar usuário.');
            }

            const alunoId = userJson.data.id;
            const resp = await fetch(`/ctt/api/alunos/${alunoId}/avaliacoes`, {
                headers: { 'Accept': 'application/json', 'X-Auth-Area': 'aluno' },
                credentials: 'same-origin',
            });
            const json = await resp.json();

            if (!resp.ok) {
                throw new Error(json.message || 'Erro ao carregar avaliações.');
            }

            avaliacoes = json.data || json || [];
            renderTela();
        } catch (error) {
            console.error('Falha ao carregar avaliações:', error);
            const container = document.getElementById('avaliacoesContainer');
            if (container) {
                container.innerHTML = `
                    <div class="eval-empty-card">
                        <i class="bi bi-exclamation-circle text-warning fs-1 mb-2"></i>
                        <h4 class="h5 text-dark fw-bold mb-1">Não foi possível carregar as avaliações</h4>
                        <p class="text-muted small mb-0">${esc(error.message)}</p>
                    </div>
                `;
            }
        }
    }

    function renderTela() {
        const container = document.getElementById('avaliacoesContainer');
        const emptyState = document.getElementById('avaliacoesEmpty');
        const statsWrap = document.getElementById('evalStatsWrap');

        if (!avaliacoes || !avaliacoes.length) {
            if (statsWrap) statsWrap.classList.add('d-none');
            if (container) container.classList.add('d-none');
            if (emptyState) emptyState.classList.remove('d-none');
            return;
        }

        if (statsWrap) statsWrap.classList.remove('d-none');
        if (container) container.classList.remove('d-none');
        if (emptyState) emptyState.classList.add('d-none');

        const ultima = avaliacoes[0];

        // Stats do topo
        const pesoEl = document.getElementById('statPeso');
        const alturaEl = document.getElementById('statAltura');
        const imcEl = document.getElementById('statImc');
        const imcBadge = document.getElementById('statImcBadge');
        const gorduraEl = document.getElementById('statGordura');
        const musculoEl = document.getElementById('statMusculo');
        const dataUltimaEl = document.getElementById('statDataUltima');

        if (pesoEl) pesoEl.textContent = ultima.peso ? `${ultima.peso} kg` : '—';
        if (alturaEl) alturaEl.textContent = ultima.altura ? `${ultima.altura} m` : '—';
        
        if (imcEl) {
            imcEl.textContent = ultima.imc || '—';
            const imcClass = classificarImc(ultima.imc);
            if (imcBadge) {
                imcBadge.className = `badge-imc ${imcClass.cls}`;
                imcBadge.textContent = imcClass.label;
            }
        }

        if (gorduraEl) gorduraEl.textContent = ultima.percentual_gordura ? `${ultima.percentual_gordura}%` : '—';
        if (musculoEl) musculoEl.textContent = ultima.percentual_musculo ? `${ultima.percentual_musculo}%` : '—';
        if (dataUltimaEl) dataUltimaEl.textContent = `Realizada em ${formatDate(ultima.data_avaliacao)}`;

        // Tabela de Histórico
        const tbody = document.getElementById('tabelaHistoricoBody');
        if (tbody) {
            tbody.innerHTML = avaliacoes.map((a, idx) => {
                const imcClass = classificarImc(a.imc);
                const avaliador = a.avaliador_nome ? `${a.avaliador_nome} ${a.avaliador_sobrenome || ''}`.trim() : 'Profissional CT';
                return `
                    <tr>
                        <td>
                            <strong>${formatDate(a.data_avaliacao)}</strong>
                            ${idx === 0 ? '<span class="badge bg-danger ms-1" style="font-size: 10px;">Mais Recente</span>' : ''}
                        </td>
                        <td>${a.peso ? `${a.peso} kg` : '—'}</td>
                        <td>
                            <span class="fw-bold">${a.imc || '—'}</span>
                            <span class="badge-imc ${imcClass.cls} ms-1">${imcClass.label}</span>
                        </td>
                        <td>${a.percentual_gordura ? `${a.percentual_gordura}%` : '—'}</td>
                        <td>${a.percentual_musculo ? `${a.percentual_musculo}%` : '—'}</td>
                        <td class="text-muted small">${esc(avaliador)}</td>
                        <td class="text-end">
                            <button class="btn btn-sm btn-outline-danger btn-ver-detalhe" data-index="${idx}">
                                <i class="bi bi-file-earmark-medical me-1"></i> Ver Ficha
                            </button>
                        </td>
                    </tr>
                `;
            }).join('');

            document.querySelectorAll('.btn-ver-detalhe').forEach(btn => {
                btn.addEventListener('click', (e) => {
                    const idx = Number(btn.getAttribute('data-index'));
                    abrirDetalhes(avaliacoes[idx]);
                });
            });
        }
    }

    function abrirDetalhes(a) {
        if (!a || !modalDetalheInstance) return;

        const dataEl = document.getElementById('modalDataAvaliacao');
        const profEl = document.getElementById('modalAvaliador');
        const avaliadorNome = a.avaliador_nome ? `${a.avaliador_nome} ${a.avaliador_sobrenome || ''}`.trim() : 'Profissional CT';

        if (dataEl) dataEl.textContent = formatDate(a.data_avaliacao);
        if (profEl) profEl.textContent = avaliadorNome;

        // Composição
        document.getElementById('mPeso').textContent = a.peso ? `${a.peso} kg` : '—';
        document.getElementById('mAltura').textContent = a.altura ? `${a.altura} m` : '—';
        document.getElementById('mImc').textContent = a.imc || '—';
        document.getElementById('mGordura').textContent = a.percentual_gordura ? `${a.percentual_gordura} %` : '—';
        document.getElementById('mMusculo').textContent = a.percentual_musculo ? `${a.percentual_musculo} %` : '—';
        document.getElementById('mMetabolismo').textContent = a.metabolismo_repouso ? `${a.metabolismo_repouso} kcal` : '—';
        document.getElementById('mIdadeBio').textContent = a.idade_biologica ? `${a.idade_biologica} anos` : '—';
        document.getElementById('mVisceral').textContent = a.gordura_visceral ? `Nível ${a.gordura_visceral}` : '—';

        // Perimetria
        document.getElementById('mCintura').textContent = a.cintura ? `${a.cintura} cm` : '—';
        document.getElementById('mTorax').textContent = a.torax ? `${a.torax} cm` : '—';
        document.getElementById('mBracoD').textContent = a.braco_d ? `${a.braco_d} cm` : '—';
        document.getElementById('mBracoDc').textContent = a.braco_dc ? `${a.braco_dc} cm` : '—';
        document.getElementById('mBracoE').textContent = a.braco_e ? `${a.braco_e} cm` : '—';
        document.getElementById('mBracoEc').textContent = a.braco_ec ? `${a.braco_ec} cm` : '—';
        document.getElementById('mCoxaD').textContent = a.coxa_d ? `${a.coxa_d} cm` : '—';
        document.getElementById('mCoxaE').textContent = a.coxa_e ? `${a.coxa_e} cm` : '—';
        document.getElementById('mPanturrilhaD').textContent = a.panturrilha_d ? `${a.panturrilha_d} cm` : '—';
        document.getElementById('mPanturrilhaE').textContent = a.panturrilha_e ? `${a.panturrilha_e} cm` : '—';

        // Observações
        const obsEl = document.getElementById('modalObservacoes');
        if (obsEl) {
            obsEl.textContent = a.observacoes ? a.observacoes : 'Nenhuma observação registrada pelo avaliador.';
        }

        modalDetalheInstance.show();
    }

    document.addEventListener('DOMContentLoaded', init);
})();
