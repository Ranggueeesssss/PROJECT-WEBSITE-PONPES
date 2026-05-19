/* ================================================================
   GALERI.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Galeri Santri
   Requires: navbar.js (di-load via components.js)
   ================================================================ */

(function () {
  'use strict';

  /* ================================================================
     DATA FOTO
     Isi field `src` dengan path gambar asli jika sudah tersedia.
     Contoh: src: 'Picture/namafile.jpg'
     ================================================================ */
    // Array FOTO sekarang di-inject langsung dari database melalui galeri.php

  /* ── State ─────────────────────────────────────────────────── */
  var currentKat    = 'Semua';
  var currentIndex  = 0;   /* index foto aktif di lightbox */
  var filteredFoto  = [];  /* foto yang sedang tampil */

  /* ================================================================
     RENDER
     ================================================================ */

  /* Buat HTML placeholder jika tidak ada gambar */
  function imgOrPlaceholder(foto, forLightbox) {
    if (foto.src) {
      var cls = forLightbox ? 'lightbox__img' : '';
      return '<img src="' + foto.src + '" alt="' + foto.judul + '" loading="lazy"'
        + (cls ? ' class="' + cls + '"' : '') + ' />';
    }
    var cls = forLightbox ? 'lightbox__placeholder' : 'img-placeholder';
    return '<div class="' + cls + '">'
      + '<i class="fas fa-image"></i>'
      + (forLightbox ? '<span>Foto belum tersedia</span>' : '<span>Foto Dokumentasi</span>')
      + '</div>';
  }

  /* Render satu kartu */
  function cardHTML(foto, idx) {
    return '<div class="galeri-card" data-index="' + idx + '" role="button" tabindex="0"'
      + ' aria-label="Buka foto: ' + foto.judul + '">'
      + '<div class="galeri-card__img">'
      +   imgOrPlaceholder(foto, false)
      +   '<div class="galeri-card__overlay"><i class="fas fa-search-plus"></i></div>'
      +   '<span class="galeri-card__badge">' + foto.kategori + '</span>'
      + '</div>'
      + '<div class="galeri-card__info">'
      +   '<h3 class="galeri-card__title">' + foto.judul + '</h3>'
      +   '<div class="galeri-card__meta">'
      +     '<i class="fas fa-calendar-alt"></i>'
      +     foto.tanggal
      +   '</div>'
      + '</div>'
      + '</div>';
  }

  /* Render grid */
  function renderGrid() {
    var grid    = document.getElementById('galeriGrid');
    var countEl = document.getElementById('galeriCount');
    if (!grid) return;

    filteredFoto = currentKat === 'Semua'
      ? FOTO.slice()
      : FOTO.filter(function (f) { return f.kategori === currentKat; });

    if (countEl) {
      countEl.textContent = filteredFoto.length + ' foto';
    }

    if (filteredFoto.length === 0) {
      grid.innerHTML = '<div class="empty-state">'
        + '<i class="fas fa-images"></i>'
        + '<h3>Belum ada foto di kategori ini</h3>'
        + '<p>Segera hadir!</p>'
        + '</div>';
      return;
    }

    grid.innerHTML = filteredFoto.map(function (f, i) {
      return cardHTML(f, i);
    }).join('');

    /* Pasang event klik pada setiap kartu */
    grid.querySelectorAll('.galeri-card').forEach(function (card) {
      card.addEventListener('click', function () {
        openLightbox(parseInt(this.dataset.index, 10));
      });
      /* Keyboard accessibility */
      card.addEventListener('keydown', function (e) {
        if (e.key === 'Enter' || e.key === ' ') {
          e.preventDefault();
          openLightbox(parseInt(this.dataset.index, 10));
        }
      });
    });

    animateCards();
  }

  /* Animasi kartu masuk */
  function animateCards() {
    document.querySelectorAll('.galeri-card').forEach(function (el, i) {
      el.style.opacity    = '0';
      el.style.transform  = 'translateY(18px)';
      el.style.transition = 'opacity .35s ease, transform .35s ease';
      setTimeout(function () {
        el.style.opacity   = '1';
        el.style.transform = 'translateY(0)';
      }, i * 60);
    });
  }

  /* ================================================================
     LIGHTBOX
     ================================================================ */
  var lightboxEl  = null;

  function buildLightbox() {
    lightboxEl = document.createElement('div');
    lightboxEl.className  = 'lightbox';
    lightboxEl.id         = 'lightbox';
    lightboxEl.setAttribute('role', 'dialog');
    lightboxEl.setAttribute('aria-modal', 'true');

    lightboxEl.innerHTML =
      '<div class="lightbox__backdrop" id="lbBackdrop"></div>'
      + '<button class="lightbox__nav lightbox__nav--prev" id="lbPrev" aria-label="Foto sebelumnya">'
      +   '<i class="fas fa-chevron-left"></i>'
      + '</button>'
      + '<div class="lightbox__content">'
      +   '<button class="lightbox__close" id="lbClose" aria-label="Tutup">'
      +     '<i class="fas fa-times"></i>'
      +   '</button>'
      +   '<div id="lbImgWrap"></div>'
      +   '<div class="lightbox__info">'
      +     '<p class="lightbox__title" id="lbTitle"></p>'
      +     '<div class="lightbox__meta">'
      +       '<i class="fas fa-tag"></i>'
      +       '<span id="lbKategori"></span>'
      +       '<span style="margin:0 8px;">·</span>'
      +       '<i class="fas fa-calendar-alt"></i>'
      +       '<span id="lbTanggal"></span>'
      +     '</div>'
      +   '</div>'
      + '</div>'
      + '<button class="lightbox__nav lightbox__nav--next" id="lbNext" aria-label="Foto berikutnya">'
      +   '<i class="fas fa-chevron-right"></i>'
      + '</button>';

    document.body.appendChild(lightboxEl);

    document.getElementById('lbClose').addEventListener('click', closeLightbox);
    document.getElementById('lbBackdrop').addEventListener('click', closeLightbox);
    document.getElementById('lbPrev').addEventListener('click', function () { navigate(-1); });
    document.getElementById('lbNext').addEventListener('click', function () { navigate(1); });
  }

  function openLightbox(index) {
    currentIndex = index;
    updateLightbox();
    lightboxEl.classList.add('open');
    document.body.style.overflow = 'hidden';
    document.getElementById('lbClose').focus();
  }

  function closeLightbox() {
    lightboxEl.classList.remove('open');
    document.body.style.overflow = '';
  }

  function navigate(dir) {
    currentIndex = (currentIndex + dir + filteredFoto.length) % filteredFoto.length;
    updateLightbox();
  }

  function updateLightbox() {
    var foto = filteredFoto[currentIndex];
    document.getElementById('lbImgWrap').innerHTML   = imgOrPlaceholder(foto, true);
    document.getElementById('lbTitle').textContent   = foto.judul;
    document.getElementById('lbKategori').textContent = foto.kategori;
    document.getElementById('lbTanggal').textContent  = foto.tanggal;
  }

  /* ================================================================
     FILTER
     ================================================================ */
  function setupFilters() {
    document.querySelectorAll('.filter-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(function (b) {
          b.classList.remove('active');
        });
        this.classList.add('active');
        currentKat = this.dataset.kat;
        renderGrid();
      });
    });
  }

  /* ================================================================
     KEYBOARD — tutup dengan Escape, navigasi dengan panah
     ================================================================ */
  function setupKeyboard() {
    document.addEventListener('keydown', function (e) {
      if (!lightboxEl || !lightboxEl.classList.contains('open')) return;
      if (e.key === 'Escape')     closeLightbox();
      if (e.key === 'ArrowLeft')  navigate(-1);
      if (e.key === 'ArrowRight') navigate(1);
    });
  }

  /* ================================================================
     INIT
     ================================================================ */
  function init() {
    buildLightbox();
    renderGrid();
    setupFilters();
    setupKeyboard();
  }

  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
