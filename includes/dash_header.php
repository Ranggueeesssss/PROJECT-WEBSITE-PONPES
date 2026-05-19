<?php 
// includes/dash_header.php 
$currentPageName = basename($_SERVER['PHP_SELF']);
$pageNames = [
    'dashboard.php' => 'Dashboard Guru',
    'data_pengajar.php' => 'Data Pengajar',
    'data_prestasi.php' => 'Informasi & Prestasi',
    'data_berita.php' => 'Kelola Berita',
    'data_galeri.php' => 'Galeri Foto',
    'data_pesan.php' => 'Pesan Masuk',
    'data_pendaftaran.php' => 'Pendaftaran Santri',
    'data_santri.php' => 'Data Santri',
    'kelola_login.php' => 'Kelola Login'
];
$currentBreadcrumb = isset($pageNames[$currentPageName]) ? $pageNames[$currentPageName] : 'Dashboard Guru';
?>
<header class="dash-header">
    <div class="dash-header-left">
        <button class="sidebar-toggle" id="sidebarToggle" aria-label="Toggle Sidebar">
            <i class="fas fa-bars"></i>
        </button>
        <nav class="breadcrumb" aria-label="Breadcrumb">
            <span>Ponpes Al-Barokah</span>
            <span class="sep"><i class="fas fa-chevron-right" style="font-size:0.6rem;"></i></span>
            <span><?php echo htmlspecialchars($currentBreadcrumb); ?></span>
        </nav>
    </div>

    <div class="dash-header-right">
        <a href="data_pesan.php" class="notif-btn" title="Pesan Masuk" aria-label="Pesan Masuk" style="text-decoration:none;">
            <i class="fas fa-bell"></i>
            <?php if(isset($r_msg) && $r_msg['unread'] > 0): ?>
                <span class="notif-dot"></span>
            <?php endif; ?>
        </a>
    </div>
</header>
