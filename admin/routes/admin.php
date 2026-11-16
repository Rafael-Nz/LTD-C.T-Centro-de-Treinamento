<?php
$startSession = function (): void {
    \Core\Auth\Auth::start();
};

$auth = function () use ($startSession) {
    $startSession();
    \Core\Auth\Auth::setContext(\Core\Auth\Auth::GUARD_ADMIN);

    $adminId = \Core\Auth\Auth::id(\Core\Auth\Auth::GUARD_ADMIN);
    $userTipo = \Core\Auth\Auth::userTipo(\Core\Auth\Auth::GUARD_ADMIN);

    if (empty($adminId)) {
        header('Location: /ctt/admin/login');
        exit;
    }

    $tiposAutorizados = ['admin', 'funcionario'];
    if (!in_array($userTipo, $tiposAutorizados, true)) {
        header('Location: /ctt/admin/login?error=permissao');
        exit;
    }
};

$guest = function () use ($startSession) {
    $startSession();
    \Core\Auth\Auth::setContext(\Core\Auth\Auth::GUARD_ADMIN);

    $adminId = \Core\Auth\Auth::id(\Core\Auth\Auth::GUARD_ADMIN);
    if (!empty($adminId)) {
        header('Location: /ctt/admin/inicio');
        exit;
    }
};

