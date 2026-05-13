class AnamneseEngine {
    constructor({
        containerId,
        alunoId = null,
        formularioId = 1,
        basePath = '/ctt/api'
    }) {
        this.container = document.getElementById(containerId);
        this.alunoId = alunoId;
        this.formularioId = formularioId;
        this.basePath = basePath.replace(/\/$/, '');
        this.perguntas = [];
        this.respostasMap = new Map();
        this.eventsBound = false;
    }

    get perguntasEndpoint() {
        return `${this.basePath}/anamnese/formularios/${this.formularioId}/perguntas`;
    }

    get respostasEndpoint() {
        return this.alunoId ? `${this.basePath}/anamnese/respostas/${this.alunoId}` : null;
    }

    async init() {
        if (!this.container) return;

        this.renderLoading();

        try {
            await this.loadPerguntas();
            await this.loadRespostas();
            this.render();
            this.bindEvents();
            this.avaliarRegrasDeExibicao();
        } catch (error) {
            console.error('Anamnese error:', error);
            this.renderError(error.message || 'Erro ao carregar informacoes medicas.');
        }
    }

    async loadPerguntas() {
        const response = await fetch(this.perguntasEndpoint);
        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Erro ao carregar estrutura da anamnese.');
        }

        const result = await response.json();
        this.perguntas = Array.isArray(result) ? result : (result.data ?? []);
    }

    async loadRespostas() {
        this.respostasMap.clear();

        if (!this.respostasEndpoint) {
            return;
        }

        const response = await fetch(this.respostasEndpoint);
        if (!response.ok) {
            const error = await response.json().catch(() => ({}));
            throw new Error(error.message || 'Erro ao carregar respostas da anamnese.');
        }

        const result = await response.json();
        const respostas = Array.isArray(result) ? result : (result.data ?? []);

        respostas.forEach((resposta) => {
            this.respostasMap.set(Number(resposta.pergunta_id), resposta.valor);
        });
    }

    render() {
        this.container.innerHTML = '';

        this.perguntas.forEach((pergunta) => {
            const wrapper = document.createElement('div');
            wrapper.className = 'mb-4 question-block';
            wrapper.dataset.id = String(pergunta.id);
            wrapper.dataset.slug = pergunta.slug;

            if (pergunta.regra_exibicao) {
                wrapper.classList.add('d-none');
                wrapper.dataset.regra = JSON.stringify(pergunta.regra_exibicao);
            }

            const label = document.createElement('label');
            label.className = `form-label fw-semibold ${pergunta.obrigatoria ? 'required' : ''}`;
            label.innerHTML = `${pergunta.pergunta}${pergunta.obrigatoria ? ' <span class="text-danger">*</span>' : ''}`;

            const inputContainer = document.createElement('div');
            inputContainer.className = 'anamnese-input-wrapper';
            inputContainer.appendChild(this.buildInput(pergunta));

            wrapper.appendChild(label);
            wrapper.appendChild(inputContainer);
            this.container.appendChild(wrapper);
        });
    }

    buildInput(pergunta) {
        const valorSalvo = this.obterValorResposta(pergunta.id);
        const placeholder = pergunta.config?.placeholder ?? '';

        switch (pergunta.tipo_input) {
            case 'radio':
            case 'boolean':
                return this.createRadioGroup(pergunta, valorSalvo);
            case 'checkbox':
                return this.createCheckboxGroup(pergunta, valorSalvo);
            case 'select':
                return this.createSelect(pergunta, valorSalvo);
            case 'textarea':
                return this.createElement('textarea', {
                    name: pergunta.slug,
                    class: 'form-control',
                    rows: 2,
                    placeholder
                }, valorSalvo);
            case 'number':
                return this.createElement('input', {
                    type: 'number',
                    name: pergunta.slug,
                    class: 'form-control',
                    placeholder
                }, valorSalvo);
            case 'date':
                return this.createElement('input', {
                    type: 'date',
                    name: pergunta.slug,
                    class: 'form-control'
                }, valorSalvo);
            default:
                return this.createElement('input', {
                    type: 'text',
                    name: pergunta.slug,
                    class: 'form-control',
                    placeholder
                }, valorSalvo);
        }
    }

    bindEvents() {
        if (this.eventsBound) {
            return;
        }

        this.container.addEventListener('change', (event) => {
            if (!event.target.closest('.question-block')) {
                return;
            }

            this.avaliarRegrasDeExibicao();
        });

        this.eventsBound = true;
    }

    avaliarRegrasDeExibicao() {
        const blocos = this.container.querySelectorAll('.question-block[data-regra]');

        blocos.forEach((bloco) => {
            const regra = JSON.parse(bloco.dataset.regra);
            const visivel = this.evaluateRule(regra);
            bloco.classList.toggle('d-none', !visivel);
        });
    }

    evaluateRule(regra) {
        const conditions = this.extractConditions(regra);
        if (!conditions.length) {
            return true;
        }

        const logic = String(regra.logic ?? regra.operator ?? 'and').toLowerCase();
        const results = conditions.map((condition) => this.evaluateCondition(condition));

        return logic === 'or' ? results.some(Boolean) : results.every(Boolean);
    }

    extractConditions(regra) {
        if (regra.if) {
            return [regra.if];
        }

        if (Array.isArray(regra.conditions)) {
            return regra.conditions;
        }

        return [];
    }

    evaluateCondition(condicao) {
        const slugPai = condicao.pergunta_slug ?? null;
        if (!slugPai) {
            return true;
        }

        const perguntaPai = this.perguntas.find((pergunta) => pergunta.slug === slugPai);
        if (!perguntaPai) {
            return false;
        }

        const valorAtual = this.getInputValue(perguntaPai.slug);
        const operator = condicao.operator ?? 'equals';
        const valorAlvo = condicao.valor;

        switch (operator) {
            case 'equals':
                return this.compareIncludes(valorAtual, valorAlvo);
            case 'contains':
                return this.compareContains(valorAtual, valorAlvo);
            case 'not_equals':
                return !this.compareIncludes(valorAtual, valorAlvo);
            case 'greater_than':
                return Number(valorAtual) > Number(valorAlvo);
            case 'less_than':
                return Number(valorAtual) < Number(valorAlvo);
            default:
                return this.compareIncludes(valorAtual, valorAlvo);
        }
    }

    getData() {
        const respostas = [];

        this.perguntas.forEach((pergunta) => {
            const block = this.container.querySelector(`.question-block[data-id="${pergunta.id}"]`);
            if (!block || block.classList.contains('d-none')) {
                return;
            }

            const valor = this.getInputValue(pergunta.slug);
            if (valor === null || valor === '' || (Array.isArray(valor) && valor.length === 0)) {
                return;
            }

            respostas.push({
                pergunta_id: pergunta.id,
                valor,
                observacao: null
            });
        });

        return respostas;
    }

    getInputValue(slug) {
        const inputs = this.container.querySelectorAll(`[name="${slug}"], [name="${slug}[]"]`);
        if (!inputs.length) {
            return null;
        }

        const firstInput = inputs[0];

        if (firstInput.type === 'checkbox') {
            return Array.from(inputs)
                .filter((input) => input.checked)
                .map((input) => input.value);
        }

        if (firstInput.type === 'radio') {
            const checked = Array.from(inputs).find((input) => input.checked);
            if (!checked) {
                return null;
            }

            if (checked.value === 'true' || checked.value === '1') return true;
            if (checked.value === 'false' || checked.value === '0') return false;
            return checked.value;
        }

        return firstInput.value || null;
    }

    obterValorResposta(perguntaId) {
        return this.respostasMap.get(Number(perguntaId)) ?? null;
    }

    createElement(tag, attrs, value = null) {
        const element = document.createElement(tag);

        Object.entries(attrs).forEach(([key, attrValue]) => {
            element.setAttribute(key, attrValue);
        });

        if (value !== null && value !== undefined) {
            element.value = value;
        }

        return element;
    }

    createSelect(pergunta, valorSalvo) {
        const select = this.createElement('select', {
            name: pergunta.slug,
            class: 'form-select'
        });

        const placeholderOption = document.createElement('option');
        placeholderOption.value = '';
        placeholderOption.textContent = pergunta.config?.placeholder ?? 'Selecione';
        select.appendChild(placeholderOption);

        (pergunta.opcoes ?? []).forEach((opcao) => {
            const option = document.createElement('option');
            option.value = opcao.valor;
            option.textContent = opcao.label;
            option.selected = String(valorSalvo) === String(opcao.valor);
            select.appendChild(option);
        });

        return select;
    }

    createRadioGroup(pergunta, valorSalvo) {
        const wrapper = document.createElement('div');
        wrapper.className = 'd-flex gap-3 mt-1 flex-wrap';

        (pergunta.opcoes ?? []).forEach((opcao) => {
            const uid = `op_${pergunta.id}_${opcao.id}`;
            const checked = this.valuesAreEqual(valorSalvo, opcao.valor);

            const field = document.createElement('div');
            field.className = 'form-check';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'radio';
            input.name = pergunta.slug;
            input.id = uid;
            input.value = opcao.valor;
            input.checked = checked;

            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.htmlFor = uid;
            label.textContent = opcao.label;

            field.appendChild(input);
            field.appendChild(label);
            wrapper.appendChild(field);
        });

        return wrapper;
    }

    createCheckboxGroup(pergunta, valorSalvo) {
        const wrapper = document.createElement('div');
        wrapper.className = 'mt-1';
        const valores = Array.isArray(valorSalvo) ? valorSalvo.map(String) : [];

        (pergunta.opcoes ?? []).forEach((opcao) => {
            const uid = `op_${pergunta.id}_${opcao.id}`;
            const checked = valores.includes(String(opcao.valor));

            const field = document.createElement('div');
            field.className = 'form-check';

            const input = document.createElement('input');
            input.className = 'form-check-input';
            input.type = 'checkbox';
            input.name = `${pergunta.slug}[]`;
            input.id = uid;
            input.value = opcao.valor;
            input.checked = checked;

            const label = document.createElement('label');
            label.className = 'form-check-label';
            label.htmlFor = uid;
            label.textContent = opcao.label;

            field.appendChild(input);
            field.appendChild(label);
            wrapper.appendChild(field);
        });

        return wrapper;
    }

    compareIncludes(valorAtual, valorAlvo) {
        if (Array.isArray(valorAtual)) {
            return valorAtual.map(String).includes(String(valorAlvo));
        }

        return this.valuesAreEqual(valorAtual, valorAlvo);
    }

    compareContains(valorAtual, valorAlvo) {
        if (Array.isArray(valorAtual)) {
            return valorAtual.map(String).includes(String(valorAlvo));
        }

        return String(valorAtual ?? '').includes(String(valorAlvo ?? ''));
    }

    valuesAreEqual(left, right) {
        if (typeof left === 'boolean' || typeof right === 'boolean') {
            return this.normalizeBoolean(left) === this.normalizeBoolean(right);
        }

        return String(left ?? '') === String(right ?? '');
    }

    normalizeBoolean(value) {
        if (value === true || value === false) {
            return value;
        }

        if (['true', '1', 1, 'sim'].includes(value)) {
            return true;
        }

        if (['false', '0', 0, 'nao'].includes(value)) {
            return false;
        }

        return value;
    }

    renderLoading() {
        this.container.innerHTML = '<div class="text-muted small">Carregando anamnese...</div>';
    }

    renderError(message) {
        this.container.innerHTML = `<div class="alert alert-warning">${message}</div>`;
    }
}
