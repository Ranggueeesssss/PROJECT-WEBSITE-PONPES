<?php
// Home.php — Ponpes Al-Barokah An-Nur Khumairoh
// Halaman utama website pesantren

require_once __DIR__ . '/koneksi.php';

// --- STATISTIK DINAMIS ---
// 1. Jumlah Santri (Mengikuti logika dashboard.php, termasuk manual mode)
$countSantriDb = 0;
$resSantri = $conn->query("SELECT COUNT(*) FROM data_santri");
if ($resSantri) {
    $countSantriDb = $resSantri->fetch_row()[0];
}

$santriManualFile = __DIR__ . '/santri_manual.json';
$finalSantriCount = $countSantriDb;
if (file_exists($santriManualFile)) {
    $fileData = json_decode(file_get_contents($santriManualFile), true);
    if ($fileData && isset($fileData['mode']) && $fileData['mode'] === 'manual') {
        $finalSantriCount = (int)$fileData['count'];
    }
}

// 2. Pengajar Pengalaman
$countPengajar = 0;
$resPengajar = $conn->query("SELECT COUNT(*) FROM profil_pengajar");
if ($resPengajar) {
    $countPengajar = $resPengajar->fetch_row()[0];
}

// 3. Prestasi
$countPrestasi = 0;
$resPrestasi = $conn->query("SELECT COUNT(*) FROM profil_prestasi");
if ($resPrestasi) {
    $countPrestasi = $resPrestasi->fetch_row()[0];
}

// 4. Tahun Berdiri (Misal dari tahun 2011, diset minimal 15)
$tahunBerdiri = 15;

// --- LOGIKA DINAMIS TAHUN AJARAN ---
$tahunSekarang = (int)date('Y');
$bulanSekarang = (int)date('n');

