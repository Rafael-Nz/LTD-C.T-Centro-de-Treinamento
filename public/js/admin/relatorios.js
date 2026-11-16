let graficoDist = null;
let graficoPresenca = null;
let versaoRelatorio = 0;
let versaoListaAlunos = 0;
let turmasCache = [];

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

    $('#turma').on('change', carregarAlunosRelatorio);

    carregarFiltros();

    $('#tipoRelatorio').on('change', function () {
        versaoRelatorio++;
        limparResultadoRelatorio();
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

    const tipoInicial = $('#tipoRelatorio').data('tipo-inicial') || new URLSearchParams(window.location.search).get('tipo');
    if (tipoInicial && $(`#tipoRelatorio option[value="${tipoInicial}"]`).length) {
        $('#tipoRelatorio').val(tipoInicial).trigger('change');
    }
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
            turmasCache = data.data || data || [];
            atualizarFiltroTurma($('#tipoRelatorio').val());
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
        url: '/ctt/api/financeiro/planos?ativos=false',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#plano');
            select.empty();
            select.append('<option value="">Todos os planos</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`));
        }
    });

    $.ajax({
        url: '/ctt/api/financeiro/servicos?ativos=false',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            const items = data.data || data || [];
            const select = $('#servico');
            select.empty();
            select.append('<option value="">Todos os serviços</option>');
            items.forEach(item => select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`));
        }
    });

    carregarAlunosRelatorio();
}

function carregarAlunosRelatorio() {
    const versaoConsulta = ++versaoListaAlunos;
    const turma = $('#tipoRelatorio').val() === 'presenca' ? $('#turma').val() : null;
    const select = $('#aluno');
    select.empty().append('<option value="">Carregando alunos...</option>')
        .prop('disabled', true).trigger('change');

    $.ajax({
        url: turma ? `/ctt/api/turmas/${encodeURIComponent(turma)}` : '/ctt/api/alunos?draw=1&start=0&length=1000',
        type: 'GET',
        dataType: 'json',
        success: function (data) {
            if (versaoConsulta !== versaoListaAlunos) return;
            const resultado = data.data || data;
            const items = turma ? resultado.alunos || [] : resultado || [];
            const placeholder = textoPlaceholderAluno($('#tipoRelatorio').val());
            select.empty();
            select.append(`<option value="">${placeholder}</option>`);
            items.forEach(item => {
                const id = turma ? item.aluno_id : item.id;
                const nome = turma ? item.aluno_nome : `${item.nome} ${item.sobrenome || ''}`;
                select.append(`<option value="${escapeHtml(id)}">${escapeHtml(nome)}</option>`);
            });
            if (!items.length) select.find('option[value=""]').text('Nenhum aluno encontrado');
            select.prop('disabled', false).trigger('change');
        },
        error: function () {
            if (versaoConsulta !== versaoListaAlunos) return;
            select.empty().append('<option value="">Não foi possível carregar os alunos</option>').trigger('change');
            Swal.fire('Erro', 'Não foi possível carregar os alunos. Selecione a turma novamente para tentar.', 'error');
        }
    });
}

function textoPlaceholderAluno(tipoRelatorio) {
    if (['contratos_planos', 'contratos_servicos'].includes(tipoRelatorio)) return 'Todos os alunos';
    return tipoRelatorio === 'avaliacoes' ? 'Selecione um aluno' : 'Selecione um aluno';
}

function atualizarFiltroAluno(tipoRelatorio) {
    const select = $('#aluno');
    const placeholder = textoPlaceholderAluno(tipoRelatorio);
    let primeiraOpcao = select.find('option[value=""]');
    if (!primeiraOpcao.length) {
        select.prepend(`<option value="">${placeholder}</option>`);
        primeiraOpcao = select.find('option[value=""]');
    }
    primeiraOpcao.text(placeholder);
    const obrigatorio = ['avaliacoes', 'presenca'].includes(tipoRelatorio);
    $('#alunoObrigatorio').toggleClass('d-none', !obrigatorio);
    select.attr('aria-required', String(obrigatorio));
    select.trigger('change');
}

function atualizarFiltroTurma(tipoRelatorio) {
    const obrigatoria = tipoRelatorio === 'treinos';
    const select = $('#turma');
    const valorAtual = select.val();
    select.empty();

    if (obrigatoria) {
        select.append('<option value="">Selecione uma turma</option>');
    } else {
        select.append('<option value="">Todas as turmas</option>');
    }

    turmasCache.forEach(item => {
        select.append(`<option value="${escapeHtml(item.id)}">${escapeHtml(item.nome)}</option>`);
    });

    if (valorAtual && select.find(`option[value="${valorAtual}"]`).length) {
        select.val(valorAtual);
    } else {
        select.val('');
    }

    select.attr('aria-required', String(obrigatoria));
    $('#turmaObrigatoria').toggleClass('d-none', !obrigatoria);
    select.trigger('change');
}

function validarTurmaRelatorio(tipoRelatorio) {
    if (tipoRelatorio === 'treinos' && !$('#turma').val()) {
        Swal.fire('Atenção', 'Selecione uma turma para o relatório de Agenda de Treinos.', 'warning');
        return false;
    }
    return true;
}

function limparResultadoRelatorio() {
    $('#btnExportar').hide();
    $('#tituloTabela').text('Selecione os filtros e gere o relatório');
    $('#cabecalhoTabela').empty();
    $('#tabelaRelatorios').html('<tr><td class="text-center text-muted py-5"><p>Selecione os filtros acima e clique em "Gerar Relatório"</p></td></tr>');
    if (graficoDist) graficoDist.destroy();
    if (graficoPresenca) graficoPresenca.destroy();
    graficoDist = null;
    graficoPresenca = null;
    $('#tituloGraficoDistribuicao, #tituloGraficoPresenca').empty();
    $('#secaoGraficos').hide();
}

function atualizarFiltros(tipoRelatorio) {
    $('#filtroModalidade, #filtroAluno, #filtroTurma, #filtroPeriodo, #filtroDataFim, #filtroStatus, #filtroCargo, #filtroPlano, #filtroServico').hide();
    $('#btnExportar').hide();
    configurarStatus(tipoRelatorio);
    atualizarFiltroAluno(tipoRelatorio);
    atualizarFiltroTurma(tipoRelatorio);

    switch (tipoRelatorio) {
        case 'alunos':
            $('#filtroStatus').show();
            break;
        case 'contratos_planos':
            $('#filtroAluno, #filtroPlano, #filtroPeriodo, #filtroDataFim, #filtroStatus').show();
            break;
        case 'contratos_servicos':
            $('#filtroAluno, #filtroServico, #filtroPeriodo, #filtroDataFim, #filtroStatus').show();
            break;
        case 'presenca':
            $('#filtroAluno, #filtroModalidade, #filtroTurma, #filtroPeriodo, #filtroDataFim').show();
            break;
        case 'avaliacoes':
            $('#filtroAluno, #filtroPeriodo, #filtroDataFim').show();
            break;
        case 'turmas':
            $('#filtroModalidade').show();
            break;
        case 'funcionarios':
            $('#filtroStatus, #filtroCargo').show();
            break;
        case 'treinos':
            $('#filtroTurma, #filtroPeriodo, #filtroDataFim, #filtroStatus').show();
            break;
    }
}

function configurarStatus(tipoRelatorio) {
    const status = $('#status');
    status.empty().append('<option value="">Todos</option>');

    if (tipoRelatorio === 'treinos') {
        status.append('<option value="agendado">Agendado</option><option value="concluido">Concluído</option><option value="cancelado">Cancelado</option>');
    } else if (['contratos_planos', 'contratos_servicos'].includes(tipoRelatorio)) {
        status.append('<option value="ativo">Ativo</option><option value="pausado">Pausado</option><option value="encerrado">Encerrado</option><option value="cancelado">Cancelado</option>');
    } else {
        status.append('<option value="ativo">Ativo</option><option value="inativo">Inativo</option>');
    }

    status.trigger('change');
}

function gerarRelatorio() {
    const tipoRelatorio = $('#tipoRelatorio').val();
    if (!validarTurmaRelatorio(tipoRelatorio)) return;
    if (!tipoRelatorio) {
        Swal.fire('Atenção', 'Selecione um tipo de relatório', 'warning');
        return;
    }

    if (['avaliacoes', 'presenca'].includes(tipoRelatorio) && !$('#aluno').val()) {
        const nomeRelatorio = tipoRelatorio === 'presenca' ? 'presença' : 'avaliações físicas';
        Swal.fire('Atenção', `Selecione um aluno para o relatório de ${nomeRelatorio}.`, 'warning');
        return;
    }

    const versaoConsulta = ++versaoRelatorio;
    const params = {
        tipo: tipoRelatorio,
        modalidade: $('#modalidade').val(),
        aluno: $('#aluno').val(),
        turma: $('#turma').val(),
        cargo: $('#cargo').val(),
        plano: $('#plano').val(),
        servico: $('#servico').val(),
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
            $('#btnExportar').hide();
            $('#tabelaRelatorios').html('<tr><td colspan="10"><div class="text-center"><div class="spinner-border" role="status"><span class="visually-hidden">Carregando...</span></div></div></td></tr>');
        },
        success: (data) => {
            if (versaoConsulta !== versaoRelatorio) return;
            data = data.data || data;
            renderizarRelatorio(data, tipoRelatorio);
            renderizarGraficos(data, tipoRelatorio);
            $('#btnExportar').show();
        },
        error: (xhr) => {
            if (versaoConsulta !== versaoRelatorio) return;
            Swal.fire('Erro', xhr.responseJSON?.message || 'Erro ao gerar relatório', 'error');
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
                const status = a.ativo ? '<span class="badge bg-success-subtle text-success-emphasis">Ativo</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis">Inativo</span>';
                html += `<tr><td>${escapeHtml(a.codigo_matricula)}</td><td>${escapeHtml(a.nome)} ${escapeHtml(a.sobrenome)}</td><td>${escapeHtml(mascararCpf(a.cpf))}</td><td>${escapeHtml(a.email)}</td><td>${escapeHtml(new Date(a.data_matricula).toLocaleDateString('pt-BR'))}</td><td>${status}</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Alunos');
            break;

        case 'contratos_planos':
            cabecalho = '<tr><th>Contrato</th><th>Aluno</th><th>Plano</th><th>Periodicidade</th><th>Início</th><th>Término</th><th>Vencimento</th><th>Valor Contratado</th><th>Status</th></tr>';
            data.registros.forEach(c => {
                const statusLabels = { ativo: 'Ativo', pausado: 'Pausado', encerrado: 'Encerrado', cancelado: 'Cancelado' };
                const statusColors = { ativo: 'success', pausado: 'warning', encerrado: 'secondary', cancelado: 'danger' };
                const st = c.status || 'ativo';
                const badge = `<span class="badge bg-${statusColors[st] || 'secondary'}-subtle text-${statusColors[st] || 'secondary'}-emphasis">${escapeHtml(statusLabels[st] || st)}</span>`;

                const periodicidadeFormatada = formatarPeriodicidade(c.periodicidade);
                const dataInicio = c.data_inicio ? formatarData(c.data_inicio) : '—';
                const dataFim = c.data_fim ? formatarData(c.data_fim) : 'Indeterminado';
                const diaVenc = c.dia_vencimento ? `Dia ${c.dia_vencimento}` : '—';

                const valorContratado = formatarMoeda(c.valor_contratado);
                let descontoInfo = '';
                if (Number(c.desconto) > 0) {
                    descontoInfo = `<small class="d-block text-muted">Desc: ${escapeHtml(formatarMoeda(c.desconto))}</small>`;
                }

                html += `<tr>
                    <td><strong>#${escapeHtml(c.contrato_id || c.id)}</strong></td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(c.aluno)}</div>
                        <small class="text-muted">${escapeHtml(mascararCpf(c.cpf))}</small>
                    </td>
                    <td><span class="badge bg-primary-subtle text-primary-emphasis">${escapeHtml(c.plano)}</span></td>
                    <td>${escapeHtml(periodicidadeFormatada)}</td>
                    <td>${escapeHtml(dataInicio)}</td>
                    <td>${escapeHtml(dataFim)}</td>
                    <td>${escapeHtml(diaVenc)}</td>
                    <td class="text-end fw-semibold">${escapeHtml(valorContratado)}${descontoInfo}</td>
                    <td class="text-center">${badge}</td>
                </tr>`;
            });
            $('#tituloTabela').text('Relatório de Contratos de Planos');
            break;

        case 'contratos_servicos':
            cabecalho = '<tr><th>Item</th><th>Contrato</th><th>Aluno</th><th>Serviço Avulso</th><th>Qtd</th><th>Valor Unit.</th><th>Desconto</th><th>Total Líquido</th><th>Data Início</th><th>Status</th></tr>';
            data.registros.forEach(s => {
                const statusLabels = { ativo: 'Ativo', pausado: 'Pausado', encerrado: 'Encerrado', cancelado: 'Cancelado' };
                const statusColors = { ativo: 'success', pausado: 'warning', encerrado: 'secondary', cancelado: 'danger' };
                const st = s.status || 'ativo';
                const badge = `<span class="badge bg-${statusColors[st] || 'secondary'}-subtle text-${statusColors[st] || 'secondary'}-emphasis">${escapeHtml(statusLabels[st] || st)}</span>`;

                const dataInicio = s.data_inicio ? formatarData(s.data_inicio) : '—';
                const valorUnit = formatarMoeda(s.valor_unitario);
                const valorDesc = Number(s.valor_desconto) > 0 ? formatarMoeda(s.valor_desconto) : '—';
                const valorTotal = formatarMoeda(s.valor_total);

                html += `<tr>
                    <td><strong>#${escapeHtml(s.item_id || s.id)}</strong></td>
                    <td>#${escapeHtml(s.contrato_id)}</td>
                    <td>
                        <div class="fw-semibold">${escapeHtml(s.aluno)}</div>
                        <small class="text-muted">${escapeHtml(mascararCpf(s.cpf))}</small>
                    </td>
                    <td><span class="badge bg-info-subtle text-info-emphasis">${escapeHtml(s.servico)}</span></td>
                    <td class="text-center">${escapeHtml(s.quantidade)}</td>
                    <td class="text-end">${escapeHtml(valorUnit)}</td>
                    <td class="text-end text-muted">${escapeHtml(valorDesc)}</td>
                    <td class="text-end fw-semibold">${escapeHtml(valorTotal)}</td>
                    <td>${escapeHtml(dataInicio)}</td>
                    <td class="text-center">${badge}</td>
                </tr>`;
            });
            $('#tituloTabela').text('Relatório de Contratos de Serviços Avulsos');
            break;

        case 'presenca':
            cabecalho = '<tr><th>Data</th><th>Turma</th><th>Modalidade</th><th>Aluno</th><th>Situação</th></tr>';
            data.registros.forEach(p => {
                let badge = '';
                if (p.situacao === 'presente') badge = '<span class="badge bg-success-subtle text-success-emphasis">Presente</span>';
                else if (p.situacao === 'ausente') badge = '<span class="badge bg-danger-subtle text-danger-emphasis">Ausente</span>';
                else badge = '<span class="badge bg-warning-subtle text-warning-emphasis">Justificado</span>';
                html += `<tr><td>${escapeHtml(new Date(p.data_treino).toLocaleDateString('pt-BR'))}</td><td>${escapeHtml(p.turma || '-')}</td><td>${escapeHtml(p.modalidade || '-')}</td><td>${escapeHtml(p.aluno)}</td><td>${badge}</td></tr>`;
            });
            $('#tituloTabela').text('Relatório de Presença');
            break;

        case 'avaliacoes':
            cabecalho = '<tr><th>Data</th><th>Aluno</th><th>Avaliador</th><th>Peso (kg)</th><th>IMC</th><th>% Gordura</th><th>% Músculo</th></tr>';
            [...data.registros].reverse().forEach(av => {
                html += `<tr><td>${escapeHtml(formatarData(av.data_avaliacao))}</td><td>${escapeHtml(av.aluno)}</td><td>${escapeHtml(av.avaliador)}</td><td>${escapeHtml(formatarNumero(av.peso))}</td><td>${escapeHtml(formatarNumero(av.imc))}</td><td>${escapeHtml(formatarPercentual(av.percentual_gordura))}</td><td>${escapeHtml(formatarPercentual(av.percentual_musculo))}</td></tr>`;
            });
            $('#tituloTabela').text(data.registros[0]?.aluno ? `Evolução de ${data.registros[0].aluno}` : 'Relatório de Avaliações');
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
                const status = f.ativo ? '<span class="badge bg-success-subtle text-success-emphasis">Ativo</span>' : '<span class="badge bg-danger-subtle text-danger-emphasis">Inativo</span>';
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
    const quantidadeColunas = $('#cabecalhoTabela th').length || 1;
    $('#tabelaRelatorios').html(html || `<tr><td colspan="${quantidadeColunas}" class="text-center text-muted">Nenhum registro encontrado</td></tr>`);
}

function mascararCpf(cpf) {
    const digitos = String(cpf || '').replace(/\D/g, '');
    if (digitos.length <= 4) return digitos || '-';
    return '*'.repeat(digitos.length - 4) + digitos.slice(-4);
}

function formatarPeriodicidade(valor) {
    const mapa = {
        mensal: 'Mensal',
        trimestral: 'Trimestral',
        semestral: 'Semestral',
        anual: 'Anual',
        avulso: 'Avulso'
    };
    return mapa[String(valor || '').toLowerCase()] || (valor ? String(valor) : '—');
}

function formatarMoeda(valor) {
    const numero = Number(valor);
    if (!Number.isFinite(numero)) return 'R$ 0,00';
    return numero.toLocaleString('pt-BR', { style: 'currency', currency: 'BRL' });
}

function renderizarGraficos(data, tipoRelatorio) {
    if (graficoDist) graficoDist.destroy();
    if (graficoPresenca) graficoPresenca.destroy();
    $('#secaoGraficos').hide();
    graficoDist = null;
    graficoPresenca = null;

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
        contratos_planos: {
            titulo1: 'Planos Mais Contratados',
            titulo2: 'Receita Contratada por Plano',
            tipo1: 'doughnut',
            tipo2: 'bar',
            dados1: agruparCampo(registros, 'plano', 'Sem plano'),
            dados2: agruparSoma(registros, 'plano', 'valor_liquido')
        },
        contratos_servicos: {
            titulo1: 'Serviços Avulsos Mais Contratados',
            titulo2: 'Receita por Serviço Avulso',
            tipo1: 'bar',
            tipo2: 'bar',
            dados1: agruparSoma(registros, 'servico', 'quantidade'),
            dados2: agruparSoma(registros, 'servico', 'valor_total')
        },
        presenca: {
            titulo1: 'Evolução Temporal da Taxa de Presença',
            titulo2: 'Distribuição de Presenças, Ausências e Justificativas',
            tipo1: 'line',
            tipo2: 'doughnut',
            dados1: serieTaxaPresenca(registros),
            dados2: {
                labels: ['Presenças', 'Ausências', 'Justificativas'],
                valores: ['presente', 'ausente', 'justificado'].map(situacao =>
                    registros.filter(registro => registro.situacao === situacao).length)
            }
        },
        avaliacoes: {
            titulo1: 'Peso ao longo do tempo',
            titulo2: 'Composição corporal ao longo do tempo',
            tipo1: 'line',
            tipo2: 'line',
            dados1: serieTemporal(registros, [
                { campo: 'peso', rotulo: 'Peso (kg)', cor: 'rgba(54, 162, 235, 1)' }
            ]),
            dados2: serieTemporal(registros, [
                { campo: 'percentual_gordura', rotulo: '% Gordura', cor: 'rgba(255, 99, 132, 1)' },
                { campo: 'percentual_musculo', rotulo: '% Músculo', cor: 'rgba(75, 192, 192, 1)' }
            ])
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
            plugins: {
                legend: { display: tipo !== 'bar' },
                tooltip: dados.percentual ? {
                    callbacks: {
                        label: contexto => `${contexto.dataset.label}: ${formatarPercentual(contexto.parsed.y)}`
                    }
                } : undefined
            },
            scales: tipo === 'doughnut' ? undefined : {
                y: dados.percentual ? {
                    min: 0,
                    max: 100,
                    ticks: { callback: valor => `${valor}%` }
                } : { beginAtZero: tipo !== 'line' }
            }
        }
    });
}

