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
    
    // Get judul & image to delete
    $q_img = $conn->query("SELECT judul, gambar FROM berita WHERE id = $id_del");
    $judul_del = 'ID ' . $id_del;
    if ($q_img && $q_img->num_rows > 0) {
        $r_img = $q_img->fetch_assoc();
        $judul_del = $r_img['judul'];
        if ($r_img['gambar'] && file_exists($r_img['gambar'])) {
            unlink($r_img['gambar']);
        }
    }
    
    $conn->query("DELETE FROM berita WHERE id = $id_del");
    catat_log($conn, "Menghapus berita: $judul_del");
    header('Location: data_berita.php?status=deleted');
    exit;
}

// Proses Duplikasi Berita
if (isset($_GET['duplikat_id'])) {
    $id_dup = (int)$_GET['duplikat_id'];
    $q_dup  = $conn->query("SELECT * FROM berita WHERE id = $id_dup");
    if ($q_dup && $r_dup = $q_dup->fetch_assoc()) {
        $judul_baru   = $conn->real_escape_string('[Salinan] ' . $r_dup['judul']);
        $kat          = $conn->real_escape_string($r_dup['kategori']);
        $pen          = $conn->real_escape_string($r_dup['penulis']);
        $isi          = $conn->real_escape_string($r_dup['isi_berita']);
        $exc          = $conn->real_escape_string($r_dup['excerpt']);
        $tgl          = date('d M Y');
        $conn->query("INSERT INTO berita (judul, kategori, penulis, isi_berita, excerpt, tanggal, gambar, featured) VALUES ('$judul_baru', '$kat', '$pen', '$isi', '$exc', '$tgl', NULL, 0)");
        catat_log($conn, "Menduplikasi berita: {$r_dup['judul']}");
    }
    header('Location: data_berita.php?status=duplikat');
    exit;
}


// Proses Jadikan Featured
if (isset($_GET['featured_id'])) {
    $id_feat = (int)$_GET['featured_id'];
    $q_feat_info = $conn->query("SELECT judul FROM berita WHERE id=$id_feat");
    $judul_feat = ($q_feat_info && $r_feat = $q_feat_info->fetch_assoc()) ? $r_feat['judul'] : 'ID '.$id_feat;
    $conn->query("UPDATE berita SET featured=0");
    $conn->query("UPDATE berita SET featured=1 WHERE id=$id_feat");
    catat_log($conn, "Menjadikan berita unggulan: $judul_feat");
    header('Location: data_berita.php?status=featured');
    exit;
}

// Proses Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id         = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $judul      = $conn->real_escape_string($_POST['judul']);
    $kategori   = $conn->real_escape_string($_POST['kategori']);
    $penulis    = $conn->real_escape_string($_POST['penulis']);
    $isi_berita = $conn->real_escape_string($_POST['isi_berita']);
    $tanggal    = date('d M Y'); // Auto current date if empty? No, let's keep it simple. We can use auto.
    
    // Auto generate excerpt (first 150 chars)
    $excerpt = strip_tags($_POST['isi_berita']);
    if(strlen($excerpt) > 150) {
        $excerpt = substr($excerpt, 0, 150) . '...';
    }
    $excerpt = $conn->real_escape_string($excerpt);
    
    $gambar_name = null;
    
    // Handle File Upload
    if (isset($_FILES['gambar']) && $_FILES['gambar']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['gambar']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            if (!is_dir('uploads/berita')) mkdir('uploads/berita', 0777, true);
            
            $new_name = 'uploads/berita/img_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['gambar']['tmp_name'], $new_name)) {
                $gambar_name = $new_name;
            }
        }
    }

    if ($id > 0) {
        // Update
        if ($gambar_name) {
            $q_old = $conn->query("SELECT gambar FROM berita WHERE id = $id");
            if ($r_old = $q_old->fetch_assoc()) {
                if ($r_old['gambar'] && file_exists($r_old['gambar'])) unlink($r_old['gambar']);
            }
            $sql = "UPDATE berita SET judul='$judul', kategori='$kategori', penulis='$penulis', isi_berita='$isi_berita', excerpt='$excerpt', gambar='$gambar_name' WHERE id=$id";
        } else {
            $sql = "UPDATE berita SET judul='$judul', kategori='$kategori', penulis='$penulis', isi_berita='$isi_berita', excerpt='$excerpt' WHERE id=$id";
        }
        $conn->query($sql);
        catat_log($conn, "Memperbarui berita: $judul");
        header('Location: data_berita.php?status=updated');
    } else {
        // Insert
        $sql = "INSERT INTO berita (judul, kategori, penulis, isi_berita, excerpt, tanggal, gambar) VALUES ('$judul', '$kategori', '$penulis', '$isi_berita', '$excerpt', '$tanggal', " . ($gambar_name ? "'$gambar_name'" : "NULL") . ")";
        $conn->query($sql);
        catat_log($conn, "Mempublikasikan berita baru: $judul");
        header('Location: data_berita.php?status=added');
    }
    exit;
}

