/**
 * AnamneseEngine - Gerencia a renderização, lógica condicional e coleta de dados
 * Integra-se ao formulário de aluno via data-attributes.
 */
class AnamneseEngine {
    constructor(containerId, alunoId = null) {
        this.container = document.getElementById(containerId);
        this.alunoId = alunoId;
        this.perguntas = [];
        this.respostasCarregadas = [];
        this.apiBase = '/ctt/api/anamnese/formularios/1/perguntas';
    }

    /**
     * Inicializa o motor: carrega o formulário e, se houver ID, as respostas
     */
    async init() {
        if (!this.container) return;

        try {
            const response = await fetch(this.apiBase);
            if (!response.ok) throw new Error('Erro ao carregar estrutura da anamnese');

            const result = await response.json();

            this.perguntas = Array.isArray(result) ? result : (result.data ?? []);

            if (this.alunoId) {
                await this.carregarRespostasAluno();
            }

            this.render();
            this.aplicarEventosRegras();
            this.avaliarRegrasDeExibicao();
        } catch (error) {
            console.error('Anamnese Error:', error);
            this.container.innerHTML = `<div class="alert alert-warning">Erro ao carregar informações médicas.</div>`;
        }
    }

    /**
     * Busca respostas existentes para edição
     */
    async carregarRespostasAluno() {
        try {
            const res = await fetch(`/api/anamnese/respostas/${this.alunoId}`);
            if (res.ok) {
                const result = await res.json();
                // CORREÇÃO 2 (mesma lógica): desempacota se vier em { data: [...] }
                this.respostasCarregadas = Array.isArray(result) ? result : (result.data ?? []);
            }
        } catch (e) {
            console.warn('Não foi possível carregar respostas prévias.');
        }
    }

    /**
     * Renderiza o HTML das perguntas seguindo o padrão Bootstrap do projeto
     */
    render() {
        this.container.innerHTML = '';

        this.perguntas.forEach(pergunta => {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-4 question-block';
            wrapper.dataset.id = pergunta.id;
            wrapper.dataset.slug = pergunta.slug;

            // Configura regra de exibição se existir
            if (pergunta.regra_exibicao) {
                wrapper.classList.add('d-none');
                wrapper.dataset.regra = JSON.stringify(pergunta.regra_exibicao);
            }

            // Label
            const label = document.createElement('label');
            label.className = `form-label fw-semibold ${pergunta.obrigatoria ? 'required' : ''}`;
            label.innerHTML = `${pergunta.pergunta}${pergunta.obrigatoria ? ' <span class="text-danger">*</span>' : ''}`;
            wrapper.appendChild(label);

            // Input
            const inputContainer = document.createElement('div');
            inputContainer.className = 'anamnese-input-wrapper';
            inputContainer.appendChild(this.buildInput(pergunta));
            wrapper.appendChild(inputContainer);

            this.container.appendChild(wrapper);
        });
    }

    /**
     * Constrói o elemento de input baseado no tipo definido no DTO
     */
    buildInput(p) {
        const valorSalvo  = this.obterValorResposta(p.id);
        const placeholder = p.config?.placeholder ?? '';

        switch (p.tipo_input) {
            case 'radio':
            case 'boolean':
                return this.createRadioGroup(p, valorSalvo);
            case 'checkbox':
                return this.createCheckboxGroup(p, valorSalvo);
            case 'textarea':
                return this.createElement('textarea', { name: p.slug, class: 'form-control', rows: 2, placeholder }, valorSalvo);
            case 'number':
                return this.createElement('input', { type: 'number', name: p.slug, class: 'form-control', placeholder }, valorSalvo);
            case 'date':
                return this.createElement('input', { type: 'date', name: p.slug, class: 'form-control' }, valorSalvo);
            default:
                return this.createElement('input', { type: 'text', name: p.slug, class: 'form-control', placeholder }, valorSalvo);
        }
    }

    /**
     * Aplica listener único no container para recalcular visibilidade a cada mudança
     */
    aplicarEventosRegras() {
        this.container.addEventListener('change', () => {
            this.avaliarRegrasDeExibicao();
        });
    }

