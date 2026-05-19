/* ================================================================
   BERITA.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Berita
   Requires: navbar.js (sudah di-load sebelumnya)
   ================================================================ */

(function () {
  'use strict';

  /* ================================================================
     DATA ARTIKEL (simulasi — ganti dengan fetch API / CMS)
     ================================================================ */
    // Array ARTICLES sekarang di-inject langsung dari database melalui berita.php

  /* ================================================================
     STATE
     ================================================================ */
  let currentKategori = 'Semua';
  let currentPage     = 1;
  let currentSearch   = '';
  const PER_PAGE      = 6;

  /* ================================================================
     FILTER & SEARCH
     ================================================================ */
  function getFiltered() {
    return ARTICLES.filter(function (a) {
      const matchKat    = currentKategori === 'Semua' || a.kategori === currentKategori;
      const matchSearch = !currentSearch ||
        a.judul.toLowerCase().includes(currentSearch.toLowerCase()) ||
        a.excerpt.toLowerCase().includes(currentSearch.toLowerCase());
      return matchKat && matchSearch;
    });
  }

  function getPaginated(list) {
    const start = (currentPage - 1) * PER_PAGE;
    return list.slice(start, start + PER_PAGE);
  }

  /* ================================================================
     RENDER
     ================================================================ */

  /* Buat HTML gambar atau placeholder */
  function imgHTML(src, alt, cls) {
    if (src) {
      return '<img src="' + src + '" alt="' + alt + '" loading="lazy" />';
    }
    return '<div class="img-placeholder ' + (cls || '') + '">'
      + '<i class="fas fa-newspaper"></i>'
      + '<span>Foto Berita</span>'
      + '</div>';
  }

  /* Render featured article */
  function renderFeatured() {
    const el      = document.getElementById('featuredArticle');
    const article = ARTICLES.find(function (a) { return a.featured; });
    if (!el || !article) return;

    el.innerHTML =
      '<div class="featured-article__img">'
      +   imgHTML(article.gambar, article.judul)
      +   '<span class="featured-article__badge">' + article.kategori + '</span>'
      + '</div>'
      + '<div class="featured-article__body">'
      +   '<span class="featured-label">Artikel Unggulan</span>'
      +   '<h2 class="featured-article__title">' + article.judul + '</h2>'
      +   '<p class="featured-article__excerpt">' + article.excerpt + '</p>'
      +   '<div class="featured-article__meta">'
      +     '<div class="meta-info">'
      +       '<span class="meta-item"><i class="fas fa-calendar-alt"></i>' + article.tanggal + '</span>'
      +       '<span class="meta-item"><i class="fas fa-user"></i>' + article.penulis + '</span>'
      +     '</div>'
      +     '<a href="javascript:void(0)" class="btn-read-more" onclick="openArticleModal(' + article.id + ')">Baca Selengkapnya <i class="fas fa-arrow-right"></i></a>'
      +   '</div>'
      + '</div>';
  }

  /* Render satu kartu berita */
  function cardHTML(a) {
    return '<article class="berita-card" data-id="' + a.id + '">'
      + '<div class="berita-card__img">'
      +   imgHTML(a.gambar, a.judul)
      +   '<span class="card-badge">' + a.kategori + '</span>'
      + '</div>'
      + '<div class="berita-card__body">'
      +   '<h3 class="berita-card__title">' + a.judul + '</h3>'
      +   '<p class="berita-card__excerpt">' + a.excerpt + '</p>'
      +   '<div class="berita-card__footer">'
      +     '<span class="meta-item"><i class="fas fa-calendar-alt"></i>' + a.tanggal + '</span>'
      +     '<a href="javascript:void(0)" class="btn-detail" onclick="openArticleModal(' + a.id + ')">Detail <i class="fas fa-chevron-right"></i></a>'
      +   '</div>'
      + '</div>'
      + '</article>';
  }

  /* Render grid artikel */
  function renderGrid() {
    const grid     = document.getElementById('beritaGrid');
    const infoEl   = document.getElementById('articleCount');
    if (!grid) return;

    const filtered  = getFiltered();
    const paginated = getPaginated(filtered);

    if (infoEl) {
      infoEl.textContent = 'Menampilkan ' + Math.min(filtered.length, PER_PAGE) + ' dari ' + filtered.length + ' artikel';
    }

    if (paginated.length === 0) {
      grid.innerHTML =
        '<div class="empty-state">'
        + '<i class="fas fa-search"></i>'
        + '<h3>Artikel tidak ditemukan</h3>'
        + '<p>Coba kata kunci atau kategori lain</p>'
        + '</div>';
      return;
    }

    grid.innerHTML = paginated.map(cardHTML).join('');
    animateCards();
    renderPagination(filtered.length);
  }

  /* Animasi masuk kartu */
  function animateCards() {
    const cards = document.querySelectorAll('.berita-card');
    cards.forEach(function (card, i) {
      card.style.opacity   = '0';
      card.style.transform = 'translateY(20px)';
      card.style.transition = 'opacity .4s ease, transform .4s ease';
      setTimeout(function () {
        card.style.opacity   = '1';
        card.style.transform = 'translateY(0)';
      }, i * 80);
    });
  }

  /* Render pagination */
  function renderPagination(total) {
    const el         = document.getElementById('pagination');
    if (!el) return;
    const totalPages = Math.ceil(total / PER_PAGE);

    if (totalPages <= 1) { el.innerHTML = ''; return; }

    let html = '';

    /* Prev */
    html += '<button class="page-btn" data-page="' + (currentPage - 1) + '" '
          + (currentPage === 1 ? 'disabled' : '') + '>'
          + '<i class="fas fa-chevron-left"></i></button>';

    /* Pages */
    for (let p = 1; p <= totalPages; p++) {
      html += '<button class="page-btn' + (p === currentPage ? ' active' : '') + '" data-page="' + p + '">' + p + '</button>';
    }

    /* Next */
    html += '<button class="page-btn" data-page="' + (currentPage + 1) + '" '
          + (currentPage === totalPages ? 'disabled' : '') + '>'
          + '<i class="fas fa-chevron-right"></i></button>';

    el.innerHTML = html;

    /* Event */
    el.querySelectorAll('.page-btn:not([disabled])').forEach(function (btn) {
      btn.addEventListener('click', function () {
        currentPage = parseInt(this.dataset.page, 10);
        renderGrid();
        document.querySelector('.berita-layout').scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  /* ================================================================
     MODAL BERITA
     ================================================================ */
  const modal = document.getElementById('beritaModal');
  const modalContent = document.getElementById('modalContent');
  const closeBtn = document.getElementById('closeModalBtn');

  window.openArticleModal = function(id) {
      const article = ARTICLES.find(a => a.id == id);
      if(!article || !modal || !modalContent) return;

      const imgSrc = article.gambar ? `<img src="${article.gambar}" style="width:100%; max-height:400px; object-fit:cover; border-radius:10px; margin-bottom:20px;" alt="${article.judul}">` : '';
      
      modalContent.innerHTML = `
          <span style="background:var(--green-50); color:var(--green-600); padding:5px 12px; border-radius:20px; font-size:0.8rem; font-weight:600; display:inline-block; margin-bottom:15px;">${article.kategori}</span>
          <h2 style="font-size:1.8rem; color:var(--text-dark); margin-bottom:15px; line-height:1.3;">${article.judul}</h2>
          <div style="display:flex; gap:15px; color:var(--gray-mid); font-size:0.85rem; margin-bottom:25px; padding-bottom:20px; border-bottom:1px solid #e5e7eb;">
              <span><i class="fas fa-calendar-alt"></i> ${article.tanggal}</span>
              <span><i class="fas fa-user-edit"></i> ${article.penulis}</span>
          </div>
          ${imgSrc}
          <div style="color:#374151; font-size:1.05rem; line-height:1.8; font-family:'Poppins', sans-serif; white-space: pre-wrap;">${article.isi_berita || article.excerpt}</div>
      `;

      modal.style.display = 'flex';
      document.body.style.overflow = 'hidden';
  }

  if(closeBtn && modal) {
      closeBtn.addEventListener('click', () => {
          modal.style.display = 'none';
          document.body.style.overflow = '';
      });
      modal.addEventListener('click', (e) => {
          if(e.target === modal) {
              modal.style.display = 'none';
              document.body.style.overflow = '';
          }
      });
  }

  /* Render sidebar: berita terkini */
  function renderRecent() {
    const el = document.getElementById('recentList');
    if (!el) return;
    const recent = ARTICLES.slice(0, 4);

    el.innerHTML = recent.map(function (a) {
      return '<div class="recent-item">'
        + '<div class="recent-item__thumb">'
        +   (a.gambar
              ? '<img src="' + a.gambar + '" alt="' + a.judul + '" />'
              : '<i class="fas fa-newspaper"></i>')
        + '</div>'
        + '<div class="recent-item__info">'
        +   '<p class="recent-item__title">' + a.judul + '</p>'
        +   '<span class="recent-item__date"><i class="fas fa-clock"></i>' + a.tanggal + '</span>'
        + '</div>'
        + '</div>';
    }).join('');
  }

  /* ================================================================
     EVENT LISTENERS
     ================================================================ */

  /* Filter kategori (tabs + chips sidebar) */
  function setupFilters() {
    /* Toolbar tabs */
    document.querySelectorAll('.filter-btn').forEach(function (btn) {
      btn.addEventListener('click', function () {
        document.querySelectorAll('.filter-btn').forEach(function (b) { b.classList.remove('active'); });
        this.classList.add('active');
        currentKategori = this.dataset.kat;
        currentPage     = 1;
        renderGrid();
      });
    });

    /* Sidebar kategori chips */
    document.querySelectorAll('.kategori-chip').forEach(function (chip) {
      chip.addEventListener('click', function () {
        document.querySelectorAll('.kategori-chip').forEach(function (c) { c.classList.remove('active'); });
        this.classList.add('active');
        currentKategori = this.dataset.kat;
        currentPage     = 1;

        /* Sync toolbar */
        document.querySelectorAll('.filter-btn').forEach(function (btn) {
          btn.classList.toggle('active', btn.dataset.kat === currentKategori);
        });

        renderGrid();
        document.querySelector('.berita-layout').scrollIntoView({ behavior: 'smooth', block: 'start' });
      });
    });
  }

  /* Search */
  function setupSearch() {
    const input = document.getElementById('searchInput');
    const btn   = document.getElementById('searchBtn');
    if (!input) return;

    function doSearch() {
      currentSearch = input.value.trim();
      currentPage   = 1;
      renderGrid();
    }

    btn && btn.addEventListener('click', doSearch);
    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter') doSearch();
    });

    /* Live search dengan debounce */
    let debounce;
    input.addEventListener('input', function () {
      clearTimeout(debounce);
      debounce = setTimeout(doSearch, 350);
    });
  }

  /* ================================================================
     INIT
     ================================================================ */
  function init() {
    renderFeatured();
    renderGrid();
    renderRecent();
    setupFilters();
    setupSearch();
  }

  /* Jalankan setelah DOM siap */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }

})();
