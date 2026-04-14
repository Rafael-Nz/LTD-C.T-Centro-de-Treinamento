document.documentElement.classList.add('preload');

window.addEventListener('load', () => {
    document.documentElement.classList.remove('preload');
});

document.addEventListener('DOMContentLoaded', function() {
    const body = document.body;
    const sidebar = document.getElementById('sidebar');
    const sidebarToggle = document.getElementById('sidebarToggle');
    const sidebarOverlay = document.getElementById('sidebarOverlay');
    const breakpoint = 768;

    const SUBMENU_STORAGE_KEY = 'sidebarSubmenuState';
    const SIDEBAR_STATE_KEY = 'sidebarLayoutState';

    // OverlayScrollbars
    const sidebarScrollContainer = document.querySelector('.sidebar-scrollable-content');
    if (sidebarScrollContainer && typeof window.OverlayScrollbars !== 'undefined') {
        OverlayScrollbars(sidebarScrollContainer, {
            className: "os-theme-light",
            scrollbars: { visibility: "auto", autoHide: "leave", autoHideDelay: 500 }
        });
    }
    // ---------------------------
    // Sidebar
    // ---------------------------
    function saveSidebarState(closed) {
        if (window.innerWidth > breakpoint) { // só salva no desktop
            localStorage.setItem(SIDEBAR_STATE_KEY, closed ? 'closed' : 'open');
        }
    }

    function loadSidebarState() {
        const saved = localStorage.getItem(SIDEBAR_STATE_KEY);
        if (window.innerWidth > breakpoint) {
            if (saved === 'closed') body.classList.add('sidebar-closed');
            else body.classList.remove('sidebar-closed');
        } else {
            body.classList.remove('sidebar-closed');
        }
    }

    function toggleSidebar() {
        if (window.innerWidth <= breakpoint){
            body.classList.toggle('sidebar-open');
        }
        else {
            body.classList.toggle('sidebar-closed');
            saveSidebarState(body.classList.contains('sidebar-closed'));
        }
    }

    sidebarToggle.addEventListener('click', e => { e.preventDefault(); toggleSidebar(); });
    sidebarOverlay.addEventListener('click', () => body.classList.remove('sidebar-open'));

    loadSidebarState();

    function handleResize() {
        if (window.innerWidth <= breakpoint && body.classList.contains('sidebar-closed')) {
            body.classList.remove('sidebar-closed');
        }
    }

    window.addEventListener('resize', handleResize);
    
    // ---------------------------
    // Submenus
    // ---------------------------
    function saveSubmenuState(id, open) {
        const states = JSON.parse(localStorage.getItem(SUBMENU_STORAGE_KEY) || '{}');
        states[id] = open;
        localStorage.setItem(SUBMENU_STORAGE_KEY, JSON.stringify(states));
    }

    function loadSubmenuState(id, el) {
        const states = JSON.parse(localStorage.getItem(SUBMENU_STORAGE_KEY) || '{}');
        let isOpen = states[id]; // Estado salvo pelo usuário
        const btn = document.querySelector(`[data-bs-target="#${id}"]`);
        if (!btn) return;

        const collapse = bootstrap.Collapse.getOrCreateInstance(el, { toggle: false });

        // Abre automaticamente se o link atual estiver dentro do submenu
        const currentPath = window.location.pathname.split('/').pop();
        const hasCurrentLinkInside = el.querySelector(`a[href="${currentPath}"]`) !== null;
        if (hasCurrentLinkInside) isOpen = true;

        if (isOpen) {
            el.classList.add('show');
            btn.classList.remove('collapsed');
            btn.setAttribute('aria-expanded', 'true');
            
            const icon = btn.querySelector('.angle-icon');
            if (icon) {
                icon.classList.add('no-transition');
                requestAnimationFrame(() => icon.classList.remove('no-transition'));
            }
        } else {
            el.classList.remove('show', 'collapsing');
            btn.classList.add('collapsed');
            btn.setAttribute('aria-expanded', 'false');
        }

        // Eventos para salvar estado
        el.addEventListener('show.bs.collapse', () => saveSubmenuState(id, true));
        el.addEventListener('hide.bs.collapse', () => saveSubmenuState(id, false));
    }

    document.querySelectorAll('.btn-toggle[data-bs-toggle="collapse"]').forEach(btn => {
        const target = document.querySelector(btn.getAttribute('data-bs-target'));
        if (!target) return;
        loadSubmenuState(target.id, target);

        // Lógica para abrir sidebar se estiver fechada
        btn.addEventListener('click', e => {
            if (window.innerWidth > breakpoint && body.classList.contains('sidebar-closed')) {
                e.preventDefault();
                e.stopPropagation();

                function openSubmenuAfterTransition(event) {
                    if (event.propertyName === 'width') {
                        const bsCollapse = bootstrap.Collapse.getOrCreateInstance(target);
                        bsCollapse.show();
                    }
                }

                sidebar.addEventListener('transitionend', openSubmenuAfterTransition, { once: true });
                body.classList.remove('sidebar-closed');
                saveSidebarState(false);
            }
        });
    });
});
