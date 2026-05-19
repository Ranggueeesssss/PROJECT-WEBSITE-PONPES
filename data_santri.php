<?php
session_start();

if (!isset($_SESSION['user_id'])) {
    header('Location: login.php');
    exit;
}

require_once __DIR__ . '/koneksi.php';

// --- AUTO-SYNC: Masukkan santri yang 'Lolos' pendaftaran ke tabel induk santri ---
// Menggunakan single query untuk performa yang jauh lebih baik (menghindari N+1 Query problem)
$syncQuery = "
    INSERT INTO data_santri (pendaftaran_id, nama_lengkap, jenjang, nomor_hp, status_santri)
    SELECT p.id, p.nama_lengkap, p.jenjang_pendaftaran, p.nomor_handphone, 'Baru'
    FROM pendaftaran p
    LEFT JOIN data_santri s ON p.id = s.pendaftaran_id
    WHERE p.status = 'Lolos' AND s.id IS NULL
";
$conn->query($syncQuery);

// --- PROSES AKSI (POST) ---
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    
    // 1. Tambah Kolom Baru
    if (isset($_POST['action']) && $_POST['action'] === 'add_column') {
        $col_name = $conn->real_escape_string(trim($_POST['col_name']));
        if (!empty($col_name)) {
            $conn->query("INSERT IGNORE INTO santri_custom_cols (col_name) VALUES ('$col_name')");
        }
        header("Location: data_santri.php?status=col_added");
        exit;
    }
    
    // 2. Hapus Kolom
    if (isset($_POST['action']) && $_POST['action'] === 'del_column') {
        $col_id = (int)$_POST['col_id'];
        $conn->query("DELETE FROM santri_custom_cols WHERE id = $col_id");
        header("Location: data_santri.php?status=col_deleted");
        exit;
    }

    // 3. Update Data Santri (Termasuk Custom Fields & Status)
    if (isset($_POST['action']) && $_POST['action'] === 'update_data') {
        $santri_id = (int)$_POST['santri_id'];
        $status_santri = $conn->real_escape_string($_POST['status_santri']);
        
        // Ambil data JSON lama
        $resOld = $conn->query("SELECT custom_data FROM data_santri WHERE id = $santri_id");
        $oldData = [];
        if ($resOld && $resOld->num_rows > 0) {
            $row = $resOld->fetch_assoc();
            $oldData = json_decode($row['custom_data'] ?: '{}', true) ?: [];
        }
        
        // Update dengan data baru dari form
        if (isset($_POST['custom_fields']) && is_array($_POST['custom_fields'])) {
            foreach ($_POST['custom_fields'] as $key => $val) {
                $oldData[$key] = $val;
            }
        }
        
        $newJson = json_encode($oldData);
        $stmtUpdate = $conn->prepare("UPDATE data_santri SET status_santri = ?, custom_data = ? WHERE id = ?");
        $stmtUpdate->bind_param("ssi", $status_santri, $newJson, $santri_id);
        $stmtUpdate->execute();
        
        header("Location: data_santri.php?status=data_updated");
        exit;
    }

    // 4. Hapus Santri
    if (isset($_POST['action']) && $_POST['action'] === 'delete_santri') {
        $santri_id = (int)$_POST['santri_id'];
        $conn->query("DELETE FROM data_santri WHERE id = $santri_id");
        header("Location: data_santri.php?status=deleted");
        exit;
    }
}

// --- AMBIL DATA ---

// 1. Ambil daftar kolom kustom
$customCols = [];
$resCols = $conn->query("SELECT * FROM santri_custom_cols ORDER BY id ASC");
if ($resCols) {
    while($r = $resCols->fetch_assoc()) {
        $customCols[] = $r;
    }
}

