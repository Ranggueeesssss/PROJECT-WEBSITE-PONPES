<?php
header('Content-Type: application/json');

// Path koneksi naik 1 level karena file ada di folder api/
require_once __DIR__ . '/../koneksi.php';

// Validasi Metode Request
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['status' => 'error', 'pesan' => 'Metode request tidak valid.']);
    exit;
}

$nik = isset($_POST['nik']) ? trim($_POST['nik']) : '';
$tgl_lahir = isset($_POST['tgl_lahir']) ? trim($_POST['tgl_lahir']) : '';

if (empty($nik) || empty($tgl_lahir)) {
    echo json_encode(['status' => 'error', 'pesan' => 'Data NIK dan Tanggal Lahir tidak lengkap.']);
    exit;
}

// 1. Cek Waktu Penjadwalan Pengumuman
$sql_jadwal = "SELECT nilai_pengaturan FROM pengaturan WHERE nama_pengaturan = 'jadwal_pengumuman' LIMIT 1";
$res_jadwal = $conn->query($sql_jadwal);

if ($res_jadwal && $res_jadwal->num_rows > 0) {
    $row_jadwal = $res_jadwal->fetch_assoc();
    $jadwal_str = $row_jadwal['nilai_pengaturan']; // Format: YYYY-MM-DD HH:MM:SS
    
    date_default_timezone_set('Asia/Jakarta');
    $waktu_sekarang = new DateTime();
    $waktu_jadwal = new DateTime($jadwal_str);
    
    if ($waktu_sekarang < $waktu_jadwal) {
        $waktu_tampil = $waktu_jadwal->format('d F Y \j\a\m H:i');
        $months = [
            'January' => 'Januari', 'February' => 'Februari', 'March' => 'Maret', 
            'April' => 'April', 'May' => 'Mei', 'June' => 'Juni', 
            'July' => 'Juli', 'August' => 'Agustus', 'September' => 'September', 
            'October' => 'Oktober', 'November' => 'November', 'December' => 'Desember'
        ];
        $waktu_tampil = strtr($waktu_tampil, $months);
        
        echo json_encode([
            'status' => 'pending_waktu',
            'pesan' => 'Pengumuman belum dibuka. Silakan cek kembali pada ' . $waktu_tampil . ' WIB.'
        ]);
        exit;
    }
}

// 2. Waktu sudah lewat (pengumuman dibuka), maka cek data kelulusan santri
// menggunakan prepared Statement untuk keamanan (mencegah SQL Injection) dan Index Database untuk kecepatan
$stmt = $conn->prepare("SELECT nama_lengkap, status FROM pendaftaran WHERE nik = ? AND tanggal_lahir = ? LIMIT 1");

if ($stmt) {
    $stmt->bind_param("ss", $nik, $tgl_lahir);
    $stmt->execute();
    $res = $stmt->get_result();
    
    if ($res->num_rows > 0) {
        $row = $res->fetch_assoc();
        $status_kelulusan = $row['status']; // 'Lolos', 'Tidak Lolos', atau 'Pending'
        
        if ($status_kelulusan === 'Lolos') {
            echo json_encode([
                'status' => 'success',
                'is_lolos' => true,
                'nama' => $row['nama_lengkap'],
                'pesan' => 'Selamat, Anda dinyatakan Lulus seleksi.'
            ]);
        } elseif ($status_kelulusan === 'Tidak Lolos') {
            echo json_encode([
                'status' => 'success',
                'is_lolos' => false,
                'nama' => $row['nama_lengkap'],
                'pesan' => 'Mohon maaf, Anda belum memenuhi kriteria Lulus.'
            ]);
        } else {
            // Kasus jika status belum dieksekusi oleh guru (masih Pending di dashboard)
            echo json_encode([
                'status' => 'error',
                'pesan' => 'Status pendaftaran Anda saat ini masih dalam proses peninjauan (Pending). Harap menunggu.'
            ]);
        }
    } else {
        // Data nama & tgl lahir tidak cocok/tidak ada di database
        echo json_encode([
            'status' => 'not_found',
            'pesan' => 'Data tidak ditemukan. Pastikan penulisan NIK dan Tanggal Lahir persis seperti saat mendaftar.'
        ]);
    }
    $stmt->close();
} else {
    echo json_encode(['status' => 'error', 'pesan' => 'Terjadi kesalahan pada sistem. Silakan lapor panitia.']);
}
?>
