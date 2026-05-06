<?php
function currentRoute(): string {
    return trim($_GET['url'] ?? '', '/');
}

function routeIs($patterns): bool {
    $current = currentRoute();

    foreach ((array)$patterns as $pattern) {
        $pattern = trim($pattern, '/');

        // dashboard (rota vazia)
        if ($pattern === '') {
            if ($current === '') return true;
            continue;
        }

        // wildcard tipo alunos.*
        if (str_ends_with($pattern, '.*')) {
            $base = substr($pattern, 0, -2);
            if ($current === $base || str_starts_with($current, $base . '/')) {
                return true;
            }
        }

        // match exato
        if ($current === $pattern) {
            return true;
        }
    }

    return false;
}

function navActive($patterns, $class = 'active root'): string {
    return routeIs($patterns) ? $class : '';
}

function navHref($path): string {
    return BASE_URL . ltrim($path, '/');
}
?>

<?php $isUserOpen = routeIs(['alunos.*', 'funcionarios.*']); ?>
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
      <p class="nav-link-text fw-normal mb-0 text-white text-truncate" title="Nickname do usuário">
        Nickname
      </p>
    </div>

    <!-- Navegação -->
    <ul class="nav flex-column nav-sidebar">
      
      <!-- Dashboard -->
      <li class="nav-item mt-2">
        <a href="<?= navHref('/') ?>" class="nav-link text-white d-flex align-items-center <?= navActive(['', 'inicio']) ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-house-line nav-icon"></i></span>
          <span class="nav-link-text">Dashboard</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('/turmas') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('turmas.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-address-book nav-icon"></i></span>
          <span class="nav-link-text">Turmas</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('/locais') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('locais.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-door nav-icon"></i></span>
          <span class="nav-link-text">Locais de Treino</span>
        </a>
      </li>

      <li class="nav-item">
        <a href="<?= navHref('/treinos') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('treinos.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-barbell nav-icon"></i></span>
          <span class="nav-link-text">Treinos</span>
        </a>
      </li>

      <!-- Módulo: Gerenciar Usuários -->
      <li class="nav-item">
        <button class="btn nav-link text-white d-flex align-items-center w-100 btn-toggle <?= $isUserOpen ? 'active root' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#submenuUsuarios" aria-expanded="<?= $isUserOpen ? 'true' : 'false' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-user nav-icon"></i></span>
          <span class="nav-link-text">Gerenciar Usuários</span>
          <i class="ph ph-caret-down angle-icon"></i>
        </button>

        <div class="collapse ps-3 <?= $isUserOpen ? 'show' : '' ?>" id="submenuUsuarios">
          <ul class="nav flex-column">
            <li class="nav-item">
              <a href="<?= navHref('/alunos') ?>" class="nav-link text-light <?= navActive('alunos.*', 'active') ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Alunos
              </a>
            </li>
            
            <li class="nav-item">
              <a href="<?= navHref('/funcionarios') ?>" class="nav-link text-light <?= navActive('funcionarios.*', 'active') ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Funcionários
              </a>
            </li>
          </ul>
        </div>
      </li>

      <!-- Relatórios -->
      <li class="nav-item">
        <a href="<?= navHref('/relatorios') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('relatorios.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-chart-line nav-icon"></i></span>
          <span class="nav-link-text">Relatórios</span>
        </a>
      </li>

      <!-- Configurações -->
      <li class="nav-item">
        <a href="<?= navHref('/configuracoes') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('configuracoes.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-gear nav-icon"></i></span>
          <span class="nav-link-text">Configurações</span>
        </a>
      </li>

      <!-- Perfil do Usuário -->
      <li class="nav-item">
        <a href="<?= navHref('/perfil') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('perfil.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-user-circle nav-icon"></i></span>
          <span class="nav-link-text">Perfil</span>
        </a>
      </li>
    </ul>
  </div>
</nav>