<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';

// Proses Hapus Data
if (isset($_GET['delete_id'])) {
    $id_del = (int)$_GET['delete_id'];
    
    // Hapus file-file terkait
    $resFiles = $conn->query("SELECT file_ijazah, file_kk, file_akta, file_ktp_ortu, file_surat_tjm FROM pendaftaran WHERE id = $id_del");
    if($resFiles && $resFiles->num_rows > 0) {
        $rowFiles = $resFiles->fetch_assoc();
        foreach($rowFiles as $file) {
            if(!empty($file) && file_exists('uploads/' . $file) && $file != 'dummy.jpg') {
                unlink('uploads/' . $file);
            }
        }
    }
    
    $conn->query("DELETE FROM pendaftaran WHERE id = $id_del");
    header('Location: data_pendaftaran.php?status=deleted');
    exit;
}

// Proses Update Status (Validasi)
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['id']) && isset($_POST['status'])) {
    $id = (int)$_POST['id'];
    $status = $conn->real_escape_string($_POST['status']);
    
    $conn->query("UPDATE pendaftaran SET status = '$status' WHERE id = $id");
    header("Location: data_pendaftaran.php?status=updated");
    exit;
}

// Ambil Data Pendaftaran
$pendaftarList = [];
$res = $conn->query("SELECT * FROM pendaftaran ORDER BY id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $pendaftarList[] = $row;
    }
}

