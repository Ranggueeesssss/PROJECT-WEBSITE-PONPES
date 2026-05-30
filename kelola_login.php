<?php
session_start();

// Cek login
if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';
require_once __DIR__ . '/includes/simple_log.php';

$response = ['status' => '', 'message' => ''];

// Proses POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_guru') {
        if ($_SESSION['user_role'] !== 'admin') {
            $response = ['status' => 'error', 'message' => 'Akses ditolak! Hanya Admin yang bisa menambahkan guru.'];
        } else {
            $nama = $conn->real_escape_string($_POST['nama']);
            $username = $conn->real_escape_string($_POST['username']);
            $password = $_POST['password'];

            $cek = $conn->query("SELECT id FROM user WHERE username='$username'");
            if ($cek->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Username sudah terdaftar!'];
            } else {
                $hashed = password_hash($password, PASSWORD_BCRYPT);
                $q = "INSERT INTO user (nama, username, password, role) VALUES ('$nama', '$username', '$hashed', 'guru')";
                if ($conn->query($q)) {
                    catat_log($conn, "Menambahkan guru baru: $nama");
                    $response = ['status' => 'success', 'message' => 'Akun Guru berhasil ditambahkan.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Gagal menambahkan akun.'];
                }
            }
        }
    } 
    elseif ($action === 'edit_password') {
        $id = (int)$_POST['user_id'];
        
        // Aturan: Hanya boleh mengedit profilnya sendiri
        if ($id !== (int)$_SESSION['user_id']) {
            $response = ['status' => 'error', 'message' => 'Anda hanya bisa mengubah akun milik Anda sendiri!'];
        } else {
            $new_username = $conn->real_escape_string($_POST['new_username']);
            
            // Cek apakah username sudah ada dan bukan miliknya
            $cek = $conn->query("SELECT id FROM user WHERE username='$new_username' AND id != $id");
            if ($cek->num_rows > 0) {
                $response = ['status' => 'error', 'message' => 'Username sudah digunakan. Silakan pilih yang lain!'];
            } else {
                if (!empty($_POST['new_password'])) {
                    $new_password = $_POST['new_password'];
                    $hashed = password_hash($new_password, PASSWORD_BCRYPT);
                    $q = "UPDATE user SET username='$new_username', password='$hashed' WHERE id=$id";
                } else {
                    $q = "UPDATE user SET username='$new_username' WHERE id=$id";
                }

                if ($conn->query($q)) {
                    catat_log($conn, "Memperbarui profil diri sendiri");
                    $response = ['status' => 'success', 'message' => 'Profil berhasil diperbarui.'];
                } else {
                    $response = ['status' => 'error', 'message' => 'Gagal memperbarui profil.'];
                }
            }
        }
    }
    elseif ($action === 'delete_guru') {
        if ($_SESSION['user_role'] !== 'admin') {
            $response = ['status' => 'error', 'message' => 'Akses ditolak! Anda tidak memiliki izin untuk menghapus akun.'];
        } else {
            $id = (int)$_POST['user_id'];

            // Pastikan bukan akun admin dan jumlah guru lebih dari 1
            $q_cek = $conn->query("SELECT role FROM user WHERE id=$id");
            if ($q_cek && $q_cek->num_rows > 0) {
                $row = $q_cek->fetch_assoc();
                if ($row['role'] === 'admin') {
                    $response = ['status' => 'error', 'message' => 'Akun Admin tidak bisa dihapus!'];
                } else {
                    // Cek jumlah guru
                    $q_count = $conn->query("SELECT count(*) as total FROM user WHERE role='guru'");
                    $count = $q_count->fetch_assoc()['total'];
                    if ($count <= 1) {
                        $response = ['status' => 'error', 'message' => 'Tidak bisa menghapus akun Guru karena harus tersisa minimal 1 akun!'];
                    } else {
                        $q_nama_del = $conn->query("SELECT nama FROM user WHERE id=$id");
                        $nama_del = ($q_nama_del && $q_nama_del->num_rows > 0) ? $q_nama_del->fetch_assoc()['nama'] : "ID $id";
                        
                        $conn->query("DELETE FROM user WHERE id=$id");
                        catat_log($conn, "Menghapus akun guru: $nama_del");
                        $response = ['status' => 'success', 'message' => 'Akun Guru berhasil dihapus.'];
                    }
                }
            }
        }
    }
}

// Fetch Users
$users = [];
$q_users = $conn->query("SELECT id, nama, username, role FROM user ORDER BY role DESC, id ASC");
if ($q_users) {
    while ($r = $q_users->fetch_assoc()) {
        $users[] = $r;
    }
}

