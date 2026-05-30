<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/simple_log.php';

// Proses Hapus Data
if (isset($_GET['delete_id'])) {
    $id_del = (int)$_GET['delete_id'];
    $q_p = $conn->query("SELECT judul FROM profil_prestasi WHERE id = $id_del");
    $judul_del_pr = ($q_p && $r_p = $q_p->fetch_assoc()) ? $r_p['judul'] : 'ID '.$id_del;
    $conn->query("DELETE FROM profil_prestasi WHERE id = $id_del");
    catat_log($conn, "Menghapus data prestasi: $judul_del_pr");
    header('Location: data_prestasi.php?status=deleted');
    exit;
}

// Proses Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $judul      = $conn->real_escape_string($_POST['judul']);
    $tingkat    = $conn->real_escape_string($_POST['tingkat']);
    $tahun      = (int)$_POST['tahun'];
    $rank_tipe  = $conn->real_escape_string($_POST['rank_tipe']);
    $rank_label = $conn->real_escape_string($_POST['rank_label']);
    
    $tampilkan_di_timeline = isset($_POST['tampilkan_di_timeline']) ? 1 : 0;
    $deskripsi  = $tampilkan_di_timeline ? $conn->real_escape_string($_POST['deskripsi']) : "NULL";

    if ($id > 0) {
        $sql = "UPDATE profil_prestasi SET judul='$judul', tingkat='$tingkat', tahun=$tahun, rank_tipe='$rank_tipe', rank_label='$rank_label', deskripsi=" . ($tampilkan_di_timeline ? "'$deskripsi'" : "NULL") . ", tampilkan_di_timeline=$tampilkan_di_timeline WHERE id=$id";
        $conn->query($sql);
        catat_log($conn, "Memperbarui data prestasi: $judul");
        header('Location: data_prestasi.php?status=updated');
    } else {
        $sql = "INSERT INTO profil_prestasi (judul, tingkat, tahun, rank_tipe, rank_label, deskripsi, tampilkan_di_timeline) VALUES ('$judul', '$tingkat', $tahun, '$rank_tipe', '$rank_label', " . ($tampilkan_di_timeline ? "'$deskripsi'" : "NULL") . ", $tampilkan_di_timeline)";
        $conn->query($sql);
        catat_log($conn, "Menambahkan data prestasi baru: $judul");
        header('Location: data_prestasi.php?status=added');
    }
    exit;
}

