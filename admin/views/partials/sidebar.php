<?php
// 1. Pega a URL limpa (ex: /ctt/admin/alunos/novo)
$currentUri = parse_url($_SERVER['REQUEST_URI'], PHP_URL_PATH);

// 2. Define o prefixo que deve ser ignorado (Base do seu projeto)
$basePath = '/ctt/admin/'; 

// 3. Obtém a rota real (ex: alunos/novo)
$currentRoute = str_replace($basePath, '', $currentUri);
$currentRoute = trim($currentRoute, '/');

/**
 * Verifica se a rota atual começa com o termo
 */
function isCurrent($needle) {
    global $currentRoute;
    if ($needle === '' || $needle === 'inicio') {
        return ($currentRoute === '' || $currentRoute === 'inicio' || $currentRoute === 'dashboard');
    }
    // Verifica se a rota começa com o termo (ex: 'alunos' engloba 'alunos/editar')
    return strpos($currentRoute, $needle) === 0;
}

/**
 * Gera o link correto baseado na BASE_URL do seu router
 */
function navHref($target) {
    // Se BASE_URL estiver no seu router.php como '/ctt/admin/'
    return '/ctt/admin/' . ltrim($target, '/');
}

// Lógica de submenus (Mesmos nomes usados no seu sidebar.js)
$isUserSubmenuOpen = isCurrent('alunos') || isCurrent('funcionarios');
$isConfigSubmenuOpen = isCurrent('configuracoes') || isCurrent('cargos') || isCurrent('perfil');
?>
<div id="sidebarOverlay"></div>

