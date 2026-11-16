<?php

declare(strict_types=1);

// Nunca conecta ao banco do .env: toda a suite de seguranca usa SQLite em memoria.
require_once dirname(__DIR__, 3) . '/autoload.php';

function securityTestDatabase(): PDO
{
    if (!in_array('sqlite', PDO::getAvailableDrivers(), true)) {
        throw new RuntimeException('A suite isolada exige pdo_sqlite.');
    }
    if (ob_get_level() === 0) ob_start();
    if (session_status() !== PHP_SESSION_ACTIVE) {
        ini_set('session.use_cookies', '0');
        session_set_save_handler(new class implements SessionHandlerInterface {
            private array $sessions = [];
            public function open(string $path, string $name): bool { return true; }
            public function close(): bool { return true; }
            public function read(string $id): string|false { return $this->sessions[$id] ?? ''; }
            public function write(string $id, string $data): bool { $this->sessions[$id] = $data; return true; }
            public function destroy(string $id): bool { unset($this->sessions[$id]); return true; }
            public function gc(int $max_lifetime): int|false { return 0; }
        }, true);
        session_start();
    }
    $_SESSION = [];
    $db = new PDO('sqlite::memory:', null, null, [PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION]);
    (new ReflectionProperty(\Core\Database\Database::class, 'connection'))->setValue(null, $db);
    $db->exec('CREATE TABLE usuario (id INTEGER PRIMARY KEY, nome TEXT, sobrenome TEXT, cpf TEXT, email TEXT UNIQUE,
        senha TEXT, ativo INTEGER, conta_ativada INTEGER, tipo_usuario TEXT, genero TEXT, endereco_id INTEGER);
        CREATE TABLE aluno (usuario_id INTEGER PRIMARY KEY, codigo_matricula TEXT, senha TEXT, conta_ativada INTEGER);
        CREATE TABLE funcionario (usuario_id INTEGER PRIMARY KEY, cargo_id INTEGER, senha TEXT, conta_ativada INTEGER);
        CREATE TABLE cargo (id INTEGER PRIMARY KEY, nome TEXT, ativo INTEGER, descricao TEXT,
            salario_base REAL, data_criacao TEXT, data_atualizacao TEXT);
        CREATE TABLE permissao (id INTEGER PRIMARY KEY, slug TEXT, modulo TEXT, nome TEXT, descricao TEXT);
        CREATE TABLE cargo_permissao (cargo_id INTEGER, permissao_id INTEGER);
        INSERT INTO cargo (id, nome, ativo) VALUES (2, \'Instrutor\', 1), (3, \'Limitado\', 1), (4, \'Gerente\', 1), (5, \'Atendente\', 1);');
    $slugs = [
        'turmas.visualizar', 'turmas.criar', 'turmas.editar', 'turmas.gerenciar', 'turmas.desativar',
        'treinos.visualizar', 'treinos.criar', 'treinos.editar', 'treinos.desativar',
        'avaliacoes.visualizar', 'avaliacoes.criar', 'avaliacoes.editar',
        'financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento',
        'relatorios.visualizar', 'relatorios.exportar', 'usuarios.gerenciar',
        'alunos.visualizar', 'alunos.criar', 'alunos.editar', 'alunos.desativar',
        'funcionarios.visualizar', 'funcionarios.gerenciar', 'cargos.visualizar', 'cargos.gerenciar',
        'modalidades.visualizar', 'modalidades.gerenciar', 'locais.visualizar', 'locais.gerenciar',
        'anamnese.visualizar', 'anamnese.preencher', 'configuracoes.gerenciar',
    ];
    foreach ($slugs as $i => $slug) {
        $db->prepare('INSERT INTO permissao VALUES (?, ?, ?, ?, ?)')->execute([$i + 1, $slug, explode('.', $slug)[0], $slug, 'Teste']);
        $cargos = [];
        if (in_array($slug, ['turmas.visualizar', 'treinos.visualizar', 'treinos.criar', 'treinos.editar', 'avaliacoes.visualizar', 'avaliacoes.criar'], true)) $cargos[] = 2;
        if (str_starts_with($slug, 'financeiro.') || str_starts_with($slug, 'relatorios.')) $cargos[] = 4;
        if ($slug === 'financeiro.registrar_pagamento') $cargos[] = 5;
        foreach ($cargos as $cargo) $db->prepare('INSERT INTO cargo_permissao VALUES (?, ?)')->execute([$cargo, $i + 1]);
    }
    $hash = password_hash('TesteSenhaSegura123!', PASSWORD_BCRYPT, ['cost' => 4]);
    foreach ([1 => 'admin', 2 => 'funcionario', 3 => 'funcionario', 4 => 'funcionario', 5 => 'funcionario', 6 => 'funcionario', 10 => 'aluno', 99 => 'aluno'] as $id => $tipo) {
        $db->prepare('INSERT INTO usuario VALUES (?, ?, ?, ?, ?, ?, 1, 0, ?, ?, NULL)')
            ->execute([$id, $id === 10 ? 'Carlos Aluno' : 'Usuario ' . $id, 'Teste', 'CPF-' . $id, 'teste' . $id . '@example.invalid', $hash, $tipo, 'O']);
    }
    foreach ([10, 99, 6] as $id) $db->prepare('INSERT INTO aluno VALUES (?, ?, ?, 0)')->execute([$id, 'M-' . $id, $hash]);
    foreach ([2 => 2, 3 => 4, 4 => 5, 5 => 3, 6 => 2] as $id => $cargo) $db->prepare('INSERT INTO funcionario VALUES (?, ?, ?, 0)')->execute([$id, $cargo, $hash]);
    securityNextRequest();
    return $db;
}

function securityNextRequest(?string $area = null, string $method = 'GET'): void
{
    foreach (['context' => null, 'validatedGuards' => []] as $property => $value) {
        if (property_exists(\Core\Auth\Auth::class, $property)) {
            (new ReflectionProperty(\Core\Auth\Auth::class, $property))->setValue(null, $value);
        }
    }
    unset($_SERVER['HTTP_X_AUTH_AREA'], $_SERVER['HTTP_X_CSRF_TOKEN']);
    if ($area !== null) $_SERVER['HTTP_X_AUTH_AREA'] = $area;
    $_SERVER['REQUEST_METHOD'] = $method;
    $_SERVER['REQUEST_URI'] = '/ctt/api/teste';
    http_response_code(200);
}
