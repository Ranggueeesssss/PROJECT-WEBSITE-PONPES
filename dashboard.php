<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';

// --- STATISTIK DINAMIS (REAL TIME) ---
$countPendaftar = $conn->query("SELECT COUNT(*) FROM pendaftaran")->fetch_row()[0];
$countPesan     = $conn->query("SELECT COUNT(*) FROM pesan_masuk")->fetch_row()[0];
$countBerita    = $conn->query("SELECT COUNT(*) FROM berita")->fetch_row()[0];
$countSantriDb  = $conn->query("SELECT COUNT(*) FROM data_santri")->fetch_row()[0];

// Cek mode Santri Manual (Bisa Auto atau Manual)
$santriManualFile = __DIR__ . '/santri_manual.json';
$useManualSantri = false;
$manualSantriCount = 0;
if (file_exists($santriManualFile)) {
    $fileData = json_decode(file_get_contents($santriManualFile), true);
    if ($fileData && isset($fileData['mode']) && $fileData['mode'] === 'manual') {
        $useManualSantri = true;
        $manualSantriCount = (int)$fileData['count'];
    }
}

// Proses jika form ganti mode santri disubmit
if (isset($_POST['set_santri_mode'])) {
    $mode = $_POST['set_santri_mode'];
    $count = (int)($_POST['manual_count'] ?? 0);
    file_put_contents($santriManualFile, json_encode(['mode' => $mode, 'count' => $count]));
    header("Location: dashboard.php");
    exit;
}

$finalSantriCount = $useManualSantri ? $manualSantriCount : $countSantriDb;

// 1. Data Users
$usersQuery  = "SELECT id, username, role FROM user ORDER BY id DESC LIMIT 5";
$usersResult = $conn->query($usersQuery);
$users = [];
if ($usersResult && $usersResult->num_rows > 0) {
    while ($row = $usersResult->fetch_assoc()) $users[] = $row;
} else {
    $users = [
        ['id' => 1, 'username' => 'guru1',  'role' => 'guru'],
        ['id' => 2, 'username' => 'admin',  'role' => 'admin'],
    ];
}

// 2. Data Berita
$berita = [];
$beritaResult = $conn->query("SELECT id, judul, tanggal, kategori FROM berita ORDER BY id DESC LIMIT 5");
if ($beritaResult) {
    while($row = $beritaResult->fetch_assoc()) {
        $berita[] = $row;
    }
}

// 3. Data Pendaftar Terbaru
$pendaftar = [];
$pendResult = $conn->query("SELECT id, nama_lengkap as nama, jenjang_pendaftaran as jenjang, status FROM pendaftaran ORDER BY id DESC LIMIT 5");
if ($pendResult) {
    while($row = $pendResult->fetch_assoc()) {
        $pendaftar[] = $row;
    }
}