// Ambil data log jika admin
$logs = [];
if (isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin') {
    $q_log = $conn->query("SELECT * FROM log_login ORDER BY id DESC LIMIT 30");
    if ($q_log) {
        while ($r = $q_log->fetch_assoc()) {
            $logs[] = $r;
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0, maximum-scale=1.0">
    <title>Kelola Login — Ponpes Al-Barokah An-Nur Khumairoh</title>
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link rel="stylesheet" href="css/dashboard/dashboard_pro.css?v=3">
    <link rel="stylesheet" href="css/dashboard/kelola_login.css">
</head>
<body>

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<div class="dash-wrapper">
    <!-- Sidebar -->
    <?php include_once 'includes/dash_sidebar.php'; ?>

    <!-- Main Content -->
    <main class="dash-main" id="dashMain">

        <!-- Header -->
        <?php include_once 'includes/dash_header.php'; ?>

        <div class="dash-content">
            
            <?php if($response['status'] === 'success'): ?>
                <div class="alert alert-success"><i class="fas fa-check-circle"></i> <?php echo $response['message']; ?></div>
            <?php elseif($response['status'] === 'error'): ?>
                <div class="alert alert-error"><i class="fas fa-exclamation-circle"></i> <?php echo $response['message']; ?></div>
            <?php endif; ?>

            <div class="page-title-bar fade-in-up">
                <div class="greeting">
                    <h1>Kelola Akses Login</h1>
                    <p>Manajemen akun admin dan guru untuk mengakses dashboard sistem.</p>
                </div>
                <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin'): ?>
                <div class="action-top" style="display: flex; gap: 10px;">
                    <button class="btn-action" onclick="openModal('modalLogSistem')" style="background:#f59e0b; color:#fff; border:none;">
                        <i class="fas fa-history"></i> Riwayat Sistem
                    </button>
                    <button class="btn-action primary" onclick="openModal('modalAddGuru')">
                        <i class="fas fa-user-plus"></i> Tambah Akun Guru
                    </button>
                </div>
                <?php endif; ?>
            </div>

            <div class="data-card fade-in-up" style="animation-delay: 0.1s;">
                <div class="table-responsive">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>#</th>
                                <th>Nama Lengkap</th>
                                <th>Username</th>
                                <th>Role</th>
                                <th>Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            <?php foreach($users as $idx => $u): ?>
                            <tr>
                                <td><?php echo $idx + 1; ?></td>
                                <td style="font-weight: 600;"><?php echo htmlspecialchars($u['nama']); ?></td>
                                <td><?php echo htmlspecialchars($u['username']); ?></td>
                                <td>
                                    <?php if($u['role'] === 'admin'): ?>
                                        <span class="status-badge" style="background:#fef3c7;color:#d97706;border:1px solid #fcd34d;">Admin</span>
                                    <?php else: ?>
                                        <span class="status-badge" style="background:#dcfce7;color:#16a34a;border:1px solid #bbf7d0;">Guru</span>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <div class="action-btns">
                                        <?php if($u['id'] == $_SESSION['user_id']): ?>
                                            <button class="action-btn" title="Edit Profil" onclick="openEditPassword(<?php echo $u['id']; ?>, '<?php echo addslashes($u['username']); ?>')">
                                                <i class="fas fa-user-edit"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="action-btn" style="opacity:0.3;cursor:not-allowed;" title="Hanya bisa mengedit akun sendiri">
                                                <i class="fas fa-user-edit"></i>
                                            </button>
                                        <?php endif; ?>

                                        <?php if(isset($_SESSION['user_role']) && $_SESSION['user_role'] === 'admin' && $u['role'] === 'guru'): ?>
                                            <button class="action-btn delete" title="Hapus Akun" onclick="confirmDelete(<?php echo $u['id']; ?>, '<?php echo addslashes($u['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="action-btn delete" style="opacity:0.3;cursor:not-allowed;" title="Tidak bisa dihapus">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php endif; ?>
                                    </div>
                                </td>
                            </tr>
                            <?php endforeach; ?>
                        </tbody>
                    </table>
                </div>
            </div>

        </div><!-- /dash-content -->

        <?php include_once 'includes/dash_footer.php'; ?>

    </main>
</div>

<!-- Modal Tambah Guru -->
<div class="modal-overlay" id="modalAddGuru">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-user-plus"></i> Tambah Akun Guru</h3>
            <button class="modal-close" onclick="closeModal('modalAddGuru')"><i class="fas fa-times"></i></button>
        </div>
        <form action="kelola_login.php" method="POST">
            <input type="hidden" name="action" value="add_guru">
            <div class="modal-body">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="nama" class="form-control" required placeholder="Contoh: Ustadz Fulan">
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="username" class="form-control" required placeholder="Untuk login">
                </div>
                <div class="form-group">
                    <label>Password</label>
                    <input type="password" name="password" class="form-control" required minlength="6" placeholder="Minimal 6 karakter">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action" onclick="closeModal('modalAddGuru')">Batal</button>
                <button type="submit" class="btn-action primary"><i class="fas fa-save"></i> Simpan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Edit Password -->
<div class="modal-overlay" id="modalEditPassword">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit Profil</h3>
            <button class="modal-close" onclick="closeModal('modalEditPassword')"><i class="fas fa-times"></i></button>
        </div>
        <form action="kelola_login.php" method="POST">
            <input type="hidden" name="action" value="edit_password">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="edit_username" name="new_username" class="form-control" required placeholder="Masukkan username">
                </div>
                <div class="form-group">
                    <label>Password Baru (Opsional)</label>
                    <input type="password" name="new_password" class="form-control" minlength="6" placeholder="Kosongkan jika tidak ingin mengubah">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action" onclick="closeModal('modalEditPassword')">Batal</button>
                <button type="submit" class="btn-action gold"><i class="fas fa-sync"></i> Perbarui</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Hapus -->
<div class="modal-overlay" id="modalDelete">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-exclamation-triangle" style="color:#dc2626;"></i> Konfirmasi Hapus</h3>
            <button class="modal-close" onclick="closeModal('modalDelete')"><i class="fas fa-times"></i></button>
        </div>
        <form action="kelola_login.php" method="POST">
            <input type="hidden" name="action" value="delete_guru">
            <input type="hidden" name="user_id" id="delete_user_id">
            <div class="modal-body text-center" style="padding: 20px;">
                <p>Apakah Anda yakin ingin menghapus akun guru <b><span id="delete_username"></span></b>?</p>
                <p style="color:var(--dash-text-light);font-size:0.85rem;margin-top:10px;">Tindakan ini tidak bisa dibatalkan.</p>
            </div>
            <div class="modal-footer" style="justify-content: center;">
                <button type="button" class="btn-action" onclick="closeModal('modalDelete')">Batal</button>
                <button type="submit" class="btn-action" style="background:#dc2626;color:#fff;"><i class="fas fa-trash"></i> Hapus Akun</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Log Sistem -->
<div class="modal-overlay" id="modalLogSistem">
    <div class="modal-box" style="max-width: 500px;">
        <div class="modal-header" style="justify-content: flex-start; gap: 10px;">
            <h3 style="margin: 0; display: flex; align-items: center;"><i class="fas fa-history" style="margin-right: 8px;"></i> Riwayat Sistem</h3>
            <button class="modal-close" onclick="closeModal('modalLogSistem')" style="margin-left: auto;"><i class="fas fa-times"></i></button>
        </div>
        <div class="modal-body" style="max-height: 400px; overflow-y: auto; padding: 20px;">
            <?php if (empty($logs)): ?>
                <div style="text-align:center; color:var(--dash-text-light); padding: 30px;">
                    <i class="fas fa-history" style="font-size:2rem; color:#cbd5e1; margin-bottom:10px; display:block;"></i>
                    Belum ada riwayat tercatat.
                </div>
            <?php else: ?>
                <div style="position:relative; border-left:2px solid #e2e8f0; padding-left:20px; margin-left:8px;">
                    <?php foreach ($logs as $l): ?>
                    <div style="margin-bottom: 20px; position:relative;">
                        <span style="position:absolute; left:-29px; top:3px; width:16px; height:16px; background:#fff; border:3px solid var(--dash-primary); border-radius:50%;"></span>
                        <div style="font-size: 0.8rem; color: var(--dash-text-light); margin-bottom: 4px;">
                            <i class="fas fa-clock"></i> <?php echo date('d M Y, H:i', strtotime($l['tanggal'])); ?>
                        </div>
                        <div style="font-size: 0.95rem; color: #1e293b;">
                            <strong><?php echo htmlspecialchars($l['nama_user']); ?></strong> <?php echo htmlspecialchars($l['aksi']); ?>
                        </div>
                    </div>
                    <?php endforeach; ?>
                </div>
            <?php endif; ?>
        </div>
    </div>
</div>

<script src="js/dashboard/dashboard.js"></script>
<script src="js/dashboard/kelola_login.js"></script>
</body>
</html>
