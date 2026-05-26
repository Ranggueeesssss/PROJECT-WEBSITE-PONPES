/* INFORMASI.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Informasi
   Requires: navbar.js (di-load via components.js) */

(function () {
  'use strict';

  /* SCROLL REVEAL
     Kartu kurikulum dan item jenjang muncul saat di-scroll */
  function setupReveal() {
    var els = document.querySelectorAll(
      '.kurikulum-card, .jenjang-list li, .info-note, .sidebar-widget'
    );

    if (!('IntersectionObserver' in window) || !els.length) return;

    els.forEach(function (el) {
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(20px)';
      el.style.transition = 'opacity .45s ease, transform .45s ease';
    });

    var obs = new IntersectionObserver(function (entries) {
      entries.forEach(function (e) {
        if (e.isIntersecting) {
          e.target.style.opacity   = '1';
          e.target.style.transform = 'translateY(0)';
          obs.unobserve(e.target);
        }
      });
    }, { threshold: 0.12 });

    /* Tambah stagger delay per elemen */
    els.forEach(function (el, i) {
      var delay = Math.min(i * 60, 360);
      el.style.transitionDelay = delay + 'ms';
      obs.observe(el);
    });
  }

  /* INIT */
  function init() {
    setupReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