// 4. Pesan Masuk
$pesanKontak = [];
$pesanResult = $conn->query("SELECT * FROM pesan_masuk ORDER BY id DESC LIMIT 3");
if ($pesanResult) {
    while($row = $pesanResult->fetch_assoc()) {
        $row['tanggal'] = date('d M Y', strtotime($row['created_at']));
        $pesanKontak[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Dashboard — Ponpes Al-Barokah An-Nur Khumairoh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard_pro.css?v=3">
</head>
<body>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Message Modal -->
<div class="modal-overlay" id="msgModal" role="dialog" aria-modal="true" aria-labelledby="modalTitle">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-envelope-open-text" style="margin-right:8px;"></i>Detail Pesan Masuk</h3>
            <button class="modal-close" id="modalClose" aria-label="Tutup"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <div class="field-label"><i class="fas fa-user" style="margin-right:4px;"></i>Pengirim</div>
                <div class="field-value" id="mNama"></div>
            </div>
            <div class="modal-field">
                <div class="field-label"><i class="fas fa-at" style="margin-right:4px;"></i>Email</div>
                <div class="field-value" id="mEmail"></div>
            </div>
            <div class="modal-field">
                <div class="field-label"><i class="fas fa-tag" style="margin-right:4px;"></i>Subjek</div>
                <div class="field-value" id="mSubjek"></div>
            </div>
            <div class="modal-field">
                <div class="field-label"><i class="fas fa-calendar" style="margin-right:4px;"></i>Tanggal</div>
                <div class="field-value" id="mTanggal"></div>
            </div>
            <div class="modal-field">
                <div class="field-label"><i class="fas fa-comment-alt" style="margin-right:4px;"></i>Isi Pesan</div>
                <div class="field-value msg-text" id="mPesan"></div>
            </div>
        </div>
        <div class="modal-footer">
            <button class="btn-action primary" id="modalCloseBtn" onclick="document.getElementById('msgModal').classList.remove('active'); document.body.style.overflow='';">
                <i class="fas fa-check"></i> Tutup
            </button>
        </div>
    </div>
</div>

<!-- Modal Setting Santri -->
<div class="modal-overlay" id="santriConfigModal">
    <div class="modal-box" style="max-width: 400px;">
        <form method="POST" action="dashboard.php">
            <div class="modal-header">
                <h3><i class="fas fa-cog" style="margin-right:8px;"></i> Pengaturan Total Santri</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('santriConfigModal').classList.remove('active')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="display:flex; flex-direction:column; gap:15px;">
                <p style="font-size:0.9rem; color:#64748b; margin-top:0;">Pilih tampilan angka Total Santri:</p>
                
                <label style="display:flex; gap:10px; align-items:center; padding:10px; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; background:#f8fafc;">
                    <input type="radio" name="set_santri_mode" value="auto" <?php echo !$useManualSantri ? 'checked' : ''; ?> onchange="document.getElementById('manualInputWrap').style.display='none'">
                    <div>
                        <div style="font-weight:600; color:#0f172a;">Otomatis dari Database</div>
                        <div style="font-size:0.8rem; color:#64748b;">Data Riil Saat Ini: <strong style="color:var(--dash-primary);"><?php echo $countSantriDb; ?> Santri</strong></div>
                    </div>
                </label>

                <label style="display:flex; gap:10px; align-items:center; padding:10px; border:1px solid #e2e8f0; border-radius:8px; cursor:pointer; background:#f8fafc;">
                    <input type="radio" name="set_santri_mode" value="manual" <?php echo $useManualSantri ? 'checked' : ''; ?> onchange="document.getElementById('manualInputWrap').style.display='block'">
                    <div>
                        <div style="font-weight:600; color:#0f172a;">Atur Angka Manual</div>
                        <div style="font-size:0.8rem; color:#64748b;">Ketik angka bebas sesuai keinginan</div>
                    </div>
                </label>

                <div id="manualInputWrap" style="display: <?php echo $useManualSantri ? 'block' : 'none'; ?>; margin-top:5px;">
                    <label style="font-size:0.85rem; font-weight:600; display:block; margin-bottom:5px; color:#0f172a;">Masukkan Angka:</label>
                    <input type="number" name="manual_count" value="<?php echo $manualSantriCount; ?>" style="width:100%; padding:10px; border-radius:6px; border:1px solid #cbd5e1; outline:none; font-family:inherit; font-size:1rem;">
                </div>
            </div>
            <div class="modal-footer">
                <button type="submit" class="btn-action primary" style="width:100%; justify-content:center;">Simpan Pengaturan</button>
            </div>
        </form>
    </div>
</div>

<div class="dash-wrapper">

    <!-- Sidebar -->
    <?php include_once 'includes/dash_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="dash-main" id="dashMain">

        <!-- Header -->
        <?php include_once 'includes/dash_header.php'; ?>

        <!-- Content -->
        <div class="dash-content">

            <!-- Page Title -->
            <div class="page-title-bar fade-in-up d1">
                <div class="greeting">
                    <h1>
                        <span id="greetingText">Selamat Datang,</span>
                        <span class="user-highlight"><?php echo htmlspecialchars($_SESSION['user_nama'] ?? 'Guru'); ?></span>
                    </h1>
                    <p><i class="fas fa-info-circle" style="margin-right:5px;color:var(--gold-500);"></i>Ringkasan informasi Pondok Pesantren Al-Barokah hari ini.</p>
                </div>
                <div class="datetime-badge">
                    <i class="fas fa-clock"></i>
                    <span id="liveClock">--:--</span>
                    &bull;
                    <span id="liveDate">---</span>
                </div>
            </div>

            <!-- Stats Grid -->
            <div class="stats-grid fade-in-up d2">
                <div class="stat-card green">
                    <div class="stat-card-top">
                        <span class="stat-label">Total Pendaftar</span>
                        <div class="stat-icon"><i class="fas fa-user-graduate"></i></div>
                    </div>
                    <div class="stat-value" data-counter="<?php echo $countPendaftar; ?>">0</div>
                    <div class="stat-footer">
                        <span class="stat-trend neutral"><i class="fas fa-circle"></i> Riil</span>
                        data pendaftar masuk
                    </div>
                </div>

                <div class="stat-card blue">
                    <div class="stat-card-top">
                        <span class="stat-label">Pesan Masuk</span>
                        <div class="stat-icon"><i class="fas fa-envelope"></i></div>
                    </div>
                    <div class="stat-value" data-counter="<?php echo $countPesan; ?>">0</div>
                    <div class="stat-footer">
                        <span class="stat-trend neutral"><i class="fas fa-circle"></i> Riil</span>
                        total pesan pengguna
                    </div>
                </div>

                <div class="stat-card gold">
                    <div class="stat-card-top">
                        <span class="stat-label">Berita Aktif</span>
                        <div class="stat-icon"><i class="far fa-newspaper"></i></div>
                    </div>
                    <div class="stat-value" data-counter="<?php echo $countBerita; ?>">0</div>
                    <div class="stat-footer">
                        <span class="stat-trend neutral"><i class="fas fa-circle"></i> Riil</span>
                        total berita aktif
                    </div>
                </div>

                <div class="stat-card purple" onclick="document.getElementById('santriConfigModal').classList.add('active')" style="cursor:pointer; position:relative; overflow:hidden;" title="Klik untuk mengubah pengaturan angka santri">
                    <div style="position:absolute; top:0; right:0; background:rgba(168, 85, 247, 0.1); padding:4px 8px; border-bottom-left-radius:8px; font-size:0.65rem; color:#9333ea; font-weight:700;"><i class="fas fa-pen"></i> KLIK EDIT</div>
                    <div class="stat-card-top">
                        <span class="stat-label">Total Santri</span>
                        <div class="stat-icon"><i class="fas fa-users"></i></div>
                    </div>
                    <div class="stat-value" data-counter="<?php echo $finalSantriCount; ?>">0</div>
                    <div class="stat-footer">
                        <?php if($useManualSantri): ?>
                            <span class="stat-trend up" style="color:#d97706; background:#fef3c7;"><i class="fas fa-hand-paper"></i> Manual</span>
                        <?php else: ?>
                            <span class="stat-trend up" style="color:#16a34a; background:#dcfce7;"><i class="fas fa-database"></i> Auto</span>
                        <?php endif; ?>
                        mode tampilan saat ini
                    </div>
                </div>
            </div>

            <!-- Quick Actions -->
            <div class="quick-actions fade-in-up d3">
                <a href="data_berita.php" class="btn-action primary" style="text-decoration: none;">
                    <i class="fas fa-plus"></i> Berita Baru
                </a>
                <a href="data_santri.php" class="btn-action gold" style="text-decoration: none;">
                    <i class="fas fa-user-plus"></i> Tambah Santri
                </a>
            </div>

            <!-- Data Sections -->
            <div class="data-sections fade-in-up d4">

                <!-- Left Column -->
                <div class="left-col">

                    <!-- Validasi Pendaftaran -->
                    <div class="data-card" style="margin-bottom:16px;">
                        <div class="data-card-header">
                            <h2>
                                <span class="card-icon"><i class="fas fa-user-check"></i></span>
                                Validasi Pendaftaran Terbaru
                            </h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>#</th>
                                        <th>Nama Calon Santri</th>
                                        <th>Jenjang</th>
                                        <th>Status</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($pendaftar as $idx => $p): ?>
                                    <tr>
                                        <td style="color:var(--dash-text-light);font-weight:600;"><?php echo $idx + 1; ?></td>
                                        <td>
                                            <div style="font-weight:700;"><?php echo htmlspecialchars($p['nama']); ?></div>
                                        </td>
                                        <td>
                                            <span style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-600);padding:3px 9px;border-radius:20px;font-size:0.72rem;font-weight:700;">
                                                <?php echo $p['jenjang']; ?>
                                            </span>
                                        </td>
                                        <td>
                                            <?php 
                                                $st = $p['status'];
                                                if ($st === 'Lolos'): ?>
                                                <span class="status-badge" style="background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;"><i class="fas fa-check-circle"></i> Lolos</span>
                                            <?php elseif ($st === 'Tidak Lolos'): ?>
                                                <span class="status-badge" style="background:#fee2e2;color:#dc2626;border:1px solid #fecaca;"><i class="fas fa-times-circle"></i> Ditolak</span>
                                            <?php else: ?>
                                                <span class="status-badge" style="background:#fef3c7;color:#d97706;border:1px solid #fcd34d;"><i class="fas fa-clock"></i> Pending</span>
                                            <?php endif; ?>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="data_pendaftaran.php" class="action-btn" title="Lihat Detail & Validasi" style="color:var(--dash-primary);background:var(--dash-primary-light);"><i class="fas fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                    <!-- Kelola Berita -->
                    <div class="data-card">
                        <div class="data-card-header">
                            <h2>
                                <span class="card-icon"><i class="fas fa-newspaper"></i></span>
                                Kelola Berita Terbaru
                            </h2>
                        </div>
                        <div class="table-responsive">
                            <table class="table">
                                <thead>
                                    <tr>
                                        <th>Judul Berita</th>
                                        <th>Tanggal</th>
                                        <th>Kategori</th>
                                        <th>Aksi</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    <?php foreach ($berita as $b): ?>
                                    <tr>
                                        <td style="font-weight:600;max-width:220px;white-space:nowrap;overflow:hidden;text-overflow:ellipsis;">
                                            <?php echo htmlspecialchars($b['judul']); ?>
                                        </td>
                                        <td style="color:var(--dash-text-light);font-size:0.82rem;white-space:nowrap;">
                                            <i class="fas fa-calendar-alt" style="margin-right:4px;color:var(--gold-400);"></i>
                                            <?php echo htmlspecialchars($b['tanggal']); ?>
                                        </td>
                                        <td>
                                            <span class="status-badge" style="background:var(--green-50); color:var(--green-600); border:1px solid var(--green-100);">
                                                <?php echo htmlspecialchars($b['kategori']); ?>
                                            </span>
                                        </td>
                                        <td>
                                            <div class="action-btns">
                                                <a href="data_berita.php" class="action-btn" title="Edit & Update Berita" style="color:var(--dash-primary);background:var(--dash-primary-light);"><i class="fas fa-search"></i></a>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                </tbody>
                            </table>
                        </div>
                    </div>

                </div><!-- /left-col -->

                <!-- Right Column -->
                <div class="right-col">

                    <!-- Pesan Masuk -->
                    <div class="data-card" style="margin-bottom:16px;">
                        <div class="data-card-header">
                            <h2>
                                <span class="card-icon"><i class="fas fa-inbox"></i></span>
                                Pesan Masuk
                            </h2>
                            <a href="#" class="card-header-link">Semua <i class="fas fa-arrow-right" style="font-size:0.65rem;"></i></a>
                        </div>
                        <?php foreach ($pesanKontak as $pk): ?>
                        <div class="msg-item" onclick="openMsgModal(
                            '<?php echo addslashes(htmlspecialchars($pk['nama'])); ?>',
                            '<?php echo addslashes(htmlspecialchars($pk['email'])); ?>',
                            '<?php echo addslashes(htmlspecialchars($pk['subjek'])); ?>',
                            '<?php echo addslashes(htmlspecialchars($pk['tanggal'])); ?>',
                            '<?php echo addslashes(htmlspecialchars($pk['pesan'])); ?>'
                        )">
                            <div class="msg-avatar">
                                <?php echo strtoupper(mb_substr($pk['nama'], 0, 1)); ?>
                            </div>
                            <div class="msg-body">
                                <div class="msg-name"><?php echo htmlspecialchars($pk['nama']); ?></div>
                                <div class="msg-subject"><?php echo htmlspecialchars($pk['subjek']); ?></div>
                                <div class="msg-preview"><?php echo htmlspecialchars(mb_substr($pk['pesan'], 0, 50)) . '...'; ?></div>
                            </div>
                            <div class="msg-meta">
                                <span class="msg-date"><?php echo $pk['tanggal']; ?></span>
                                <span class="msg-unread-dot"></span>
                            </div>
                        </div>
                        <?php endforeach; ?>
                    </div>

                    <!-- Akses Login -->
                    <div class="data-card">
                        <div class="data-card-header">
                            <h2>
                                <span class="card-icon"><i class="fas fa-shield-alt"></i></span>
                                Akses Login
                            </h2>
                        </div>
                        <?php foreach ($users as $u): ?>
                        <div class="user-card-item">
                            <div class="user-item-avatar">
                                <?php echo strtoupper(mb_substr($u['username'], 0, 1)); ?>
                            </div>
                            <span class="user-item-name"><?php echo htmlspecialchars($u['username']); ?></span>
                            <span class="role-badge <?php echo $u['role']; ?>">
                                <?php echo htmlspecialchars($u['role']); ?>
                            </span>
                        </div>
                        <?php endforeach; ?>
                    </div>

                </div><!-- /right-col -->

            </div><!-- /data-sections -->

        </div><!-- /dash-content -->

        <!-- Footer -->
        <?php include_once 'includes/dash_footer.php'; ?>

    </main>
</div>

<script src="js/dashboard.js"></script>
</body>
</html>
