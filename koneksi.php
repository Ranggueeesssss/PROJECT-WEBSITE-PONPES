<?php
// FILE KONEKSI DATABASE — db_ponpes
// Sertakan file ini di semua file PHP yang membutuhkan koneksi database
// menggunakan:
//   require_once __DIR__ . '/koneksi.php';
//
// Jika file PHP kamu berada di sub-folder (misal: admin/halaman.php):
//   require_once __DIR__ . '/../koneksi.php';


// --- Cegah akses langsung ke file ini melalui browser ---
// Pola "defined sentinel": file lain harus mendefinisikan konstanta ini
// sebelum memanggil require_once, ATAU kita cukup cek script yang berjalan.
if (realpath($_SERVER['SCRIPT_FILENAME']) === __FILE__) {
    http_response_code(403);
    exit('Akses langsung tidak diizinkan.');
}

// --- Cegah definisi ulang jika di-include lebih dari sekali ---
if (defined('DB_CONNECTED')) {
    return; // Sudah terhubung, lewati
}
define('DB_CONNECTED', true);

// --- Konfigurasi Database ---
define('DB_HOST', 'localhost');
define('DB_USER', 'root');
define('DB_PASS', '');
define('DB_NAME', 'db_ponpes'); // Sesuaikan dengan nama database di phpMyAdmin

// --- Buat Koneksi ---
$conn = new mysqli(DB_HOST, DB_USER, DB_PASS, DB_NAME);

// --- Cek Koneksi ---
if ($conn->connect_error) {
    // Catat detail error di server log, jangan tampilkan ke user
    error_log('[DB ERROR] Koneksi Database Gagal: ' . $conn->connect_error);
    http_response_code(500);
    // Kembalikan JSON jika request dari AJAX, atau teks biasa jika dari browser
    $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) &&
              strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';
    if ($isAjax) {
        header('Content-Type: application/json');
        die(json_encode(['status' => 'error', 'pesan' => 'Koneksi ke database gagal.']));
    }
    die('<p style="font-family:sans-serif;color:#c00;padding:20px;">Koneksi ke database gagal. Silakan coba beberapa saat lagi.</p>');
}

// --- Set Charset ke UTF-8 agar tidak ada masalah karakter ---
$conn->set_charset('utf8mb4');
