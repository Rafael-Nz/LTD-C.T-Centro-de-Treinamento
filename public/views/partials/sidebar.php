<?php
$usuarioNome = htmlspecialchars(\Core\Auth\Auth::aluno()->user('nome') ?? $_SESSION['auth_aluno']['user_nome'] ?? $_SESSION['user_nome'] ?? 'Aluno', ENT_QUOTES, 'UTF-8');
$usuarioSobrenome = htmlspecialchars(\Core\Auth\Auth::aluno()->user('sobrenome') ?? $_SESSION['auth_aluno']['user_sobrenome'] ?? '', ENT_QUOTES, 'UTF-8');
$nomeCompleto = trim($usuarioNome . ' ' . $usuarioSobrenome);
if (empty($nomeCompleto)) $nomeCompleto = 'Aluno';

$iniciais = 'AL';
$partes = preg_split('/\s+/', trim($nomeCompleto));
if (!empty($partes[0])) {
    $first = mb_substr($partes[0], 0, 1, 'UTF-8');
    $last = count($partes) > 1 ? mb_substr(end($partes), 0, 1, 'UTF-8') : '';
    $iniciais = mb_strtoupper($first . $last, 'UTF-8');
}
?>
<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T" class="sidebar-logo-img">
        <span>Cross C.T</span>
    </div>

    <!-- Perfil do Aluno (em cima da navegação) -->
    <div class="sidebar-user" id="userProfile">
        <div class="sidebar-avatar"><?= $iniciais ?></div>
        <div class="sidebar-user-info">
            <span class="sidebar-user-name" title="<?= $nomeCompleto ?>"><?= $nomeCompleto ?></span>
            <span class="sidebar-user-role">Aluno</span>
        </div>
    </div>

    <p class="sidebar-label">MENU</p>

    <!-- Navegação -->
    <nav class="d-flex flex-column gap-1 flex-grow-1">
        <a href="/ctt/dashboard" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house-door me-2"></i><span>Início</span>
        </a>
        <a href="/ctt/dados-pessoais" class="sidebar-link <?= ($currentPage ?? '') === 'dados-pessoais' ? 'active' : '' ?>">
            <i class="bi bi-person me-2"></i><span>Dados Pessoais</span>
        </a>
        <a href="/ctt/matricula" class="sidebar-link <?= ($currentPage ?? '') === 'matricula' ? 'active' : '' ?>">
            <i class="bi bi-card-checklist me-2"></i><span>Matrícula</span>
        </a>
        <a href="/ctt/avaliacoes" class="sidebar-link <?= ($currentPage ?? '') === 'avaliacoes' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-data me-2"></i><span>Avaliações</span>
        </a>
        <a href="/ctt/horarios" class="sidebar-link <?= ($currentPage ?? '') === 'horarios' ? 'active' : '' ?>">
            <i class="bi bi-clock me-2"></i><span>Horários</span>
        </a>
        <a href="/ctt/financeiro" class="sidebar-link <?= ($currentPage ?? '') === 'financeiro' ? 'active' : '' ?>">
            <i class="bi bi-wallet2 me-2"></i><span>Financeiro</span>
        </a>
    </nav>

    <!-- Sair no fim da sidebar -->
    <div class="sidebar-footer-wrap mt-auto pt-2 border-top border-secondary border-opacity-25">
        <a href="/ctt/logout" class="sidebar-link text-danger d-flex align-items-center" id="sidebarLogoutLink">
            <i class="bi bi-box-arrow-left me-2 text-danger"></i><span>Sair</span>
        </a>
    </div>
</aside>

<div class="sidebar-overlay d-none" id="sidebarOverlay"></div>
