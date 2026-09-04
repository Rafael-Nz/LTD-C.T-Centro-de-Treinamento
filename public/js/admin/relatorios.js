let graficoDist = null;
let graficoPresenca = null;

function escapeHtml(value) {
    return String(value ?? '')
        .replace(/&/g, '&amp;')
        .replace(/</g, '&lt;')
        .replace(/>/g, '&gt;')
        .replace(/"/g, '&quot;')
        .replace(/'/g, '&#039;');
}

$(document).ready(function () {
    $('.select2').select2({
        theme: 'bootstrap-5',
        width: '100%',
        language: 'pt-BR'
    });

    carregarFiltros();

    $('#tipoRelatorio').on('change', function () {
        atualizarFiltros($(this).val());
    });

    $('#filtroRelatorios').on('submit', function (e) {
        e.preventDefault();
        gerarRelatorio();
    });

    $('#btnExportar').on('click', '[data-formato]', function () {
        exportarRelatorio($(this).data('formato'));
    });

    inicializarDatePicker('datetimepicker_inicio');
    inicializarDatePicker('datetimepicker_fim');
});

function inicializarDatePicker(elementId) {
    const tempus = window.tempusDominus || window.tempusdominus;
    const element = document.getElementById(elementId);
    if (!tempus?.TempusDominus || !element) return;

    new tempus.TempusDominus(element, {
        localization: tempus.locales?.pt || { locale: 'pt-BR', format: 'dd/MM/yyyy' },
        display: {
            components: {
                calendar: true,
                date: true,
                month: true,
                year: true,
                decades: true,
                clock: false,
                hours: false,
                minutes: false,
                seconds: false
            },
            buttons: { today: true, clear: true, close: true }
        }
    });
}

function carregarFiltros() {
    $.ajax({
        url: '/ctt/api/modalidades?simple=true',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#modalidade');
            select.empty();
            select.append('<option value="">Todas</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`));
        }
    });

    $.ajax({
        url: '/ctt/api/turmas?simple=true',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#turma');
            select.empty();
            select.append('<option value="">Todas</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`));
        }
    });

    $.ajax({
        url: '/ctt/api/cargos?simple=true',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#cargo');
            select.empty();
            select.append('<option value="">Todos</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`));
        }
    });

    $.ajax({
        url: '/ctt/api/alunos?draw=1&start=0&length=1000',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#aluno');
            select.empty();
            select.append('<option value="">Todos</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)} ${escapeHtml(item.sobrenome || '')}</option>`));
        }
    });
}

function atualizarFiltros(tipoRelatorio) {
    $('#filtroModalidade, #filtroAluno, #filtroTurma, #filtroPeriodo, #filtroDataFim, #filtroStatus, #filtroCargo').hide();
    $('#btnExportar').hide();
    configurarStatus(tipoRelatorio);

    switch (tipoRelatorio) {
        case 'alunos':
            $('#filtroStatus').show();
            $('#btnExportar').show();
            break;
        case 'presenca':
            $('#filtroModalidade, #filtroTurma, #filtroPeriodo, #filtroDataFim').show();
            $('#btnExportar').show();
            break;
        case 'avaliacoes':
            $('#filtroAluno, #filtroPeriodo, #filtroDataFim').show();
            $('#btnExportar').show();
            break;
        case 'turmas':
            $('#filtroModalidade').show();
            $('#btnExportar').show();
            break;
        case 'funcionarios':
            $('#filtroStatus, #filtroCargo').show();
            $('#btnExportar').show();
            break;
        case 'treinos':
            $('#filtroTurma, #filtroPeriodo, #filtroDataFim, #filtroStatus').show();
            $('#btnExportar').show();
            break;
    }
}

function configurarStatus(tipoRelatorio) {
    const status = $('#status');
    status.empty().append('<option value="">Todos</option>');

    if (tipoRelatorio === 'treinos') {
        status.append('<option value="agendado">Agendado</option><option value="concluido">Concluído</option><option value="cancelado">Cancelado</option>');
    } else {
        status.append('<option value="ativo">Ativo</option><option value="inativo">Inativo</option>');
    }

    status.trigger('change');
}

function gerarRelatorio() {
    const tipoRelatorio = $('#tipoRelatorio').val();
    if (!tipoRelatorio) {
        Swal.fire('Atenção', 'Selecione um tipo de relatório', 'warning');
        return;
    }

    const params = {
        tipo: tipoRelatorio,
        modalidade: $('#modalidade').val(),
        aluno: $('#aluno').val(),
        turma: $('#turma').val(),
        cargo: $('#cargo').val(),
        dataInicio: $('#dataInicio').val(),
        dataFim: $('#dataFim').val(),
        status: $('#status').val()
    };

    $.ajax({
        url: '/ctt/api/relatorios/gerar',
        type: 'GET',
        data: params,
        dataType: 'json',
        beforeSend: () => {
            $('#tabelaRelatorios').html('<tr><td colspan="6"><div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div></td></tr>');
        },
        success: (data) => {
            data = data.data || data;
            renderizarRelatorio(data, tipoRelatorio);
            renderizarGraficos(data, tipoRelatorio);
        },
        error: () => {
            Swal.fire('Erro', 'Erro ao gerar relatório', 'error');
        }
    });
}

function renderizarRelatorio(data, tipoRelatorio) {
    let html = '';
    let cabecalho = '';

    switch (tipoRelatorio) {
        case 'alunos':
            cabecalho = '<tr><th>Matrícula</th><th>Nome</th><th>CPF</th><th>Email</th><th>Data</th><th>Status</th></tr>';
            data.registros.forEach(a => {
                const status = a.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
                html += `<tr><td>${escapeHtml(a.codigo_matricula)}</td><td>${escapeHtml(a.nome)} ${escapeHtml(a.sobrenome)}</td><td>${escapeHtml(mascararCpf(a.cpf))}</td><td>${escapeHtml(a.email)}</td><td>${escapeHtml(new Date(a.data_matricula).toLocaleDateString('pt-BR'))}</td><td>${status}</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Alunos');
            break;

        case 'presenca':
            cabecalho = '<tr><th>Data</th><th>Turma</th><th>Modalidade</th><th>Aluno</th><th>Situação</th></tr>';
            data.registros.forEach(p => {
                let badge = '';
                if (p.situacao === 'presente') badge = '<span class="badge bg-success">Presente</span>';
                else if (p.situacao === 'ausente') badge = '<span class="badge bg-danger">Ausente</span>';
                else badge = '<span class="badge bg-warning">Justificado</span>';
                html += `<tr><td>${escapeHtml(new Date(p.data_treino).toLocaleDateString('pt-BR'))}</td><td>${escapeHtml(p.turma || '-')}</td><td>${escapeHtml(p.modalidade || '-')}</td><td>${escapeHtml(p.aluno)}</td><td>${badge}</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Presença');
            break;

        case 'avaliacoes':
            cabecalho = '<tr><th>Data</th><th>Modalidade</th><th>Aluno</th><th>Avaliador</th><th>Peso (kg)</th><th>Altura (m)</th><th>% Gordura</th></tr>';
            data.registros.forEach(av => {
                html += `<tr><td>${escapeHtml(new Date(av.data_avaliacao).toLocaleDateString('pt-BR'))}</td><td>${escapeHtml(av.modalidade || '-')}</td><td>${escapeHtml(av.aluno)}</td><td>${escapeHtml(av.avaliador)}</td><td>${escapeHtml(av.peso)}</td><td>${escapeHtml(av.altura)}</td><td>${escapeHtml(av.percentual_gordura)}%</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Avaliações');
            break;

        case 'turmas':
            cabecalho = '<tr><th>Turma</th><th>Modalidade</th><th>Instrutor</th><th>Alunos</th><th>Capacidade</th><th>Ocupação</th></tr>';
            data.registros.forEach(t => {
                html += `<tr><td>${escapeHtml(t.nome)}</td><td>${escapeHtml(t.modalidade || '-')}</td><td>${escapeHtml(t.instrutor || '-')}</td><td>${escapeHtml(t.alunos)}</td><td>${escapeHtml(t.alunos)}/${escapeHtml(t.capacidade_maxima)}</td><td>${escapeHtml(t.ocupacao || 0)}%</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Turmas');
            break;

        case 'funcionarios':
            cabecalho = '<tr><th>Nome</th><th>CPF</th><th>Email</th><th>Cargo</th><th>Registro</th><th>Status</th></tr>';
            data.registros.forEach(f => {
                const status = f.ativo ? '<span class="badge bg-success">Ativo</span>' : '<span class="badge bg-danger">Inativo</span>';
                html += `<tr><td>${escapeHtml(f.nome)} ${escapeHtml(f.sobrenome)}</td><td>${escapeHtml(mascararCpf(f.cpf))}</td><td>${escapeHtml(f.email)}</td><td>${escapeHtml(f.cargo)}</td><td>${escapeHtml(f.registro_profissional || '-')}</td><td>${status}</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Funcionários');
            break;

        case 'treinos':
            cabecalho = '<tr><th>Início</th><th>Treino</th><th>Turma</th><th>Espaço</th><th>Instrutor</th><th>Status</th></tr>';
            data.registros.forEach(t => { html += `<tr><td>${escapeHtml(new Date(t.data_hora_inicio).toLocaleString('pt-BR'))}</td><td>${escapeHtml(t.treino)}</td><td>${escapeHtml(t.turma || '-')}</td><td>${escapeHtml(t.espaco)}</td><td>${escapeHtml(t.instrutor || '-')}</td><td>${escapeHtml(t.status)}</td></tr>`; });
            $('#tituloTabela').text('Agenda de Treinos');
            break;

    }

    $('#cabecalhoTabela').html(cabecalho);
    const quantidadeColunas = $('#cabecalhoTabela th').length;
    $('#tabelaRelatorios').html(html || `<tr><td colspan="${quantidadeColunas}" class="text-center text-muted">Nenhum registro encontrado</td></tr>`);
}

function mascararCpf(cpf) {
    const digitos = String(cpf || '').replace(/\D/g, '');
    if (digitos.length <= 4) return digitos || '-';
    return '*'.repeat(digitos.length - 4) + digitos.slice(-4);
}

function renderizarGraficos(data, tipoRelatorio) {
    if (graficoDist) graficoDist.destroy();
    if (graficoPresenca) graficoPresenca.destroy();
    $('#secaoGraficos').hide();

    const registros = data.registros || [];
    if (!registros.length) return;

    const configuracoes = {
        alunos: {
            titulo1: 'Alunos por modalidade',
            titulo2: 'Alunos por status',
            tipo1: 'doughnut',
            tipo2: 'bar',
            dados1: agruparModalidades(registros),
            dados2: agruparStatus(registros)
        },
        presenca: {
            titulo1: 'Presença por turma',
            titulo2: 'Registros por modalidade',
            tipo1: 'bar',
            tipo2: 'doughnut',
            dados1: agruparPresencaPorTurma(registros),
            dados2: agruparCampo(registros, 'modalidade', 'Sem modalidade')
        },
        avaliacoes: {
            titulo1: 'Peso médio por aluno',
            titulo2: 'Gordura corporal por aluno',
            tipo1: 'bar',
            tipo2: 'bar',
            dados1: agruparMedia(registros, 'aluno', 'peso'),
            dados2: agruparMedia(registros, 'aluno', 'percentual_gordura')
        },
        turmas: {
            titulo1: 'Alunos por turma',
            titulo2: 'Turmas por modalidade',
            tipo1: 'bar',
            tipo2: 'doughnut',
            dados1: agruparValor(registros, 'nome', 'alunos'),
            dados2: agruparCampo(registros, 'modalidade', 'Sem modalidade')
        },
        funcionarios: {
            titulo1: 'Funcionários por cargo',
            titulo2: 'Funcionários por status',
            tipo1: 'doughnut',
            tipo2: 'bar',
            dados1: agruparCampo(registros, 'cargo', 'Sem cargo'),
            dados2: agruparStatus(registros)
        },
        treinos: {
            titulo1: 'Treinos por status',
            titulo2: 'Treinos por turma',
            tipo1: 'doughnut',
            tipo2: 'bar',
            dados1: agruparCampo(registros, 'status', 'Sem status'),
            dados2: agruparCampo(registros, 'turma', 'Sem turma')
        }
    };

    const configuracao = configuracoes[tipoRelatorio];
    if (!configuracao) return;

    $('#tituloGraficoDistribuicao').text(configuracao.titulo1);
    $('#tituloGraficoPresenca').text(configuracao.titulo2);
    $('#secaoGraficos').show();
    graficoDist = criarGrafico('graficoDistribuicao', configuracao.tipo1, configuracao.dados1);
    graficoPresenca = criarGrafico('graficoPresenca', configuracao.tipo2, configuracao.dados2);
}

function criarGrafico(elementId, tipo, dados) {
    const ctx = document.getElementById(elementId).getContext('2d');
    const datasets = dados.datasets || [{
        data: dados.valores,
        backgroundColor: coresGrafico(dados.labels.length)
    }];

    return new Chart(ctx, {
        type: tipo,
        data: { labels: dados.labels, datasets },
        options: {
            responsive: true,
            maintainAspectRatio: false,
            plugins: { legend: { display: tipo === 'doughnut' } },
            scales: tipo === 'bar' ? { y: { beginAtZero: true } } : undefined
        }
    });
}

function coresGrafico(tamanho) {
    const cores = [
        'rgba(54, 162, 235, 0.8)',
        'rgba(75, 192, 192, 0.8)',
        'rgba(255, 206, 86, 0.8)',
        'rgba(153, 102, 255, 0.8)',
        'rgba(255, 99, 132, 0.8)',
        'rgba(255, 159, 64, 0.8)'
    ];
    return Array.from({ length: tamanho }, (_, indice) => cores[indice % cores.length]);
}

function agruparCampo(registros, campo, valorPadrao) {
    const agrupado = {};
    registros.forEach(registro => {
        const valor = registro[campo] || valorPadrao;
        agrupado[valor] = (agrupado[valor] || 0) + 1;
    });
    return { labels: Object.keys(agrupado), valores: Object.values(agrupado) };
}

function agruparValor(registros, campoLabel, campoValor) {
    return {
        labels: registros.map(registro => registro[campoLabel] || '-'),
        valores: registros.map(registro => Number(registro[campoValor]) || 0)
    };
}

function agruparMedia(registros, campoLabel, campoValor) {
    const valores = {};
    registros.forEach(registro => {
        const label = registro[campoLabel] || '-';
        const valor = Number(registro[campoValor]);
        if (!Number.isNaN(valor)) {
            valores[label] ??= [];
            valores[label].push(valor);
        }
    });
    return {
        labels: Object.keys(valores),
        valores: Object.values(valores).map(itens => itens.reduce((total, valor) => total + valor, 0) / itens.length)
    };
}

function agruparStatus(registros) {
    const status = {};
    registros.forEach(registro => {
        const valor = Number(registro.ativo) === 1 ? 'Ativos' : 'Inativos';
        status[valor] = (status[valor] || 0) + 1;
    });
    return { labels: Object.keys(status), valores: Object.values(status) };
}

function agruparModalidades(registros) {
    const modalidades = {};
    registros.forEach(registro => {
        String(registro.modalidades || 'Sem modalidade').split(', ').forEach(modalidade => {
            modalidades[modalidade] = (modalidades[modalidade] || 0) + 1;
        });
    });
    return { labels: Object.keys(modalidades), valores: Object.values(modalidades) };
}

function agruparPresencaPorTurma(registros) {
    const turmas = {};
    registros.forEach(registro => {
        const turma = registro.turma || 'Sem turma';
        turmas[turma] ??= { presentes: 0, ausentes: 0, justificados: 0 };
        if (registro.situacao === 'presente') turmas[turma].presentes++;
        else if (registro.situacao === 'ausente') turmas[turma].ausentes++;
        else turmas[turma].justificados++;
    });
    const labels = Object.keys(turmas);
    return {
        labels,
        datasets: [
            { label: 'Presentes', data: labels.map(turma => turmas[turma].presentes), backgroundColor: 'rgba(75, 192, 192, 0.8)' },
            { label: 'Ausentes', data: labels.map(turma => turmas[turma].ausentes), backgroundColor: 'rgba(255, 99, 132, 0.8)' },
            { label: 'Justificados', data: labels.map(turma => turmas[turma].justificados), backgroundColor: 'rgba(255, 206, 86, 0.8)' }
        ]
    };
}

function exportarRelatorio(formato) {
    const params = new URLSearchParams({
        tipo: $('#tipoRelatorio').val(),
        modalidade: $('#modalidade').val(),
        aluno: $('#aluno').val(),
        turma: $('#turma').val(),
        cargo: $('#cargo').val(),
        dataInicio: $('#dataInicio').val(),
        dataFim: $('#dataFim').val(),
        status: $('#status').val(),
        formato: formato
    });
    window.location.href = `/ctt/api/relatorios/exportar?${params.toString()}`;
}
