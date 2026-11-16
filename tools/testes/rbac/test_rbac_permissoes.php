<?php

declare(strict_types=1);

require_once __DIR__ . '/security_fixture.php';
$pdo = securityTestDatabase();

use Core\Auth\Auth;
use Core\Auth\PermissionMiddleware;
use Core\Database\Database;
use Cargo\CargoRepository;
use Cargo\CargoService;
use Usuario\UsuarioRepository;

function testAssert(bool $condition, string $message): void
{
    if (!$condition) {
        throw new RuntimeException("FALHA: {$message}");
    }
    echo "  [PASS] {$message}\n";
}

echo "\n=== INICIANDO TESTES DO SISTEMA RBAC ===\n\n";

// ---------------------------------------------------------
// 1. Integridade do Banco de Dados
// ---------------------------------------------------------
echo "1. Verificando tabelas e perfis na fixture em memoria...\n";

$permStmt = $pdo->query("SELECT COUNT(*) FROM permissao");
$permCount = (int) $permStmt->fetchColumn();
testAssert($permCount >= 30, "Tabela 'permissao' possui pelo menos 30 permissões cadastradas (atual: {$permCount}).");

$columns = $pdo->query('PRAGMA table_info(permissao)')->fetchAll(PDO::FETCH_ASSOC);
testAssert(in_array('modulo', array_column($columns, 'name'), true), "Coluna 'modulo' existe na tabela 'permissao'.");

$cargoPermStmt = $pdo->query("SELECT COUNT(*) FROM cargo_permissao");
$cargoPermCount = (int) $cargoPermStmt->fetchColumn();
testAssert($cargoPermCount > 0, "Tabela 'cargo_permissao' possui registros populados (atual: {$cargoPermCount}).");

// ---------------------------------------------------------
// 2. Auth Core RBAC (can, canAny, admin bypass)
// ---------------------------------------------------------
echo "\n2. Verificando Auth::can() e Auth::canAny()...\n";

// Teste 2.1: Admin Superuser Bypass
Auth::login(['id' => 1], 'admin');

testAssert(Auth::isAdmin() === true, "Auth::isAdmin() retorna true para tipo 'admin'.");
testAssert(Auth::can('qualquer.coisa') === true, "Admin tem bypass total em Auth::can().");
testAssert(Auth::can('financeiro.gerenciar') === true, "Admin tem acesso a permissão financeira.");
testAssert(Auth::canAny(['inexistente.1', 'inexistente.2']) === true, "Admin tem bypass em Auth::canAny().");

// Teste 2.2: Funcionário Instrutor (escopo limitado)
Auth::login(['id' => 2], 'admin');

testAssert(Auth::isAdmin() === false, "Funcionário não é identificado como admin.");
testAssert(Auth::can('turmas.visualizar') === true, "Instrutor pode visualizar turmas.");
testAssert(Auth::can('treinos.criar') === true, "Instrutor pode criar treinos.");
testAssert(Auth::can('financeiro.visualizar') === false, "Instrutor NÃO pode visualizar financeiro.");
testAssert(Auth::can('cargos.gerenciar') === false, "Instrutor NÃO pode gerenciar cargos.");
testAssert(Auth::can('cargos.visualizar') === false, "Instrutor NÃO pode visualizar cargos.");
testAssert(Auth::can('modalidades.visualizar') === false, "Instrutor NÃO pode visualizar modalidades.");
testAssert(Auth::canAny(['cargos.visualizar', 'cargos.gerenciar', 'modalidades.visualizar', 'modalidades.gerenciar', 'configuracoes.gerenciar', 'usuarios.gerenciar']) === false, "Instrutor NÃO tem acesso ao módulo de configurações.");
testAssert(Auth::canAny(['financeiro.gerenciar', 'turmas.visualizar']) === true, "Instrutor passa em canAny quando tem ao menos uma permissão.");
testAssert(Auth::canAny(['financeiro.visualizar', 'financeiro.gerenciar']) === false, "Instrutor falha em canAny quando não tem nenhuma permissão.");

// Teste 2.3: Aluno (sem permissões administrativas)
Auth::logout('admin');
Auth::login(['id' => 99], 'aluno');

