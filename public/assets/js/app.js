/**
 * ATLEX - Sport — JavaScript global
 * Navigation mobile, scroll reveal, lien actif, flash auto-dismiss.
 */
(function () {
    'use strict';

    // --- Menu hamburger mobile ---
    var hamburger = document.querySelector('.nav-hamburger');
    var mobileMenu = document.querySelector('.nav-mobile-menu');
    if (hamburger && mobileMenu) {
        hamburger.addEventListener('click', function () {
            mobileMenu.classList.toggle('hidden');
        });
    }

    // --- Scroll reveal (IntersectionObserver) ---
    var reveals = document.querySelectorAll('.reveal');
    if ('IntersectionObserver' in window && reveals.length) {
        var observer = new IntersectionObserver(function (entries) {
            entries.forEach(function (entry) {
                if (entry.isIntersecting) {
                    entry.target.classList.add('visible');
                    observer.unobserve(entry.target);
                }
            });
        }, { threshold: 0.12 });
        reveals.forEach(function (el) { observer.observe(el); });
    } else {
        reveals.forEach(function (el) { el.classList.add('visible'); });
    }

    // --- Lien de navigation actif ---
    var path = window.location.pathname.replace(/\/+$/, '') || '/';
    document.querySelectorAll('.nav-link, .admin-nav-link').forEach(function (link) {
        var linkPath = link.getAttribute('data-path');
        if (!linkPath) { return; }
        if (linkPath === '/' ? path === '/' : path.indexOf(linkPath) === 0) {
            link.classList.add('active');
        }
    });

    // --- Disparition automatique des messages flash ---
    document.querySelectorAll('[data-flash]').forEach(function (el) {
        setTimeout(function () {
            el.style.transition = 'opacity 0.4s';
            el.style.opacity = '0';
            setTimeout(function () { el.remove(); }, 400);
        }, 4000);
    });
})();