// Ambil Jadwal Saat Ini (untuk ditampilkan di input)
$jadwal_saat_ini = '';
$res_j = $conn->query("SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'jadwal_pengumuman'");
if ($res_j && $res_j->num_rows > 0) {
    $row_j = $res_j->fetch_assoc();
    $jadwal_saat_ini = date('Y-m-d\TH:i', strtotime($row_j['nilai_pengaturan']));
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Validasi Pendaftaran Santri Baru — Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard_pro.css?v=3">
    <link rel="stylesheet" href="css/data_pendaftaran.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Rincian Pendaftar -->
<div class="modal-overlay" id="detailModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3 id="modalTitle"><i class="fas fa-user-graduate" style="margin-right:8px;"></i><span>Detail Calon Santri</span></h3>
            <button type="button" class="modal-close" onclick="closeDetailModal()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="padding: 25px;">
            <div class="detail-grid">
                <div class="detail-group">
                    <span class="detail-label">Nama Lengkap</span>
                    <div class="detail-value" id="detNama"></div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Jenjang Pendaftaran</span>
                    <div class="detail-value" id="detJenjang"></div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Tempat, Tanggal Lahir</span>
                    <div class="detail-value" id="detTTL"></div>
                </div>
                <div class="detail-group">
                    <span class="detail-label">Nomor Handphone (WA)</span>
                    <div class="detail-value" id="detNoHP"></div>
                </div>
                <div class="detail-group" style="grid-column: 1 / -1;">
                    <span class="detail-label">Alamat Lengkap</span>
                    <div class="detail-value" id="detAlamat"></div>
                </div>
                <div class="detail-group" style="grid-column: 1 / -1;">
                    <span class="detail-label">Info Pondok Dari</span>
                    <div class="detail-value" id="detInfo"></div>
                </div>
            </div>

            <h4 style="margin-bottom: 15px; color: var(--dash-text);"><i class="fas fa-folder-open"></i> Kelengkapan Berkas</h4>
            <div class="doc-list">
                <div class="doc-item">
                    <div style="display:flex; align-items:center;">
                        <i class="fas fa-file-invoice"></i>
                        <div class="doc-info">
                            <span class="doc-name">Ijazah / SKL</span>
                            <span class="doc-status" id="stIjazah">Tersedia</span>
                        </div>
                    </div>
                    <a href="#" target="_blank" class="btn-view-doc" id="btnIjazah"><i class="fas fa-eye"></i> Lihat</a>
                </div>
                <div class="doc-item">
                    <div style="display:flex; align-items:center;">
                        <i class="fas fa-users"></i>
                        <div class="doc-info">
                            <span class="doc-name">Kartu Keluarga (KK)</span>
                            <span class="doc-status" id="stKK">Tersedia</span>
                        </div>
                    </div>
                    <a href="#" target="_blank" class="btn-view-doc" id="btnKK"><i class="fas fa-eye"></i> Lihat</a>
                </div>
                <div class="doc-item">
                    <div style="display:flex; align-items:center;">
                        <i class="fas fa-child"></i>
                        <div class="doc-info">
                            <span class="doc-name">Akta Kelahiran</span>
                            <span class="doc-status" id="stAkta">Tersedia</span>
                        </div>
                    </div>
                    <a href="#" target="_blank" class="btn-view-doc" id="btnAkta"><i class="fas fa-eye"></i> Lihat</a>
                </div>
                <div class="doc-item">
                    <div style="display:flex; align-items:center;">
                        <i class="fas fa-id-card"></i>
                        <div class="doc-info">
                            <span class="doc-name">KTP Orang Tua / Wali</span>
                            <span class="doc-status" id="stKTP">Tersedia</span>
                        </div>
                    </div>
                    <a href="#" target="_blank" class="btn-view-doc" id="btnKTP"><i class="fas fa-eye"></i> Lihat</a>
                </div>
                <div class="doc-item">
                    <div style="display:flex; align-items:center;">
                        <i class="fas fa-file-signature"></i>
                        <div class="doc-info">
                            <span class="doc-name">Surat Tanggung Jawab Mutlak (SPJM)</span>
                            <span class="doc-status" id="stSPJM">Tersedia</span>
                        </div>
                    </div>
                    <a href="#" target="_blank" class="btn-view-doc" id="btnSPJM"><i class="fas fa-eye"></i> Lihat</a>
                </div>
            </div>

            <!-- Validation Form -->
            <form method="POST" action="data_pendaftaran.php" id="formValidasi">
                <input type="hidden" name="id" id="valId">
                <div class="validation-actions">
                    <div class="validation-title">
                        <i class="fas fa-clipboard-check" style="font-size:1.5rem; color:var(--dash-primary);"></i>
                        <div>
                            <div>Keputusan Validasi</div>
                            <div style="font-size:0.8rem; color:#64748b; font-weight:400;">Ubah status calon santri ini</div>
                        </div>
                    </div>
                    <div style="display:flex; gap:10px;">
                        <button type="button" class="btn-val btn-val-tolak" onclick="validateForm('Tidak Lolos', 'Anda yakin ingin menolak calon santri ini?', 'reject')"><i class="fas fa-times"></i> Tidak Lolos</button>
                        <button type="button" class="btn-val btn-val-pending" onclick="validateForm('Pending', 'Ubah status kembali menjadi Pending?', 'info')"><i class="fas fa-clock"></i> Pending</button>
                        <button type="button" class="btn-val btn-val-lolos" onclick="validateForm('Lolos', 'Anda yakin ingin menerima calon santri ini (Lolos)?', 'accept')"><i class="fas fa-check"></i> Lolos</button>
                    </div>
                </div>
            </form>
        </div>
    </div>
</div>

<div class="dash-wrapper">
    <?php include_once 'includes/dash_sidebar.php'; ?>

    <main class="dash-main" id="dashMain">
        <?php include_once 'includes/dash_header.php'; ?>

        <div class="dash-content">
            <div class="page-title-bar fade-in-up">
                <div class="greeting">
                    <h1>Validasi Pendaftaran Santri</h1>
                    <p>Manajemen data calon santri baru, verifikasi berkas, dan penentuan kelulusan.</p>
                </div>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-700);padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                <?php 
                    if($_GET['status']=='updated') echo 'Status validasi pendaftar berhasil diperbarui.';
                    elseif($_GET['status']=='deleted') echo 'Data pendaftar dan berkas berhasil dihapus permanen.';
                ?>
            </div>
            <?php endif; ?>

            <!-- Widget Jadwal Pengumuman -->
            <div class="schedule-widget fade-in-up">
                <div class="schedule-info">
                    <div class="schedule-title"><i class="fas fa-calendar-alt"></i> Pengaturan Rilis Pengumuman</div>
                    <div class="schedule-desc">Tentukan tanggal dan jam kapan santri dapat mulai melihat hasil kelulusan di website.</div>
                </div>
                <form id="formJadwalPengumuman" class="schedule-form">
                    <input type="datetime-local" id="inputJadwal" name="jadwal_pengumuman" class="schedule-input" value="<?php echo htmlspecialchars($jadwal_saat_ini); ?>" required>
                    <button type="submit" class="btn-save-schedule" id="btnSaveSchedule">
                        <i class="fas fa-save"></i> Simpan
                    </button>
                </form>
            </div>

            <div class="data-card fade-in-up d1">
                <div class="data-card-header">
                    <h2><span class="card-icon"><i class="fas fa-users"></i></span> Daftar Pendaftar Masuk</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Lengkap</th>
                                <th>Jenjang</th>
                                <th>Tanggal Daftar</th>
                                <th>Status</th>
                                <th style="width: 120px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pendaftarList) > 0): ?>
                                <?php foreach($pendaftarList as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--dash-text); font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($p['nama_lengkap']); ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--dash-text-light); margin-top: 4px;">
                                            <i class="fas fa-phone-alt"></i> <?php echo htmlspecialchars($p['nomor_handphone']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span style="background:#f3f4f6; color:#4b5563; padding:4px 10px; border-radius:6px; font-size:0.8rem; font-weight:600; border:1px solid #d1d5db;">
                                            <?php echo htmlspecialchars($p['jenjang_pendaftaran']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; font-weight: 500; color: var(--dash-text-light);">
                                            <?php echo (isset($p['created_at']) && !empty($p['created_at'])) ? date('d M Y', strtotime($p['created_at'])) : 'Belum Tersedia'; ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php 
                                            $st = htmlspecialchars($p['status']);
                                            $icon = 'clock';
                                            if($st == 'Lolos') $icon = 'check-circle';
                                            if($st == 'Tidak Lolos') $icon = 'times-circle';
                                        ?>
                                        <span class="status-badge status-<?php echo substr($st, 0, 5); ?>">
                                            <i class="fas fa-<?php echo $icon; ?>"></i> <?php echo $st; ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            <button class="action-btn" title="Detail & Validasi" onclick='openDetailModal(<?php echo htmlspecialchars(json_encode($p)); ?>)' style="background:var(--dash-primary-light); color:var(--dash-primary);">
                                                <i class="fas fa-search"></i>
                                            </button>
                                            <a href="data_pendaftaran.php?delete_id=<?php echo $p['id']; ?>" class="action-btn delete" title="Hapus Permanen" onclick="event.preventDefault(); showConfirm('Konfirmasi Hapus', 'Yakin ingin menghapus data santri ini beserta seluruh berkas pendaftarannya secara permanen?', this.href, 'delete');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--dash-text-light);">
                                        <i class="fas fa-clipboard-list" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>
                                        Belum ada data pendaftar baru yang masuk.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
        <?php include_once 'includes/dash_footer.php'; ?>
    </main>
</div>

<script src="js/dashboard.js"></script>
<script src="js/data_pendaftaran.js"></script>
</body>
</html>
