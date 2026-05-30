<?php 
// includes/dash_sidebar.php 
$currentPage = basename($_SERVER['PHP_SELF']);
?>
<aside class="dash-sidebar" id="dashSidebar">

    <!-- Brand Header -->
    <div class="dash-sidebar-header">
        <div class="sidebar-logo-icon">
            <i class="fas fa-mosque"></i>
        </div>
        <div class="sidebar-brand-text">
            <span class="brand-name">Al-Barokah</span>
            <span class="brand-sub">An-Nur Khumairoh</span>
        </div>
    </div>

    <!-- Scrollable Menu -->
    <div class="dash-sidebar-menu">

        <div class="menu-section">
            <div class="menu-label">Menu Utama</div>
            <ul>
                <li>
                    <a href="dashboard.php" class="<?php echo $currentPage == 'dashboard.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-th-large"></i></span>
                        Dashboard
                    </a>
                </li>
                <li>
                    <a href="data_pengajar.php" class="<?php echo $currentPage == 'data_pengajar.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-chalkboard-teacher"></i></span>
                        Data Pengajar
                    </a>
                </li>
                <li>
                    <a href="data_prestasi.php" class="<?php echo $currentPage == 'data_prestasi.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-bullhorn"></i></span>
                        Informasi & Prestasi
                    </a>
                </li>
                <li>
                    <a href="data_berita.php" class="<?php echo $currentPage == 'data_berita.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-newspaper"></i></span>
                        Kelola Berita
                    </a>
                </li>
                <li>
                    <a href="data_galeri.php" class="<?php echo $currentPage == 'data_galeri.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-images"></i></span>
                        Galeri Foto
                    </a>
                </li>
                <li>
                    <a href="data_pesan.php" class="<?php echo $currentPage == 'data_pesan.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-envelope"></i></span>
                        Pesan Masuk
                        <?php
                            $q_msg = $conn->query("SELECT count(*) as unread FROM pesan_masuk WHERE status='unread'");
                            if ($q_msg) {
                                $r_msg = $q_msg->fetch_assoc();
                                if($r_msg['unread'] > 0) echo '<span class="menu-badge">'.$r_msg['unread'].'</span>';
                            }
                        ?>
                    </a>
                </li>
            </ul>
        </div>

        <div class="menu-section">
            <div class="menu-label">Pengaturan Sistem</div>
            <ul>
                <li>
                    <a href="kelola_login.php" class="<?php echo $currentPage == 'kelola_login.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-user-shield"></i></span>
                        Kelola Login
                    </a>
                </li>
            </ul>
        </div>

        <div class="menu-section">
            <div class="menu-label">Manajemen Santri</div>
            <ul>
                <li>
                    <a href="data_pendaftaran.php" class="<?php echo $currentPage == 'data_pendaftaran.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-user-plus"></i></span>
                        Pendaftaran Santri
                    </a>
                </li>
                <li>
                    <a href="data_santri.php" class="<?php echo $currentPage == 'data_santri.php' ? 'active' : ''; ?>">
                        <span class="menu-icon"><i class="fas fa-users"></i></span>
                        Data Santri
                    </a>
                </li>
            </ul>
        </div>

    </div>

    <!-- User Info Card -->
    <div class="sidebar-user-card">
        <div class="sidebar-avatar">
            <i class="fas fa-user"></i>
        </div>
        <div class="sidebar-user-info">
            <span class="s-name"><?php echo htmlspecialchars($_SESSION['user_nama'] ?? 'Guru / Admin'); ?></span>
            <span class="s-role"><?php echo htmlspecialchars($_SESSION['user_role'] ?? 'Guru'); ?></span>
        </div>
    </div>

    <!-- Footer Logout -->
    <div class="sidebar-footer">
        <a href="logout.php" class="btn-logout">
            <i class="fas fa-sign-out-alt"></i> Keluar dari Sistem
        </a>
    </div>
</aside>
