<div class="site-header" id="siteHeader">
  <!-- BARIS 1 : TOP BAR -->
  <div class="topbar">
    <div class="topbar__info">
      <span class="topbar__info-item">
        <i class="fas fa-map-marker-alt"></i>
        Jl. Raung Klanceng Timur Kec. Ajung Kab. Jember, Jawa Timur
      </span>
      <span class="topbar__info-item">
        <i class="fas fa-phone"></i>
        (0331) 421603
      </span>
    </div>
    <div class="topbar__right">
      <a href="login.php" class="topbar__login" id="loginNavBtn">
        <i class="fas fa-sign-in-alt"></i> Login
      </a>
    </div>
  </div>

  <!-- BARIS 2 : MAIN HEADER -->
  <header>
    <a class="logo" aria-label="Beranda Ponpes Al-Barokah">
      <div class="logo__icon">
        <img src="Picture/logo.jpg" alt="Logo Ponpes Al-Barokah" />
      </div>
      <div class="logo__divider" aria-hidden="true"></div>
      <div class="logo__text">
        <span class="logo__name">Ponpes Al-Barokah<br>An-Nur Khumairoh</span>
      </div>
    </a>
    <nav id="mainNav" aria-label="Menu utama">
      <ul>
        <li><a href="Home.php">Home</a></li>
        <li><a href="informasi.php">Informasi</a></li>
        <li><a href="pengajar.php">Pengajar</a></li>
        <li><a href="prestasi.php">Prestasi</a></li>
        <li><a href="berita.php">Berita</a></li>
        <li><a href="galeri.php">Galeri</a></li>
        <li><a href="pendaftaran.php">Pendaftaran</a></li>
        <li><a href="hubungi-kami.php">Hubungi Kami</a></li>
      </ul>
    </nav>
    <button
      class="hamburger"
      id="hamburger"
      onclick="toggleNav()"
      aria-label="Buka/tutup menu"
      aria-expanded="false"
      aria-controls="mainNav"
    >
      <span></span><span></span><span></span>
    </button>
  </header>
</div>

<script>
  /* Tandai tombol Login sebagai aktif jika berada di halaman login */
  (function markLoginActive() {
    var page = window.location.pathname.split('/').pop();
    if (page === 'login.php' || page === 'login.html') {
      var loginBtn = document.getElementById('loginNavBtn');
      if (loginBtn) loginBtn.classList.add('is-active');
    }
  })();
</script>
