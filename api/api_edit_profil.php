<?php
session_start();
if (!isset($_SESSION['user_id'])) {
    header('Location: ../login.php');
    exit;
}

require_once __DIR__ . '/../koneksi.php';
require_once __DIR__ . '/../includes/simple_log.php';

if ($_SERVER['REQUEST_METHOD'] === 'POST' && isset($_POST['action']) && $_POST['action'] === 'edit_profil_global') {
    $id = (int)$_SESSION['user_id'];
    $new_nama = $conn->real_escape_string($_POST['new_nama']);
    $new_username = $conn->real_escape_string($_POST['new_username']);
    
    // Cek apakah username sudah digunakan orang lain
    $cek = $conn->query("SELECT id FROM user WHERE username='$new_username' AND id != $id");
    if ($cek->num_rows > 0) {
        $_SESSION['profil_msg'] = 'Username sudah digunakan. Silakan pilih yang lain!';
        $_SESSION['profil_status'] = 'error';
    } else {
        if (!empty($_POST['new_password'])) {
            $new_password = $_POST['new_password'];
            $hashed = password_hash($new_password, PASSWORD_BCRYPT);
            $q = "UPDATE user SET nama='$new_nama', username='$new_username', password='$hashed' WHERE id=$id";
        } else {
            $q = "UPDATE user SET nama='$new_nama', username='$new_username' WHERE id=$id";
        }

        if ($conn->query($q)) {
            // Perbarui session
            $_SESSION['user_nama'] = $new_nama;
            catat_log($conn, "Memperbarui profil akun sendiri");
            $_SESSION['profil_msg'] = 'Profil berhasil diperbarui.';
            $_SESSION['profil_status'] = 'success';
        } else {
            $_SESSION['profil_msg'] = 'Gagal memperbarui profil.';
            $_SESSION['profil_status'] = 'error';
        }
    }
}

// Redirect back to the previous page
$referer = isset($_SERVER['HTTP_REFERER']) ? $_SERVER['HTTP_REFERER'] : '../dashboard.php';
header('Location: ' . $referer);
exit;
?>
