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
