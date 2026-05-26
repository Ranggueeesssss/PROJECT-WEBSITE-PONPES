/* PRESTASI.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Prestasi Santri
   Requires: navbar.js (di-load sebelumnya via components.js) */

(function () {
  'use strict';

  /* DATA PRESTASI */
    // Array PRESTASI sekarang di-inject langsung dari database melalui prestasi.php

  /* RENDER HIGHLIGHT LIST (Daftar dalam highlight-bar) */
  function renderHighlightList() {
    var el = document.getElementById('highlightList');
    if (!el) return;

    el.innerHTML = PRESTASI.slice(0, 8).map(function (p, i) {
      return '<div class="prestasi-list__item">'
        + '<span class="prestasi-list__num">' + (i + 1) + '</span>'
        + '<span class="prestasi-list__text">'
        +   '<span class="highlight">' + p.rankLabel + '</span> ' + p.judul
        + '</span>'
        + '</div>';
    }).join('');
  }

  /* RENDER SIDEBAR PODIUM (Prestasi Terbaik) */
  function renderPodium() {
    var el = document.getElementById('podiumList');
    if (!el) return;

    var medals = { gold: '🥇', silver: '🥈', bronze: '🥉', green: '🏆' };

    var top = PRESTASI.filter(function (p) {
      return p.rank === 'gold' || p.rank === 'silver';
    }).slice(0, 5);

    el.innerHTML = top.map(function (p) {
      return '<div class="podium-item">'
        + '<span class="podium-item__medal">' + medals[p.rank] + '</span>'
        + '<div class="podium-item__info">'
        +   '<p class="podium-item__title">' + p.judul + '</p>'
        +   '<span class="podium-item__sub">' + p.tingkat + ' · ' + p.tahun + '</span>'
        + '</div>'
        + '</div>';
    }).join('');
  }

  /* ANIMASI COUNTER — Hero Stats */
  function animateHeroStats() {
    document.querySelectorAll('.hero-stat__number[data-target]').forEach(function (el) {
      var target = parseInt(el.dataset.target, 10);
      var suffix = el.dataset.suffix || '';
      var count  = 0;
      var step   = Math.ceil(target / 40);

      var timer = setInterval(function () {
        count += step;
        if (count >= target) { count = target; clearInterval(timer); }
        el.textContent = count + suffix;
      }, 35);
    });
  }

  /* SCROLL REVEAL — Timeline & Sidebar */
  function setupReveal() {
    var els = document.querySelectorAll('.timeline-item__card, .sidebar-widget, .highlight-bar');
    if (!('IntersectionObserver' in window) || !els.length) return;

    els.forEach(function (el) {
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(18px)';
      el.style.transition = 'opacity .5s ease, transform .5s ease';
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

    els.forEach(function (el) { obs.observe(el); });
  }

  /* INIT */
  function init() {
    renderHighlightList();
    renderPodium();
    animateHeroStats();
    setupReveal();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
