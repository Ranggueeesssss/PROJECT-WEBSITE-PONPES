<?php
require_once 'koneksi.php';

// Ambil data berita dari database
$query = "SELECT * FROM berita ORDER BY id DESC";
$result = $conn->query($query);

$beritaList = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        // Convert featured to boolean for JS
        $row['featured'] = (bool)$row['featured'];
        $beritaList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Berita terbaru Ponpes Al-Barokah An-Nur Khumairoh." />
  <title>Berita — Ponpes Al-Barokah An-Nur Khumairoh</title>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/shared/base.css"    />
  <link rel="stylesheet" href="css/shared/navbar.css"  />
  <link rel="stylesheet" href="css/shared/header.css"  />
  <link rel="stylesheet" href="css/shared/footer.css"  />
  <link rel="stylesheet" href="css/website/berita.css"  />
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="page-hero">
  <div class="page-hero__inner">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="Home.html"><i class="fas fa-home"></i> Home</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <span class="current">Berita</span>
    </nav>
    <h1 class="page-hero__title">Berita</h1>
    <p class="page-hero__sub">Al Barokah</p>
    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots"><span></span><span></span><span></span></div>
    </div>
  </div>
</div>

<main class="berita-wrapper">

  <article id="featuredArticle" class="featured-article" aria-label="Artikel unggulan">
    <!-- Di-render oleh berita.js -->
  </article>

  <div class="berita-layout">

    <div class="berita-main">
      <div class="berita-toolbar">
        <div class="berita-search" role="search">
          <input type="text" id="searchInput" placeholder="Cari berita..." aria-label="Cari berita" />
          <button type="button" id="searchBtn" aria-label="Cari"><i class="fas fa-search"></i></button>
        </div>
        <div class="berita-filters" role="group" aria-label="Filter kategori">
          <button class="filter-btn active" data-kat="Semua">Semua</button>
          <button class="filter-btn" data-kat="Kegiatan">Kegiatan</button>
          <button class="filter-btn" data-kat="Prestasi">Prestasi</button>
          <button class="filter-btn" data-kat="Pengumuman">Pengumuman</button>
          <button class="filter-btn" data-kat="Pendidikan">Pendidikan</button>
        </div>
      </div>

      <div class="section-header">
        <h2>Semua Berita</h2>
        <span id="articleCount" style="font-size:.78rem; color:var(--gray-mid);"></span>
      </div>

      <div id="beritaGrid" class="berita-grid" aria-live="polite" aria-label="Daftar artikel berita">
        <!-- Di-render oleh berita.js -->
      </div>

      <nav id="pagination" class="pagination" aria-label="Navigasi halaman"></nav>
    </div>

    <aside class="sidebar" aria-label="Sidebar berita">

      <div class="sidebar-widget">
        <div class="sidebar-widget__head">
          <i class="fas fa-clock"></i><h3>Berita Terkini</h3>
        </div>
        <div class="sidebar-widget__body">
          <div id="recentList" class="recent-list"></div>
        </div>
      </div>

      <div class="sidebar-widget">
        <div class="sidebar-widget__head">
          <i class="fas fa-tags"></i><h3>Kategori</h3>
        </div>
        <div class="sidebar-widget__body">
          <div class="kategori-list">
            <button class="kategori-chip active" data-kat="Semua">Semua</button>
            <button class="kategori-chip" data-kat="Kegiatan">Kegiatan</button>
            <button class="kategori-chip" data-kat="Prestasi">Prestasi</button>
            <button class="kategori-chip" data-kat="Pengumuman">Pengumuman</button>
            <button class="kategori-chip" data-kat="Pendidikan">Pendidikan</button>
          </div>
        </div>
      </div>
    </aside>
  </div>

</main>

<!-- Modal Baca Berita -->
<div class="modal-overlay" id="beritaModal" style="display:none; position:fixed; top:0; left:0; width:100%; height:100%; background:rgba(0,0,0,0.6); z-index:9999; align-items:center; justify-content:center; padding:20px;">
    <div class="modal-box" style="background:#fff; border-radius:15px; width:100%; max-width:800px; max-height:90vh; overflow-y:auto; box-shadow:0 10px 30px rgba(0,0,0,0.2); position:relative; animation: fadeUp 0.3s ease;">
        <button id="closeModalBtn" style="position:absolute; top:20px; right:20px; background:#f3f4f6; border:none; width:35px; height:35px; border-radius:50%; cursor:pointer; display:flex; align-items:center; justify-content:center; color:#4b5563; transition:0.2s;"><i class="fas fa-times"></i></button>
        <div id="modalContent" style="padding:40px;">
            <!-- Content Injected via JS -->
        </div>
    </div>
</div>
<style>
@keyframes fadeUp { from { opacity:0; transform:translateY(20px); } to { opacity:1; transform:translateY(0); } }
#closeModalBtn:hover { background:#e5e7eb; color:#1f2937; }
@media (max-width: 768px) { #modalContent { padding: 20px !important; } }
</style>

<?php include 'includes/footer.php'; ?>

<!-- Data Dinamis PHP -->
<script>
    var ARTICLES = <?php echo json_encode($beritaList); ?>;
</script>


<script src="js/shared/navbar.js"></script>
<script src="js/website/berita.js"></script>

</body>
</html>