// Ambil Data Prestasi
$prestasiList = [];
$res = $conn->query("SELECT * FROM profil_prestasi ORDER BY tahun DESC, id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $prestasiList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Informasi & Prestasi — Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard/dashboard_pro.css?v=3">
    <style>
        .modal-body.form-layout { gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--dash-text); }
        .form-control {
            padding: 10px 15px; border: 1px solid var(--dash-border); border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; outline: none; transition: 0.2s;
        }
        .form-control:focus { border-color: var(--dash-primary); box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1); }
        select.form-control { cursor: pointer; }
        .rank-badge { padding: 4px 10px; border-radius: 20px; font-size: 0.8rem; font-weight: 600; display: inline-block; }
        .rank-gold { background: #fef3c7; color: #d97706; border: 1px solid #fcd34d; }
        .rank-silver { background: #f3f4f6; color: #4b5563; border: 1px solid #d1d5db; }
        .rank-bronze { background: #ffedd5; color: #c2410c; border: 1px solid #fdba74; }
        .rank-green { background: #dcfce7; color: #16a34a; border: 1px solid #bbf7d0; }
        .timeline-box { margin-top:15px; padding:15px; border:1px dashed var(--dash-border); border-radius:8px; background:#fafafa; display:none; }
        
        /* Mobile Form Flexibility */
        .form-row-flex { display: flex; gap: 15px; }
        .checkbox-group { display: flex; flex-direction: row; align-items: center; gap: 10px; margin-top: 10px; }
        @media (max-width: 576px) {
            .form-row-flex { flex-direction: column; gap: 10px; }
            .checkbox-group { align-items: flex-start; }
            .checkbox-group input { margin-top: 3px; }
        }
    </style>
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Form Prestasi -->
<div class="modal-overlay" id="formModal">
    <div class="modal-box" style="max-width: 600px;">
        <form method="POST" action="data_prestasi.php">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-trophy" style="margin-right:8px;"></i><span>Tambah Prestasi</span></h3>
                <button type="button" class="modal-close" onclick="closeFormModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body form-layout">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Judul Prestasi / Informasi</label>
                    <input type="text" name="judul" id="formJudul" class="form-control" required placeholder="Contoh: Juara 1 Lomba Kaligrafi Kabupaten">
                </div>

                <div class="form-row-flex">
                    <div class="form-group" style="flex:1;">
                        <label>Tingkat</label>
                        <select name="tingkat" id="formTingkat" class="form-control" required>
                            <option value="Internal">Internal (Pesantren)</option>
                            <option value="Kecamatan">Kecamatan</option>
                            <option value="Kabupaten">Kabupaten</option>
                            <option value="Provinsi">Provinsi</option>
                            <option value="Nasional">Nasional</option>
                            <option value="Lainnya">Lainnya</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Tahun</label>
                        <input type="number" name="tahun" id="formTahun" class="form-control" value="<?php echo date('Y'); ?>" required>
                    </div>
                </div>

                <div class="form-row-flex">
                    <div class="form-group" style="flex:1;">
                        <label>Warna Ikon (Medali)</label>
                        <select name="rank_tipe" id="formRankTipe" class="form-control" required>
                            <option value="gold">Emas (Juara 1)</option>
                            <option value="silver">Perak (Juara 2)</option>
                            <option value="bronze">Perunggu (Juara 3)</option>
                            <option value="green">Hijau (Harapan / Top / Umum)</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Label Rank</label>
                        <input type="text" name="rank_label" id="formRankLabel" class="form-control" required placeholder="Contoh: Juara 1, Top 10">
                    </div>
                </div>

                <div class="form-group checkbox-group">
                    <input type="checkbox" id="formTimelineCheck" name="tampilkan_di_timeline" value="1" style="width:18px;height:18px;cursor:pointer;flex-shrink:0;">
                    <label for="formTimelineCheck" style="cursor:pointer;font-size:0.90rem;line-height:1.4;">Tampilkan di bagian "Perjalanan Prestasi" (Timeline)</label>
                </div>

                <div class="form-group timeline-box" id="timelineDescBox">
                    <label>Deskripsi Singkat Timeline</label>
                    <textarea name="deskripsi" id="formDeskripsi" class="form-control" rows="3" placeholder="Ceritakan singkat tentang pencapaian ini..."></textarea>
                </div>

            </div>
            <div class="modal-footer" style="gap: 10px;">
                <button type="button" class="btn-action outline" onclick="closeFormModal()">Batal</button>
                <button type="submit" class="btn-action primary"><i class="fas fa-save"></i> Simpan Prestasi</button>
            </div>
        </form>
    </div>
</div>

<div class="dash-wrapper">
    <?php include_once 'includes/dash_sidebar.php'; ?>

    <main class="dash-main" id="dashMain">
        <?php include_once 'includes/dash_header.php'; ?>

        <div class="dash-content">
            <div class="page-title-bar fade-in-up">
                <div class="greeting">
                    <h1>Kelola Informasi & Prestasi</h1>
                    <p>Manajemen data pencapaian santri dan rekam jejak perjalanan pesantren.</p>
                </div>
                <button class="btn-action primary" onclick="openFormModal()">
                    <i class="fas fa-plus"></i> Tambah Prestasi
                </button>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-700);padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                <?php 
                    if($_GET['status']=='added') echo 'Data prestasi berhasil ditambahkan.';
                    elseif($_GET['status']=='updated') echo 'Data prestasi berhasil diperbarui.';
                    elseif($_GET['status']=='deleted') echo 'Data prestasi berhasil dihapus.';
                ?>
            </div>
            <?php endif; ?>

            <div class="data-card fade-in-up d1">
                <div class="data-card-header">
                    <h2><span class="card-icon"><i class="fas fa-trophy"></i></span> Daftar Prestasi Santri</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Nama Prestasi / Informasi</th>
                                <th>Tingkat</th>
                                <th>Tahun</th>
                                <th>Peringkat</th>
                                <th>Timeline</th>
                                <th style="width: 100px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($prestasiList) > 0): ?>
                                <?php foreach($prestasiList as $p): ?>
                                <tr>
                                    <td>
                                        <div style="font-weight: 600; color: var(--dash-text); font-size: 0.95rem;">
                                            <?php echo htmlspecialchars($p['judul']); ?>
                                        </div>
                                    </td>
                                    <td><?php echo htmlspecialchars($p['tingkat']); ?></td>
                                    <td><strong><?php echo $p['tahun']; ?></strong></td>
                                    <td>
                                        <span class="rank-badge rank-<?php echo htmlspecialchars($p['rank_tipe']); ?>">
                                            <?php echo htmlspecialchars($p['rank_label']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <?php if($p['tampilkan_di_timeline']): ?>
                                            <span style="color:#10b981;"><i class="fas fa-check-circle"></i> Ya</span>
                                        <?php else: ?>
                                            <span style="color:#9ca3af;"><i class="fas fa-times"></i> Tidak</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            <button class="action-btn" title="Edit" onclick='editPrestasi(<?php echo htmlspecialchars(json_encode($p)); ?>)'>
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="data_prestasi.php?delete_id=<?php echo $p['id']; ?>" class="action-btn delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="6" style="text-align: center; padding: 40px; color: var(--dash-text-light);">
                                        Belum ada data prestasi.
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

<script src="js/dashboard/dashboard.js"></script>
<script>
    const formModal = document.getElementById('formModal');
    const tCheck = document.getElementById('formTimelineCheck');
    const tBox = document.getElementById('timelineDescBox');

    tCheck.addEventListener('change', function() {
        tBox.style.display = this.checked ? 'flex' : 'none';
        document.getElementById('formDeskripsi').required = this.checked;
    });

    function openFormModal() {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Tambah Prestasi';
        document.getElementById('formId').value = '';
        document.getElementById('formJudul').value = '';
        document.getElementById('formTingkat').value = 'Kabupaten';
        document.getElementById('formTahun').value = new Date().getFullYear();
        document.getElementById('formRankTipe').value = 'gold';
        document.getElementById('formRankLabel').value = '';
        
        tCheck.checked = false;
        tCheck.dispatchEvent(new Event('change'));
        document.getElementById('formDeskripsi').value = '';
        
        formModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function editPrestasi(data) {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Edit Prestasi';
        document.getElementById('formId').value = data.id;
        document.getElementById('formJudul').value = data.judul;
        
        // Find option or add it if doesn't exist
        let selectTingkat = document.getElementById('formTingkat');
        if(!Array.from(selectTingkat.options).some(opt => opt.value === data.tingkat)) {
            let newOpt = new Option(data.tingkat, data.tingkat);
            selectTingkat.add(newOpt);
        }
        selectTingkat.value = data.tingkat;
        
        document.getElementById('formTahun').value = data.tahun;
        document.getElementById('formRankTipe').value = data.rank_tipe;
        document.getElementById('formRankLabel').value = data.rank_label;
        
        tCheck.checked = parseInt(data.tampilkan_di_timeline) === 1;
        tCheck.dispatchEvent(new Event('change'));
        document.getElementById('formDeskripsi').value = data.deskripsi || '';
        
        formModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function closeFormModal() {
        formModal.classList.remove('active');
        document.body.style.overflow = '';
    }
</script>
</body>
</html>
