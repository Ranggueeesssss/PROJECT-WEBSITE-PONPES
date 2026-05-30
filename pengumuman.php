<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Pengumuman Hasil Kelulusan Santri Baru Ponpes Al-Barokah An-Nur Khumairoh." />
  <title>Pengumuman Santri Baru — Ponpes Al-Barokah</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/shared/base.css"        />  
  <link rel="stylesheet" href="css/shared/navbar.css"      />  
  <link rel="stylesheet" href="css/shared/header.css"      />  
  <link rel="stylesheet" href="css/shared/footer.css"      />  
  <link rel="stylesheet" href="css/website/pendaftaran.css" /> 
  <link rel="stylesheet" href="css/website/pengumuman.css" /> 
</head>
<body>

<!-- SITE HEADER -->
<?php include 'includes/header.php'; ?>

<!-- PAGE HERO -->
<div class="page-hero">
  <div class="page-hero__inner">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="Home.html"><i class="fas fa-home"></i> Home</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <a href="pendaftaran.php">Pendaftaran</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <span class="current">Pengumuman</span>
    </nav>

    <h1 class="page-hero__title">Pengumuman Santri Baru</h1>
    <p class="page-hero__sub">Tahun Ajaran 2024 / 2025</p>

    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<main class="pend-main">
  <div class="pend-container">
    
    <div class="pengumuman-section fade-up-element visible" id="pengumuman-santri" style="margin-top: 0; padding-top: 0; border-top: none;">
        <div class="pengumuman-card">
            <div class="pengumuman-header">
                <div class="pengumuman-icon-wrapper">
                    <div class="pengumuman-icon">
                        <i class="fas fa-bullhorn"></i>
                    </div>
                    <div class="pulse-ring"></div>
                </div>
                <h3 class="pengumuman-title">Cek Hasil Seleksi Santri Baru</h3>
                <p class="pengumuman-desc">Silakan masukkan NIK dan tanggal lahir untuk melihat hasil seleksi pendaftaran.</p>
            </div>
            
            <form id="form-pengumuman" class="pengumuman-form">
                <div class="form-row">
                    <div class="form-group input-effect">
                        <label for="cek_nik">NIK (Nomor Induk Kependudukan)</label>
                        <div class="input-wrapper">
                            <i class="fas fa-id-card input-icon"></i>
                            <input type="text" id="cek_nik" name="cek_nik" placeholder="Masukkan 16 digit NIK..." required autocomplete="off" maxlength="16" inputmode="numeric" pattern="\d{16}">
                            <span class="focus-border"></span>
                        </div>
                    </div>
                    <div class="form-group input-effect">
                        <label for="cek_tgl_lahir">Tanggal Lahir</label>
                        <div class="input-wrapper">
                            <i class="fas fa-calendar-alt input-icon"></i>
                            <input type="date" id="cek_tgl_lahir" name="cek_tgl_lahir" required>
                            <span class="focus-border"></span>
                        </div>
                    </div>
                </div>
                <div class="form-action">
                    <button type="submit" class="btn-cek-pengumuman" id="btn-submit-cek">
                        <span class="btn-text">Lihat Hasil Seleksi</span>
                        <div class="btn-icon-circle"><i class="fas fa-arrow-right"></i></div>
                    </button>
                </div>
            </form>

            <div id="pengumuman-result" class="pengumuman-result hidden">
                <!-- Hasil pengumuman akan dimuat di sini oleh JS -->
            </div>
        </div>
    </div>

  </div>
</main>

<!-- FOOTER -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->

<script src="js/shared/navbar.js"></script>
<script src="js/website/pengumuman.js"></script>

</body>
</html>
