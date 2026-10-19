<?php

declare(strict_types=1);

// Teste de carga do router admin e das views com a nova autenticação/RBAC
$_GET['url'] = 'inicio';
session_start();
$_SESSION['user_id'] = 1;
$_SESSION['user_tipo'] = 'admin';
$_SESSION['user_permissions'] = ['*'];

ob_start();
require dirname(__DIR__, 3) . '/admin/index.php';
$html = ob_get_clean();

if (str_contains($html, 'Centro de Treinamento') && str_contains($html, 'Dashboard')) {
    echo "SUCCESS: Admin router and sidebar rendered successfully for Admin!\n";
} else {
    echo "FAILED: Content was: " . substr($html, 0, 500) . "\n";
    exit(1);
}

// Teste com Instrutor
$_SESSION['user_tipo'] = 'funcionario';
$_SESSION['user_permissions'] = ['turmas.visualizar', 'treinos.visualizar'];
$_SESSION['permissoes'] = $_SESSION['user_permissions'];

ob_start();
include dirname(__DIR__, 3) . '/admin/views/partials/sidebar.php';
$sidebarHtml = ob_get_clean();

$hasTurmas = str_contains($sidebarHtml, 'Turmas');
$hasTreinos = str_contains($sidebarHtml, 'Treinos');
$hasFinanceiro = str_contains($sidebarHtml, 'Gerenciar Financeiro');
$hasConfiguracoes = str_contains($sidebarHtml, 'Configurações');

if ($hasTurmas && $hasTreinos && !$hasFinanceiro && !$hasConfiguracoes) {
    echo "SUCCESS: Sidebar filtered correctly for Instrutor (Financial and Configurações hidden, Turmas/Treinos visible)!\n";
} else {
    echo "FAILED: Sidebar did not filter properly for Instrutor (Configurações visible: " . ($hasConfiguracoes ? 'yes' : 'no') . ")\n";
    exit(1);
}