// Ambil Data Berita
$beritaList = [];
$res = $conn->query("SELECT * FROM berita ORDER BY id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $beritaList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Berita & Pengumuman — Dashboard Guru</title>
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
        .btn-featured { color: #f59e0b; background: #fef3c7; border: 1px solid #fcd34d; }
        .btn-featured:hover { background: #fde68a; }
        .badge-feat { background: #fef3c7; color: #d97706; padding: 3px 8px; border-radius: 4px; font-size: 0.7rem; font-weight: 700; margin-left: 10px; display: inline-flex; align-items: center; gap: 4px; }

        /* ── Mobile Responsive Modal ── */
        .form-row-flex { display: flex; gap: 15px; }
        @media (max-width: 576px) {
            .form-row-flex { flex-direction: column; gap: 10px; }
        }
    </style>

</head>
<body>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Form Berita -->
<div class="modal-overlay" id="formModal">
    <div class="modal-box" style="max-width: 700px;">
        <form method="POST" action="data_berita.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-newspaper" style="margin-right:8px;"></i><span>Tulis Berita Baru</span></h3>
                <button type="button" class="modal-close" onclick="closeFormModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body form-layout">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Judul Berita</label>
                    <input type="text" name="judul" id="formJudul" class="form-control" required placeholder="Masukkan judul berita yang menarik">
                </div>

                <div class="form-row-flex">
                    <div class="form-group" style="flex:1;">
                        <label>Kategori</label>
                        <select name="kategori" id="formKategori" class="form-control" required>
                            <option value="Kegiatan">Kegiatan</option>
                            <option value="Prestasi">Prestasi</option>
                            <option value="Pengumuman">Pengumuman</option>
                            <option value="Pendidikan">Pendidikan</option>
                        </select>
                    </div>
                    <div class="form-group" style="flex:1;">
                        <label>Penulis</label>
                        <input type="text" name="penulis" id="formPenulis" class="form-control" value="Admin Ponpes" required>
                    </div>
                </div>


                <div class="form-group">
                    <label>Isi Berita Lengkap</label>
                    <textarea name="isi_berita" id="formIsi" class="form-control" rows="5" placeholder="Tuliskan detail berita atau pengumuman di sini..." required></textarea>
                </div>


                <div class="form-group">
                    <label>Gambar Utama (Thumbnail)</label>
                    <input type="file" name="gambar" id="formGambar" class="form-control" accept="image/*">
                    <small style="color:var(--dash-text-light);font-size:0.75rem;">Biarkan kosong jika tidak ingin mengubah gambar.</small>
                </div>
            </div>
            <div class="modal-footer" style="gap: 10px;">
                <button type="button" class="btn-action outline" onclick="closeFormModal()">Batal</button>
                <button type="submit" class="btn-action primary"><i class="fas fa-paper-plane"></i> Publikasikan</button>
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
            <div class="page-title-bar fade-in-up">
                <div class="greeting">
                    <h1>Kelola Berita & Informasi</h1>
                    <p>Pusat kontrol manajemen artikel, berita, dan pengumuman pesantren.</p>
                </div>
                <button class="btn-action primary" onclick="openFormModal()">
                    <i class="fas fa-pen-nib"></i> Tulis Berita
                </button>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-700);padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                <?php 
                    if($_GET['status']=='added')    echo 'Berita baru berhasil dipublikasikan.';
                    elseif($_GET['status']=='updated')  echo 'Berita berhasil diperbarui.';
                    elseif($_GET['status']=='deleted')  echo 'Berita berhasil dihapus.';
                    elseif($_GET['status']=='featured') echo 'Artikel berhasil di-set sebagai Unggulan (Sorotan Utama).';
                    elseif($_GET['status']=='duplikat') echo 'Berita berhasil diduplikasi sebagai salinan baru.';
                ?>
            </div>
            <?php endif; ?>


            <div class="data-card fade-in-up d1">
                <div class="data-card-header">
                    <h2><span class="card-icon"><i class="fas fa-newspaper"></i></span> Arsip Berita</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 90px;">Gambar</th>
                                <th>Judul Artikel</th>
                                <th>Kategori</th>
                                <th>Tgl Publikasi</th>
                                <th style="width: 180px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($beritaList) > 0): ?>
                                <?php foreach($beritaList as $b): ?>
                                <tr>
                                    <td>
                                        <?php if ($b['gambar'] && file_exists($b['gambar'])): ?>
                                            <img src="<?php echo htmlspecialchars($b['gambar']); ?>" alt="Gambar" style="width:70px; height:50px; object-fit:cover; border-radius:6px; border:1px solid var(--dash-border);">
                                        <?php else: ?>
                                            <div style="width:70px; height:50px; background:#f3f4f6; border-radius:6px; display:flex; align-items:center; justify-content:center; color:#9ca3af;"><i class="fas fa-image"></i></div>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; color: var(--dash-text); font-size: 0.95rem; line-height: 1.4;">
                                            <?php echo htmlspecialchars($b['judul']); ?>
                                            <?php if($b['featured']): ?>
                                                <span class="badge-feat"><i class="fas fa-star"></i> Unggulan</span>
                                            <?php endif; ?>
                                        </div>
                                        <div style="font-size: 0.8rem; color: var(--dash-text-light); margin-top: 4px;">
                                            <i class="fas fa-user-edit"></i> <?php echo htmlspecialchars($b['penulis']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <span class="status-badge" style="background:var(--green-50); color:var(--green-600); border:1px solid var(--green-100);">
                                            <?php echo htmlspecialchars($b['kategori']); ?>
                                        </span>
                                    </td>
                                    <td>
                                        <div style="font-size: 0.85rem; font-weight: 500; color: var(--dash-text-light);">
                                            <?php echo htmlspecialchars($b['tanggal']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            <a href="data_berita.php?featured_id=<?php echo $b['id']; ?>" class="action-btn" title="Jadikan Artikel Unggulan" style="<?php echo $b['featured'] ? 'color:#d97706; background:#fef3c7;' : ''; ?>">
                                                <i class="fas fa-star"></i>
                                            </a>
                                            <button class="action-btn" title="Edit" onclick='editBerita(<?php echo htmlspecialchars(json_encode($b)); ?>)'>
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="data_berita.php?duplikat_id=<?php echo $b['id']; ?>" class="action-btn" title="Duplikasi Berita" style="color:#3b82f6; background:#eff6ff;">
                                                <i class="fas fa-copy"></i>
                                            </a>
                                            <a href="data_berita.php?delete_id=<?php echo $b['id']; ?>" class="action-btn delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>

                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--dash-text-light);">
                                        <i class="fas fa-newspaper" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>
                                        Belum ada berita yang dipublikasikan.
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

<script src="js/dashboard/dashboard.js"></script>
<script>
    const formModal = document.getElementById('formModal');
    
    function openFormModal() {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Tulis Berita Baru';
        document.getElementById('formId').value = '';
        document.getElementById('formJudul').value = '';
        document.getElementById('formKategori').value = 'Kegiatan';
        document.getElementById('formPenulis').value = 'Admin Ponpes';
        document.getElementById('formIsi').value = '';
        document.getElementById('formGambar').value = '';
        
        formModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function editBerita(data) {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Edit Berita';
        document.getElementById('formId').value = data.id;
        document.getElementById('formJudul').value = data.judul;
        document.getElementById('formKategori').value = data.kategori;
        document.getElementById('formPenulis').value = data.penulis;
        document.getElementById('formIsi').value = data.isi_berita;
        document.getElementById('formGambar').value = '';
        
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