function serieTaxaPresenca(registros) {
    const dias = {};
    registros.forEach(registro => {
        const dia = String(registro.data_treino || '').slice(0, 10);
        if (!dia || !['presente', 'ausente', 'justificado'].includes(registro.situacao)) return;
        dias[dia] ??= { presentes: 0, total: 0 };
        dias[dia].total++;
        if (registro.situacao === 'presente') dias[dia].presentes++;
    });
    const datas = Object.keys(dias).sort();
    return {
        percentual: true,
        labels: datas.map(formatarData),
        datasets: [{
            label: 'Presenças / registros do dia (%) — inclui ausências justificadas no total',
            data: datas.map(dia => dias[dia].presentes / dias[dia].total * 100),
            borderColor: 'rgba(54, 162, 235, 1)',
            backgroundColor: 'rgba(54, 162, 235, 0.15)',
            tension: 0,
            pointRadius: 4,
            fill: false
        }]
    };
}

function formatarData(valor) {
    if (!valor) return '-';
    const data = new Date(`${String(valor).slice(0, 10)}T00:00:00`);
    return Number.isNaN(data.getTime()) ? String(valor) : data.toLocaleDateString('pt-BR');
}

function formatarNumero(valor) {
    const numero = Number(valor);
    return Number.isFinite(numero) ? numero.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 2 }) : '-';
}