<nav id="sidebar" class="sidebar">
  <!-- Header do Sidebar -->
  <div class="sidebar-header border-bottom border-secondary d-flex align-items-center">
    <span class="nav-icon-wrapper">
      <img src="/ctt/public/img/logo.png" alt="Brand Logo" class="rounded-circle" width="30" height="30">
    </span>
    <p class="nav-link-text fw-normal mb-0 text-white text-truncate">Centro de Treinamento</p>
  </div>

  <!-- Conteúdo Rolável -->
  <div class="sidebar-scrollable-content">

    <!-- Perfil -->
    <div class="sidebar-header border-bottom border-secondary d-flex align-items-center">
      <span class="nav-icon-wrapper">
        <img src="" alt="Foto do perfil" class="rounded-circle" width="30" height="30" style="object-fit: cover;">
      </span>
      <p class="nav-link-text fw-normal mb-0 text-white text-truncate" title="<?= htmlspecialchars($nicknameUsuario ?: $nomeUsuario) ?>">
        <?= htmlspecialchars($nomeExibicao) ?>
      </p>
    </div>

    <!-- Navegação -->
    <ul class="nav flex-column nav-sidebar">
      
      <!-- Dashboard -->
      <li class="nav-item mt-2">
        <a href="<?= navHref('/') ?>" 
           class="nav-link text-white d-flex align-items-center <?= isCurrent('d') ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-house-line nav-icon"></i></span>
          <span class="nav-link-text">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('turmas') ?>" 
           class="nav-link text-white d-flex align-items-center <?= (isCurrent('turmas') || isCurrent('turma_form') || isCurrent('turma_alunos')) ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-address-book nav-icon"></i></span>
          <span class="nav-link-text">Turmas</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('locais') ?>" 
           class="nav-link text-white d-flex align-items-center <?= (isCurrent('locais') || isCurrent('local_form')) ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-door nav-icon"></i></span>
          <span class="nav-link-text">Locais de Treino</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('treinos') ?>" 
           class="nav-link text-white d-flex align-items-center <?= (isCurrent('treinos') || isCurrent('treino_form')) ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-barbell nav-icon"></i></span>
          <span class="nav-link-text">Treinos</span>
        </a>
      </li>

      <!-- Módulo: Gerenciar Usuários -->
      <li class="nav-item">
        <button class="btn nav-link text-white d-flex align-items-center w-100 btn-toggle <?= $isUserSubmenuOpen ? '' : 'collapsed' ?> <?= $isUserRootActive ? 'active root' : '' ?>"
          data-bs-toggle="collapse" data-bs-target="#submenuUsuarios"
          aria-expanded="<?= $isUserSubmenuOpen ? 'true' : 'false' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-user nav-icon"></i></span>
          <span class="nav-link-text">Gerenciar Usuários</span>
          <i class="ph ph-caret-down angle-icon"></i>
        </button>

        <div class="collapse ps-3 <?= $isUserSubmenuOpen ? 'show' : '' ?>" id="submenuUsuarios">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="<?= navHref('alunos') ?>" class="nav-link text-light <?= (isCurrent('alunos') || isCurrent('aluno_form')) ? 'active' : '' ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Alunos
              </a>
            </li>
            
            <li class="nav-item">
              <a href="<?= navHref('funcionarios') ?>" class="nav-link text-light <?= (isCurrent('funcionarios') || isCurrent('funcionario_form')) ? 'active' : '' ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Funcionários
              </a>
            </li>
          </ul>
        </div>
      </li>

      <!-- Módulo: Financeiro -->
      <!-- <li class="nav-item">
        <button class="btn nav-link text-white d-flex align-items-center w-100 btn-toggle <?= $isFinanceiroSubmenuOpen ? '' : 'collapsed' ?> <?= $isFinanceiroSubmenuOpen ? 'active root' : '' ?>"
          data-bs-toggle="collapse" data-bs-target="#submenuFinanceiro"
          aria-expanded="<?= $isFinanceiroSubmenuOpen ? 'true' : 'false' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-currency-dollar nav-icon"></i></span>
          <span class="nav-link-text">Financeiro</span>
          <i class="ph ph-caret-down angle-icon"></i>
        </button>

        <div class="collapse ps-3 <?= $isFinanceiroSubmenuOpen ? 'show' : '' ?>" id="submenuFinanceiro">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="<?= navHref('mensalidades') ?>" class="nav-link text-light <?= isCurrent('mensalidades') ? 'active' : '' ?>">
                <i class="ph ph-credit-card me-2 nav-icon"></i>Mensalidades
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= navHref('faturas') ?>" class="nav-link text-light <?= isCurrent('faturas') ? 'active' : '' ?>">
                <i class="ph ph-receipt me-2 nav-icon"></i>Faturas
              </a>
            </li>
            <li class="nav-item">
              <a href="<?= navHref('planos') ?>" class="nav-link text-light <?= isCurrent('planos') ? 'active' : '' ?>">
                <i class="ph ph-tag me-2 nav-icon"></i>Planos & Preços
              </a>
            </li>
          </ul>
        </div>
      </li> -->

      <!-- Relatórios -->
      <li class="nav-item">
        <a href="<?= navHref('relatorios') ?>" 
           class="nav-link text-white d-flex align-items-center <?= (isCurrent('relatorios') || isCurrent('relatorio_form')) ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-chart-line nav-icon"></i></span>
          <span class="nav-link-text">Relatórios</span>
        </a>
      </li>

      <!-- Configurações -->
      <li class="nav-item">
        <a href="<?= navHref('configuracoes') ?>" 
           class="nav-link text-white d-flex align-items-center <?= $isConfigRootActive ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-gear nav-icon"></i></span>
          <span class="nav-link-text">Configurações</span>
        </a>
      </li>

      <!-- Perfil do Usuário -->
      <li class="nav-item">
        <a href="<?= navHref('perfil') ?>" 
           class="nav-link text-white d-flex align-items-center <?= (isCurrent('perfil') && !isCurrent('perfil_form')) ? 'active root' : '' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-user-circle nav-icon"></i></span>
          <span class="nav-link-text">Perfil</span>
        </a>
      </li>
    </ul>
  </div>
</nav>