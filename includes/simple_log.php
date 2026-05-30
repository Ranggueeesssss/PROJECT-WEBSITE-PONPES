<?php
// includes/simple_log.php
function catat_log($conn, $aksi, $user_id = null, $nama_user = null) {
    // Jika tidak dilempar parameternya, coba ambil dari session (jika ada)
    if ($user_id === null && isset($_SESSION['user_id'])) {
        $user_id = $_SESSION['user_id'];
    }
    if ($nama_user === null && isset($_SESSION['user_nama'])) {
        $nama_user = $_SESSION['user_nama'];
    }
    
    // Jika masih null (misalnya cron/sistem), beri nama 'Sistem'
    if ($nama_user === null) {
        $nama_user = 'Sistem';
    }

    // Bersihkan string untuk mencegah error SQL
    $aksi = $conn->real_escape_string($aksi);
    $nama_user = $conn->real_escape_string($nama_user);
    $user_id_val = $user_id ? (int)$user_id : 'NULL';

    $q = "INSERT INTO log_login (user_id, nama_user, aksi, tanggal) VALUES ($user_id_val, '$nama_user', '$aksi', NOW())";
    $conn->query($q);

    // Auto-prune: Hanya simpan 50 log terbaru agar database tidak bengkak
    $conn->query("DELETE FROM log_login WHERE id NOT IN (SELECT id FROM (SELECT id FROM log_login ORDER BY id DESC LIMIT 50) foo)");
}
?>
