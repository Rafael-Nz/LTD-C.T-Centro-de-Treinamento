<?php
// routes/admin.php
return function($router) {
    // Dashboard
    $router->add('', 'home.php');
    $router->add('inicio', 'home.php'); // Alias para Dashboard

    // Alunos
    $router->add('alunos', 'alunos.php');
    $router->add('alunos/cadastrar', 'aluno_form.php', ['acao' => 'cadastrar']);
    $router->add('alunos/editar/{id}', 'aluno_form.php', ['acao' => 'editar']);
    $router->add('alunos/visualizar/{id}', 'aluno_detalhe.php');
    $router->add('alunos/{id}/treinos', 'aluno_treinos.php');

    // Turmas
    $router->add('turmas', 'turmas.php');
    $router->add('turmas/cadastrar', 'turma_form.php', ['acao' => 'cadastrar']);
    $router->add('turmas/editar/{id}', 'turma_form.php', ['acao' => 'editar']);
    $router->add('turmas/{id}/alunos', 'turma_alunos.php');
    $router->add('turmas/{id}/gerenciar', 'gerenciar_turma.php');

    // Treinos
    $router->add('treinos', 'treinos.php');
    $router->add('treinos/cadastrar', 'treino_form.php', ['acao' => 'cadastrar']);
    $router->add('treinos/editar/{id}', 'treino_form.php', ['acao' => 'editar']);

    // Funcionários
    $router->add('funcionarios', 'funcionarios.php');
    $router->add('funcionarios/cadastrar', 'funcionario_form.php', ['acao' => 'cadastrar']);
    $router->add('funcionarios/editar/{id}', 'funcionario_form.php', ['acao' => 'editar']);

    // Locais
    $router->add('locais', 'locais.php');
    $router->add('locais/cadastrar', 'local_form.php', ['acao' => 'cadastrar']);
    $router->add('locais/editar/{id}', 'local_form.php', ['acao' => 'editar']);

    // Configurações
    $router->add('configuracoes', 'configuracoes.php');
    $router->add('configuracoes/geral', 'configuracoes.php', ['tab' => 'geral']);
    $router->add('configuracoes/usuarios', 'configuracoes.php', ['tab' => 'usuarios']);
    $router->add('configuracoes/permissoes', 'configuracoes.php', ['tab' => 'permissoes']);

    // Perfil
    $router->add('perfil', 'perfil.php');
    $router->add('perfil/editar', 'perfil_form.php');
    $router->add('perfil/senha', 'perfil_form.php', ['tab' => 'senha']);

    // Relatórios
    $router->add('relatorios', 'relatorios.php');
    $router->add('relatorios/alunos', 'relatorios.php', ['tipo' => 'alunos']);
    $router->add('relatorios/financeiro', 'relatorios.php', ['tipo' => 'financeiro']);

    // Cargos
    $router->add('cargos', 'cargos.php');
    $router->add('cargos/cadastrar', 'cargo_form.php', ['acao' => 'cadastrar']);
    $router->add('cargos/editar/{id}', 'cargo_form.php', ['acao' => 'editar']);

    // Avaliações
    $router->add('avaliacoes/cadastrar', 'avaliacao_form.php', ['acao' => 'cadastrar']);
    $router->add('avaliacoes/editar/{id}', 'avaliacao_form.php', ['acao' => 'editar']);

    // Autenticação
    $router->add('login', 'login.php');
    $router->add('logout', 'logout.php');
    $router->add('esqueci-senha', 'recuperar-senha.php');
    $router->add('redefinir-senha/{token}', 'redefinir-senha.php');
};