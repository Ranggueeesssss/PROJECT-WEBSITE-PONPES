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
    
    // Get nama & foto to delete
    $q_foto = $conn->query("SELECT nama, foto FROM profil_pengajar WHERE id = $id_del");
    $nama_del_p = 'ID ' . $id_del;
    if ($q_foto && $q_foto->num_rows > 0) {
        $r_foto = $q_foto->fetch_assoc();
        $nama_del_p = $r_foto['nama'];
        if ($r_foto['foto'] && file_exists('uploads/' . $r_foto['foto'])) {
            unlink('uploads/' . $r_foto['foto']);
        }
    }
    
    $conn->query("DELETE FROM profil_pengajar WHERE id = $id_del");
    catat_log($conn, "Menghapus data pengajar: $nama_del_p");
    header('Location: data_pengajar.php?status=deleted');
    exit;
}

// Proses Tambah / Edit Data
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $id        = isset($_POST['id']) ? (int)$_POST['id'] : 0;
    $nama      = $conn->real_escape_string($_POST['nama']);
    $kategori  = $conn->real_escape_string($_POST['kategori']);
    $jabatan   = $conn->real_escape_string($_POST['jabatan']);
    $deskripsi = $conn->real_escape_string($_POST['deskripsi']);
    
    $foto_name = null;
    
    // Handle File Upload
    if (isset($_FILES['foto']) && $_FILES['foto']['error'] == 0) {
        $allowed = ['jpg', 'jpeg', 'png', 'webp'];
        $filename = $_FILES['foto']['name'];
        $ext = strtolower(pathinfo($filename, PATHINFO_EXTENSION));
        
        if (in_array($ext, $allowed)) {
            if (!is_dir('uploads')) mkdir('uploads', 0777, true);
            $new_name = 'pengajar_' . time() . '.' . $ext;
            if (move_uploaded_file($_FILES['foto']['tmp_name'], 'uploads/' . $new_name)) {
                $foto_name = $new_name;
            }
        }
    }

    if ($id > 0) {
        // Update
        if ($foto_name) {
            // Delete old photo
            $q_old = $conn->query("SELECT foto FROM profil_pengajar WHERE id = $id");
            if ($r_old = $q_old->fetch_assoc()) {
                if ($r_old['foto'] && file_exists('uploads/' . $r_old['foto'])) {
                    unlink('uploads/' . $r_old['foto']);
                }
            }
            $sql = "UPDATE profil_pengajar SET nama='$nama', kategori='$kategori', jabatan='$jabatan', deskripsi='$deskripsi', foto='$foto_name' WHERE id=$id";
        } elseif (isset($_POST['hapus_foto']) && $_POST['hapus_foto'] == '1') {
            $q_old = $conn->query("SELECT foto FROM profil_pengajar WHERE id = $id");
            if ($r_old = $q_old->fetch_assoc()) {
                if ($r_old['foto'] && file_exists('uploads/' . $r_old['foto'])) {
                    unlink('uploads/' . $r_old['foto']);
                }
            }
            $sql = "UPDATE profil_pengajar SET nama='$nama', kategori='$kategori', jabatan='$jabatan', deskripsi='$deskripsi', foto=NULL WHERE id=$id";
        } else {
            $sql = "UPDATE profil_pengajar SET nama='$nama', kategori='$kategori', jabatan='$jabatan', deskripsi='$deskripsi' WHERE id=$id";
        }
        $conn->query($sql);
        catat_log($conn, "Memperbarui data pengajar: $nama");
        header('Location: data_pengajar.php?status=updated');
    } else {
        // Insert
        $sql = "INSERT INTO profil_pengajar (nama, kategori, jabatan, deskripsi, foto) VALUES ('$nama', '$kategori', '$jabatan', '$deskripsi', '$foto_name')";
        $conn->query($sql);
        catat_log($conn, "Menambahkan pengajar baru: $nama");
        header('Location: data_pengajar.php?status=added');
    }
    exit;
}

