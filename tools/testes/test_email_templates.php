<?php

require_once __DIR__ . '/../../autoload.php';

// Test template files exist and read them
$templates = [
    'password-reset' => __DIR__ . '/../../api/src/auth/password-reset.html',
    'initial-password' => __DIR__ . '/../../api/src/auth/initial-password.html',
    'account-activation' => __DIR__ . '/../../api/src/auth/account-activation.html',
];

$dadosMock = [
    'nome' => 'Carlos Eduardo Silva',
    'email' => 'carlos.silva@exemplo.com',
    'matricula' => '202610000042',
    'senha_inicial' => 'Ct#2026!Provisoria',
    'link' => 'http://localhost/ctt/ativar-conta/teste-token-12345?perfil=aluno',
    'login_url' => 'http://localhost/ctt/login',
    'ano' => date('Y'),
];

echo "=== TESTE DE TEMPLATES DE E-MAIL ===\n";

foreach ($templates as $tipo => $caminho) {
    if (!file_exists($caminho)) {
        echo "[FAIL] Template {$tipo} não existe em {$caminho}\n";
        exit(1);
    }

    $conteudo = file_get_contents($caminho);
    if (empty($conteudo)) {
        echo "[FAIL] Template {$tipo} está vazio\n";
        exit(1);
    }

    echo "[PASS] Template {$tipo} encontrado (" . strlen($conteudo) . " bytes)\n";

    if ($tipo === 'password-reset') {
        $renderizado = str_replace(
            ['{{nome}}', '{{link}}', '{{ano}}'],
            [$dadosMock['nome'], $dadosMock['link'], $dadosMock['ano']],
            $conteudo
        );

        if (strpos($renderizado, '{{nome}}') !== false || strpos($renderizado, '{{link}}') !== false) {
            echo "[FAIL] Placeholders não substituídos em {$tipo}\n";
            exit(1);
        }
        if (strpos($renderizado, $dadosMock['nome']) === false || strpos($renderizado, $dadosMock['link']) === false) {
            echo "[FAIL] Conteúdo mock não encontrado no HTML renderizado de {$tipo}\n";
            exit(1);
        }
        echo "[PASS] Renderização e substituição de {$tipo} OK!\n";
    }

    if ($tipo === 'account-activation') {
        $renderizado = str_replace(
            ['{{nome}}', '{{link}}', '{{ano}}'],
            [$dadosMock['nome'], $dadosMock['link'], $dadosMock['ano']],
            $conteudo
        );

        if (strpos($renderizado, '{{nome}}') !== false || strpos($renderizado, '{{link}}') !== false) {
            echo "[FAIL] Placeholders não substituídos em {$tipo}\n";
            exit(1);
        }
        if (strpos($renderizado, $dadosMock['nome']) === false || strpos($renderizado, $dadosMock['link']) === false) {
            echo "[FAIL] Conteúdo mock não encontrado no HTML renderizado de {$tipo}\n";
            exit(1);
        }
        echo "[PASS] Renderização e substituição de {$tipo} OK!\n";
    }

    if ($tipo === 'initial-password') {
        $renderizado = str_replace(
            ['{{nome}}', '{{matricula}}', '{{email}}', '{{senha_inicial}}', '{{login_url}}', '{{ano}}'],
            [$dadosMock['nome'], $dadosMock['matricula'], $dadosMock['email'], $dadosMock['senha_inicial'], $dadosMock['login_url'], $dadosMock['ano']],
            $conteudo
        );

        if (
            strpos($renderizado, '{{nome}}') !== false ||
            strpos($renderizado, '{{matricula}}') !== false ||
            strpos($renderizado, '{{email}}') !== false ||
            strpos($renderizado, '{{senha_inicial}}') !== false ||
            strpos($renderizado, '{{login_url}}') !== false
        ) {
            echo "[FAIL] Placeholders não substituídos em {$tipo}\n";
            exit(1);
        }

        if (
            strpos($renderizado, $dadosMock['matricula']) === false ||
            strpos($renderizado, $dadosMock['senha_inicial']) === false ||
            strpos($renderizado, $dadosMock['email']) === false
        ) {
            echo "[FAIL] Conteúdo de matrícula/senha/email não encontrado no HTML renderizado de {$tipo}\n";
            exit(1);
        }
        echo "[PASS] Renderização e substituição de {$tipo} com matrícula OK!\n";
    }
}

echo "Todos os testes de templates de e-mail passaram com sucesso!\n";
