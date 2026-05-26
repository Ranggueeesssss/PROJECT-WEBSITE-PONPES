<?php
require_once 'koneksi.php';

// Ambil data prestasi
$prestasiList = [];
$timelineList = [];

$res = $conn->query("SELECT * FROM profil_prestasi ORDER BY tahun DESC, id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $prestasiList[] = $row;
        if($row['tampilkan_di_timeline'] == 1 && !empty($row['deskripsi'])) {
            $timelineList[] = $row;
        }
    }
}

// Menghitung statistik dinamis (opsional, jika ingin stat hero dinamis)
$totalPrestasi = count($prestasiList);
$juara1 = 0;
foreach($prestasiList as $p) {
    if(strtolower($p['rank_label']) == 'juara 1' || strtolower($p['rank_label']) == 'juara umum' || strpos(strtolower($p['rank_label']), 'juara 1') !== false) {
        $juara1++;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Prestasi santri Ponpes Al-Barokah An-Nur Khumairoh." />
  <title>Prestasi — Ponpes Al-Barokah An-Nur Khumairoh</title>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/shared/base.css"    />  
  <link rel="stylesheet" href="css/shared/navbar.css"  />  
  <link rel="stylesheet" href="css/shared/header.css"  />  
  <link rel="stylesheet" href="css/shared/footer.css"  />  
  <link rel="stylesheet" href="css/website/prestasi.css" />
</head>
<body>

<?php include 'includes/header.php'; ?>

<div class="page-hero">
  <div class="page-hero__inner">
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="Home.html"><i class="fas fa-home"></i> Home</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <span class="current">Prestasi</span>
    </nav>
    <h1 class="page-hero__title">Prestasi</h1>
    <p class="page-hero__sub">Al Barokah</p>
    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots"><span></span><span></span><span></span></div>
    </div>
    <div class="hero-stats" aria-label="Ringkasan prestasi">
      <div class="hero-stat">
        <span class="hero-stat__number" data-target="<?php echo $totalPrestasi; ?>" data-suffix="+">0+</span>
        <span class="hero-stat__label">Total Prestasi</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat__number" data-target="<?php echo $juara1; ?>" data-suffix="">0</span>
        <span class="hero-stat__label">Juara 1</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat__number" data-target="4" data-suffix="">0</span>
        <span class="hero-stat__label">Kategori</span>
      </div>
      <div class="hero-stat">
        <span class="hero-stat__number" data-target="<?php echo date('Y'); ?>" data-suffix="">0</span>
        <span class="hero-stat__label">Terkini</span>
      </div>
    </div>
  </div>
</div>

<main class="prestasi-wrapper">

  <div class="highlight-bar" aria-label="Daftar prestasi yang tercapai">
    <div class="highlight-bar__left">
      <p class="highlight-bar__eyebrow">📋 Rekapitulasi</p>
      <h2 class="highlight-bar__title">Prestasi yang <span>Tercapai</span></h2>
      <p class="highlight-bar__sub">
        Deretan pencapaian membanggakan para santri Ponpes Al-Barokah
        dalam berbagai bidang — akademik, seni, olahraga, dan keagamaan.
      </p>
    </div>
    <div class="highlight-bar__right">
      <div id="highlightList" class="prestasi-list" aria-live="polite">
        <!-- Di-render oleh prestasi.js -->
      </div>
    </div>
  </div>

  <div class="prestasi-layout">

    <div class="prestasi-main">
      <div class="timeline-section">
        <div class="section-header"><h2>Perjalanan Prestasi</h2></div>
        <div class="timeline" aria-label="Timeline prestasi santri">

          <?php if (count($timelineList) > 0): ?>
            <?php foreach($timelineList as $t): ?>
            <div class="timeline-item">
              <p class="timeline-item__year"><?php echo $t['tahun']; ?></p>
              <div class="timeline-item__card">
                <h4>
                  <?php 
                    $medal = '🌟';
                    if($t['rank_tipe'] == 'gold') $medal = '🥇';
                    if($t['rank_tipe'] == 'silver') $medal = '🥈';
                    if($t['rank_tipe'] == 'bronze') $medal = '🥉';
                    if($t['rank_tipe'] == 'green') $medal = '🏆';
                    echo $medal . ' ' . htmlspecialchars($t['judul']); 
                  ?>
                </h4>
                <p><?php echo htmlspecialchars($t['deskripsi']); ?></p>
              </div>
            </div>
            <?php endforeach; ?>
          <?php else: ?>
            <p style="color:var(--gray-mid); font-style:italic;">Belum ada catatan perjalanan prestasi.</p>
          <?php endif; ?>

        </div>
      </div>
    </div>

    <aside class="sidebar" aria-label="Sidebar prestasi">

      <div class="sidebar-widget">
        <div class="sidebar-widget__head">
          <i class="fas fa-chart-bar"></i><h3>Statistik Prestasi</h3>
        </div>
        <div class="sidebar-widget__body">
          <div class="stat-grid">
            <?php
              $j1=0; $j2=0; $j3=0; $fin=0;
              foreach($prestasiList as $p) {
                  $lbl = strtolower($p['rank_label']);
                  if($p['rank_tipe'] == 'gold' || strpos($lbl, 'juara 1') !== false) $j1++;
                  elseif($p['rank_tipe'] == 'silver' || strpos($lbl, 'juara 2') !== false) $j2++;
                  elseif($p['rank_tipe'] == 'bronze' || strpos($lbl, 'juara 3') !== false) $j3++;
                  else $fin++;
              }
            ?>
            <div class="stat-item"><span class="stat-item__number"><?php echo $j1; ?></span><span class="stat-item__label">Juara 1</span></div>
            <div class="stat-item"><span class="stat-item__number"><?php echo $j2; ?></span><span class="stat-item__label">Juara 2</span></div>
            <div class="stat-item"><span class="stat-item__number"><?php echo $j3; ?></span><span class="stat-item__label">Juara 3</span></div>
            <div class="stat-item"><span class="stat-item__number"><?php echo $fin; ?></span><span class="stat-item__label">Lainnya</span></div>
          </div>
        </div>
      </div>
    </aside>
  </div>

</main>

<?php include 'includes/footer.php'; ?>

<!-- Data Dinamis PHP -->
<script>
    var PRESTASI = <?php echo json_encode($prestasiList); ?>;
</script>


<script src="js/shared/navbar.js"></script>
<script src="js/website/prestasi.js"></script>

</body>
</html>
