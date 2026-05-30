<?php
session_start();
header('Content-Type: application/json');

if (!isset($_SESSION['user_id'])) {
    echo json_encode(['status' => 'error', 'pesan' => 'Unauthorized']);
    exit;
}

// Path koneksi naik 1 level karena file ada di folder api/
require_once __DIR__ . '/../koneksi.php';

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'pesan' => 'Metode tidak diizinkan.']);
    exit;
}

$jadwal_raw = isset($_POST['jadwal_pengumuman']) ? trim($_POST['jadwal_pengumuman']) : '';

if(empty($jadwal_raw)) {
    echo json_encode(['status' => 'error', 'pesan' => 'Jadwal tidak boleh kosong.']);
    exit;
}

// Konversi format datetime-local (YYYY-MM-DDTHH:MM) ke MySQL datetime (YYYY-MM-DD HH:MM:SS)
$jadwal_mysql = date('Y-m-d H:i:s', strtotime($jadwal_raw));

$sql = "UPDATE pengaturan SET nilai_pengaturan = '$jadwal_mysql' WHERE nama_pengaturan = 'jadwal_pengumuman'";
if ($conn->query($sql)) {
    echo json_encode(['status' => 'success', 'pesan' => 'Jadwal pengumuman berhasil disimpan.']);
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Gagal menyimpan jadwal: ' . $conn->error]);
}
?>