function formatarPercentual(valor) {
    const numero = Number(valor);
    return Number.isFinite(numero) ? `${numero.toLocaleString('pt-BR', { minimumFractionDigits: 1, maximumFractionDigits: 2 })}%` : '-';
}

function serieTemporal(registros, series) {
    const ordenados = [...registros].sort((a, b) => String(a.data_avaliacao).localeCompare(String(b.data_avaliacao)));
    return {
        labels: ordenados.map(registro => formatarData(registro.data_avaliacao)),
        datasets: series.map(serie => ({
            label: serie.rotulo,
            data: ordenados.map(registro => {
                const valor = Number(registro[serie.campo]);
                return Number.isFinite(valor) ? valor : null;
            }),
            borderColor: serie.cor,
            backgroundColor: serie.cor.replace('1)', '0.15)'),
            spanGaps: true,
            tension: 0.25,
            fill: false
        }))
    };
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

function agruparSoma(registros, campoLabel, campoValor) {
    const soma = {};
    registros.forEach(registro => {
        const rotulo = registro[campoLabel] || 'Outro';
        const valor = Number(registro[campoValor]) || 0;
        soma[rotulo] = (soma[rotulo] || 0) + valor;
    });
    return {
        labels: Object.keys(soma),
        valores: Object.values(soma).map(v => Math.round(v * 100) / 100)
    };
}

function agruparStatusContrato(registros) {
    const statusLabels = { ativo: 'Ativo', pausado: 'Pausado', encerrado: 'Encerrado', cancelado: 'Cancelado' };
    const agrupado = { 'Ativo': 0, 'Pausado': 0, 'Encerrado': 0, 'Cancelado': 0 };
    registros.forEach(registro => {
        const rotulo = statusLabels[registro.status] || 'Outro';
        agrupado[rotulo] = (agrupado[rotulo] || 0) + 1;
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
    const tipoRelatorio = $('#tipoRelatorio').val();
    if (!validarTurmaRelatorio(tipoRelatorio)) return;
    if (['avaliacoes', 'presenca'].includes(tipoRelatorio) && !$('#aluno').val()) {
        const nomeRelatorio = tipoRelatorio === 'presenca' ? 'presença' : 'avaliações físicas';
        Swal.fire('Atenção', `Selecione um aluno para exportar o relatório de ${nomeRelatorio}.`, 'warning');
        return;
    }

    const params = new URLSearchParams({
        tipo: tipoRelatorio,
        modalidade: $('#modalidade').val(),
        aluno: $('#aluno').val(),
        turma: $('#turma').val(),
        cargo: $('#cargo').val(),
        plano: $('#plano').val(),
        servico: $('#servico').val(),
        dataInicio: $('#dataInicio').val(),
        dataFim: $('#dataFim').val(),
        status: $('#status').val(),
        formato: formato
    });
    window.location.href = `/ctt/api/relatorios/exportar?${params.toString()}`;
}
