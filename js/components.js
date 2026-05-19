/* ================================================================
   COMPONENTS.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Meng-inject komponen Navbar (topbar + header) dan Footer
   ke setiap halaman secara otomatis.

   Cara pakai di HTML:
     <div id="site-header-placeholder"></div>
     ... konten halaman ...
     <div id="site-footer-placeholder"></div>
     <script src="js/components.js"></script>
     <script src="js/navbar.js"></script>
   ================================================================ */

(function () {
  'use strict';

  /* ================================================================
     TEMPLATE: SITE HEADER
     Berisi topbar (baris 1) + main header (baris 2)
     Active class diatur otomatis oleh navbar.js (markCurrentPage)
     ================================================================ */
  var SITE_HEADER = '\
<div class="site-header" id="siteHeader">\
\
  <!-- BARIS 1 : TOP BAR -->\
  <div class="topbar">\
    <div class="topbar__info">\
      <span class="topbar__info-item">\
        <i class="fas fa-map-marker-alt"></i>\
        Jl. Pesantren No.1, Jember\
      </span>\
      <span class="topbar__info-item">\
        <i class="fas fa-phone"></i>\
        (0331) 123-4567\
      </span>\
      <span class="topbar__info-item">\
        <i class="fas fa-envelope"></i>\
        info@albarokah.sch.id\
      </span>\
    </div>\
    <div class="topbar__right">\
      <a href="login.php" class="topbar__login" id="loginNavBtn">\
        <i class="fas fa-sign-in-alt"></i> Login\
      </a>\
    </div>\
  </div>\
\
  <!-- BARIS 2 : MAIN HEADER -->\
  <header>\
    <a class="logo" aria-label="Beranda Ponpes Al-Barokah">\
      <div class="logo__icon">\
        <img src="Picture/logo.jpg" alt="Logo Ponpes Al-Barokah" />\
      </div>\
      <div class="logo__divider" aria-hidden="true"></div>\
      <div class="logo__text">\
        <span class="logo__name">Ponpes Al-Barokah<br>An-Nur Khumairoh</span>\
      </div>\
    </a>\
    <nav id="mainNav" aria-label="Menu utama">\
      <ul>\
        <li><a href="Home.php">Home</a></li>\
        <li><a href="informasi.html">Informasi</a></li>\
        <li><a href="pengajar.php">Pengajar</a></li>\
        <li><a href="prestasi.php">Prestasi</a></li>\
        <li><a href="berita.php">Berita</a></li>\
        <li><a href="galeri.php">Galeri</a></li>\
        <li><a href="pendaftaran.php">Pendaftaran</a></li>\
        <li><a href="hubungi-kami.php">Hubungi Kami</a></li>\
      </ul>\
    </nav>\
    <button\
      class="hamburger"\
      id="hamburger"\
      onclick="toggleNav()"\
      aria-label="Buka/tutup menu"\
      aria-expanded="false"\
      aria-controls="mainNav"\
    >\
      <span></span><span></span><span></span>\
    </button>\
  </header>\
\
</div>';

  /* ================================================================
     TEMPLATE: FOOTER
     ================================================================ */
  var SITE_FOOTER = '\
<footer>\
  <p>&copy; 2026 <span>Ponpes Al-Barokah An-Nur Khumairoh</span>. Semua Hak Dilindungi.</p>\
</footer>';

  /* ================================================================
     INJECT ke placeholder yang ada di setiap halaman
     ================================================================ */
  function inject(id, html) {
    var el = document.getElementById(id);
    if (!el) return;
    /* Ganti placeholder dengan HTML komponen */
    var wrapper = document.createElement('div');
    wrapper.innerHTML = html;
    el.parentNode.replaceChild(wrapper.firstElementChild, el);
  }

  inject('site-header-placeholder', SITE_HEADER);
  inject('site-footer-placeholder', SITE_FOOTER);

  /* ================================================================
     Tandai tombol Login sebagai aktif jika berada di halaman login
     ================================================================ */
  (function markLoginActive() {
    var page = window.location.pathname.split('/').pop();
    if (page === 'login.php' || page === 'login.html') {
      var loginBtn = document.getElementById('loginNavBtn');
      if (loginBtn) loginBtn.classList.add('is-active');
    }
  })();

})();
