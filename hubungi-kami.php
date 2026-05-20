<?php
require_once 'koneksi.php';

$success_msg = '';
$error_msg = '';

if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $nama    = $conn->real_escape_string($_POST['nama']);
    $email   = $conn->real_escape_string($_POST['email']);
    $telepon = $conn->real_escape_string($_POST['telepon']);
    $subjek  = $conn->real_escape_string($_POST['subjek']);
    $pesan   = $conn->real_escape_string($_POST['pesan']);

    $sql = "INSERT INTO pesan_masuk (nama, email, no_hp, subjek, pesan) 
            VALUES ('$nama', '$email', '$telepon', '$subjek', '$pesan')";

    if ($conn->query($sql) === TRUE) {
        $success_msg = "Pesan Anda berhasil dikirim! Admin kami akan segera membalasnya.";
    } else {
        $error_msg = "Terjadi kesalahan saat mengirim pesan. Silakan coba lagi.";
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Hubungi Kami — Ponpes Al-Barokah An-Nur Khumairoh. Layanan informasi dan pendaftaran santri baru." />
  <title>Hubungi Kami — Ponpes Al-Barokah An-Nur Khumairoh</title>

  <!-- Google Fonts -->
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700;800&display=swap" rel="stylesheet" />
  <!-- Font Awesome -->
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />

  <!-- Stylesheets -->
  <link rel="stylesheet" href="css/base.css"    />  
  <link rel="stylesheet" href="css/navbar.css"  />  
  <link rel="stylesheet" href="css/header.css"  />  
  <link rel="stylesheet" href="css/footer.css"  />  
  <link rel="stylesheet" href="css/hubungi-kami.css" /> 
</head>
<body>

<!-- SITE HEADER — di-inject oleh components.js -->
<?php include 'includes/header.php'; ?>

<!-- PAGE HERO -->
<div class="page-hero">
  <div class="page-hero__inner">
    <!-- Breadcrumb -->
    <nav class="breadcrumb" aria-label="Breadcrumb">
      <a href="Home.html"><i class="fas fa-home"></i> Home</a>
      <span class="sep"><i class="fas fa-chevron-right"></i></span>
      <span class="current">Hubungi Kami</span>
    </nav>

    <h1 class="page-hero__title">Hubungi Kami</h1>
    <p class="page-hero__sub">Al Barokah</p>

    <div class="page-hero__deco" aria-hidden="true">
      <div class="page-hero__deco-line"></div>
      <div class="page-hero__deco-dots">
        <span></span><span></span><span></span>
      </div>
    </div>
  </div>
</div><!-- /page-hero -->

<!-- CONTACT WRAPPER -->
<main class="contact-main">
  <div class="contact-container">
    
    <div class="contact-grid">
      
      <!-- Kolom Kiri: Informasi Kontak -->
      <div class="contact-info-card fade-up-element">
        <div class="contact-header">
          <h2>Informasi Kontak</h2>
          <p>Silakan hubungi kami melalui kontak di bawah ini untuk pertanyaan lebih lanjut atau informasi pendaftaran.</p>
        </div>

        <ul class="contact-list">
          <li>
            <div class="icon-wrap"><i class="fas fa-map-marker-alt"></i></div>
            <div class="info-content">
              <strong>Alamat Lengkap</strong>
              <p>Gumuk Kerang - Ajung, Kec. Ajung<br>Kabupaten Jember - Jawa Timur</p>
            </div>
          </li>
          <li>
            <div class="icon-wrap"><i class="fas fa-phone-alt"></i></div>
            <div class="info-content">
              <strong>Telepon / WhatsApp</strong>
              <p>(0852) 3057 4234</p>
            </div>
          </li>
          <li>
            <div class="icon-wrap"><i class="fas fa-envelope"></i></div>
            <div class="info-content">
              <strong>Email Layanan</strong>
              <p>info@albarokah.sch.id</p>
            </div>
          </li>
        </ul>

        <div class="social-connect">
          <h3>Terhubung Bersama Kami</h3>
          <div class="social-links">
            <a href="#" class="soc-link youtube" aria-label="Youtube"><i class="fab fa-youtube"></i></a>
            <a href="#" class="soc-link instagram" aria-label="Instagram"><i class="fab fa-instagram"></i></a>
            <a href="https://wa.me/6285230574234" target="_blank" class="soc-link whatsapp" aria-label="WhatsApp"><i class="fab fa-whatsapp"></i></a>
          </div>
        </div>
      </div>

      <!-- Kolom Kanan: Form Kirim Pesan -->
      <div class="contact-form-card fade-up-element" style="animation-delay: 0.2s;">
        <div class="contact-header">
          <h2>Kirim Pesan</h2>
          <p>Memiliki pertanyaan spesifik? Kirimkan pesan melalui form di bawah ini dan admin kami akan membalas secepatnya.</p>
        </div>

        <?php if($success_msg): ?>
            <div style="background-color: #dcfce7; color: #16a34a; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                <i class="fas fa-check-circle"></i> <?php echo $success_msg; ?>
            </div>
        <?php endif; ?>

        <?php if($error_msg): ?>
            <div style="background-color: #fee2e2; color: #dc2626; padding: 15px; border-radius: 8px; margin-bottom: 20px; font-weight: 500;">
                <i class="fas fa-exclamation-circle"></i> <?php echo $error_msg; ?>
            </div>
        <?php endif; ?>

        <form id="contactForm" class="contact-form" method="POST" action="hubungi-kami.php">
          <div class="form-group">
            <label for="nama"><i class="fas fa-user"></i> Nama Lengkap</label>
            <input type="text" id="nama" name="nama" placeholder="Masukkan nama Anda" required>
          </div>
          
          <div class="form-row">
            <div class="form-group">
              <label for="email"><i class="fas fa-envelope"></i> Email</label>
              <input type="email" id="email" name="email" placeholder="alamat@email.com" required>
            </div>
            <div class="form-group">
              <label for="telepon"><i class="fas fa-phone"></i> No. HP / WA</label>
              <input type="tel" id="telepon" name="telepon" placeholder="Contoh: 0812..." required>
            </div>
          </div>

          <div class="form-group">
            <label for="subjek"><i class="fas fa-tag"></i> Subjek Pertanyaan</label>
            <select id="subjek" name="subjek" required>
              <option value="" disabled selected>Pilih Kategori...</option>
              <option value="Info Pendaftaran Santri Baru">Info Pendaftaran Santri Baru</option>
              <option value="Info Biaya Pendidikan">Info Biaya Pendidikan</option>
              <option value="Program & Kurikulum">Program & Kurikulum</option>
              <option value="Lainnya">Lainnya</option>
            </select>
          </div>

          <div class="form-group">
            <label for="pesan"><i class="fas fa-comment-alt"></i> Isi Pesan</label>
            <textarea id="pesan" name="pesan" rows="4" placeholder="Tulis pesan Anda di sini..." required></textarea>
          </div>

          <button type="submit" class="btn-submit">
            <span>Kirim Pesan</span> <i class="fas fa-paper-plane"></i>
          </button>
        </form>
      </div>

    </div><!-- /contact-grid -->

    <!-- Section Jadwal Sholat API -->
    <div class="prayer-times-card fade-up-element" style="animation-delay: 0.3s;">
      <div class="contact-header text-center" style="margin-bottom: 10px;">
        <h2>Jadwal Sholat Harian</h2>
        <p>Wilayah Jember & Sekitarnya (<span id="prayer-date">Memuat...</span>)</p>
        <div class="vm-deco mx-auto" aria-hidden="true" style="margin: 10px auto;">
          <div class="vm-deco__line"></div>
          <div class="vm-deco__dots"><span></span><span></span><span></span></div>
        </div>
      </div>
      <div class="prayer-times-container" id="prayer-times-container">
        <div style="text-align: center; color: var(--text-soft); padding: 20px; width: 100%;">
          <i class="fas fa-spinner fa-spin"></i> Memuat data jadwal dari server API...
        </div>
      </div>
    </div>

    <!-- Section Maps Terpisah -->
    <div class="contact-map-section fade-up-element" style="animation-delay: 0.4s;">
      <div class="contact-header text-center">
        <h2>Lokasi Pesantren</h2>
        <div class="vm-deco mx-auto" aria-hidden="true" style="margin: 10px auto;">
          <div class="vm-deco__line"></div>
          <div class="vm-deco__dots"><span></span><span></span><span></span></div>
        </div>
      </div>
      <div class="map-container">
        <iframe 
          src="https://maps.google.com/maps?q=Ponpes%20Al-Barokah%20An-Nur%20Khumairoh,%20Ajung,%20Jember&t=&z=15&ie=UTF8&iwloc=&output=embed" 
          width="100%" 
          height="450" 
          style="border:0; border-radius: 12px; box-shadow: 0 4px 20px rgba(0,0,0,0.08);" 
          allowfullscreen="" 
          loading="lazy" 
          referrerpolicy="no-referrer-when-downgrade">
        </iframe>
        <div style="text-align: center; margin-top: 20px;">
          <a href="https://maps.app.goo.gl/BemuL7k5KaZrPHAK8" target="_blank" class="btn-map" style="display: inline-flex; align-items: center; gap: 8px; padding: 10px 20px; background: #fff; border: 2px solid var(--green-primary); color: var(--green-primary); border-radius: 8px; font-weight: 600; text-decoration: none; transition: all 0.3s ease;">
            <i class="fas fa-external-link-alt"></i> Buka di Aplikasi Google Maps
          </a>
        </div>
      </div>
    </div>
  </div>
</main>

<!-- FOOTER — di-inject oleh components.js -->
<?php include 'includes/footer.php'; ?>

<!-- Scripts -->

<script src="js/navbar.js"></script>
<script src="js/hubungi-kami.js"></script>

</body>
</html>