    /**
     * Avalia todas as regras de exibição e mostra/oculta os blocos correspondentes.
     */
    avaliarRegrasDeExibicao() {
        const blocos = this.container.querySelectorAll('.question-block[data-regra]');

        blocos.forEach(bloco => {
            const regra = JSON.parse(bloco.dataset.regra);

            // TODO: suporta apenas uma condição simples (regra.if ou conditions[0]).
            // Lógica AND/OR múltipla (RegraExibicaoDTO.conditions + logic) não está implementada.
            const condicao = regra.if ?? (regra.conditions?.[0] ?? null);
            if (!condicao) return;

            const slugPai = condicao.pergunta_slug ?? null;
            if (!slugPai) return;

            const perguntaPai = this.perguntas.find(p => p.slug === slugPai);

            if (!perguntaPai) {
                bloco.classList.add('d-none');
                return;
            }

            const valorAtual = this.getInputValue(perguntaPai.slug);
            const operator   = condicao.operator ?? 'equals';
            const valorAlvo  = String(condicao.valor);

            let isMatch = false;

            switch (operator) {
                case 'equals':
                    isMatch = Array.isArray(valorAtual)
                        ? valorAtual.map(String).includes(String(valorAlvo))
                        : String(valorAtual ?? '') === String(valorAlvo);
                    break;

                case 'contains':
                    isMatch = Array.isArray(valorAtual)
                        ? valorAtual.map(String).includes(String(valorAlvo))
                        : String(valorAtual ?? '').includes(String(valorAlvo));
                    break;

                case 'not_equals':
                    isMatch = Array.isArray(valorAtual)
                        ? !valorAtual.map(String).includes(String(valorAlvo))
                        : String(valorAtual ?? '') !== String(valorAlvo);
                    break;

                case 'greater_than':
                    isMatch = parseFloat(valorAtual) > parseFloat(valorAlvo);
                    break;

                case 'less_than':
                    isMatch = parseFloat(valorAtual) < parseFloat(valorAlvo);
                    break;

                default:
                    isMatch = String(valorAtual ?? '') === valorAlvo;
            }

            bloco.classList.toggle('d-none', !isMatch);
        });
    }

    /**
     * Coleta os dados no formato esperado pelo EnvioAnamneseDTO (apenas blocos visíveis)
     */
    getData() {
        const respostas = [];

        this.perguntas.forEach(p => {
            const block = this.container.querySelector(`.question-block[data-id="${p.id}"]`);
            if (!block || block.classList.contains('d-none')) return;

            const valor = this.getInputValue(p.slug);

            if (valor !== null && valor !== '' && !(Array.isArray(valor) && valor.length === 0)) {
                respostas.push({
                    pergunta_id: p.id,
                    valor,
                    observacao: null
                });
            }
        });

        return respostas;
    }

    // ─── Helpers de DOM ──────────────────────────────────────────────────────────

    getInputValue(slug) {
        const inputs = this.container.querySelectorAll(`[name="${slug}"], [name="${slug}[]"]`);
        if (!inputs.length) return null;

        if (inputs[0].type === 'checkbox') {
            return Array.from(inputs).filter(i => i.checked).map(i => i.value);
        }
        
        if (inputs[0].type === 'radio') {
            const checked = Array.from(inputs).find(i => i.checked);
            if (!checked) return null;
            if (checked.value === 'true' || checked.value === '1') return true;
            if (checked.value === 'false' || checked.value === '0') return false;
            return checked.value;
        }
        return inputs[0].value || null;
    }

    obterValorResposta(perguntaId) {
        const resp = this.respostasCarregadas.find(r => r.pergunta_id === perguntaId);
        return resp ? resp.valor : null;
    }

    createElement(tag, attrs, value = null) {
        const el = document.createElement(tag);
        Object.entries(attrs).forEach(([k, v]) => el.setAttribute(k, v));
        if (value !== null && value !== undefined) el.value = value;
        return el;
    }

    createRadioGroup(p, valorSalvo) {
        const div = document.createElement('div');
        div.className = 'd-flex gap-3 mt-1';

        (p.opcoes ?? []).forEach(op => {
            const uid     = `op_${p.id}_${op.id}`;
            const checked = String(valorSalvo) === String(op.valor);

            const wrap  = document.createElement('div');
            wrap.className = 'form-check';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type      = 'radio';
            input.name      = p.slug;
            input.id        = uid;
            input.value     = op.valor;
            if (checked) input.checked = true;

            const lbl = document.createElement('label');
            lbl.className   = 'form-check-label';
            lbl.htmlFor     = uid;
            lbl.textContent = op.label;

            wrap.appendChild(input);
            wrap.appendChild(lbl);
            div.appendChild(wrap);
        });

        return div;
    }

    createCheckboxGroup(p, valorSalvo) {
        const div    = document.createElement('div');
        div.className = 'mt-1';
        const valores = Array.isArray(valorSalvo) ? valorSalvo.map(String) : [];

        (p.opcoes ?? []).forEach(op => {
            const uid     = `op_${p.id}_${op.id}`;
            const checked = valores.includes(String(op.valor));

            const wrap  = document.createElement('div');
            wrap.className = 'form-check';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type      = 'checkbox';
            input.name      = `${p.slug}[]`;
            input.id        = uid;
            input.value     = op.valor;
            if (checked) input.checked = true;

            const lbl = document.createElement('label');
            lbl.className   = 'form-check-label';
            lbl.htmlFor     = uid;
            lbl.textContent = op.label;

            wrap.appendChild(input);
            wrap.appendChild(lbl);
            div.appendChild(wrap);
        });

        return div;
    }
}