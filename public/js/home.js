var header = document.querySelector('.header');
var navbarCollapse = document.getElementById('navbarHome');
var menuOpen = false;

window.addEventListener('scroll', function () {
    header.classList.toggle('scrolled', window.scrollY > 50 || menuOpen);
});

navbarCollapse.addEventListener('show.bs.collapse', function () {
    menuOpen = true;
    header.classList.add('scrolled');
});

navbarCollapse.addEventListener('hidden.bs.collapse', function () {
    menuOpen = false;
    if (window.scrollY <= 50) {
        header.classList.remove('scrolled');
    }
});

function initCarousel(config) {
    var track = document.querySelector(config.track);
    if (!track) return;
    var dots = document.querySelectorAll(config.dots);
    var current = 0;
    var total = track.children.length;
    var autoplayTimer;
    var breakpoint = config.breakpoint || 768;

    function isMobile() {
        return window.innerWidth < breakpoint;
    }

    function goTo(index) {
        current = index;
        if (isMobile()) {
            var slideWidth = track.parentElement.offsetWidth;
            track.style.transform = 'translateX(' + (-index * slideWidth) + 'px)';
        } else {
            track.style.transform = 'none';
        }
        dots.forEach(function (dot, i) {
            dot.classList.toggle('active', i === index);
        });
    }

    function next() {
        goTo((current + 1) % total);
    }

    function startAutoplay() {
        stopAutoplay();
        if (isMobile()) {
            autoplayTimer = setInterval(next, 4000);
        }
    }

    function stopAutoplay() {
        clearInterval(autoplayTimer);
    }

    dots.forEach(function (dot) {
        dot.addEventListener('click', function () {
            goTo(parseInt(this.dataset.index));
            startAutoplay();
        });
    });

    track.addEventListener('touchstart', stopAutoplay, { passive: true });
    track.addEventListener('touchend', startAutoplay, { passive: true });

    window.addEventListener('resize', function () {
        if (isMobile()) {
            goTo(current);
            startAutoplay();
        } else {
            goTo(0);
            stopAutoplay();
        }
    });

    startAutoplay();
}

initCarousel({
    track: '.modalidades-track',
    dots: '.modalidade-dots .dot',
    breakpoint: 768
});

initCarousel({
    track: '.planos-track',
    dots: '.plano-dots .dot',
    breakpoint: 992
});
