/* EquipTrack landing page behaviour. Smooth scrolling itself is CSS (scroll-behavior). */
(function () {
    'use strict';

    // sticky navbar gets a stronger background after scrolling
    var nav = document.querySelector('.lp-nav');
    function onScroll() { if (nav) nav.classList.toggle('scrolled', window.scrollY > 12); }
    onScroll();
    window.addEventListener('scroll', onScroll, { passive: true });

    // reveal sections as they enter the screen
    var revealEls = document.querySelectorAll('[data-reveal]');
    if ('IntersectionObserver' in window) {
        var reveal = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { e.target.classList.add('in'); reveal.unobserve(e.target); } });
        }, { threshold: 0.12 });
        revealEls.forEach(function (el) { reveal.observe(el); });
    } else {
        revealEls.forEach(function (el) { el.classList.add('in'); });
    }

    // highlight the nav link of the section currently on screen
    var links = Array.prototype.slice.call(document.querySelectorAll('.lp-nav .nav-link[href^="#"]'));
    var pairs = links.map(function (l) { return { link: l, section: document.querySelector(l.getAttribute('href')) }; }).filter(function (p) { return p.section; });
    if ('IntersectionObserver' in window && pairs.length) {
        var spy = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) {
                if (!e.isIntersecting) return;
                links.forEach(function (l) { l.classList.remove('active'); });
                var hit = pairs.filter(function (p) { return p.section === e.target; })[0];
                if (hit) hit.link.classList.add('active');
            });
        }, { rootMargin: '-45% 0px -50% 0px' });
        pairs.forEach(function (p) { spy.observe(p.section); });
    }

    // close the mobile menu after choosing a link
    var menu = document.getElementById('lpNav');
    links.forEach(function (l) {
        l.addEventListener('click', function () {
            if (menu && menu.classList.contains('show') && window.bootstrap) window.bootstrap.Collapse.getOrCreateInstance(menu).hide();
        });
    });

    // count-up for the real numbers in the highlights band
    var counters = document.querySelectorAll('[data-count]');
    function run(el) {
        var target = parseInt(el.getAttribute('data-count'), 10) || 0, start = null, dur = 1200;
        function step(ts) {
            if (start === null) start = ts;
            var p = Math.min((ts - start) / dur, 1);
            el.textContent = Math.round(target * (1 - Math.pow(1 - p, 3))).toLocaleString();
            if (p < 1) requestAnimationFrame(step);
        }
        requestAnimationFrame(step);
    }
    if ('IntersectionObserver' in window) {
        var count = new IntersectionObserver(function (entries) {
            entries.forEach(function (e) { if (e.isIntersecting) { run(e.target); count.unobserve(e.target); } });
        }, { threshold: 0.6 });
        counters.forEach(function (el) { count.observe(el); });
    } else {
        counters.forEach(function (el) { el.textContent = (parseInt(el.getAttribute('data-count'), 10) || 0).toLocaleString(); });
    }
})();
