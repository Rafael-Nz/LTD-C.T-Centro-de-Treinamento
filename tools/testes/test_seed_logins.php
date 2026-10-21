<?php
require_once dirname(__DIR__, 2) . '/autoload.php';

use Core\Database\Database;
use Auth\AuthService;
use Auth\DTO\LoginDTO;

$pdo = Database::getConnection();

echo "=== USUARIOS ===\n";
$users = $pdo->query("SELECT id, nome, email, cpf, tipo_usuario, ativo, conta_ativada FROM usuario ORDER BY id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($users as $u) {
    echo "ID: {$u['id']} | {$u['nome']} | {$u['email']} | {$u['tipo_usuario']} | Ativo: {$u['ativo']} | Ativada: {$u['conta_ativada']}\n";
}

echo "\n=== FUNCIONARIOS ===\n";
$funcs = $pdo->query("SELECT f.usuario_id, u.nome, f.cargo_id, f.conta_ativada, f.senha FROM funcionario f JOIN usuario u ON u.id = f.usuario_id ORDER BY f.usuario_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($funcs as $f) {
    $ok = password_verify('Senha@Teste123', $f['senha']) ? 'SIM' : 'NAO';
    echo "UID: {$f['usuario_id']} | {$f['nome']} | Cargo: {$f['cargo_id']} | Ativada: {$f['conta_ativada']} | SenhaOK: $ok\n";
}

echo "\n=== ALUNOS ===\n";
$alunos = $pdo->query("SELECT a.usuario_id, u.nome, a.codigo_matricula, a.conta_ativada, a.senha FROM aluno a JOIN usuario u ON u.id = a.usuario_id ORDER BY a.usuario_id")->fetchAll(PDO::FETCH_ASSOC);
foreach ($alunos as $a) {
    $ok = password_verify('Senha@Teste123', $a['senha']) ? 'SIM' : 'NAO';
    echo "UID: {$a['usuario_id']} | {$a['nome']} | Matricula: {$a['codigo_matricula']} | Ativada: {$a['conta_ativada']} | SenhaOK: $ok\n";
}

echo "\n=== TESTE DE LOGIN REAL VIA AUTHSERVICE ===\n";
$auth = new AuthService();

// 1. Teste Login Admin
$dtoAdmin = new LoginDTO(['login' => 'admin@centrotreinamento.com', 'senha' => 'Senha@Teste123']);
try {
    $res = $auth->login($dtoAdmin, 'admin');
    echo "[PASS] Login Admin bem-sucedido! Cargo: {$res['cargo_nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Admin falhou: " . $e->getMessage() . "\n";
}

// 2. Teste Login Gerente (Colaborador)
$dtoGerente = new LoginDTO(['login' => 'roberto.gerente@ctt.teste', 'senha' => 'Senha@Teste123']);
try {
    $res = $auth->login($dtoGerente, 'admin');
    echo "[PASS] Login Gerente bem-sucedido! Cargo: {$res['cargo_nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Gerente falhou: " . $e->getMessage() . "\n";
}

// 3. Teste Login Instrutor (Colaborador)
$dtoInstrutor = new LoginDTO(['login' => 'paulo.instrutor@ctt.teste', 'senha' => 'Senha@Teste123']);
try {
    $res = $auth->login($dtoInstrutor, 'admin');
    echo "[PASS] Login Instrutor bem-sucedido! Cargo: {$res['cargo_nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Instrutor falhou: " . $e->getMessage() . "\n";
}

// 4. Teste Login Atendente (Colaborador)
$dtoAtendente = new LoginDTO(['login' => 'camila.atendente@ctt.teste', 'senha' => 'Senha@Teste123']);
try {
    $res = $auth->login($dtoAtendente, 'admin');
    echo "[PASS] Login Atendente bem-sucedido! Cargo: {$res['cargo_nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Atendente falhou: " . $e->getMessage() . "\n";
}

// 5. Teste Login Aluna (com E-mail na Area do Aluno)
$dtoAlunoEmail = new LoginDTO(['login' => 'ana.souza@ctt.teste', 'senha' => 'Senha@Teste123']);
try {
    $res = $auth->login($dtoAlunoEmail, 'aluno');
    echo "[PASS] Login Aluna (E-mail) bem-sucedido! Cargo: {$res['cargo_nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Aluna (E-mail) falhou: " . $e->getMessage() . "\n";
}

// 6. Teste Login Aluno (com Matricula na Area do Aluno)
$dtoAlunoMatricula = new LoginDTO(['login' => '202604000002', 'senha' => 'Senha@Teste123']); // Bruno Lima
try {
    $res = $auth->login($dtoAlunoMatricula, 'aluno');
    echo "[PASS] Login Aluno (Matricula) bem-sucedido! Nome: {$res['nome']}\n";
} catch (Exception $e) {
    echo "[FAIL] Login Aluno (Matricula) falhou: " . $e->getMessage() . "\n";
}

// 6b. Teste Bloqueio: Aluno tentando entrar com CPF (substituido por matricula)
$dtoAlunoCpf = new LoginDTO(['login' => '81000000002', 'senha' => 'Senha@Teste123']);
try {
    $auth->login($dtoAlunoCpf, 'aluno');
    echo "[FAIL] Aluno conseguiu logar com CPF na area do aluno!\n";
} catch (Exception $e) {
    echo "[PASS] Bloqueio correto de CPF na area do aluno: " . $e->getMessage() . "\n";
}

// 7. Teste Bloqueio: Aluno tentando entrar na area administrativa
try {
    $auth->login($dtoAlunoEmail, 'admin');
    echo "[FAIL] Aluno conseguiu entrar na area administrativa!\n";
} catch (Exception $e) {
    echo "[PASS] Bloqueio correto de aluno na area administrativa: " . $e->getMessage() . "\n";
}

// 8. Teste Bloqueio: Funcionario tentando entrar na area do aluno
try {
    $auth->login($dtoGerente, 'aluno');
    echo "[FAIL] Funcionario conseguiu entrar na area do aluno!\n";
} catch (Exception $e) {
    echo "[PASS] Bloqueio correto de funcionario na area do aluno: " . $e->getMessage() . "\n";
}