if ($bulanSekarang >= 8) {
    $tahunAjaranBuka = ($tahunSekarang + 1) . '/' . ($tahunSekarang + 2);
    $tahunBuka = $tahunSekarang + 1;
} else {
    $tahunAjaranBuka = $tahunSekarang . '/' . ($tahunSekarang + 1);
    $tahunBuka = $tahunSekarang;
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Ponpes Al-Barokah An-Nur Khumairoh — Pondok Pesantren Modern berlandaskan Al-Qur'an dan As-Sunnah." />
  <title>Home — Ponpes Al-Barokah An-Nur Khumairoh</title>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/shared/base.css"   />
  <link rel="stylesheet" href="css/shared/navbar.css" />
  <link rel="stylesheet" href="css/shared/header.css" />
  <link rel="stylesheet" href="css/shared/footer.css" />
  <link rel="stylesheet" href="css/website/home.css"  />
</head>
<body>

<?php include 'includes/header.php'; ?>

<section class="hero" aria-label="Banner utama">
  <div class="hero__bg" aria-hidden="true"></div>
  <div class="hero__content">
    <span class="hero__label">Selamat Datang</span>
    <h1 class="hero__title">About Us</h1>
    <p class="hero__subtitle">Al Barokah</p>
    <p class="hero__desc">
      <span>Pondok Pesantren Modern</span> dengan Mengedepankan Pendidikan Karakter,
      Nilai-Nilai Islam dan Pembinaan Ahlaqul Karimah
    </p>
    <a href="#pendaftaran" class="hero__cta">
      <i class="fas fa-user-plus" aria-hidden="true"></i> Panduan Pendaftaran
    </a>
  </div>
</section>

<div class="stats" aria-label="Statistik pesantren">
  <div class="stats__item">
    <i class="fas fa-user-graduate stats__icon" aria-hidden="true"></i>
    <span class="stats__number" data-target="<?php echo $finalSantriCount; ?>" data-suffix="+"><?php echo $finalSantriCount; ?>+</span>
    <span class="stats__label">Jumlah Santri</span>
  </div>
  <div class="stats__item">
    <i class="fas fa-chalkboard-teacher stats__icon" aria-hidden="true"></i>
    <span class="stats__number" data-target="<?php echo $countPengajar; ?>" data-suffix="+"><?php echo $countPengajar; ?>+</span>
    <span class="stats__label">Pengajar Pengalaman</span>
  </div>
  <div class="stats__item">
    <i class="fas fa-mosque stats__icon" aria-hidden="true"></i>
    <span class="stats__number" data-target="<?php echo $tahunBerdiri; ?>" data-suffix="+"><?php echo $tahunBerdiri; ?>+</span>
    <span class="stats__label">Tahun Berdiri</span>
  </div>
  <div class="stats__item">
    <i class="fas fa-trophy stats__icon" aria-hidden="true"></i>
    <span class="stats__number" data-target="<?php echo $countPrestasi; ?>" data-suffix="+"><?php echo $countPrestasi; ?>+</span>
    <span class="stats__label">Prestasi</span>
  </div>
</div>

<section class="about" id="about" aria-label="Tentang kami">
  <div class="about__image" role="img" aria-label="Foto kegiatan santri"></div>
  <div class="about__content">
    <span class="section__tag">Tentang Kami</span>
    <h2 class="section__title">Ponpes Al-Barokah<br>An-Nur Khumairoh</h2>
    <p class="section__body">
      Pondok Pesantren Al-Barokah An-Nur Khumairoh adalah lembaga pendidikan Islam modern
      yang berdiri di atas dan untuk semua golongan. Kami berkomitmen menghasilkan generasi
      muslim yang berilmu, berakhlak mulia, dan siap berkontribusi bagi bangsa dan agama.
    </p>
    <p class="section__body">
      Dengan kurikulum terintegrasi antara ilmu agama dan ilmu umum, para santri dibimbing
      oleh pengajar berpengalaman dalam lingkungan yang kondusif dan Islami.
    </p>
    <a href="Informasi.php" class="btn-primary">
      <i class="fas fa-arrow-right" aria-hidden="true"></i> Selengkapnya
    </a>
  </div>
</section>

<!-- VISI & MISI — Redesigned Section -->
<section class="visi-misi" id="visi-misi" aria-label="Visi dan Misi">

  <!-- Gambar latar + overlay -->
  <div class="vm-hero-img" aria-hidden="true">
    <div class="vm-hero-img__overlay"></div>
  </div>

  <!-- Heading -->
  <div class="visi-misi__heading">
    <span class="section__tag vm-tag">Tentang Kami</span>
    <h2>Visi &amp; Misi</h2>
    <p>Ponpes Al-Barokah An-Nur Khumairoh</p>
    <div class="vm-deco" aria-hidden="true">
      <div class="vm-deco__line"></div>
      <div class="vm-deco__dots"><span></span><span></span><span></span></div>
    </div>
  </div>

  <!-- Konten utama: gambar kiri + kartu kanan -->
  <div class="vm-main-layout">

    <!-- Kolom kiri: Gambar ilustrasi -->
    <div class="vm-image-col">
      <div class="vm-image-wrap">
        <img src="Picture/visi_misi_bg.jpg" alt="Ilustrasi visi misi Pondok Pesantren Al-Barokah" loading="lazy" />
      </div>
      <!-- Quote inspiratif -->
      <blockquote class="vm-quote">
        <i class="fas fa-quote-left vm-quote__icon" aria-hidden="true"></i>
        <p>"Sebaik-baik manusia adalah yang paling bermanfaat bagi manusia lain."</p>
        <cite>— HR. Ahmad &amp; Thabrani</cite>
      </blockquote>
    </div>

    <!-- Kolom kanan: Visi + Misi -->
    <div class="vm-cards-col">

      <!-- Kartu VISI -->
      <article class="vm-card vm-card--visi" data-reveal>
        <div class="vm-card__accent"></div>
        <div class="vm-card__header">
          <div class="vm-card__icon-wrap" aria-hidden="true">
            <i class="fas fa-eye"></i>
          </div>
          <div>
            <span class="vm-card__kicker">Landasan Utama</span>
            <h3 class="vm-card__title">Visi</h3>
          </div>
        </div>
        <div class="vm-card__body">
          <p>
            Sebagai lembaga pendidikan Islam dan ladang dakwah yang mencetak kader-kader
            pemimpin umat yang Muslim Mukmin,
            <strong class="highlight">berakhlakul karimah, intelek, mandiri dan berjiwa santri.</strong>
          </p>
        </div>
      </article>

      <!-- Kartu MISI -->
      <article class="vm-card vm-card--misi" data-reveal>
        <div class="vm-card__accent"></div>
        <div class="vm-card__header">
          <div class="vm-card__icon-wrap" aria-hidden="true">
            <i class="fas fa-bullseye"></i>
          </div>
          <div>
            <span class="vm-card__kicker">Tujuan Strategis</span>
            <h3 class="vm-card__title">Misi</h3>
          </div>
        </div>
        <ul class="misi-list">
          <li>
            <div class="num" aria-hidden="true">1</div>
            <span>Mempersiapkan generasi unggul demi terbentuknya <strong class="highlight">khoirol umah</strong></span>
          </li>
          <li>
            <div class="num" aria-hidden="true">2</div>
            <span>Mendidik dan mengembangkan generasi yang sehat jasmani &amp; rohani, berpengetahuan luas dan <strong class="highlight">berakhlakul kharimah</strong></span>
          </li>
          <li>
            <div class="num" aria-hidden="true">3</div>
            <span>Mewujudkan warga negara yang <strong class="highlight">berkepribadian</strong> Indonesia, yang beriman dan bertaqwa kepada Allah SWT</span>
          </li>
        </ul>
      </article>
    </div>
  </div>
</section>


<section class="pendaftaran" id="pendaftaran" aria-label="Pendaftaran santri baru">
  <div class="pendaftaran__visual">
    <div>
      <span class="pendaftaran__badge">Penerimaan Santri Baru</span>
      <h2 class="pendaftaran__visual-title">
        PonPes Al-Barokah<br>An-Nur Khumairoh
        <span>Tahun <?php echo $tahunBuka; ?></span>
      </h2>
    </div>
    <div class="pendaftaran__info-boxes">
      <div class="pend-box">
        <div class="pend-box__title"><i class="fas fa-school" aria-hidden="true"></i> Jenjang Pendidikan</div>
        <ul>
          <li>RA (Raudhatul Athfal)</li>
          <li>MI (Madrasah Ibtidaiyah)</li>
          <li>MTS (Madrasah Tsanawiyah)</li>
          <li>MA (Madrasah Aliyah)</li>
          <li>MADIN (Madrasah Diniyah)</li>
        </ul>
      </div>
      <div class="pend-box">
        <div class="pend-box__title"><i class="fas fa-clipboard-list" aria-hidden="true"></i> Syarat Pendaftaran</div>
        <ul>
          <li>Mengisi formulir pendaftaran</li>
          <li>Foto copy ijazah / surat kelulusan &amp; raport halaman pertama</li>
          <li>Menyerahkan nomor NISN SD/MI, SMP/MTS</li>
          <li>Pas foto hitam putih &amp; berwarna 3×4 dan 2×5 (masing-masing 2 lembar)</li>
          <li>Pelayanan setiap hari kerja (08.00 – 16.00 WIB)</li>
        </ul>
      </div>
      <div class="pend-box">
        <div class="pend-box__title"><i class="fas fa-address-book" aria-hidden="true"></i> Contact Person</div>
        <div class="pend-box__contact">
          <span><i class="fas fa-phone" aria-hidden="true"></i> Ust Bayyati Amir : 0853 3037 3310</span>
          <span><i class="fas fa-phone" aria-hidden="true"></i> H.j : 0812 3057 4234</span>
          <span><i class="fas fa-phone" aria-hidden="true"></i> Ahmad Lutfi : 0313 3449 0334</span>
        </div>
      </div>
    </div>
  </div>

  <div class="pendaftaran__cta">
    <p class="pendaftaran__cta-eyebrow">Pondok Pesantren Modern</p>
    <h2 class="pendaftaran__cta-title">Pendaftaran <span>santri baru</span></h2>
    <p class="pendaftaran__cta-sub">Pondok Pesantren Modern Al-Barokah</p>
    <div class="pendaftaran__highlight-box">
      <h3>Penerimaan Santri Baru<br>Al-Barokah TA <?php echo $tahunAjaranBuka; ?></h3>
      <p class="tahun">Tahun Ajaran Baru segera dibuka</p>
      <div class="ayo-mondok">
        <span>Ayo daftarkan diri anda</span>
        <span class="dot-red" aria-hidden="true"></span>
      </div>
      <div class="pendaftaran__btns">
        <a href="pendaftaran.php" class="pend-btn pend-btn--panduan">
          <i class="fas fa-book-open" aria-hidden="true"></i> Panduan Pendaftaran
        </a>
        <a href="pendaftaran.php" class="pend-btn pend-btn--form">
          <i class="fas fa-check-circle" aria-hidden="true"></i> Form Pendaftaran!
        </a>
      </div>
      <div class="pendaftaran__contact">
        <strong>Contact :</strong>
        <span class="phone">(0852) 3057 4234</span>
      </div>
    </div>
  </div>
</section>

<?php include 'includes/footer.php'; ?>


<script src="js/shared/navbar.js"></script>
<script src="js/website/home.js"></script>

</body>
</html>
