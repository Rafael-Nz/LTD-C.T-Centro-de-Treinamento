<?php

declare(strict_types=1);

/**
 * Script CLI para criacao e redefinicao do usuario Administrador do sistema CTT.
 * 
 * Uso interativo:
 *   php tools/sistema/criar_admin.php
 * 
 * Uso com parametros (automacao / CI / instalacao silenciosa):
 *   php tools/sistema/criar_admin.php --email=admin@seuclube.com --senha="SuaSenhaForte!123" --nome="Administrador"
 * 
 * Redefinir senha de admin existente:
 *   php tools/sistema/criar_admin.php --reset
 */

if (PHP_SAPI !== 'cli') {
    http_response_code(403);
    echo "Este script deve ser executado exclusivamente via linha de comando (CLI).\n";
    exit(1);
}

$projectRoot = dirname(__DIR__, 2);

// 1. Carrega autoloader global e variaveis de ambiente
require_once $projectRoot . '/autoload.php';

use Core\Database\Database;
use Core\Auth\PasswordPolicy;

// Funcoes auxiliares de terminal
function printHeader(string $title): void {
    echo "\n======================================================\n";
    echo "  " . $title . "\n";
    echo "======================================================\n\n";
}

function printSuccess(string $msg): void {
    echo "\033[32m[SUCESSO]\033[0m " . $msg . "\n";
}

function printError(string $msg): void {
    echo "\033[31m[ERRO]\033[0m " . $msg . "\n";
}

function printInfo(string $msg): void {
    echo "\033[36m[INFO]\033[0m " . $msg . "\n";
}

function printWarning(string $msg): void {
    echo "\033[33m[AVISO]\033[0m " . $msg . "\n";
}

function prompt(string $message, string $default = ''): string {
    $suffix = $default !== '' ? " [{$default}]: " : ": ";
    echo $message . $suffix;
    $input = trim((string) fgets(STDIN));
    return $input !== '' ? $input : $default;
}

function promptSecret(string $message): string {
    echo $message . ": ";
    // Em Windows, oculta ou le via PowerShell se possivel, com fallback seguro para STDIN
    if (strtoupper(substr(PHP_OS, 0, 3)) === 'WIN') {
        $psCommand = 'powershell -Command "$p = Read-Host -AsSecureString; [Runtime.InteropServices.Marshal]::PtrToStringAuto([Runtime.InteropServices.Marshal]::SecureStringToBSTR($p))"';
        $output = @shell_exec($psCommand);
        if ($output !== null) {
            return trim($output);
        }
    }

    // Unix ou fallback Windows
    if (function_exists('readline')) {
        $input = trim((string) readline());
        return $input;
    }

    $input = trim((string) fgets(STDIN));
    return $input;
}

// 2. Parse de argumentos CLI
$options = getopt('', [
    'email:',
    'senha:',
    'nome::',
    'sobrenome::',
    'cpf::',
    'reset',
    'help'
]);

if (isset($options['help'])) {
    echo <<<HELP
Script de Configuracao do Administrador Inicial - LTD-C.T

Opcoes disponiveis:
  --email=<email>         E-mail de acesso do administrador
  --senha=<senha>         Senha de acesso (min. 12 caracteres, maiuscula, minuscula, numero, especial)
  --nome=<nome>           Nome do administrador (padrao: Administrador)
  --sobrenome=<sobrenome> Sobrenome do administrador (opcional)
  --cpf=<cpf>             CPF do administrador (11 digitos, padrao: 00000000000)
  --reset                 Modo de redefinicao de senha para admin existente
  --help                  Exibe esta ajuda

Exemplo:
  php tools/sistema/criar_admin.php --email=admin@empresa.com --senha="SenhaForte@2026"

HELP;
    exit(0);
}

printHeader("LTD-C.T - Configuracao do Administrador do Sistema");

// 3. Testa conexao com o banco
try {
    $pdo = Database::getConnection();
} catch (Throwable $e) {
    printError("Nao foi possivel conectar ao banco de dados MySQL.");
    printInfo("Verifique se o MySQL esta rodando no XAMPP e confira as credenciais no arquivo .env");
    printInfo("Erro: " . $e->getMessage());
    exit(1);
}

// 4. Verifica se a tabela usuario existe
try {
    $pdo->query("SELECT 1 FROM usuario LIMIT 1");
} catch (PDOException $e) {
    printError("A tabela 'usuario' nao foi encontrada no banco.");
    printInfo("Execute primeiro o script de criacao do banco: docs/sql/setup/banco.sql");
    exit(1);
}

