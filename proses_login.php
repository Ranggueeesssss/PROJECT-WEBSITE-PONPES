<?php
// =========================================================================
// PROSES_LOGIN.PHP — Backend Autentikasi Login
// Endpoint ini dipanggil via AJAX dari js/login.js
// =========================================================================

// Hanya terima metode POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    exit(json_encode(['status' => 'error', 'pesan' => 'Metode tidak diizinkan.']));
}

// Wajib kirim header JSON
header('Content-Type: application/json');

// Mulai sesi
session_start();

// Hubungkan ke database
require_once __DIR__ . '/koneksi.php';

// ── Ambil & Bersihkan Input ──────────────────────────────────────────────
$username = isset($_POST['username']) ? trim($_POST['username']) : '';
$password = isset($_POST['password']) ? trim($_POST['password']) : '';

// Validasi dasar di sisi server
if ($username === '' || $password === '') {
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Username dan password wajib diisi.'
    ]);
    exit;
}

// ── Query ke Database (Prepared Statement — aman dari SQL Injection) ──────
$stmt = $conn->prepare(
    "SELECT id, nama, username, password, role FROM user WHERE username = ? LIMIT 1"
);

if (!$stmt) {
    error_log('[LOGIN ERROR] Prepare gagal: ' . $conn->error);
    echo json_encode(['status' => 'error', 'pesan' => 'Terjadi kesalahan server.']);
    exit;
}

$stmt->bind_param('s', $username);
$stmt->execute();
$result = $stmt->get_result();

// ── Cek User Ditemukan ───────────────────────────────────────────────────
if ($result->num_rows === 0) {
    // Username tidak ada — jangan beri tahu user mana yang salah (security)
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Username atau password salah.'
    ]);
    $stmt->close();
    $conn->close();
    exit;
}

$user = $result->fetch_assoc();
$stmt->close();

// ── Verifikasi Password (BCrypt) ─────────────────────────────────────────
if (!password_verify($password, $user['password'])) {
    echo json_encode([
        'status' => 'error',
        'pesan'  => 'Username atau password salah.'
    ]);
    $conn->close();
    exit;
}

// ── Login Berhasil — Simpan ke Sesi ─────────────────────────────────────
// Regenerasi session ID untuk mencegah Session Fixation Attack
session_regenerate_id(true);

$_SESSION['user_id']   = $user['id'];
$_SESSION['user_nama'] = $user['nama'];
$_SESSION['user_role'] = $user['role'];
$_SESSION['login_at']  = time();

// ── Tentukan Halaman Tujuan Berdasarkan Role ─────────────────────────────
$redirectUrl = '';
switch ($user['role']) {
    case 'admin':
        $redirectUrl = 'dashboard.php'; 
        break;
    case 'guru':
        $redirectUrl = 'dashboard.php';  
        break;
    default:
        $redirectUrl = 'Home.html';
}

$conn->close();

echo json_encode([
    'status'   => 'success',
    'pesan'    => 'Login berhasil! Selamat datang, ' . htmlspecialchars($user['nama']) . '.',
    'role'     => $user['role'],
    'redirect' => $redirectUrl
]);
