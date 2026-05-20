<?php
require_once 'koneksi.php';

// Ambil data galeri dari database
$query = "SELECT id, judul, kategori, tanggal, src FROM media_galeri ORDER BY id DESC";
$result = $conn->query($query);

$fotoList = [];
if ($result && $result->num_rows > 0) {
    while($row = $result->fetch_assoc()) {
        $fotoList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Galeri santri Ponpes Al-Barokah An-Nur Khumairoh — dokumentasi kegiatan, prestasi, dan momen berharga pesantren." />
  <title>Galeri Santri — Ponpes Al-Barokah An-Nur Khumairoh</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Stylesheets (urut: umum → spesifik) -->
  <link rel="stylesheet" href="css/base.css"    />  
  <link rel="stylesheet" href="css/navbar.css"  />  
  <link rel="stylesheet" href="css/header.css"  />  
  <link rel="stylesheet" href="css/footer.css"  />  
  <link rel="stylesheet" href="css/galeri.css"  />  
</head>
<body>

<!-- SITE HEADER — di-inject oleh components.js -->
<?php include 'includes/header.php'; ?>


<!-- PAGE HERO -->
<div class="page-hero">
  <div class="page-hero__inner">

    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="Home.html"><i class="fas fa-home"></i> Home</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <span class="current">Galeri</span>
    </nav>

    <h1 class="page-hero__title">Galeri Santri</h1>
    <p class="page-hero__sub">Al Barokah</p>

    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots">
        <span></span><span></span><span></span>
      </div>
    </div>

  </div>
</div><!-- /page-hero -->


<!-- GALERI WRAPPER -->
<main class="galeri-wrapper">

  <!-- Toolbar: filter + jumlah foto -->
  <div class="galeri-toolbar">

    <!-- Filter Kategori -->
    <div class="galeri-filters" role="group" aria-label="Filter kategori galeri">
      <button class="filter-btn active" data-kat="Semua">
        <i class="fas fa-th"></i> Semua
      </button>
      <button class="filter-btn" data-kat="Kegiatan">
        <i class="fas fa-calendar-check"></i> Kegiatan
      </button>
      <button class="filter-btn" data-kat="Prestasi">
        <i class="fas fa-trophy"></i> Prestasi
      </button>
      <button class="filter-btn" data-kat="Ibadah">
        <i class="fas fa-mosque"></i> Ibadah
      </button>
      <button class="filter-btn" data-kat="Fasilitas">
        <i class="fas fa-building"></i> Fasilitas
      </button>
      <button class="filter-btn" data-kat="Lainnya">
        <i class="fas fa-images"></i> Lainnya
      </button>
    </div>

    <!-- Jumlah foto -->
    <span id="galeriCount" class="galeri-count"></span>

  </div><!-- /galeri-toolbar -->

  <!-- Grid Foto -->
  <div
    id="galeriGrid"
    class="galeri-grid"
    aria-live="polite"
    aria-label="Kumpulan foto galeri santri"
  >
    <!-- Di-render oleh galeri.js -->
  </div>

</main><!-- /galeri-wrapper -->


<!-- FOOTER — di-inject oleh components.js -->
<?php include 'includes/footer.php'; ?>

<!-- Data Dinamis PHP -->
<script>
    var FOTO = <?php echo json_encode($fotoList); ?>;
</script>

<!-- Scripts -->

<script src="js/navbar.js"></script>       
<script src="js/galeri.js"></script>       

</body>
</html>
