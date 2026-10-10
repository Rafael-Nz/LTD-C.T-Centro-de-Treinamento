<aside class="sidebar" id="sidebar">
    <div class="sidebar-logo">
        <img src="<?= PUBLIC_URL ?>img/logo.png" alt="Cross C.T" class="sidebar-logo-img">
        <span>Cross C.T</span>
    </div>

    <p class="sidebar-label">MENU</p>

    <nav class="d-flex flex-column gap-1 flex-grow-1">
        <a href="/ctt/dashboard" class="sidebar-link <?= ($currentPage ?? '') === 'dashboard' ? 'active' : '' ?>">
            <i class="bi bi-house-door me-2"></i><span>Início</span>
        </a>
        <a href="#" class="sidebar-link <?= ($currentPage ?? '') === 'dados-pessoais' ? 'active' : '' ?>">
            <i class="bi bi-person me-2"></i><span>Dados Pessoais</span>
        </a>
        <a href="#" class="sidebar-link <?= ($currentPage ?? '') === 'matricula' ? 'active' : '' ?>">
            <i class="bi bi-card-checklist me-2"></i><span>Matrícula</span>
        </a>
        <a href="#" class="sidebar-link <?= ($currentPage ?? '') === 'avaliacoes' ? 'active' : '' ?>">
            <i class="bi bi-clipboard-data me-2"></i><span>Avaliações</span>
        </a>
        <a href="#" class="sidebar-link <?= ($currentPage ?? '') === 'horarios' ? 'active' : '' ?>">
            <i class="bi bi-clock me-2"></i><span>Horários</span>
        </a>
        <a href="/ctt/mensalidade" class="sidebar-link <?= ($currentPage ?? '') === 'mensalidade' ? 'active' : '' ?>">
            <i class="bi bi-wallet2 me-2"></i><span>Mensalidade</span>
        </a>
    </nav>

    <div class="sidebar-user-wrap">
        <div class="sidebar-dropdown" id="userDropdown">
            <a href="/ctt/login" class="sidebar-dropdown-item">
                <i class="bi bi-box-arrow-left me-2"></i><span>Sair</span>
            </a>
        </div>
        <div class="sidebar-user" id="userProfile">
            <div class="sidebar-avatar">CS</div>
            <div class="sidebar-user-info">
                <span class="sidebar-user-name">Carlos Silva</span>
                <span class="sidebar-user-role">Aluno</span>
            </div>
        </div>
    </div>
</aside>

<div class="sidebar-overlay d-none" id="sidebarOverlay"></div>