// 5. Verifica se ja existe um admin cadastrado
$stmt = $pdo->query("
    SELECT u.id, u.nome, u.sobrenome, u.email, u.cpf, u.ativo 
    FROM usuario u 
    WHERE u.tipo_usuario = 'admin' 
    ORDER BY u.id ASC
");
$adminsExistentes = $stmt->fetchAll(PDO::FETCH_ASSOC);

$modoReset = isset($options['reset']);
$adminAlvo = null;

if (!empty($adminsExistentes)) {
    printInfo("Administrador(es) atualmente cadastrado(s) no sistema:");
    foreach ($adminsExistentes as $adm) {
        $status = $adm['ativo'] ? 'Ativo' : 'Inativo';
        echo "  - ID #{$adm['id']}: {$adm['nome']} {$adm['sobrenome']} ({$adm['email']}) [{$status}]\n";
    }
    echo "\n";

    if (!$modoReset && !isset($options['email'])) {
        $escolha = strtolower(prompt("Ja existe um administrador cadastrado. O que deseja fazer?\n  [1] Redefinir senha de um administrador existente\n  [2] Cadastrar um novo administrador\n  [3] Sair\nEscolha", "1"));
        if ($escolha === '3' || $escolha === 'sair') {
            printInfo("Operacao cancelada pelo usuario.");
            exit(0);
        }
        if ($escolha === '1') {
            $modoReset = true;
        }
    }
}

// 6. Coleta e validacao de dados
$email = $options['email'] ?? null;
$senha = $options['senha'] ?? null;
$nome = $options['nome'] ?? 'Administrador';
$sobrenome = $options['sobrenome'] ?? '';
$cpf = preg_replace('/\D/', '', (string)($options['cpf'] ?? ''));

if ($modoReset) {
    printHeader("Redefinicao de Senha do Administrador");

    if (empty($email)) {
        $emailSugerido = !empty($adminsExistentes) ? $adminsExistentes[0]['email'] : '';
        $email = prompt("Informe o e-mail do administrador para redefinir", $emailSugerido);
    }

    $stmt = $pdo->prepare("SELECT id, nome, sobrenome, email FROM usuario WHERE email = ? AND tipo_usuario = 'admin'");
    $stmt->execute([$email]);
    $adminAlvo = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$adminAlvo) {
        printError("Nenhum usuario administrador encontrado com o e-mail: {$email}");
        exit(1);
    }

    printInfo("Usuario selecionado: #{$adminAlvo['id']} - {$adminAlvo['nome']} ({$adminAlvo['email']})");

    if (empty($senha)) {
        while (true) {
            $senha = promptSecret("Digite a nova senha forte para o administrador");
            try {
                PasswordPolicy::validate($senha);
            } catch (InvalidArgumentException $e) {
                printWarning($e->getMessage());
                printInfo("Requisitos: min. 12 caracteres, 1 maiuscula, 1 minuscula, 1 numero e 1 especial.");
                continue;
            }

            $confirmacao = promptSecret("Confirme a nova senha");
            if ($senha !== $confirmacao) {
                printWarning("As senhas digitadas nao conferem. Tente novamente.\n");
                continue;
            }
            break;
        }
    } else {
        try {
            PasswordPolicy::validate($senha);
        } catch (InvalidArgumentException $e) {
            printError("Senha invalida: " . $e->getMessage());
            exit(1);
        }
    }

    // Executa atualizacao
    try {
        $pdo->beginTransaction();
        $hash = password_hash($senha, PASSWORD_ARGON2ID);

        $stmt = $pdo->prepare("UPDATE usuario SET senha = ?, ativo = 1, conta_ativada = 1, data_atualizacao = NOW() WHERE id = ?");
        $stmt->execute([$hash, $adminAlvo['id']]);

        // Atualiza tambem a tabela funcionario se existir
        $stmtFunc = $pdo->prepare("UPDATE funcionario SET senha = ?, conta_ativada = 1 WHERE usuario_id = ?");
        $stmtFunc->execute([$hash, $adminAlvo['id']]);

        $pdo->commit();
        printSuccess("Senha do administrador #{$adminAlvo['id']} ({$adminAlvo['email']}) redefinida com sucesso!");
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        printError("Falha ao redefinir a senha: " . $e->getMessage());
        exit(1);
    }

} else {
    // Modo criacao de novo administrador
    printHeader("Criacao de Novo Administrador");

    // E-mail
    while (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
        if (!empty($email)) {
            printWarning("Formato de e-mail invalido.");
        }
        $email = prompt("E-mail de acesso do administrador");
    }

    // Verifica se ja esta em uso
    $stmt = $pdo->prepare("SELECT id, tipo_usuario FROM usuario WHERE email = ?");
    $stmt->execute([$email]);
    if ($stmt->fetch()) {
        printError("O e-mail '{$email}' ja esta em uso por outro usuario no sistema.");
        printInfo("Dica: Use a opcao --reset para redefinir a senha de uma conta existente.");
        exit(1);
    }

    $isNonInteractive = !empty($options['email']) || !empty($options['senha']);

    // Nome e Sobrenome
    if (empty($options['nome'])) {
        $nome = $isNonInteractive ? 'Administrador' : prompt("Nome", "Administrador");
    }
    if (!isset($options['sobrenome'])) {
        $sobrenome = $isNonInteractive ? '' : prompt("Sobrenome (opcional)", "");
    }

    // CPF
    $cpfPadrao = empty($adminsExistentes) ? '00000000000' : sprintf('%011d', rand(10000000000, 99999999999));
    if (empty($cpf) || strlen($cpf) !== 11) {
        if ($isNonInteractive) {
            $cpf = $cpfPadrao;
        } else {
            $cpfInput = prompt("CPF (11 digitos numericos)", $cpfPadrao);
            $cpf = preg_replace('/\D/', '', $cpfInput);
            if (strlen($cpf) !== 11) {
                $cpf = $cpfPadrao;
            }
        }
    }

    // Verifica unicidade do CPF
    $stmt = $pdo->prepare("SELECT id FROM usuario WHERE cpf = ?");
    $stmt->execute([$cpf]);
    if ($stmt->fetch()) {
        printError("O CPF '{$cpf}' ja esta em uso por outro usuario.");
        exit(1);
    }

    // Senha
    if (empty($senha)) {
        while (true) {
            $senha = promptSecret("Digite uma senha forte para o administrador");
            try {
                PasswordPolicy::validate($senha);
            } catch (InvalidArgumentException $e) {
                printWarning($e->getMessage());
                printInfo("A senha deve ter: min. 12 caracteres, 1 maiuscula, 1 minuscula, 1 numero e 1 caractere especial.");
                continue;
            }

            $confirmacao = promptSecret("Confirme a senha");
            if ($senha !== $confirmacao) {
                printWarning("As senhas digitadas nao conferem. Tente novamente.\n");
                continue;
            }
            break;
        }
    } else {
        try {
            PasswordPolicy::validate($senha);
        } catch (InvalidArgumentException $e) {
            printError("Senha invalida: " . $e->getMessage());
            exit(1);
        }
    }

    // Insercao no banco de dados
    try {
        $pdo->beginTransaction();

        // 1. Garante que exista endereco valido
        $stmtEnd = $pdo->query("SELECT id FROM endereco LIMIT 1");
        $enderecoId = $stmtEnd->fetchColumn();
        if (!$enderecoId) {
            $pdo->exec("
                INSERT INTO endereco (logradouro, numero, cidade, bairro, cep)
                VALUES ('Sede Central', 'S/N', 'Sao Luis', 'Centro', '65000000')
            ");
            $enderecoId = (int)$pdo->lastInsertId();
        }

        // 2. Garante que exista o cargo Administrador (id 1)
        $stmtCargo = $pdo->query("SELECT id FROM cargo WHERE id = 1 OR nome LIKE '%Admin%' LIMIT 1");
        $cargoId = $stmtCargo->fetchColumn();
        if (!$cargoId) {
            $pdo->exec("
                INSERT INTO cargo (id, nome, descricao, salario_base, ativo)
                VALUES (1, 'Administrador', 'Responsavel pela gestao completa do sistema', 0.00, 1)
            ");
            $cargoId = 1;
        }

        // 3. Insere o usuario admin
        $hash = password_hash($senha, PASSWORD_ARGON2ID);
        $dataNascimento = '1990-01-01';

        $stmtUser = $pdo->prepare("
            INSERT INTO usuario (nome, sobrenome, cpf, email, senha, data_nascimento, tipo_usuario, endereco_id, ativo, conta_ativada)
            VALUES (?, ?, ?, ?, ?, ?, 'admin', ?, 1, 1)
        ");
        $stmtUser->execute([
            $nome,
            $sobrenome,
            $cpf,
            $email,
            $hash,
            $dataNascimento,
            $enderecoId
        ]);
        $usuarioId = (int)$pdo->lastInsertId();

        // 4. Cria registro na tabela funcionario vinculado ao cargo admin
        $stmtFunc = $pdo->prepare("
            INSERT INTO funcionario (usuario_id, cargo_id, registro_profissional, observacoes, senha, conta_ativada)
            VALUES (?, ?, ?, 'Conta administrativa principal', ?, 1)
        ");
        $stmtFunc->execute([
            $usuarioId,
            $cargoId,
            'ADM-' . str_pad((string)$usuarioId, 2, '0', STR_PAD_LEFT),
            $hash
        ]);

        // 5. Se o formulario padrao de anamnese estiver sem criador, vincula ao novo admin
        $pdo->prepare("
            UPDATE anamnese_formulario 
            SET criado_por = ? 
            WHERE criado_por IS NULL
        ")->execute([$usuarioId]);

        $pdo->commit();

        printSuccess("Administrador cadastrado com sucesso!");
        echo "\n";
        echo "  ID:    #{$usuarioId}\n";
        echo "  Nome:  {$nome} {$sobrenome}\n";
        echo "  Email: {$email}\n";
        echo "  Cargo: Administrador (ID: {$cargoId})\n";
        echo "\n";
    } catch (Throwable $e) {
        if ($pdo->inTransaction()) {
            $pdo->rollBack();
        }
        printError("Falha ao registrar o administrador: " . $e->getMessage());
        exit(1);
    }
}

// 7. Informacoes finais de acesso
$appUrl = $_ENV['APP_URL'] ?? 'http://localhost/ctt';
$loginUrl = rtrim($appUrl, '/') . '/admin/login';

echo "------------------------------------------------------\n";
printInfo("Acesse o painel administrativo pelo navegador:");
echo "  URL: \033[34m{$loginUrl}\033[0m\n";
echo "  Login: {$email}\n";
echo "------------------------------------------------------\n\n";
exit(0);
