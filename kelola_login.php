<?php
session_start();

// Cek login & Hak Akses Admin
if (!isset($_SESSION['user_id']) || $_SESSION['user_role'] !== 'admin') {
    header('Location: dashboard.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';

$response = ['status' => '', 'message' => ''];

// Proses POST Actions
if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action'])) {
    $action = $_POST['action'];

    if ($action === 'add_guru') {
        $nama = $conn->real_escape_string($_POST['nama']);
        $username = $conn->real_escape_string($_POST['username']);
        $password = $_POST['password'];

        // Cek username
        $cek = $conn->query("SELECT id FROM user WHERE username='$username'");
        if ($cek->num_rows > 0) {
            $response = ['status' => 'error', 'message' => 'Username sudah terdaftar!'];
        } else {
            $hashed = password_hash($password, PASSWORD_BCRYPT);
            $q = "INSERT INTO user (nama, username, password, role) VALUES ('$nama', '$username', '$hashed', 'guru')";
            if ($conn->query($q)) {
                $response = ['status' => 'success', 'message' => 'Akun Guru berhasil ditambahkan.'];
            } else {
                $response = ['status' => 'error', 'message' => 'Gagal menambahkan akun.'];
            }
        }
    } 
    elseif ($action === 'edit_password') {
        $id = (int)$_POST['user_id'];
        $new_password = $_POST['new_password'];
        $hashed = password_hash($new_password, PASSWORD_BCRYPT);

        // Hanya boleh edit password akun yang valid
        $q = "UPDATE user SET password='$hashed' WHERE id=$id";
        if ($conn->query($q)) {
            $response = ['status' => 'success', 'message' => 'Password berhasil diperbarui.'];
        } else {
            $response = ['status' => 'error', 'message' => 'Gagal memperbarui password.'];
        }
    }
    elseif ($action === 'delete_guru') {
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
                    $conn->query("DELETE FROM user WHERE id=$id");
                    $response = ['status' => 'success', 'message' => 'Akun Guru berhasil dihapus.'];
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
                <div class="action-top">
                    <button class="btn-action primary" onclick="openModal('modalAddGuru')">
                        <i class="fas fa-user-plus"></i> Tambah Akun Guru
                    </button>
                </div>
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
                                        <button class="action-btn" title="Edit Password" onclick="openEditPassword(<?php echo $u['id']; ?>, '<?php echo addslashes($u['username']); ?>')">
                                            <i class="fas fa-key"></i>
                                        </button>
                                        <?php if($u['role'] !== 'admin'): ?>
                                            <button class="action-btn delete" title="Hapus Akun" onclick="confirmDelete(<?php echo $u['id']; ?>, '<?php echo addslashes($u['username']); ?>')">
                                                <i class="fas fa-trash"></i>
                                            </button>
                                        <?php else: ?>
                                            <button class="action-btn" style="opacity:0.3;cursor:not-allowed;" title="Admin tidak bisa dihapus">
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
            <h3><i class="fas fa-key"></i> Edit Password</h3>
            <button class="modal-close" onclick="closeModal('modalEditPassword')"><i class="fas fa-times"></i></button>
        </div>
        <form action="kelola_login.php" method="POST">
            <input type="hidden" name="action" value="edit_password">
            <input type="hidden" name="user_id" id="edit_user_id">
            <div class="modal-body">
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" id="edit_username" class="form-control" readonly style="background:#f1f5f9;">
                </div>
                <div class="form-group">
                    <label>Password Baru</label>
                    <input type="password" name="new_password" class="form-control" required minlength="6" placeholder="Masukkan password baru">
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

<script src="js/dashboard/dashboard.js"></script>
<script src="js/dashboard/kelola_login.js"></script>
</body>
</html>
