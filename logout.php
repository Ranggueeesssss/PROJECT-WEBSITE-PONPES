<?php
session_start();

if (isset($_SESSION['user_id'])) {
    require_once __DIR__ . '/koneksi.php';
    require_once __DIR__ . '/includes/simple_log.php';
    catat_log($conn, 'Melakukan logout dari sistem');
}

// Hapus semua data sesi
$_SESSION = [];

// Hancurkan sesi
if (ini_get("session.use_cookies")) {
    $params = session_get_cookie_params();
    setcookie(session_name(), '', time() - 42000,
        $params["path"], $params["domain"],
        $params["secure"], $params["httponly"]
    );
}
session_destroy();

// Redirect kembali ke halaman login
header('Location: login.php');
exit;