// routes/admin.php
return function ($router) use ($auth, $guest, $startSession) {

    $logout = function () use ($startSession): void {
        if (($_SERVER['REQUEST_METHOD'] ?? 'GET') !== 'POST') {
            http_response_code(405);
            header('Allow: POST');
            exit;
        }
        $startSession();
        \Core\Auth\Csrf::validate();
        \Core\Auth\Auth::logout(\Core\Auth\Auth::GUARD_ADMIN);
        header('Location: /ctt/admin/login');
        exit;
    };

    $can = function (string $permission) use ($auth) {
        return function () use ($auth, $permission) {
            $auth();
            if (!\Core\Auth\Auth::can($permission)) {
                header('Location: /ctt/admin/inicio?error=sem_permissao');
                exit;
            }
        };
    };

    $canAny = function (array $permissions) use ($auth) {
        return function () use ($auth, $permissions) {
            $auth();
            if (!\Core\Auth\Auth::canAny($permissions)) {
                header('Location: /ctt/admin/inicio?error=sem_permissao');
                exit;
            }
        };
    };

    $router->add('logout', '', [], [$logout]);

    // Dashboard
    $router->add('', 'home.php', [], [$auth]);
    $router->add('inicio', 'home.php', [], [$auth]); // Alias para Dashboard

    // Alunos
    $router->add('alunos', 'alunos.php', [], [$can('alunos.visualizar')]);
    $router->add('alunos/cadastrar', 'aluno_form.php', ['acao' => 'cadastrar'], [$can('alunos.criar')]);
    $router->add('alunos/editar/{id}', 'aluno_form.php', ['acao' => 'editar'], [$can('alunos.editar')]);
    $router->add('alunos/visualizar/{id}', 'aluno_detalhe.php', [], [$can('alunos.visualizar')]);
    $router->add('alunos/{aluno_id}/avaliacoes/cadastrar', 'avaliacao_form.php', ['acao' => 'cadastrar'], [$can('avaliacoes.criar')]);
    $router->add('alunos/{aluno_id}/avaliacoes/editar/{id}', 'avaliacao_form.php', ['acao' => 'editar'], [$can('avaliacoes.editar')]);
    $router->add('alunos/{id}/treinos', 'aluno_treinos.php', [], [$canAny(['alunos.visualizar', 'treinos.visualizar'])]);

    // Turmas
    $router->add('turmas', 'turmas.php', [], [$can('turmas.visualizar')]);
    $router->add('turmas/cadastrar', 'turma_form.php', ['acao' => 'cadastrar'], [$can('turmas.criar')]);
    $router->add('turmas/editar/{id}', 'turma_form.php', ['acao' => 'editar'], [$can('turmas.editar')]);
    $router->add('turmas/{id}/gerenciar', 'gerenciar_turma.php', [], [$canAny(['turmas.visualizar', 'turmas.gerenciar'])]);

    // Treinos
    $router->add('treinos', 'treinos.php', [], [$can('treinos.visualizar')]);
    $router->add('treinos/cadastrar', 'treino_form.php', ['acao' => 'cadastrar'], [$can('treinos.criar')]);
    $router->add('treinos/editar/{id}', 'treino_form.php', ['acao' => 'editar'], [$can('treinos.editar')]);

    // Funcionários
    $router->add('funcionarios', 'funcionarios.php', [], [$can('funcionarios.visualizar')]);
    $router->add('funcionarios/cadastrar', 'funcionario_form.php', ['acao' => 'cadastrar'], [$can('funcionarios.gerenciar')]);
    $router->add('funcionarios/editar/{id}', 'funcionario_form.php', ['acao' => 'editar'], [$can('funcionarios.gerenciar')]);

    // Locais
    $router->add('locais', 'locais.php', [], [$can('locais.visualizar')]);
    $router->add('locais/cadastrar', 'local_form.php', ['acao' => 'cadastrar'], [$can('locais.gerenciar')]);
    $router->add('locais/editar/{id}', 'local_form.php', ['acao' => 'editar'], [$can('locais.gerenciar')]);

    // Configurações
    $router->add('configuracoes', 'configuracoes.php', [], [$canAny(['cargos.visualizar', 'cargos.gerenciar', 'modalidades.visualizar', 'modalidades.gerenciar', 'configuracoes.gerenciar', 'usuarios.gerenciar'])]);
    $router->add('configuracoes/geral', 'configuracoes.php', ['tab' => 'geral'], [$can('configuracoes.gerenciar')]);
    $router->add('configuracoes/usuarios', 'configuracoes.php', ['tab' => 'usuarios'], [$can('usuarios.gerenciar')]);
    $router->add('configuracoes/permissoes', 'configuracoes.php', ['tab' => 'permissoes'], [$can('cargos.gerenciar')]);

    // Perfil
    $router->add('perfil', 'perfil.php', [], [$auth]);
    $router->add('perfil/editar', 'perfil_form.php', [], [$auth]);
    $router->add('perfil/senha', 'perfil_form.php', ['tab' => 'senha'], [$auth]);

    // Relatórios
    $router->add('relatorios', 'relatorios.php', [], [$can('relatorios.visualizar')]);
    $router->add('relatorios/alunos', 'relatorios.php', ['tipo' => 'alunos'], [$can('relatorios.visualizar')]);
    $router->add('relatorios/contratos-planos', 'relatorios.php', ['tipo' => 'contratos_planos'], [$can('relatorios.visualizar')]);
    $router->add('relatorios/contratos-servicos', 'relatorios.php', ['tipo' => 'contratos_servicos'], [$can('relatorios.visualizar')]);
    $router->add('relatorios/contratos', 'relatorios.php', ['tipo' => 'contratos_planos'], [$can('relatorios.visualizar')]);
    $router->add('relatorios/financeiro', 'relatorios.php', ['tipo' => 'financeiro'], [$can('relatorios.visualizar')]);

    // Financeiro
    $router->add('financeiro', 'financeiro.php', [], [$canAny(['financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento'])]);
    $router->add('financeiro/servicos', 'financeiro_servicos.php', [], [$canAny(['financeiro.visualizar', 'financeiro.gerenciar'])]);
    $router->add('financeiro/planos', 'financeiro_planos.php', [], [$canAny(['financeiro.visualizar', 'financeiro.gerenciar'])]);
    $router->add('financeiro/servicos/cadastrar', 'servico_form.php', [], [$can('financeiro.gerenciar')]);
    $router->add('financeiro/planos/cadastrar', 'plano_form.php', [], [$can('financeiro.gerenciar')]);
    $router->add('financeiro/servicos/editar/{id}', 'servico_form.php', [], [$can('financeiro.gerenciar')]);
    $router->add('financeiro/planos/editar/{id}', 'plano_form.php', [], [$can('financeiro.gerenciar')]);
    $router->add('financeiro/contratos', '', [], [$canAny(['financeiro.visualizar', 'financeiro.gerenciar']), static function () {
        header('Location: /ctt/admin/financeiro');
        exit;
    }]);
    $router->add('financeiro/cobrancas', 'financeiro_cobrancas.php', [], [$canAny(['financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento'])]);
    $router->add('financeiro/contratos/cadastrar', 'contrato_form.php', ['acao' => 'cadastrar'], [$can('financeiro.gerenciar')]);

    // Cargos
    $router->add('cargos', 'configuracoes.php', ['tab' => 'cargos'], [$canAny(['cargos.visualizar', 'cargos.gerenciar'])]);
    $router->add('cargos/cadastrar', 'cargo_form.php', ['acao' => 'cadastrar'], [$can('cargos.gerenciar')]);
    $router->add('cargos/editar/{id}', 'cargo_form.php', ['acao' => 'editar'], [$can('cargos.gerenciar')]);

    // Modalidades
    $router->add('modalidades', 'configuracoes.php', ['tab' => 'modalidades'], [$canAny(['modalidades.visualizar', 'modalidades.gerenciar'])]);
    $router->add('modalidades/cadastrar', 'modalidade_form.php', ['acao' => 'cadastrar'], [$can('modalidades.gerenciar')]);
    $router->add('modalidades/editar/{id}', 'modalidade_form.php', ['acao' => 'editar'], [$can('modalidades.gerenciar')]);

    // Avaliações
    $router->add('avaliacoes/cadastrar', 'avaliacao_form.php', ['acao' => 'cadastrar'], [$can('avaliacoes.criar')]);
    $router->add('avaliacoes/editar/{id}', 'avaliacao_form.php', ['acao' => 'editar'], [$can('avaliacoes.editar')]);

    // Autenticação
    $router->add('login', 'login.php', [], [$guest]);
    $router->add('ativar-conta/{token}', 'ativar-conta.php');
    $router->add('esqueci-senha', 'recuperar-senha.php', [], [$guest]);
    $router->add('redefinir-senha/{token}', 'redefinir-senha.php', [], [$guest]);
};
