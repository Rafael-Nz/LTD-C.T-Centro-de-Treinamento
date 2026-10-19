var sidebar = document.getElementById('sidebar');
var overlay = document.getElementById('sidebarOverlay');
var isMobile = function() { return window.innerWidth < 992; };

document.querySelector('.sidebar-logo').addEventListener('click', function() {
    if (isMobile()) {
        sidebar.classList.remove('open');
        overlay.classList.add('d-none');
    } else {
        sidebar.classList.toggle('collapsed');
    }
});

document.getElementById('btnMenu').addEventListener('click', function() {
    sidebar.classList.remove('collapsed');
    sidebar.classList.add('open');
    overlay.classList.remove('d-none');
});

overlay.addEventListener('click', function() {
    sidebar.classList.remove('open');
    overlay.classList.add('d-none');
});

var userProfile = document.getElementById('userProfile');
var userDropdown = document.getElementById('userDropdown');

userProfile.addEventListener('click', function(e) {
    e.stopPropagation();
    userDropdown.classList.toggle('open');
});

document.addEventListener('click', function() {
    userDropdown.classList.remove('open');
});

document.querySelector('a[href="/ctt/logout"]')?.addEventListener('click', async function (event) {
    event.preventDefault();
    try {
        await fetch('/ctt/api/auth/logout', {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-Auth-Area': 'aluno'
            },
            body: JSON.stringify({ area: 'aluno' })
        });
    } catch (_) {
        // Prossegue com redirecionamento em caso de oscilação de rede
    } finally {
        window.location.href = '/ctt/login';
    }
});
