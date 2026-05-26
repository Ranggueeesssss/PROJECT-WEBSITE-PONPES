<?php
// LOGIN.PHP — Halaman Login (versi PHP) Konversi dari login.html dengan tambahan proteksi sesi
session_start();

// Jika sudah login, redirect ke halaman sesuai role
if (isset($_SESSION['user_id']) && isset($_SESSION['user_role'])) {
    switch ($_SESSION['user_role']) {
        case 'admin': header('Location: dashboard.php'); exit;
        case 'guru':  header('Location: dashboard.php'); exit;
        default:      header('Location: Home.php');     exit;
    }
}
?>
<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8" />
  <meta name="viewport" content="width=device-width, initial-scale=1.0" />
  <meta name="description" content="Login — Ponpes Al-Barokah An-Nur Khumairoh" />
  <title>Login — Ponpes Al-Barokah An-Nur Khumairoh</title>
  <link href="https://fonts.googleapis.com/css2?family=Amiri:wght@400;700&family=Poppins:wght@300;400;500;600;700&display=swap" rel="stylesheet" />
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.0/css/all.min.css" />
  <link rel="stylesheet" href="css/shared/base.css" />
  <link rel="stylesheet" href="css/dashboard/login.css" />
</head>
<body class="page-login">

<div class="login-page">

  <!-- PANEL KIRI: Visual / Branding -->
  <div class="lp-visual" aria-hidden="true">
    <div class="lp-visual__overlay"></div>

    <!-- Logo + Nama di sudut atas -->
    <div class="lp-visual__brand">
      <div class="lp-visual__logo">
        <img src="Picture/logo.jpg" alt="Logo Ponpes Al-Barokah" />
      </div>
      <div>
        <p class="lp-visual__pondok">Ponpes Al-Barokah</p>
        <p class="lp-visual__pondok-sub">An-Nur Khumairoh</p>
      </div>
    </div>

    <!-- Konten tengah panel -->
    <div class="lp-visual__center">
      <span class="lp-visual__tag">Portal Akademik</span>
      <h1 class="lp-visual__headline">Selamat Datang<br>Kembali</h1>
      <p class="lp-visual__desc">
        Masuk ke portal administrasi Pondok Pesantren Al-Barokah An-Nur Khumairoh.
        Kelola data santri, pengajar, dan program unggulan dari satu tempat.
      </p>
    </div>
  </div>

  <!-- PANEL KANAN: Form Login -->
  <div class="lp-form-panel">

    <!-- Tombol kembali ke beranda -->
    <a href="Home.php" class="lp-back" aria-label="Kembali ke beranda">
      <i class="fas fa-arrow-left"></i>
      <span>Beranda</span>
    </a>

    <div class="lp-form-wrap login-card">

      <!-- Heading form -->
      <div class="lp-form__heading">
        <h2 class="lp-form__title">Masuk ke Akun</h2>
        <p class="lp-form__subtitle">Masukkan kredensial Anda untuk melanjutkan</p>
      </div>

      <!-- Alert area -->
      <div id="login-alert" class="login-alert" role="alert" aria-live="polite" style="display:none;"></div>

      <!-- Form fields -->
      <div class="form-group">
        <label for="username">Nama Pengguna</label>
        <div class="input-wrap">
          <i class="fas fa-user input-icon--left" aria-hidden="true"></i>
          <input type="text" id="username" name="username"
                 placeholder="Masukkan username" autocomplete="username" />
        </div>
      </div>

      <div class="form-group">
        <label for="password">Kata Sandi</label>
        <div class="input-wrap">
          <i class="fas fa-lock input-icon--left" aria-hidden="true"></i>
          <input type="password" id="password" name="password"
                 placeholder="Masukkan kata sandi" autocomplete="current-password" />
          <i class="fas fa-lock input-icon toggle-pw" id="togglePw" aria-label="Tampilkan kata sandi" style="cursor:pointer;"></i>
        </div>
      </div>

      <!-- Show password + forgot -->
      <div class="show-pw-row">
        <label for="showPwCheck">
          <input type="checkbox" id="showPwCheck" />
          Tampilkan kata sandi
        </label>
        <a href="#" class="forgot-link">Lupa kata sandi?</a>
      </div>

      <!-- Submit -->
      <button class="btn-login" type="button" id="loginBtn">
        <i class="fas fa-sign-in-alt" aria-hidden="true"></i>
        Masuk
      </button>

      <!-- Security note -->
      <p class="security-note">
        <i class="fas fa-shield-alt" aria-hidden="true"></i>
        Koneksi Anda aman dan terenkripsi
      </p>

    </div><!-- /lp-form-wrap -->
  </div><!-- /lp-form-panel -->

</div><!-- /login-page -->

<script src="js/dashboard/login.js"></script>

</body>
</html>
