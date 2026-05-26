<?php
require_once 'koneksi.php';

// Ambil data pengajar dari database
$query = "SELECT * FROM profil_pengajar ORDER BY kategori ASC, id ASC";
$result = $conn->query($query);

$pimpinan = [];
$guru = [];

if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        if ($row['kategori'] == 'pimpinan') {
            $pimpinan[] = $row;
        } else {
            $guru[] = $row;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Profil Pimpinan dan Dewan Asatidz Ponpes Al-Barokah An-Nur Khumairoh." />
  <title>Pengajar — Ponpes Al-Barokah An-Nur Khumairoh</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/shared/base.css"    />  
  <link rel="stylesheet" href="css/shared/navbar.css"  />  
  <link rel="stylesheet" href="css/shared/header.css"  />  
  <link rel="stylesheet" href="css/shared/footer.css"  />  
  <link rel="stylesheet" href="css/website/pengajar.css" /> 
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
      <span class="current">Pengajar</span>
    </nav>

    <h1 class="page-hero__title">Pimpinan & Pengajar</h1>
    <p class="page-hero__sub">Al Barokah</p>

    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</div>

<!-- MAIN CONTENT -->
<main class="pengajar-main">
  <div class="pengajar-container">
    
    <div class="section-intro fade-up-element">
        <h2 class="section__title text-center">Dewan Asatidz & Pengurus</h2>
        <p class="section__body text-center mx-auto" style="max-width: 650px;">
            Sistem pendidikan Pondok Pesantren Al-Barokah dibimbing langsung oleh figur-figur pengajar yang berdedikasi tinggi, berpengalaman, dan memiliki visi kuat dalam mendidik generasi Khoirol Umah.
        </p>
    </div>

    <!-- TAB FILTER (FITUR INTERAKTIF) -->
    <div class="pengajar-tabs fade-up-element" style="animation-delay: 0.1s;">
        <button class="tab-btn active" data-filter="all">
            <i class="fas fa-users"></i> Semua
        </button>
        <button class="tab-btn" data-filter="pimpinan">
            <i class="fas fa-user-tie"></i> Pimpinan Utama
        </button>
        <button class="tab-btn" data-filter="guru">
            <i class="fas fa-chalkboard-teacher"></i> Dewan Guru
        </button>
    </div>

    <!-- GRID PENGAJAR -->
    <div class="pengajar-grid mt-5" id="pengajarGrid">
        
        <!-- KATEGORI: PIMPINAN -->
        <?php foreach ($pimpinan as $p): ?>
        <div class="pengajar-card pimpinan fade-up-element" data-category="pimpinan">
            <div class="card-inner">
                <div class="card-badge"><?php echo htmlspecialchars($p['jabatan']); ?></div>
                <div class="card-img-wrap">
                    <?php if ($p['foto'] && file_exists('uploads/' . $p['foto'])): ?>
                        <img src="uploads/<?php echo $p['foto']; ?>" alt="<?php echo htmlspecialchars($p['nama']); ?>" class="profile-img">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($p['nama']); ?>&background=2d5a3d&color=fff&size=300" alt="<?php echo htmlspecialchars($p['nama']); ?>" class="profile-img">
                    <?php endif; ?>
                </div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($p['nama']); ?></h3>
                    <p class="role pimpinan-role"><?php echo htmlspecialchars($p['jabatan']); ?></p>
                    <p class="desc"><?php echo htmlspecialchars($p['deskripsi']); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

        <!-- KATEGORI: GURU -->
        <?php foreach ($guru as $g): ?>
        <div class="pengajar-card guru fade-up-element" data-category="guru">
            <div class="card-inner">
                <div class="card-img-wrap">
                    <?php if ($g['foto'] && file_exists('uploads/' . $g['foto'])): ?>
                        <img src="uploads/<?php echo $g['foto']; ?>" alt="<?php echo htmlspecialchars($g['nama']); ?>" class="profile-img">
                    <?php else: ?>
                        <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($g['nama']); ?>&background=f2f7f4&color=3d5c4a&size=300" alt="<?php echo htmlspecialchars($g['nama']); ?>" class="profile-img">
                    <?php endif; ?>
                </div>
                <div class="card-info">
                    <h3><?php echo htmlspecialchars($g['nama']); ?></h3>
                    <p class="role"><?php echo htmlspecialchars($g['jabatan']); ?></p>
                    <div class="card-divider"></div>
                    <p class="subject"><?php echo htmlspecialchars($g['deskripsi']); ?></p>
                </div>
            </div>
        </div>
        <?php endforeach; ?>

    </div>

    <!-- Empty State for Filters -->
    <div id="noResultsMsg" class="no-results-msg" style="display: none;">
        <i class="fas fa-search"></i>
        <p>Tidak ada pengajar dalam kategori ini.</p>
    </div>

  </div>
</main>

<!-- FOOTER -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->

<script src="js/shared/navbar.js"></script>
<script src="js/website/pengajar.js"></script>

</body>
</html>
