<?php
$auth = function() {
    if (session_status() === PHP_SESSION_NONE) session_start();

    $userId = $_SESSION['user_id'] ?? null;
    $userTipo = $_SESSION['user_tipo'] ?? null;

    if (empty($userId)) {
        header('Location: /ctt/admin/login');
        exit;
    }
    
    $tiposAutorizados = ['admin', 'funcionario'];
    if (!in_array($userTipo, $tiposAutorizados)) {
        // Opcional: Destruir a sessão ou apenas redirecionar com erro
        header('Location: /ctt/admin/login?error=permissao');
        exit;
    }
};

$guest = function() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    if (!empty($_SESSION['user_id'])) {
        header('Location: /ctt/admin/inicio');
        exit;
    }
};

$logoutAction = function() {
    if (session_status() === PHP_SESSION_NONE) session_start();
    $_SESSION = []; // Limpa os dados
    session_destroy(); // Destrói a sessão
    header('Location: /ctt/admin/login'); // Redireciona
    exit;
};

// routes/admin.php
return function($router) use ($auth, $guest, $logoutAction) {
    
    // Dashboard
    $router->add('', 'home.php', [], [$auth]);
    $router->add('inicio', 'home.php', [], [$auth]); // Alias para Dashboard

    // Alunos
    $router->add('alunos', 'alunos.php', [], [$auth]);
    $router->add('alunos/cadastrar', 'aluno_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('alunos/editar/{id}', 'aluno_form.php', ['acao' => 'editar'], [$auth]);
    $router->add('alunos/visualizar/{id}', 'aluno_detalhe.php', [], [$auth]);
    $router->add('alunos/{aluno_id}/avaliacoes/cadastrar', 'avaliacao_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('alunos/{aluno_id}/avaliacoes/editar/{id}', 'avaliacao_form.php', ['acao' => 'editar'], [$auth]);
    $router->add('alunos/{id}/treinos', 'aluno_treinos.php', [], [$auth]);

    // Turmas
    $router->add('turmas', 'turmas.php', [], [$auth]);
    $router->add('turmas/cadastrar', 'turma_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('turmas/editar/{id}', 'turma_form.php', ['acao' => 'editar'], [$auth]);
    $router->add('turmas/{id}/gerenciar', 'gerenciar_turma.php', [], [$auth]);

    // Treinos
    $router->add('treinos', 'treinos.php', [], [$auth]);
    $router->add('treinos/cadastrar', 'treino_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('treinos/editar/{id}', 'treino_form.php', ['acao' => 'editar'], [$auth]);

    // Funcionários
    $router->add('funcionarios', 'funcionarios.php', [], [$auth]);
    $router->add('funcionarios/cadastrar', 'funcionario_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('funcionarios/editar/{id}', 'funcionario_form.php', ['acao' => 'editar'], [$auth]);

    // Locais
    $router->add('locais', 'locais.php', [], [$auth]);
    $router->add('locais/cadastrar', 'local_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('locais/editar/{id}', 'local_form.php', ['acao' => 'editar'], [$auth]);

    // Configurações
    $router->add('configuracoes', 'configuracoes.php', [], [$auth]);
    $router->add('configuracoes/geral', 'configuracoes.php', ['tab' => 'geral'], [$auth]);
    $router->add('configuracoes/usuarios', 'configuracoes.php', ['tab' => 'usuarios'], [$auth]);
    $router->add('configuracoes/permissoes', 'configuracoes.php', ['tab' => 'permissoes'], [$auth]);

    // Perfil
    $router->add('perfil', 'perfil.php', [], [$auth]);
    $router->add('perfil/editar', 'perfil_form.php', [], [$auth]);
    $router->add('perfil/senha', 'perfil_form.php', ['tab' => 'senha'], [$auth]);

    // Relatórios
    $router->add('relatorios', 'relatorios.php', [], [$auth]);
    $router->add('relatorios/alunos', 'relatorios.php', ['tipo' => 'alunos'], [$auth]);
    $router->add('relatorios/financeiro', 'relatorios.php', ['tipo' => 'financeiro'], [$auth]);

    // Cargos
    $router->add('cargos', 'cargos.php', [], [$auth]);
    $router->add('cargos/cadastrar', 'cargo_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('cargos/editar/{id}', 'cargo_form.php', ['acao' => 'editar'], [$auth]);

    // Avaliações
    $router->add('avaliacoes/cadastrar', 'avaliacao_form.php', ['acao' => 'cadastrar'], [$auth]);
    $router->add('avaliacoes/editar/{id}', 'avaliacao_form.php', ['acao' => 'editar'], [$auth]);

    // Autenticação
    $router->add('login', 'login.php', [], [$guest]);
    $router->add('logout', '', [], [$logoutAction]); // Rota de logout sem view, apenas ação
    $router->add('esqueci-senha', 'recuperar-senha.php', [], [$guest]);
    $router->add('redefinir-senha/{token}', 'redefinir-senha.php', [], [$guest]);
};
