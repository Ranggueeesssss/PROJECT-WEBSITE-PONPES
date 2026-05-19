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
    
    // Get foto to delete
    $q_foto = $conn->query("SELECT src FROM media_galeri WHERE id = $id_del");
    if ($q_foto && $q_foto->num_rows > 0) {
        $r_foto = $q_foto->fetch_assoc();
        if ($r_foto['src'] && file_exists($r_foto['src'])) {
            unlink($r_foto['src']);
        }
    }
    
    $conn->query("DELETE FROM media_galeri WHERE id = $id_del");
    header('Location: data_galeri.php?status=deleted');
    exit;
}

// Proses Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id       = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $judul    = $conn->real_escape_string($_POST['judul']);
    $kategori = $conn->real_escape_string($_POST['kategori']);
    $tanggal  = $conn->real_escape_string($_POST['tanggal']);
    
    $src_name = null;
    
    // Handle File Upload
    if (isset($_FILES['src']) && $_FILES['src']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['src']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            if (!is_dir('uploads/galeri')) mkdir('uploads/galeri', 0777, true);
            
            $new_name = 'uploads/galeri/img_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['src']['tmp_name'], $new_name)) {
                $src_name = $new_name;
            }
        }
    }

    if ($id > 0) {
        // Update
        if ($src_name) {
            // Delete old photo
            $q_old = $conn->query("SELECT src FROM media_galeri WHERE id = $id");
            if ($r_old = $q_old->fetch_assoc()) {
                if ($r_old['src'] && file_exists($r_old['src'])) {
                    unlink($r_old['src']);
                }
            }
            $sql = "UPDATE media_galeri SET judul='$judul', kategori='$kategori', tanggal='$tanggal', src='$src_name' WHERE id=$id";
        } else {
            $sql = "UPDATE media_galeri SET judul='$judul', kategori='$kategori', tanggal='$tanggal' WHERE id=$id";
        }
        $conn->query($sql);
        header('Location: data_galeri.php?status=updated');
    } else {
        // Insert
        $sql = "INSERT INTO media_galeri (judul, kategori, tanggal, src) VALUES ('$judul', '$kategori', '$tanggal', " . ($src_name ? "'$src_name'" : "NULL") . ")";
        $conn->query($sql);
        header('Location: data_galeri.php?status=added');
    }
    exit;
}

