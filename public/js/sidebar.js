var sidebar = document.getElementById('sidebar');
var overlay = document.getElementById('sidebarOverlay');
var isMobile = function() { return window.innerWidth < 992; };

document.querySelector('.sidebar-logo')?.addEventListener('click', function() {
    if (isMobile()) {
        sidebar.classList.remove('open');
        overlay.classList.add('d-none');
    } else {
        sidebar.classList.toggle('collapsed');
    }
});

document.getElementById('btnMenu')?.addEventListener('click', function() {
    sidebar.classList.remove('collapsed');
    sidebar.classList.add('open');
    overlay.classList.remove('d-none');
});

overlay?.addEventListener('click', function() {
    sidebar.classList.remove('open');
    overlay.classList.add('d-none');
});

document.querySelectorAll('a[href="/ctt/logout"]').forEach(btn => {
    btn.addEventListener('click', async function (event) {
        event.preventDefault();
        const csrf = document.cookie
            .split('; ')
            .find((item) => item.startsWith('CTT_CSRF_TOKEN='))
            ?.split('=').slice(1).join('=');
        try {
            await fetch('/ctt/api/auth/logout', {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-Auth-Area': 'aluno',
                    ...(csrf ? { 'X-CSRF-Token': decodeURIComponent(csrf) } : {})
                },
                body: JSON.stringify({ area: 'aluno' })
            });
        } catch (_) {
        } finally {
            window.location.href = '/ctt/login';
        }
    });
});