// 2. Ambil data santri (Urutkan yang 'Baru' di atas, lalu berdasarkan nama)
$santriList = [];
$resSantri = $conn->query("SELECT * FROM data_santri ORDER BY FIELD(status_santri, 'Baru', 'Aktif', 'Alumni') ASC, nama_lengkap ASC");
if ($resSantri) {
    while($r = $resSantri->fetch_assoc()) {
        $r['parsed_custom'] = json_decode($r['custom_data'] ?: '{}', true) ?: [];
        $santriList[] = $r;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Data Induk Santri — Dashboard</title>
    
    <!-- Fonts & Icons -->
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css">
    <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
    
    <!-- Base Styles -->
    <link rel="stylesheet" href="css/dashboard_pro.css?v=3">
    <!-- Module Styles -->
    <link rel="stylesheet" href="css/data_santri.css">
</head>
<body style="font-family: 'Plus Jakarta Sans', sans-serif; background: #f9fafb;">

<div class="sidebar-overlay" id="sidebarOverlay"></div>

<!-- Modal Edit Data Santri -->
<div class="modal-overlay" id="modalEditData">
    <div class="modal-box" style="max-width: 550px; border-radius: 20px;">
        <form method="POST" action="data_santri.php">
            <input type="hidden" name="action" value="update_data">
            <input type="hidden" name="santri_id" id="editSantriId">
            
            <div class="modal-header" style="border:none; padding: 25px 25px 10px;">
                <h3 style="font-weight: 800; color: #1e4d2b;"><i class="fas fa-user-edit me-2"></i> Update Data Santri</h3>
                <button type="button" class="modal-close" onclick="closeEditSantri()"><i class="fas fa-times"></i></button>
            </div>
            
            <div class="modal-body" style="padding: 10px 25px 25px; max-height: 70vh; overflow-y: auto;">
                <div style="background: #f0fdf4; padding: 20px; border-radius: 15px; margin-bottom: 24px; border: 1px solid #dcfce7;">
                    <div id="editNama" style="font-size: 1.25rem; font-weight: 800; color: #166534;">Nama Santri</div>
                    <div id="editJenjang" style="font-size: 0.9rem; color: #15803d; margin-top: 4px; font-weight: 600;">Jenjang</div>
                </div>

                <div class="form-group mb-4">
                    <label class="form-label">Status Santri</label>
                    <select name="status_santri" id="editStatus" class="form-control" style="border-radius: 12px; height: 48px;">
                        <option value="Baru">Baru (Belum Aktif)</option>
                        <option value="Aktif">Aktif (Berdiam di Ponpes)</option>
                        <option value="Alumni">Alumni</option>
                    </select>
                </div>

                <div style="display: flex; align-items: center; gap: 10px; margin-bottom: 15px;">
                    <hr style="flex: 1; border: 0; border-top: 1px solid #e5e7eb;">
                    <span style="font-size: 0.7rem; font-weight: 800; color: #9ca3af; text-transform: uppercase; letter-spacing: 1px;">Data Tambahan</span>
                    <hr style="flex: 1; border: 0; border-top: 1px solid #e5e7eb;">
                </div>

                <div id="customFieldsContainer">
                    <!-- Fields generated via JS -->
                </div>
                
                <div id="emptyFieldsMsg" style="text-align: center; padding: 20px; color: #9ca3af; font-style: italic; display: none;">
                    Belum ada kolom kustom. Klik "Tambah Kolom" di halaman utama.
                </div>
            </div>

            <div class="modal-footer" style="border:none; padding: 0 25px 25px; background: transparent;">
                <button type="button" class="btn-premium" style="background: #f1f5f9; color: #475569; width: 100%; justify-content: center;" onclick="closeEditSantri()">Batal</button>
                <button type="submit" class="btn-premium" style="background: #1e4d2b; color: white; width: 100%; justify-content: center; margin-top: 10px;">Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<!-- Modal Tambah Kolom -->
<div class="modal-overlay" id="modalAddCol">
    <div class="modal-box" style="max-width: 400px; border-radius: 20px;">
        <form method="POST" action="data_santri.php">
            <input type="hidden" name="action" value="add_column">
            <div class="modal-header" style="border:none; padding: 25px 25px 10px;">
                <h3 style="font-weight: 800;"><i class="fas fa-plus-square me-2"></i> Tambah Kolom</h3>
                <button type="button" class="modal-close" onclick="document.getElementById('modalAddCol').classList.remove('active')"><i class="fas fa-times"></i></button>
            </div>
            <div class="modal-body" style="padding: 25px;">
                <div class="form-group">
                    <label class="form-label">Nama Kolom</label>
                    <input type="text" name="col_name" class="form-control" placeholder="Contoh: Kamar, Kelas, No Kamar" required style="border-radius: 12px; height: 48px;">
                </div>
            </div>
            <div class="modal-footer" style="border:none; padding: 0 25px 25px;">
                <button type="submit" class="btn-premium" style="background: #1e4d2b; color: white; width: 100%; justify-content: center;">Simpan Kolom</button>
            </div>
        </form>
    </div>
</div>

<div class="dash-wrapper">
    <?php include_once 'includes/dash_sidebar.php'; ?>

    <main class="dash-main" id="dashMain">
        <?php include_once 'includes/dash_header.php'; ?>

        <div class="dash-content">
            <div class="santri-container">
                
                <!-- Page Header -->
                <div class="page-header">
                    <div class="header-info">
                        <h1>Data Induk Santri</h1>
                        <p>Manajemen data santri aktif dan alumni Ponpes Al-Barokah.</p>
                    </div>
                    <div class="toolbar-actions">
                        <button class="btn-premium btn-add-column" onclick="document.getElementById('modalAddCol').classList.add('active')">
                            <i class="fas fa-plus-circle"></i> Tambah Kolom
                        </button>
                    </div>
                </div>

                <!-- Feedback Messages -->
                <?php if(isset($_GET['status'])): ?>
                <div class="alert fade-in" style="background: #f0fdf4; border: 1px solid #bbf7d0; color: #166534; padding: 16px 20px; border-radius: 12px; margin-bottom: 24px; display: flex; align-items: center; gap: 12px;">
                    <i class="fas fa-check-circle" style="font-size: 1.25rem;"></i>
                    <span style="font-weight: 600;">
                        <?php 
                            if($_GET['status']=='col_added') echo 'Kolom baru berhasil ditambahkan.';
                            elseif($_GET['status']=='col_deleted') echo 'Kolom berhasil dihapus.';
                            elseif($_GET['status']=='data_updated') echo 'Data santri diperbarui.';
                            elseif($_GET['status']=='deleted') echo 'Data santri telah dihapus.';
                        ?>
                    </span>
                </div>
                <?php endif; ?>

                <!-- Stats Bar -->
                <div style="background: white; border-radius: 12px; padding: 15px 20px; border: 1px solid #e5e7eb; margin-bottom: 20px; display: flex; align-items: center; justify-content: space-between;">
                    <div style="display: flex; align-items: center; gap: 12px;">
                        <div style="width: 40px; height: 40px; background: #e8f5e9; color: #1e4d2b; border-radius: 10px; display: flex; align-items: center; justify-content: center; font-size: 1.2rem;">
                            <i class="fas fa-users"></i>
                        </div>
                        <div>
                            <div style="font-size: 0.75rem; color: #6b7280; font-weight: 700; text-transform: uppercase;">Total Santri</div>
                            <div style="font-size: 1.1rem; font-weight: 800; color: #1e4d2b;"><?php echo count($santriList); ?> Orang</div>
                        </div>
                    </div>
                    <div style="font-size: 0.85rem; color: #6b7280;">
                        <span style="margin-right: 15px;"><i class="fas fa-circle" style="color: #f59e0b; font-size: 0.6rem; margin-right: 5px;"></i> <?php echo count(array_filter($santriList, fn($s) => $s['status_santri'] == 'Baru')); ?> Baru</span>
                        <span><i class="fas fa-circle" style="color: #10b981; font-size: 0.6rem; margin-right: 5px;"></i> <?php echo count(array_filter($santriList, fn($s) => $s['status_santri'] == 'Aktif')); ?> Aktif</span>
                    </div>
                </div>

                <!-- Table Card -->
                <div class="santri-card">
                    <div class="table-responsive">
                        <table class="santri-table">
                            <thead>
                                <tr>
                                    <th style="width: 60px; text-align: center;">No</th>
                                    <th>Nama Santri</th>
                                    <th>Status</th>
                                    <th>Jenjang</th>
                                    <th>No. HP</th>
                                    <!-- Dynamic Columns -->
                                    <?php foreach($customCols as $col): ?>
                                    <th>
                                        <div class="col-header-wrap">
                                            <span><?php echo htmlspecialchars($col['col_name']); ?></span>
                                            <i class="fas fa-times-circle btn-remove-col" onclick="deleteColumn(<?php echo $col['id']; ?>, '<?php echo addslashes($col['col_name']); ?>')" title="Hapus Kolom"></i>
                                        </div>
                                    </th>
                                    <?php endforeach; ?>
                                    <th style="width: 100px; text-align: center;">Aksi</th>
                                </tr>
                            </thead>
                            <tbody>
                                <?php if(count($santriList) > 0): ?>
                                    <?php foreach($santriList as $index => $s): ?>
                                    <tr <?php echo $s['status_santri'] == 'Baru' ? 'style="background-color: #fffbeb;"' : ''; ?>>
                                        <td style="text-align: center; color: #9ca3af; font-weight: 700;"><?php echo $index + 1; ?></td>
                                        <td>
                                            <div style="font-weight: 700; color: #1f2937;">
                                                <?php if($s['status_santri'] == 'Baru'): ?>
                                                    <span style="color: #d97706; margin-right: 5px;" title="Santri Baru"><i class="fas fa-star"></i></span>
                                                <?php endif; ?>
                                                <?php echo htmlspecialchars($s['nama_lengkap']); ?>
                                            </div>
                                            <div style="font-size: 0.75rem; color: #9ca3af; margin-top: 2px;">ID: #ST<?php echo str_pad($s['id'], 4, '0', STR_PAD_LEFT); ?></div>
                                        </td>
                                        <td>
                                            <span class="badge-status status-<?php echo strtolower($s['status_santri']); ?>">
                                                <i class="fas fa-<?php echo $s['status_santri'] == 'Baru' ? 'clock' : ($s['status_santri'] == 'Aktif' ? 'check-circle' : 'graduation-cap'); ?>"></i>
                                                <?php echo $s['status_santri']; ?>
                                            </span>
                                        </td>
                                        <td><span class="jenjang-badge"><?php echo htmlspecialchars($s['jenjang']); ?></span></td>
                                        <td style="font-family: monospace; font-weight: 600; color: #475569;"><?php echo htmlspecialchars($s['nomor_hp'] ?: '-'); ?></td>
                                        
                                        <!-- Dynamic Values -->
                                        <?php foreach($customCols as $col): ?>
                                        <?php $val = $s['parsed_custom'][$col['col_name']] ?? ''; ?>
                                        <td style="font-weight: 500;">
                                            <?php if($val === ''): ?>
                                                <span style="color: #d1d5db; font-style: italic; font-size: 0.85rem;">- Kosong -</span>
                                            <?php else: ?>
                                                <?php echo htmlspecialchars($val); ?>
                                            <?php endif; ?>
                                        </td>
                                        <?php endforeach; ?>

                                        <td>
                                            <div class="action-group" style="justify-content: center;">
                                                <button class="btn-icon" title="Edit & Lengkapi" onclick='openEditSantri(<?php echo htmlspecialchars(json_encode($s), ENT_QUOTES, "UTF-8"); ?>, <?php echo htmlspecialchars(json_encode($customCols), ENT_QUOTES, "UTF-8"); ?>)'>
                                                    <i class="fas fa-edit"></i>
                                                </button>
                                                <button class="btn-icon delete" title="Hapus" onclick="deleteSantri(<?php echo $s['id']; ?>, '<?php echo addslashes($s['nama_lengkap']); ?>')">
                                                    <i class="fas fa-trash"></i>
                                                </button>
                                            </div>
                                        </td>
                                    </tr>
                                    <?php endforeach; ?>
                                <?php else: ?>
                                    <tr>
                                        <td colspan="<?php echo 6 + count($customCols); ?>">
                                            <div class="empty-state">
                                                <i class="fas fa-user-graduate"></i>
                                                <h3>Belum Ada Data Santri</h3>
                                                <p>Santri yang dinyatakan Lolos pada pendaftaran akan muncul di sini secara otomatis.</p>
                                            </div>
                                        </td>
                                    </tr>
                                <?php endif; ?>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div style="margin-top: 30px; text-align: center; color: #9ca3af; font-size: 0.85rem;">
                    <i class="fas fa-info-circle"></i> Klik tombol <strong>Edit</strong> untuk mengubah status santri menjadi <strong>Aktif</strong> jika mereka sudah mulai menetap di pondok.
                </div>

            </div>
        </div>
        <?php include_once 'includes/dash_footer.php'; ?>
    </main>
</div>

<!-- Scripts -->
<script src="js/dashboard.js"></script>
<script src="js/data_santri.js"></script>

</body>
</html>
