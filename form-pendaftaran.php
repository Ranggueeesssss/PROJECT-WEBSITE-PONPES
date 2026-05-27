<?php
// KONEKSI DATABASE — diambil dari file koneksi terpusat
require_once __DIR__ . '/koneksi.php';

$status_pesan = "";
$tipe_pesan = "";

if ($_SERVER["REQUEST_METHOD"] == "POST") {
    // Ambil Data Teks
    $nama_lengkap        = $conn->real_escape_string($_POST['nama_lengkap']);
    $tempat_lahir        = $conn->real_escape_string($_POST['tempat_lahir']);
    $tanggal_lahir       = $conn->real_escape_string($_POST['tanggal_lahir']);
    $alamat_lengkap      = $conn->real_escape_string($_POST['alamat_lengkap']);
    $nomor_handphone     = $conn->real_escape_string($_POST['nomor_handphone']);
    $jenjang_pendaftaran = isset($_POST['jenjang_pendaftaran']) ? $conn->real_escape_string($_POST['jenjang_pendaftaran']) : '';
    
    // Tahu Ponpes (Bisa dari checkbox array atau option, sesuai form kita pakai radio atau checkbox, kita asumsikan array dari checkbox)
    $tahu_ponpes_arr = isset($_POST['tahu_ponpes']) ? $_POST['tahu_ponpes'] : [];
    
    // Jika ada input "Yang lain:"
    if (isset($_POST['tahu_ponpes_lainnya']) && trim($_POST['tahu_ponpes_lainnya']) != "") {
        $tahu_ponpes_arr[] = $conn->real_escape_string(trim($_POST['tahu_ponpes_lainnya']));
    }
    
    $tahu_ponpes_str = implode(", ", $tahu_ponpes_arr);
    $nama_informan   = $conn->real_escape_string($_POST['nama_informan']);
    
    // UPLOAD FILE
    $upload_dir = "uploads/";
    if (!is_dir($upload_dir)) {
        mkdir($upload_dir, 0777, true);
    }
    
    function uploadFile($fileInput, $dir) {
        if (isset($_FILES[$fileInput]) && $_FILES[$fileInput]['error'] == 0) {
            if ($_FILES[$fileInput]['size'] > 10 * 1024 * 1024) {
                return "ERROR_SIZE";
            }
            // Kombinasi uniqid + mt_rand agar nama file selalu unik
            // meski beberapa file diupload dalam waktu bersamaan
            $ext      = strtolower(pathinfo($_FILES[$fileInput]['name'], PATHINFO_EXTENSION));
            $safeName = uniqid(mt_rand(), true) . '.' . $ext;
            $targetPath = $dir . $safeName;
            if (move_uploaded_file($_FILES[$fileInput]['tmp_name'], $targetPath)) {
                return $safeName;
            }
        }
        return "";
    }
    
    $file_ijazah    = uploadFile('file_ijazah',    $upload_dir);
    $file_kk        = uploadFile('file_kk',        $upload_dir);
    $file_akta      = uploadFile('file_akta',      $upload_dir);
    $file_ktp_ortu  = uploadFile('file_ktp_ortu',  $upload_dir);
    $file_surat_tjm = uploadFile('file_surat_tjm', $upload_dir);
    
    $upload_errors = [$file_ijazah, $file_kk, $file_akta, $file_ktp_ortu, $file_surat_tjm];
    
    if (in_array("ERROR_SIZE", $upload_errors)) {
        $status_pesan = "Gagal: Ukuran file maksimal 10MB.";
        $tipe_pesan = "error";
    } elseif ($file_kk === "" || $file_ktp_ortu === "" || $file_surat_tjm === "") {
        $status_pesan = "Gagal: File Kartu Keluarga, KTP Orang Tua/Wali, dan Surat Tanggung Jawab Mutlak wajib diunggah.";
        $tipe_pesan = "error";
    } else {
        // Query Insert
        $sql = "INSERT INTO pendaftaran 
                (nama_lengkap, tempat_lahir, tanggal_lahir, alamat_lengkap, nomor_handphone, jenjang_pendaftaran, tahu_ponpes, nama_informan, file_ijazah, file_kk, file_akta, file_ktp_ortu, file_surat_tjm) 
                VALUES 
                ('$nama_lengkap', '$tempat_lahir', '$tanggal_lahir', '$alamat_lengkap', '$nomor_handphone', '$jenjang_pendaftaran', '$tahu_ponpes_str', '$nama_informan', '$file_ijazah', '$file_kk', '$file_akta', '$file_ktp_ortu', '$file_surat_tjm')";
                
        if ($conn->query($sql) === TRUE) {
            $status_pesan = "Pendaftaran berhasil dikirim! Kami akan segera menghubungi Anda.";
            $tipe_pesan = "success";
        } else {
            $status_pesan = "Terjadi kesalahan: " . $conn->error;
            $tipe_pesan = "error";
        }
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <title>Formulir PPDB — Ponpes Al-Barokah</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/shared/base.css" />  
  <link rel="stylesheet" href="css/shared/navbar.css" />  
  <link rel="stylesheet" href="css/shared/header.css" />  
  <link rel="stylesheet" href="css/shared/footer.css" />  
  <link rel="stylesheet" href="css/website/form-pendaftaran.css" /> 
</head>
<body>

<!-- Header dari komponen js -->
<?php include 'includes/header.php'; ?>

<!-- MAIN FORM -->
<main class="form-wrapper">
    <div class="form-container">
        
        <?php if($status_pesan != ""): ?>
            <?php if($tipe_pesan == 'success'): ?>
            <!-- SUCCESS CARD — Pendaftaran Berhasil -->
            <div class="success-card">

                <!-- Ikon Centang Animasi -->
                <div class="success-icon-wrapper">
                    <div class="success-icon">
                        <i class="fas fa-check"></i>
                    </div>
                    <div class="success-pulse"></div>
                </div>

                <!-- Judul & Deskripsi -->
                <h2 class="success-title">Pendaftaran Berhasil Dikirim!</h2>
                <p class="success-subtitle">
                    Terima kasih telah mendaftarkan putra/putri Anda ke <strong>Ponpes Al-Barokah An-Nur Khumairoh</strong>.
                    Data Anda telah kami terima dan sedang dalam proses verifikasi oleh panitia.
                </p>

                <!-- Info Steps -->
                <div class="success-steps">
                    <div class="success-step">
                        <div class="success-step__icon"><i class="fas fa-clipboard-check"></i></div>
                        <div class="success-step__text">
                            <strong>Verifikasi Berkas</strong>
                            <span>Panitia akan memeriksa kelengkapan berkas yang telah Anda unggah.</span>
                        </div>
                    </div>
                    <div class="success-step">
                        <div class="success-step__icon"><i class="fas fa-bell"></i></div>
                        <div class="success-step__text">
                            <strong>Pantau Pengumuman Secara Berkala</strong>
                            <span>Cek halaman pengumuman kami secara rutin untuk melihat perkembangan status pendaftaran Anda.</span>
                        </div>
                    </div>
                    <div class="success-step">
                        <div class="success-step__icon"><i class="fas fa-phone-alt"></i></div>
                        <div class="success-step__text">
                            <strong>Dihubungi oleh Panitia</strong>
                            <span>Panitia akan menghubungi Anda melalui nomor WhatsApp yang telah didaftarkan.</span>
                        </div>
                    </div>
                </div>

                <!-- Kontak Panitia -->
                <div class="success-contact">
                    <p class="success-contact__label"><i class="fas fa-headset"></i> Butuh bantuan? Hubungi Panitia:</p>
                    <a href="https://wa.me/6285230574234" target="_blank" rel="noopener noreferrer" class="success-wa-btn">
                        <i class="fab fa-whatsapp"></i>
                        <span>0852-3057-4234 &mdash; Panitia PPDB</span>
                    </a>
                </div>

                <!-- Reminder -->
                <div class="success-reminder">
                    <i class="fas fa-info-circle"></i>
                    <span>Pengumuman hasil seleksi dapat dicek di halaman <a href="pengumuman.php">Pengumuman Santri Baru</a>. Pantau secara berkala agar tidak ketinggalan informasi.</span>
                </div>

                <!-- Tombol Aksi -->
                <div class="success-actions">
                    <a href="pengumuman.php" class="btn-success-secondary">
                        <i class="fas fa-search"></i> Cek Pengumuman
                    </a>
                    <a href="pendaftaran.php" class="btn-success-primary">
                        <i class="fas fa-home"></i> Kembali ke Panduan
                    </a>
                </div>

            </div>
            <?php else: ?>
            <!-- ERROR ALERT -->
            <div class="alert-box error">
                <p><?php echo $status_pesan; ?></p>
            </div>
            <?php endif; ?>
        <?php endif; ?>


        <?php if($tipe_pesan != 'success'): ?>
        
        <!-- Header Image & Title -->
        <div class="modern-header-card">
            <i class="fas fa-mosque"></i>
            <h1>Pendaftaran Santri Baru</h1>
            <p>Tahun Ajaran 2026/2027 PP. Al-Barokah An-Nur Khumairoh</p>
        </div>

        <div class="modern-form-card">
            
            <form action="form-pendaftaran.php" method="POST" enctype="multipart/form-data" id="ppdbForm">
                
                <!-- Stepper -->
                <div class="form-stepper">
                    <div class="step-indicator active" id="indicator-1">
                        <div class="step-number">1</div>
                        <span>Biodata</span>
                    </div>
                    <div class="step-line"></div>
                    <div class="step-indicator" id="indicator-2">
                        <div class="step-number">2</div>
                        <span>Berkas</span>
                    </div>
                </div>
                
                <span class="req-text">* Menunjukkan pertanyaan yang wajib diisi</span>

                <!-- STEP 1: BIODATA -->
                <div class="form-step active" id="step-1">
                    
                    <div class="input-row">
                        <!-- Input: Nama Lengkap -->
                        <div class="input-group full-width">
                            <label class="input-label">Nama Lengkap Santri <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="nama_lengkap" class="modern-input" placeholder="Masukkan nama lengkap" required>
                                <i class="fas fa-user input-icon"></i>
                            </div>
                        </div>

                        <!-- Input: Tempat Lahir -->
                        <div class="input-group">
                            <label class="input-label">Tempat Lahir <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="tempat_lahir" class="modern-input" placeholder="Kabupaten/Kota" required>
                                <i class="fas fa-map-marker-alt input-icon"></i>
                            </div>
                        </div>

                        <!-- Input: Tanggal Lahir -->
                        <div class="input-group">
                            <label class="input-label">Tanggal Lahir <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="date" name="tanggal_lahir" class="modern-input" required>
                                <i class="fas fa-calendar-alt input-icon"></i>
                            </div>
                        </div>

                        <!-- Input: Alamat -->
                        <div class="input-group full-width">
                            <label class="input-label">Alamat Lengkap (Sesuai KK) <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="alamat_lengkap" class="modern-input" placeholder="Sertakan RT/RW, Desa, Kecamatan" required>
                                <i class="fas fa-home input-icon"></i>
                            </div>
                        </div>

                        <!-- Input: No HP -->
                        <div class="input-group full-width">
                            <label class="input-label">Nomor Handphone (WhatsApp aktif) <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="number" name="nomor_handphone" class="modern-input" placeholder="Contoh: 08123456789" required>
                                <i class="fas fa-phone-alt input-icon"></i>
                            </div>
                        </div>

                        <!-- Input: Jenjang -->
                        <div class="input-group full-width">
                            <label class="input-label">Jenjang Pendaftaran <span class="req">*</span></label>
                            <div class="options-grid">
                                <label class="option-card">
                                    <input type="radio" name="jenjang_pendaftaran" value="R.A (Raudhatul Athfal)" required>
                                    <span class="custom-radio"></span>
                                    <span class="option-text">R.A (Raudhatul Athfal)</span>
                                </label>
                                <label class="option-card">
                                    <input type="radio" name="jenjang_pendaftaran" value="M.I (Madrasah Ibtidaiyah)">
                                    <span class="custom-radio"></span>
                                    <span class="option-text">M.I (Ibtidaiyah)</span>
                                </label>
                                <label class="option-card">
                                    <input type="radio" name="jenjang_pendaftaran" value="MTs (Madrasah Tsanawiyah)">
                                    <span class="custom-radio"></span>
                                    <span class="option-text">MTs (Tsanawiyah)</span>
                                </label>
                                <label class="option-card">
                                    <input type="radio" name="jenjang_pendaftaran" value="M.A (Madrasah Aliyah)">
                                    <span class="custom-radio"></span>
                                    <span class="option-text">M.A (Aliyah)</span>
                                </label>
                                <label class="option-card">
                                    <input type="radio" name="jenjang_pendaftaran" value="MADIN (Madrasah Diniyah)">
                                    <span class="custom-radio"></span>
                                    <span class="option-text">MADIN (Diniyah)</span>
                                </label>
                            </div>
                        </div>

                        <!-- Input: Tahu Ponpes -->
                        <div class="input-group full-width">
                            <label class="input-label">Tahu Informasi Pondok Pesantren Dari <span class="req">*</span></label>
                            <div class="options-grid">
                                <label class="option-card">
                                    <input type="checkbox" name="tahu_ponpes[]" value="Teman">
                                    <span class="custom-checkbox"></span>
                                    <span class="option-text">Teman</span>
                                </label>
                                <label class="option-card">
                                    <input type="checkbox" name="tahu_ponpes[]" value="Saudara">
                                    <span class="custom-checkbox"></span>
                                    <span class="option-text">Saudara</span>
                                </label>
                                <label class="option-card">
                                    <input type="checkbox" name="tahu_ponpes[]" value="Guru">
                                    <span class="custom-checkbox"></span>
                                    <span class="option-text">Guru</span>
                                </label>
                                <label class="option-card">
                                    <input type="checkbox" name="tahu_ponpes[]" value="Wali Santri">
                                    <span class="custom-checkbox"></span>
                                    <span class="option-text">Wali Santri</span>
                                </label>
                                <div style="grid-column: 1 / -1;">
                                    <label class="option-card" style="margin-bottom: 5px;">
                                        <input type="checkbox" id="check-lainnya" value="Yang lain:">
                                        <span class="custom-checkbox"></span>
                                        <span class="option-text">Lainnya</span>
                                    </label>
                                    <div class="lainnya-input-wrapper">
                                        <input type="text" name="tahu_ponpes_lainnya" id="input-lainnya" class="lainnya-input" placeholder="Sebutkan (opsional jika memilih lainnya)" disabled>
                                    </div>
                                </div>
                            </div>
                        </div>

                        <!-- Input: Nama Informan -->
                        <div class="input-group full-width">
                            <label class="input-label">Nama Informan (Orang yang memberi tahu) <span class="req">*</span></label>
                            <div class="input-wrapper">
                                <input type="text" name="nama_informan" class="modern-input" placeholder="Masukkan nama informan" required>
                                <i class="fas fa-info-circle input-icon"></i>
                            </div>
                        </div>
                    </div>

                    <!-- Next Button -->
                    <div class="form-actions">
                        <button type="reset" class="btn-modern btn-prev" style="margin-right: auto; margin-left: 0;">
                            <i class="fas fa-redo"></i> Reset
                        </button>
                        <button type="button" class="btn-modern btn-next" id="btnNext">
                            Selanjutnya <i class="fas fa-arrow-right"></i>
                        </button>
                    </div>

                </div>

                <!-- STEP 2: BERKAS -->
                <div class="form-step" id="step-2">
                    
                    <div class="section-header">
                        <h2>Kelengkapan Berkas</h2>
                        <p>Silakan unggah dokumen yang diperlukan. Format: JPG, PNG, PDF (Maks 10MB)</p>
                    </div>

                    <!-- File 1 -->
                    <div class="input-group">
                        <label class="input-label">1. Scan Ijazah / SKL (Jika Ada)</label>
                        <label class="file-upload-card" for="file_ijazah">
                            <i class="fas fa-file-invoice"></i>
                            <div class="file-upload-title">Klik untuk unggah file</div>
                            <div class="file-upload-desc">Ijazah / Surat Keterangan Lulus</div>
                            <input type="file" name="file_ijazah" id="file_ijazah" class="file-input" accept=".pdf,image/*">
                            <span class="file-name-display" id="name_ijazah">Belum ada file dipilih</span>
                        </label>
                    </div>

                    <!-- File 2 -->
                    <div class="input-group">
                        <label class="input-label">2. Scan Kartu Keluarga <span class="req">*</span></label>
                        <label class="file-upload-card" for="file_kk">
                            <i class="fas fa-users"></i>
                            <div class="file-upload-title">Klik untuk unggah file</div>
                            <div class="file-upload-desc">Kartu Keluarga terbaru</div>
                            <input type="file" name="file_kk" id="file_kk" class="file-input" accept=".pdf,image/*" required>
                            <span class="file-name-display" id="name_kk">Belum ada file dipilih</span>
                        </label>
                    </div>

                    <!-- File 3 -->
                    <div class="input-group">
                        <label class="input-label">3. Scan Akta Kelahiran (Jika Ada)</label>
                        <label class="file-upload-card" for="file_akta">
                            <i class="fas fa-child"></i>
                            <div class="file-upload-title">Klik untuk unggah file</div>
                            <div class="file-upload-desc">Akta Kelahiran Calon Santri</div>
                            <input type="file" name="file_akta" id="file_akta" class="file-input" accept=".pdf,image/*">
                            <span class="file-name-display" id="name_akta">Belum ada file dipilih</span>
                        </label>
                    </div>

                    <!-- File 4 -->
                    <div class="input-group">
                        <label class="input-label">4. Scan KTP Kedua Orang Tua / Wali Calon Peserta Didik <span class="req">*</span></label>
                        <label class="file-upload-card" for="file_ktp_ortu">
                            <i class="fas fa-id-card"></i>
                            <div class="file-upload-title">Klik untuk unggah file</div>
                            <div class="file-upload-desc">KTP Ayah & Ibu atau Wali Calon Santri — PDF atau Gambar</div>
                            <input type="file" name="file_ktp_ortu" id="file_ktp_ortu" class="file-input" accept=".pdf,image/*" required>
                            <span class="file-name-display" id="name_ktp_ortu">Belum ada file dipilih</span>
                        </label>
                    </div>

                    <!-- File 5 -->
                    <div class="input-group">
                        <label class="input-label">5. Scan Surat Tanggung Jawab Mutlak Orang Tua <span class="req">*</span></label>
                        <label class="file-upload-card" for="file_surat_tjm">
                            <i class="fas fa-file-signature"></i>
                            <div class="file-upload-title">Klik untuk unggah file</div>
                            <div class="file-upload-desc">Surat Tanggung Jawab Mutlak bertanda tangan Orang Tua / Wali — PDF atau Gambar</div>
                            <input type="file" name="file_surat_tjm" id="file_surat_tjm" class="file-input" accept=".pdf,image/*" required>
                            <span class="file-name-display" id="name_surat_tjm">Belum ada file dipilih</span>
                        </label>
                    </div>

                    <!-- Action Buttons -->
                    <div class="form-actions" style="justify-content: space-between;">
                        <button type="button" class="btn-modern btn-prev" id="btnPrev">
                            <i class="fas fa-arrow-left"></i> Kembali
                        </button>
                        <button type="submit" class="btn-modern btn-submit" id="btnSubmit">
                            Kirim Pendaftaran <i class="fas fa-paper-plane"></i>
                        </button>
                    </div>

                </div>

            </form>
        </div>
        <?php endif; ?>

    </div>
</main>

<!-- Footer -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->

<script src="js/shared/navbar.js"></script>
<script src="js/website/form-pendaftaran.js?v=3"></script>

</body>
</html>
