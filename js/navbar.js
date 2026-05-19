/* ================================================================
   NAVBAR.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Shared navigation logic — dipakai di semua halaman
   ================================================================ */

(function () {
  'use strict';

  /* ── Selector Cache ──────────────────────────────────────────── */
  const siteHeader = document.querySelector('.site-header');
  const nav        = document.getElementById('mainNav');
  const hamburger  = document.getElementById('hamburger');

  /* ── 1. Scroll Shadow ────────────────────────────────────────── */
  function onScroll() {
    if (!siteHeader) return;
    siteHeader.classList.toggle('scrolled', window.scrollY > 10);
  }

  window.addEventListener('scroll', onScroll, { passive: true });
  onScroll(); // run on load

  /* ── 2. Hamburger Toggle ─────────────────────────────────────── */
  function openNav()  {
    nav.classList.add('open');
    hamburger.classList.add('active');
    hamburger.setAttribute('aria-expanded', 'true');
  }

  function closeNav() {
    nav.classList.remove('open');
    hamburger.classList.remove('active');
    hamburger.setAttribute('aria-expanded', 'false');
  }

  function toggleNav() {
    nav.classList.contains('open') ? closeNav() : openNav();
  }

  /* Expose globally (dipakai onclick di HTML) */
  window.toggleNav = toggleNav;

  /* ── 3. Tutup nav saat klik di luar ─────────────────────────── */
  document.addEventListener('click', function (e) {
    if (!nav || !hamburger) return;
    if (
      nav.classList.contains('open') &&
      !nav.contains(e.target) &&
      !hamburger.contains(e.target)
    ) {
      closeNav();
    }
  });

  /* ── 4. Tutup nav + tandai active saat klik link ─────────────── */
  if (nav) {
    nav.querySelectorAll('a').forEach(function (link) {
      link.addEventListener('click', function () {
        /* Set active */
        nav.querySelectorAll('a').forEach(function (l) {
          l.classList.remove('active');
        });
        this.classList.add('active');

        /* Tutup hamburger menu di mobile */
        closeNav();
      });
    });
  }

  /* ── 5. Auto-mark active berdasarkan URL saat ini ─────────────── */
  (function markCurrentPage() {
    if (!nav) return;
    const currentFile = window.location.pathname.split('/').pop() || 'index.html';
    nav.querySelectorAll('a').forEach(function (link) {
      const linkFile = link.getAttribute('href').split('/').pop();
      if (linkFile === currentFile) {
        link.classList.add('active');
      }
    });
  })();

})();
