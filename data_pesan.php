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
    $conn->query("DELETE FROM pesan_masuk WHERE id = $id_del");
    header('Location: data_pesan.php?status=deleted');
    exit;
}

// Proses Tandai Sudah Dibaca
if (isset($_GET['read_id'])) {
    $id_read = (int)$_GET['read_id'];
    $conn->query("UPDATE pesan_masuk SET status='read' WHERE id = $id_read");
    header('Location: data_pesan.php');
    exit;
}

// Ambil Data Pesan
$pesanList = [];
$res = $conn->query("SELECT * FROM pesan_masuk ORDER BY id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $pesanList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Pesan Masuk — Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard_pro.css?v=3">
    <style>
        .msg-row.unread { background-color: #f0fdf4; font-weight: 600; }
        .msg-row.unread td { color: var(--dash-text); }
        .msg-row.read td { color: var(--dash-text-light); }
        .badge-kategori { padding: 4px 10px; border-radius: 6px; font-size: 0.75rem; font-weight: 700; }
        .badge-kategori.unread { background: var(--green-100); color: var(--green-700); }
        .badge-kategori.read { background: #f3f4f6; color: #6b7280; }
    </style>
</head>
<body>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Message Detail Modal -->
<div class="modal-overlay" id="msgDetailModal">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-envelope-open-text" style="margin-right:8px;"></i>Detail Pesan Masuk</h3>
            <button class="modal-close" onclick="closeMsgDetail()"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body">
            <div class="modal-field">
                <div class="field-label">Pengirim</div>
                <div class="field-value" id="mdNama"></div>
            </div>
            <div style="display:flex; gap:15px;">
                <div class="modal-field" style="flex:1;">
                    <div class="field-label">Email</div>
                    <div class="field-value" id="mdEmail"></div>
                </div>
                <div class="modal-field" style="flex:1;">
                    <div class="field-label">No. HP / WA</div>
                    <div class="field-value" id="mdPhone"></div>
                </div>
            </div>
            <div class="modal-field">
                <div class="field-label">Subjek Pertanyaan</div>
                <div class="field-value" id="mdSubjek"></div>
            </div>
            <div class="modal-field">
                <div class="field-label">Tanggal Dikirim</div>
                <div class="field-value" id="mdTanggal"></div>
            </div>
            <div class="modal-field">
                <div class="field-label">Isi Pesan</div>
                <div class="field-value msg-text" id="mdPesan"></div>
            </div>
        </div>
        <div class="modal-footer">
            <a href="#" id="btnTandaiSelesai" class="btn-action primary">Tandai Sudah Dibaca</a>
            <button class="btn-action outline" onclick="closeMsgDetail()">Tutup</button>
        </div>
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
            <div class="page-title-bar fade-in-up">
                <div class="greeting">
                    <h1>Pesan Masuk</h1>
                    <p>Daftar pesan dan pertanyaan dari halaman Hubungi Kami.</p>
                </div>
                <div class="datetime-badge" style="background:var(--green-50); color:var(--green-700); border-color:var(--green-200);">
                    <i class="fas fa-inbox"></i>
                    <span>Total: <?php echo count($pesanList); ?> Pesan</span>
                </div>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:#fef2f2;border:1px solid #fecaca;color:#ef4444;padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-trash-alt" style="font-size:1.2rem;"></i>
                Data pesan berhasil dihapus permanen.
            </div>
            <?php endif; ?>

            <div class="data-card fade-in-up d1">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 50px;">Status</th>
                                <th>Pengirim & Kontak</th>
                                <th>Subjek</th>
                                <th>Tanggal</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pesanList) > 0): ?>
                                <?php foreach($pesanList as $p): ?>
                                <tr class="msg-row <?php echo $p['status']; ?>">
                                    <td style="text-align:center;">
                                        <?php if ($p['status'] == 'unread'): ?>
                                            <i class="fas fa-envelope" style="color:var(--green-600); font-size:1.2rem;" title="Belum Dibaca"></i>
                                        <?php else: ?>
                                            <i class="fas fa-envelope-open" style="color:#9ca3af; font-size:1.2rem;" title="Sudah Dibaca"></i>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.95rem;"><?php echo htmlspecialchars($p['nama']); ?></div>
                                        <div style="font-size: 0.75rem; font-weight:normal; margin-top:2px;">
                                            <i class="fas fa-at"></i> <?php echo htmlspecialchars($p['email']); ?> &nbsp;
                                            <i class="fas fa-phone"></i> <?php echo htmlspecialchars($p['no_hp'] ?: '-'); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="badge-kategori <?php echo $p['status']; ?>">
                                            <?php echo htmlspecialchars($p['subjek']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size:0.8rem; font-weight:normal;">
                                            <?php echo date('d M Y, H:i', strtotime($p['created_at'])); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            <button class="action-btn" style="color:var(--dash-primary); background:#eff6ff;" title="Lihat Pesan" onclick="openMsgDetail(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                                <i class="fas fa-eye"></i>
                                            </button>
                                            <a href="data_pesan.php?delete_id=<?php echo $p['id']; ?>" class="action-btn delete" title="Hapus" onclick="return confirm('Hapus pesan dari <?php echo addslashes($p['nama']); ?>?');">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--dash-text-light);">
                                        <i class="fas fa-inbox" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>
                                        Tidak ada pesan masuk saat ini.
                                    </td>
                                </tr>
                            <?php endif; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div>

        <!-- Footer -->
        <?php include_once 'includes/dash_footer.php'; ?>
    </main>
</div>

<script src="js/dashboard.js"></script>
<script>
    const msgDetailModal = document.getElementById('msgDetailModal');
    
    function openMsgDetail(data) {
        document.getElementById('mdNama').textContent = data.nama;
        document.getElementById('mdEmail').textContent = data.email;
        document.getElementById('mdPhone').textContent = data.no_hp ? data.no_hp : '-';
        document.getElementById('mdSubjek').textContent = data.subjek;
        document.getElementById('mdTanggal').textContent = new Date(data.created_at).toLocaleString('id-ID');
        document.getElementById('mdPesan').textContent = data.pesan;
        
        let btnTandai = document.getElementById('btnTandaiSelesai');
        if (data.status === 'unread') {
            btnTandai.style.display = 'inline-flex';
            btnTandai.href = 'data_pesan.php?read_id=' + data.id;
        } else {
            btnTandai.style.display = 'none';
        }
        
        msgDetailModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeMsgDetail() {
        msgDetailModal.classList.remove('active');
        document.body.style.overflow = '';
    }
</script>
</body>
</html>