testAssert(Auth::can('turmas.visualizar') === false, "Aluno não pode visualizar turmas do admin.");
testAssert(Auth::can('financeiro.visualizar') === false, "Aluno não pode visualizar financeiro administrativo.");

// ---------------------------------------------------------
// 3. PermissionMiddleware
// ---------------------------------------------------------
echo "\n3. Verificando PermissionMiddleware...\n";

// Simulando usuário Instrutor
Auth::login(['id' => 2], 'admin');
Auth::setContext(Auth::GUARD_ADMIN);

$middleware = PermissionMiddleware::require('turmas.visualizar');
$result = $middleware->handle();
testAssert($result === true, "PermissionMiddleware::require permite execução quando usuário tem permissão.");

// ---------------------------------------------------------
// 4. UsuarioRepository & CargoRepository
// ---------------------------------------------------------
echo "\n4. Verificando repositórios e serviços de Cargo/Permissões...\n";

$usuarioRepo = new UsuarioRepository();
$instrutorPerms = $usuarioRepo->findPermissionsByCargoId(2);
testAssert(is_array($instrutorPerms), "findPermissionsByCargoId(2) retorna array de permissões.");
testAssert(in_array('turmas.visualizar', $instrutorPerms, true), "Cargo Instrutor possui 'turmas.visualizar' no banco de dados.");
testAssert(!in_array('modalidades.visualizar', $instrutorPerms, true), "Cargo Instrutor NÃO possui 'modalidades.visualizar' no banco de dados.");
testAssert(!in_array('cargos.visualizar', $instrutorPerms, true), "Cargo Instrutor NÃO possui 'cargos.visualizar' no banco de dados.");
testAssert(!in_array('cargos.gerenciar', $instrutorPerms, true), "Cargo Instrutor NÃO possui 'cargos.gerenciar' no banco de dados.");

$cargoRepo = new CargoRepository();
$allPerms = $cargoRepo->listAllPermissions();
testAssert(count($allPerms) >= 30, "listAllPermissions() retorna todas as permissões do sistema.");

$details = $cargoRepo->findById(2);
testAssert(!empty($details['id']), "CargoRepository::findById(2) retorna os dados do cargo.");

$instrutorSlugs = $cargoRepo->findPermissionSlugs(2);
testAssert(is_array($instrutorSlugs) && count($instrutorSlugs) > 0, "CargoRepository::findPermissionSlugs(2) retorna slugs do cargo.");

// Teste 4.1: Teste de sincronização de permissões com rollback
$pdo->beginTransaction();
try {
    $novasPerms = ['turmas.visualizar', 'turmas.criar'];
    $cargoRepo->syncPermissions(2, $novasPerms);

    $slugsAposSync = $cargoRepo->findPermissionSlugs(2);
    sort($novasPerms);
    sort($slugsAposSync);
    testAssert($slugsAposSync === $novasPerms, "findPermissionSlugs(2) reflete exatamente as permissões sincronizadas.");
} finally {
    $pdo->rollBack();
}

// ---------------------------------------------------------
// 5. Verificação das permissões do Gerente e Atendente
// ---------------------------------------------------------
echo "\n5. Verificando perfis padrão de Gerente e Atendente...\n";

$gerentePerms = $usuarioRepo->findPermissionsByCargoId(4); // Gerente
testAssert(in_array('financeiro.visualizar', $gerentePerms, true), "Gerente possui permissão 'financeiro.visualizar'.");
testAssert(in_array('financeiro.gerenciar', $gerentePerms, true), "Gerente possui permissão 'financeiro.gerenciar'.");
testAssert(in_array('relatorios.visualizar', $gerentePerms, true), "Gerente possui permissão 'relatorios.visualizar'.");

$atendentePerms = $usuarioRepo->findPermissionsByCargoId(5); // Atendente
testAssert(in_array('financeiro.registrar_pagamento', $atendentePerms, true), "Atendente possui 'financeiro.registrar_pagamento'.");
testAssert(!in_array('cargos.gerenciar', $atendentePerms, true), "Atendente NÃO possui 'cargos.gerenciar'.");

echo "\n============================================\n";
echo "TODOS OS TESTES DE RBAC FORAM APROVADOS COM SUCESSO!\n";
echo "============================================\n\n";
