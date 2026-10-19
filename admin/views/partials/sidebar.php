<?php
if (!class_exists(\Core\Auth\Auth::class)) {
    require_once dirname(__DIR__, 2) . '/autoload.php';
}

if (!function_exists('currentRoute')) {
    function currentRoute(): string
    {
        return trim($_GET['url'] ?? '', '/');
    }
}

if (!function_exists('routeIs')) {
    function routeIs($patterns): bool
    {
        $current = currentRoute();

        foreach ((array) $patterns as $pattern) {
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
}

if (!function_exists('navActive')) {
    function navActive($patterns, $class = 'active root'): string
    {
        return routeIs($patterns) ? $class : '';
    }
}

if (!function_exists('navHref')) {
    function navHref($path): string
    {
        $base = defined('BASE_URL') ? BASE_URL : '/ctt/admin/';
        return $base . ltrim($path, '/');
    }
}
?>

<?php
$isUserOpen = routeIs(['alunos.*', 'funcionarios.*']);
$isFinanceOpen = routeIs(['financeiro.*']);

$canTurmas = \Core\Auth\Auth::can('turmas.visualizar');
$canLocais = \Core\Auth\Auth::can('locais.visualizar');
$canTreinos = \Core\Auth\Auth::can('treinos.visualizar');
$canAlunos = \Core\Auth\Auth::can('alunos.visualizar');
$canFuncionarios = \Core\Auth\Auth::can('funcionarios.visualizar');
$canUserModule = $canAlunos || $canFuncionarios;

$canFinanceiroVisao = \Core\Auth\Auth::canAny(['financeiro.visualizar', 'financeiro.gerenciar']);
$canFinanceiroServicos = \Core\Auth\Auth::canAny(['financeiro.visualizar', 'financeiro.gerenciar']);
$canFinanceiroPlanos = \Core\Auth\Auth::canAny(['financeiro.visualizar', 'financeiro.gerenciar']);
$canFinanceiroCobrancas = \Core\Auth\Auth::canAny(['financeiro.visualizar', 'financeiro.gerenciar', 'financeiro.registrar_pagamento']);
$canFinanceiroModule = $canFinanceiroVisao || $canFinanceiroServicos || $canFinanceiroPlanos || $canFinanceiroCobrancas;

$canRelatorios = \Core\Auth\Auth::can('relatorios.visualizar');
$canConfiguracoes = \Core\Auth\Auth::canAny(['cargos.visualizar', 'cargos.gerenciar', 'modalidades.visualizar', 'modalidades.gerenciar', 'configuracoes.gerenciar', 'usuarios.gerenciar']);
?>
<script>
  window.CTT_USER = {
    tipo: <?= json_encode(\Core\Auth\Auth::userTipo()) ?>,
    cargo_id: <?= json_encode(\Core\Auth\Auth::cargoId()) ?>,
    cargo_nome: <?= json_encode(\Core\Auth\Auth::cargoNome()) ?>,
    permissoes: <?= json_encode(\Core\Auth\Auth::permissions()) ?>
  };
  window.can = function(permission) {
    if (!window.CTT_USER) return false;
    if (window.CTT_USER.tipo === 'admin') return true;
    if (Array.isArray(window.CTT_USER.permissoes) && window.CTT_USER.permissoes.includes('*')) return true;
    return Array.isArray(window.CTT_USER.permissoes) && window.CTT_USER.permissoes.includes(permission);
  };
  window.canAny = function(permissions) {
    if (!window.CTT_USER) return false;
    if (window.CTT_USER.tipo === 'admin') return true;
    if (Array.isArray(window.CTT_USER.permissoes) && window.CTT_USER.permissoes.includes('*')) return true;
    return Array.isArray(permissions) && permissions.some(p => window.can(p));
  };

  (function() {
    document.body.classList.add('sidebar-initializing');

    const isDesktop = window.innerWidth > 768;
    const isClosed = localStorage.getItem('sidebarLayoutState') === 'closed';

    if (isDesktop && isClosed) {
      document.body.classList.add('sidebar-closed');
    }
  }());
</script>
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
        <div class="profile-avatar" id="sidebarProfileAvatar">--</div>
      </span>
      <p class="nav-link-text fw-normal mb-0 text-white text-truncate" id="sidebarProfileName" title="Nome do usuário">
        Carregando...
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

      <?php if ($canTurmas): ?>
      <li class="nav-item">
        <a href="<?= navHref('/turmas') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('turmas.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-address-book nav-icon"></i></span>
          <span class="nav-link-text">Turmas</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($canLocais): ?>
      <li class="nav-item">
        <a href="<?= navHref('/locais') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('locais.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-door nav-icon"></i></span>
          <span class="nav-link-text">Locais de Treino</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($canTreinos): ?>
      <li class="nav-item">
        <a href="<?= navHref('/treinos') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('treinos.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-barbell nav-icon"></i></span>
          <span class="nav-link-text">Treinos</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($canUserModule): ?>
      <!-- Módulo: Gerenciar Usuários -->
      <li class="nav-item">
        <button class="btn nav-link text-white d-flex align-items-center w-100 btn-toggle <?= $isUserOpen ? 'active root' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#submenuUsuarios" aria-expanded="<?= $isUserOpen ? 'true' : 'false' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-user nav-icon"></i></span>
          <span class="nav-link-text">Gerenciar Usuários</span>
          <i class="ph ph-caret-down angle-icon"></i>
        </button>

        <div class="collapse <?= $isUserOpen ? 'show' : '' ?>" id="submenuUsuarios">
          <ul class="nav flex-column">
            <?php if ($canAlunos): ?>
            <li class="nav-item">
              <a href="<?= navHref('/alunos') ?>" class="nav-link text-light <?= navActive('alunos.*', 'active') ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Alunos
              </a>
            </li>
            <?php endif; ?>

            <?php if ($canFuncionarios): ?>
            <li class="nav-item">
              <a href="<?= navHref('/funcionarios') ?>" class="nav-link text-light <?= navActive('funcionarios.*', 'active') ?>">
                <i class="ph ph-users-three me-2 nav-icon"></i>Funcionários
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </li>
      <?php endif; ?>

      <?php if ($canFinanceiroModule): ?>
      <!-- Módulo: Gerenciar Financeiro -->
      <li class="nav-item">
        <button class="btn nav-link text-white d-flex align-items-center w-100 btn-toggle <?= $isFinanceOpen ? 'active root' : 'collapsed' ?>" data-bs-toggle="collapse" data-bs-target="#submenuFinanceiro" aria-expanded="<?= $isFinanceOpen ? 'true' : 'false' ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-wallet nav-icon"></i></span>
          <span class="nav-link-text">Gerenciar Financeiro</span>
          <i class="ph ph-caret-down angle-icon"></i>
        </button>

        <div class="collapse <?= $isFinanceOpen ? 'show' : '' ?>" id="submenuFinanceiro">
          <ul class="nav flex-column">
            <?php if ($canFinanceiroVisao): ?>
            <li class="nav-item">
              <a href="<?= navHref('/financeiro') ?>" class="nav-link text-light <?= navActive(['financeiro', 'financeiro/contratos.*'], 'active') ?>">
                <i class="ph ph-chart-line-up me-2 nav-icon"></i>Visão geral
              </a>
            </li>
            <?php endif; ?>

            <?php if ($canFinanceiroServicos): ?>
            <li class="nav-item">
              <a href="<?= navHref('/financeiro/servicos') ?>" class="nav-link text-light <?= navActive('financeiro/servicos.*', 'active') ?>">
                <i class="ph ph-tag me-2 nav-icon"></i>Serviços
              </a>
            </li>
            <?php endif; ?>

            <?php if ($canFinanceiroPlanos): ?>
            <li class="nav-item">
              <a href="<?= navHref('/financeiro/planos') ?>" class="nav-link text-light <?= navActive('financeiro/planos.*', 'active') ?>">
                <i class="ph ph-stack me-2 nav-icon"></i>Planos
              </a>
            </li>
            <?php endif; ?>

            <?php if ($canFinanceiroCobrancas): ?>
            <li class="nav-item">
              <a href="<?= navHref('/financeiro/cobrancas') ?>" class="nav-link text-light <?= navActive('financeiro/cobrancas', 'active') ?>">
                <i class="ph ph-receipt me-2 nav-icon"></i>Cobranças
              </a>
            </li>
            <?php endif; ?>
          </ul>
        </div>
      </li>
      <?php endif; ?>

      <!-- Relatórios -->
      <?php if ($canRelatorios): ?>
      <li class="nav-item">
        <a href="<?= navHref('/relatorios') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('relatorios.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-chart-line nav-icon"></i></span>
          <span class="nav-link-text">Relatórios</span>
        </a>
      </li>
      <?php endif; ?>

      <?php if ($canConfiguracoes): ?>
      <!-- Configurações -->
      <li class="nav-item">
        <a href="<?= navHref('/configuracoes') ?>" class="nav-link text-white d-flex align-items-center <?= navActive('configuracoes.*') ?>">
          <span class="nav-icon-wrapper"><i class="ph ph-gear nav-icon"></i></span>
          <span class="nav-link-text">Configurações</span>
        </a>
      </li>
      <?php endif; ?>

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
