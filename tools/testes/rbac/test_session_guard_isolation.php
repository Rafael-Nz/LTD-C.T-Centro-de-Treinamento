<?php

declare(strict_types=1);

require_once dirname(__DIR__, 3) . '/autoload.php';

use Core\Auth\Auth;

function assertTest(bool $condition, string $message): void
{
    if (!$condition) {
        echo "  [FAIL] {$message}\n";
        throw new RuntimeException("FALHA: {$message}");
    }
    echo "  [PASS] {$message}\n";
}

echo "\n=== TESTE DE ISOLAMENTO DE GUARDS DE SESSÃO (ADMIN vs ALUNO) ===\n\n";

// -------------------------------------------------------------
// 1. Inicializa Sessão Limpa
// -------------------------------------------------------------
echo "1. Inicializando ambiente de sessão...\n";
if (session_status() === PHP_SESSION_ACTIVE) {
    session_destroy();
}
$_SESSION = [];
Auth::start();
assertTest(empty($_SESSION), "Sessão iniciada limpa.");

// -------------------------------------------------------------
// 2. Simula Login do Aluno
// -------------------------------------------------------------
echo "\n2. Efetuando Login de Aluno...\n";
Auth::login([
    'id' => 10,
    'nome' => 'Carlos Aluno',
    'tipo' => 'aluno',
    'cargo_id' => null,
    'cargo_nome' => null,
    'permissoes' => []
], 'aluno');

assertTest(isset($_SESSION['auth_aluno']), "\$_SESSION['auth_aluno'] foi preenchida.");
assertTest($_SESSION['auth_aluno']['user_id'] === 10, "Aluno ID é 10.");
assertTest($_SESSION['auth_aluno']['user_tipo'] === 'aluno', "Aluno tipo é 'aluno'.");
assertTest(Auth::aluno()->id() === 10, "Auth::aluno()->id() retorna 10.");
assertTest(Auth::aluno()->isAuthenticated() === true, "Auth::aluno()->isAuthenticated() é true.");
assertTest(Auth::admin()->isAuthenticated() === false, "Auth::admin()->isAuthenticated() é false (admin não está logado).");

// -------------------------------------------------------------
// 3. Simula Tentativa de Acesso ao Admin tendo apenas Sessão de Aluno
// -------------------------------------------------------------
echo "\n3. Verificando comportamento de acesso ao Admin com sessão de Aluno ativa...\n";
Auth::setContext(Auth::GUARD_ADMIN);
$adminId = Auth::id(Auth::GUARD_ADMIN);
$userTipo = Auth::userTipo(Auth::GUARD_ADMIN);

assertTest($adminId === null, "Auth::id('admin') é null quando apenas aluno está logado.");
assertTest($userTipo === null, "Auth::userTipo('admin') é null.");

// Simula a lógica do middleware $auth do admin:
$redirectionDestination = null;
if (empty($adminId)) {
    $redirectionDestination = '/ctt/admin/login';
} elseif (!in_array($userTipo, ['admin', 'funcionario'], true)) {
    $redirectionDestination = '/ctt/admin/login?error=permissao';
}
assertTest($redirectionDestination === '/ctt/admin/login', "Middleware Admin redireciona para '/ctt/admin/login' (NUNCA para '/ctt/dashboard')!");

// -------------------------------------------------------------
// 4. Simula Login do Admin mantendo Sessão do Aluno Simultânea
// -------------------------------------------------------------
echo "\n4. Efetuando Login de Admin em outra aba (coexistência)...\n";
Auth::login([
    'id' => 1,
    'nome' => 'Diretor Admin',
    'tipo' => 'admin',
    'cargo_id' => null,
    'cargo_nome' => 'Administrador',
    'permissoes' => ['*']
], 'admin');

assertTest(isset($_SESSION['auth_admin']), "\$_SESSION['auth_admin'] foi preenchida.");
assertTest(isset($_SESSION['auth_aluno']), "\$_SESSION['auth_aluno'] PERMANECE INTACTA sem ser sobrescrita!");
assertTest(Auth::admin()->id() === 1, "Admin ID é 1.");
assertTest(Auth::aluno()->id() === 10, "Aluno ID continua sendo 10.");
assertTest(Auth::admin()->isAdmin() === true, "Auth::admin()->isAdmin() é true.");
assertTest(Auth::can('financeiro.gerenciar') === true, "Admin tem acesso ao módulo Financeiro.");

// -------------------------------------------------------------
// 5. Verificando Logout Independente (Aluno desloga, Admin permanece)
// -------------------------------------------------------------
echo "\n5. Verificando Logout Independente...\n";
Auth::logout(Auth::GUARD_ALUNO);
assertTest(!isset($_SESSION['auth_aluno']), "Sessão do aluno foi removida após logout.");
assertTest(isset($_SESSION['auth_admin']), "Sessão do admin CONTINUA ATIVA após logout do aluno!");
assertTest(Auth::admin()->isAuthenticated() === true, "Admin continua autenticado.");
assertTest(Auth::aluno()->isAuthenticated() === false, "Aluno agora está deslogado.");

// Agora desloga o admin
Auth::logout(Auth::GUARD_ADMIN);
assertTest(!isset($_SESSION['auth_admin']), "Sessão do admin foi removida após logout.");
assertTest(session_status() !== PHP_SESSION_ACTIVE || empty($_SESSION), "Sessão foi completamente encerrada quando ambos deslogaram.");

echo "\n=======================================================\n";
echo "TODOS OS TESTES DE ISOLAMENTO DE GUARDS PASSARAM COM SUCESSO!\n";
echo "=======================================================\n\n";