// Ambil Data Galeri
$galeriList = [];
$res = $conn->query("SELECT * FROM media_galeri ORDER BY id DESC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $galeriList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Galeri Foto — Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard_pro.css?v=3">
    <style>
        .galeri-grid {
            display: grid;
            grid-template-columns: repeat(auto-fill, minmax(250px, 1fr));
            gap: 20px;
            margin-top: 20px;
        }
        .galeri-card {
            background: #fff;
            border-radius: 12px;
            overflow: hidden;
            border: 1px solid var(--dash-border);
            box-shadow: 0 4px 15px rgba(0,0,0,0.03);
            transition: 0.3s ease;
        }
        .galeri-card:hover {
            transform: translateY(-5px);
            box-shadow: 0 8px 25px rgba(0,0,0,0.08);
        }
        .galeri-img {
            width: 100%;
            height: 180px;
            object-fit: cover;
            background-color: #f3f4f6;
            display: flex;
            align-items: center;
            justify-content: center;
            color: #9ca3af;
        }
        .galeri-info {
            padding: 15px;
        }
        .galeri-title {
            font-size: 0.95rem;
            font-weight: 600;
            color: var(--dash-text);
            margin-bottom: 8px;
            display: -webkit-box;
            -webkit-line-clamp: 2;
            -webkit-box-orient: vertical;
            overflow: hidden;
            line-height: 1.4;
        }
        .galeri-meta {
            display: flex;
            justify-content: space-between;
            font-size: 0.75rem;
            color: var(--dash-text-light);
            margin-bottom: 15px;
        }
        .galeri-actions {
            display: flex;
            gap: 10px;
            border-top: 1px solid var(--dash-border);
            padding-top: 12px;
        }
        
        .modal-body.form-layout { gap: 20px; }
        .form-group { display: flex; flex-direction: column; gap: 8px; }
        .form-group label { font-size: 0.85rem; font-weight: 600; color: var(--dash-text); }
        .form-control {
            padding: 10px 15px; border: 1px solid var(--dash-border); border-radius: 8px;
            font-family: inherit; font-size: 0.95rem; outline: none; transition: 0.2s;
        }
        .form-control:focus { border-color: var(--dash-primary); box-shadow: 0 0 0 3px rgba(74, 124, 89, 0.1); }
        select.form-control { cursor: pointer; }
    </style>
</head>
<body>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Form Galeri -->
<div class="modal-overlay" id="formModal">
    <div class="modal-box" style="max-width: 500px;">
        <form method="POST" action="data_galeri.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-images" style="margin-right:8px;"></i><span>Tambah Foto Baru</span></h3>
                <button type="button" class="modal-close" onclick="closeFormModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body form-layout">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Judul Foto</label>
                    <input type="text" name="judul" id="formJudul" class="form-control" required placeholder="Contoh: Kegiatan Shalat Berjamaah">
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" id="formKategori" class="form-control" required>
                        <option value="Kegiatan">Kegiatan</option>
                        <option value="Ibadah">Ibadah</option>
                        <option value="Prestasi">Prestasi</option>
                        <option value="Fasilitas">Fasilitas</option>
                        <option value="Lainnya">Lainnya</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Tahun / Tanggal (Opsional)</label>
                    <input type="text" name="tanggal" id="formTanggal" class="form-control" placeholder="Contoh: 2024 atau 12 April 2024">
                </div>

                <div class="form-group">
                    <label>Pilih File Foto</label>
                    <input type="file" name="src" id="formSrc" class="form-control" accept="image/*">
                    <small style="color:var(--dash-text-light);font-size:0.75rem;">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG, WEBP.</small>
                </div>
            </div>
            <div class="modal-footer" style="gap: 10px;">
                <button type="button" class="btn-action outline" onclick="closeFormModal()">Batal</button>
                <button type="submit" class="btn-action primary"><i class="fas fa-save"></i> Simpan Foto</button>
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
                    <h1>Kelola Galeri Foto</h1>
                    <p>Manajemen foto kegiatan, prestasi, dan fasilitas pesantren.</p>
                </div>
                <button class="btn-action primary" onclick="openFormModal()">
                    <i class="fas fa-plus"></i> Tambah Foto
                </button>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-700);padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                <?php 
                    if($_GET['status']=='added') echo 'Foto berhasil diunggah ke galeri.';
                    elseif($_GET['status']=='updated') echo 'Informasi foto berhasil diperbarui.';
                    elseif($_GET['status']=='deleted') echo 'Foto berhasil dihapus dari sistem.';
                ?>
            </div>
            <?php endif; ?>

            <?php if (count($galeriList) > 0): ?>
                <div class="galeri-grid fade-in-up d1">
                    <?php foreach($galeriList as $g): ?>
                    <div class="galeri-card">
                        <?php if ($g['src'] && file_exists($g['src'])): ?>
                            <img src="<?php echo htmlspecialchars($g['src']); ?>" alt="<?php echo htmlspecialchars($g['judul']); ?>" class="galeri-img">
                        <?php else: ?>
                            <div class="galeri-img"><i class="fas fa-image" style="font-size: 3rem;"></i></div>
                        <?php endif; ?>
                        
                        <div class="galeri-info">
                            <div class="galeri-title"><?php echo htmlspecialchars($g['judul']); ?></div>
                            <div class="galeri-meta">
                                <span><i class="fas fa-tag"></i> <?php echo htmlspecialchars($g['kategori']); ?></span>
                                <span><i class="fas fa-calendar-alt"></i> <?php echo htmlspecialchars($g['tanggal'] ?: '-'); ?></span>
                            </div>
                            <div class="galeri-actions">
                                <button class="btn-action outline" style="flex:1; padding: 6px; font-size: 0.8rem;" onclick='editGaleri(<?php echo htmlspecialchars(json_encode($g)); ?>)'>
                                    <i class="fas fa-pen"></i> Edit
                                </button>
                                <a href="data_galeri.php?delete_id=<?php echo $g['id']; ?>" class="btn-action" style="flex:1; padding: 6px; font-size: 0.8rem; background:#fee2e2; color:#ef4444; text-align:center; border:none;" onclick="return confirm('Yakin ingin menghapus foto ini?');">
                                    <i class="fas fa-trash"></i> Hapus
                                </a>
                            </div>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php else: ?>
                <div class="data-card fade-in-up d1" style="text-align: center; padding: 60px 20px;">
                    <i class="fas fa-images" style="font-size: 4rem; color: var(--dash-border); margin-bottom: 20px;"></i>
                    <h3 style="color: var(--dash-text); margin-bottom: 10px;">Belum ada foto di galeri</h3>
                    <p style="color: var(--dash-text-light);">Silakan klik tombol "Tambah Foto" di pojok kanan atas untuk mulai mengunggah album pesantren.</p>
                </div>
            <?php endif; ?>

        </div>

        <!-- Footer -->
        <?php include_once 'includes/dash_footer.php'; ?>

    </main>
</div>

<script src="js/dashboard.js"></script>
<script>
    const formModal = document.getElementById('formModal');
    
    function openFormModal() {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Tambah Foto Baru';
        document.getElementById('formId').value = '';
        document.getElementById('formJudul').value = '';
        document.getElementById('formKategori').value = 'Kegiatan';
        document.getElementById('formTanggal').value = '<?php echo date('Y'); ?>';
        document.getElementById('formSrc').value = '';
        
        formModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function editGaleri(data) {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Edit Foto Galeri';
        document.getElementById('formId').value = data.id;
        document.getElementById('formJudul').value = data.judul;
        document.getElementById('formKategori').value = data.kategori;
        document.getElementById('formTanggal').value = data.tanggal;
        document.getElementById('formSrc').value = '';
        
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