// Ambil Data Pengajar
$pengajarList = [];
$res = $conn->query("SELECT * FROM profil_pengajar ORDER BY kategori DESC, id ASC");
if ($res) {
    while($row = $res->fetch_assoc()) {
        $pengajarList[] = $row;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Kelola Data Pengajar — Dashboard Guru</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard/dashboard_pro.css?v=3">
    <style>
        .teacher-avatar {
            width: 50px; height: 50px; border-radius: 12px; object-fit: cover;
            background: var(--dash-body-bg); border: 1px solid var(--dash-border);
        }
    </style>
</head>
<body>

<!-- Mobile Sidebar Overlay -->
<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Form Pengajar -->
<div class="modal-overlay" id="formModal">
    <div class="modal-box" style="max-width: 600px;">
        <form method="POST" action="data_pengajar.php" enctype="multipart/form-data">
            <div class="modal-header">
                <h3 id="modalTitle"><i class="fas fa-chalkboard-teacher" style="margin-right:8px;"></i><span>Tambah Pengajar</span></h3>
                <button type="button" class="modal-close" onclick="closeFormModal()"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body form-layout">
                <input type="hidden" name="id" id="formId" value="">
                
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" id="formNama" class="form-control" required placeholder="Contoh: Ust. Fulan, S.Pd.">
                </div>

                <div class="form-group">
                    <label>Kategori</label>
                    <select name="kategori" id="formKategori" class="form-control" required>
                        <option value="pimpinan">Pimpinan Utama / Pengurus</option>
                        <option value="guru">Dewan Guru / Asatidz</option>
                    </select>
                </div>

                <div class="form-group">
                    <label>Jabatan / Bidang Studi</label>
                    <input type="text" name="jabatan" id="formJabatan" class="form-control" required placeholder="Contoh: Waka Kurikulum atau Guru Bahasa Arab">
                </div>

                <div class="form-group">
                    <label>Deskripsi Tambahan</label>
                    <textarea name="deskripsi" id="formDeskripsi" class="form-control" placeholder="Tuliskan detail singkat mengenai fokus pengajaran atau latar belakang..."></textarea>
                </div>

                <div class="form-group" id="hapusFotoGroup" style="display:none; flex-direction:row; align-items:center; gap:8px; margin-top:-5px; margin-bottom:10px;">
                    <input type="checkbox" name="hapus_foto" id="hapusFoto" value="1" style="cursor:pointer; width:16px; height:16px; accent-color:var(--dash-danger);">
                    <label for="hapusFoto" style="cursor:pointer; color:var(--dash-danger); margin:0;">Hapus foto yang ada (Kembali ke default)</label>
                </div>

                <div class="form-group">
                    <label>Foto (Opsional)</label>
                    <input type="file" name="foto" id="formFoto" class="form-control" accept="image/*" onchange="if(this.value) document.getElementById('hapusFoto').checked = false;">
                    <small style="color:var(--dash-text-light);font-size:0.75rem;">Biarkan kosong jika tidak ingin mengubah foto. Format: JPG, PNG, WEBP.</small>
                </div>
            </div>
            <div class="modal-footer" style="gap: 10px;">
                <button type="button" class="btn-action outline" onclick="closeFormModal()">Batal</button>
                <button type="submit" class="btn-action primary"><i class="fas fa-save"></i> Simpan Data</button>
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
                    <h1>Kelola Data Pengajar</h1>
                    <p>Manajemen profil pimpinan, dewan asatidz, dan tenaga pendidik.</p>
                </div>
                <button class="btn-action primary" onclick="openFormModal()">
                    <i class="fas fa-user-plus"></i> Tambah Pengajar
                </button>
            </div>

            <?php if(isset($_GET['status'])): ?>
            <div style="background:var(--green-50);border:1px solid var(--green-200);color:var(--green-700);padding:15px 20px;border-radius:12px;margin-bottom:25px;display:flex;align-items:center;gap:10px;font-weight:500;">
                <i class="fas fa-check-circle" style="font-size:1.2rem;"></i>
                <?php 
                    if($_GET['status']=='added') echo 'Data pengajar berhasil ditambahkan.';
                    elseif($_GET['status']=='updated') echo 'Data pengajar berhasil diperbarui.';
                    elseif($_GET['status']=='deleted') echo 'Data pengajar berhasil dihapus.';
                ?>
            </div>
            <?php endif; ?>

            <div class="data-card fade-in-up d1">
                <div class="data-card-header">
                    <h2><span class="card-icon"><i class="fas fa-users"></i></span> Daftar Pimpinan & Asatidz</h2>
                </div>
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th style="width: 80px;">Foto</th>
                                <th>Nama Lengkap</th>
                                <th>Kategori</th>
                                <th>Jabatan / Studi</th>
                                <th style="width: 120px; text-align: center;">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php if (count($pengajarList) > 0): ?>
                                <?php foreach($pengajarList as $p): ?>
                                <tr>
                                    <td>
                                        <?php if ($p['foto'] && file_exists('uploads/'.$p['foto'])): ?>
                                            <img src="uploads/<?php echo $p['foto']; ?>" alt="Foto" class="teacher-avatar">
                                        <?php else: ?>
                                            <img src="https://ui-avatars.com/api/?name=<?php echo urlencode($p['nama']); ?>&background=e2e8e5&color=2d4538" alt="Foto" class="teacher-avatar">
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 700; color: var(--dash-text);"><?php echo htmlspecialchars($p['nama']); ?></div>
                                        <div style="font-size: 0.8rem; color: var(--dash-text-light); margin-top: 3px; max-width: 300px; white-space: nowrap; overflow: hidden; text-overflow: ellipsis;">
                                            <?php echo htmlspecialchars($p['deskripsi']); ?>
                                        </div>
                                    </td>
                                    <td>
                                        <?php if ($p['kategori'] === 'pimpinan'): ?>
                                            <span class="status-badge" style="background:#fef3c7; color:#d97706;">Pimpinan Utama</span>
                                        <?php else: ?>
                                            <span class="status-badge" style="background:var(--green-50); color:var(--green-600);">Dewan Guru</span>
                                        <?php endif; ?>
                                    </td>
                                    <td>
                                        <div style="font-weight: 600; font-size: 0.85rem;"><?php echo htmlspecialchars($p['jabatan']); ?></div>
                                    </td>
                                    <td>
                                        <div class="action-btns" style="justify-content: center;">
                                            <button class="action-btn" title="Edit" onclick="editPengajar(<?php echo htmlspecialchars(json_encode($p)); ?>)">
                                                <i class="fas fa-pen"></i>
                                            </button>
                                            <a href="data_pengajar.php?delete_id=<?php echo $p['id']; ?>" class="action-btn delete" title="Hapus">
                                                <i class="fas fa-trash"></i>
                                            </a>
                                        </div>
                                    </td>
                                </tr>
                                <?php endforeach; ?>
                            <?php else: ?>
                                <tr>
                                    <td colspan="5" style="text-align: center; padding: 40px; color: var(--dash-text-light);">
                                        <i class="fas fa-box-open" style="font-size: 3rem; margin-bottom: 15px; opacity: 0.5;"></i><br>
                                        Belum ada data pengajar yang ditambahkan.
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
        document.getElementById('modalTitle').querySelector('span').textContent = 'Tambah Pengajar';
        document.getElementById('formId').value = '';
        document.getElementById('formNama').value = '';
        document.getElementById('formKategori').value = 'guru';
        document.getElementById('formJabatan').value = '';
        document.getElementById('formDeskripsi').value = '';
        document.getElementById('hapusFotoGroup').style.display = 'none';
        document.getElementById('hapusFoto').checked = false;
        
        formModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }

    function editPengajar(data) {
        document.getElementById('modalTitle').querySelector('span').textContent = 'Edit Pengajar';
        document.getElementById('formId').value = data.id;
        document.getElementById('formNama').value = data.nama;
        document.getElementById('formKategori').value = data.kategori;
        document.getElementById('formJabatan').value = data.jabatan;
        document.getElementById('formDeskripsi').value = data.deskripsi;
        document.getElementById('hapusFotoGroup').style.display = data.foto ? 'flex' : 'none';
        document.getElementById('hapusFoto').checked = false;
        
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
