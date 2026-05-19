/* ================================================================
   LOGIN.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Login (login.php)
   Menggunakan Fetch API untuk autentikasi ke proses_login.php
   ================================================================ */

(function () {
  'use strict';

  /* ── Selector Cache ──────────────────────────────────────────── */
  const usernameInput = document.getElementById('username');
  const passwordInput = document.getElementById('password');
  const showPwCheck   = document.getElementById('showPwCheck');
  const togglePwIcon  = document.getElementById('togglePw');
  const loginBtn      = document.getElementById('loginBtn');
  const loginCard     = document.querySelector('.login-card');
  const loginAlert    = document.getElementById('login-alert');

  /* ── 1. Toggle Kata Sandi ────────────────────────────────────── */
  function syncPasswordVisibility() {
    const visible      = showPwCheck.checked;
    passwordInput.type = visible ? 'text' : 'password';
    togglePwIcon.classList.toggle('fa-lock',      !visible);
    togglePwIcon.classList.toggle('fa-lock-open',  visible);
  }

  if (showPwCheck) {
    showPwCheck.addEventListener('change', syncPasswordVisibility);
  }

  if (togglePwIcon) {
    togglePwIcon.addEventListener('click', function () {
      if (showPwCheck) {
        showPwCheck.checked = !showPwCheck.checked;
        syncPasswordVisibility();
      }
    });
  }

  /* ── 2. Tampilkan Pesan Alert ────────────────────────────────── */
  /**
   * @param {string} pesan   - Teks pesan yang ditampilkan
   * @param {string} tipe    - 'error' | 'success' | 'info'
   */
  function showAlert(pesan, tipe) {
    if (!loginAlert) return;

    const iconMap = {
      error:   'fa-circle-exclamation',
      success: 'fa-circle-check',
      info:    'fa-circle-info'
    };

    loginAlert.innerHTML =
      '<i class="fas ' + (iconMap[tipe] || 'fa-circle-info') + '"></i> ' +
      '<span>' + pesan + '</span>';

    loginAlert.className = 'login-alert login-alert--' + tipe;
    loginAlert.style.display = 'flex';

    // Sembunyikan otomatis setelah 5 detik (kecuali success — dibiarkan)
    if (tipe !== 'success') {
      setTimeout(function () {
        loginAlert.style.display = 'none';
      }, 5000);
    }
  }

  /* ── 3. Animasi Shake ────────────────────────────────────────── */
  function shakeCard() {
    if (!loginCard) return;
    loginCard.style.animation = 'none';
    void loginCard.offsetHeight; /* force reflow */
    loginCard.style.animation  = 'shake .4s ease';
  }

  /* ── 4. Validasi Sisi Klien ──────────────────────────────────── */
  function validate() {
    const user = usernameInput ? usernameInput.value.trim() : '';
    const pass = passwordInput ? passwordInput.value.trim() : '';

    if (!user || !pass) {
      shakeCard();
      showAlert('Username dan password wajib diisi.', 'error');

      [usernameInput, passwordInput].forEach(function (input) {
        if (input && !input.value.trim()) {
          input.style.borderColor = '#e57373';
          input.addEventListener('input', function () {
            input.style.borderColor = '';
          }, { once: true });
        }
      });
      return false;
    }
    return true;
  }

  /* ── 5. Set State Loading pada Tombol ───────────────────────── */
  function setLoading(isLoading) {
    if (!loginBtn) return;
    if (isLoading) {
      loginBtn.innerHTML  = '<i class="fas fa-spinner fa-spin" style="margin-right:8px;"></i>Memproses...';
      loginBtn.disabled   = true;
      loginBtn.style.opacity = '0.8';
    } else {
      loginBtn.innerHTML  = '<i class="fas fa-sign-in-alt" style="margin-right:8px;"></i>Masuk';
      loginBtn.disabled   = false;
      loginBtn.style.opacity = '1';
    }
  }

  /* ── 6. Handle Login (Fetch ke proses_login.php) ────────────── */
  function handleLogin() {
    if (!validate()) return;

    setLoading(true);

    const formData = new FormData();
    formData.append('username', usernameInput.value.trim());
    formData.append('password', passwordInput.value.trim());

    fetch('proses_login.php', {
      method:  'POST',
      headers: { 'X-Requested-With': 'XMLHttpRequest' },
      body:    formData
    })
      .then(function (response) {
        if (!response.ok) {
          throw new Error('HTTP ' + response.status);
        }
        return response.json();
      })
      .then(function (data) {
        if (data.status === 'success') {
          /* ── Login Berhasil ── */
          loginBtn.innerHTML = '<i class="fas fa-check" style="margin-right:8px;"></i>Berhasil Masuk!';
          loginBtn.style.background = 'linear-gradient(135deg, #2d7a50, #1e5235)';
          showAlert(data.pesan, 'success');

          /* Redirect ke halaman sesuai role setelah 1.2 detik */
          setTimeout(function () {
            window.location.href = data.redirect || 'Home.html';
          }, 1200);

        } else {
          /* ── Login Gagal ── */
          setLoading(false);
          shakeCard();
          showAlert(data.pesan || 'Login gagal. Coba lagi.', 'error');

          /* Highlight field password */
          if (passwordInput) {
            passwordInput.style.borderColor = '#e57373';
            passwordInput.value = '';
            passwordInput.focus();
            passwordInput.addEventListener('input', function () {
              passwordInput.style.borderColor = '';
            }, { once: true });
          }
        }
      })
      .catch(function (err) {
        setLoading(false);
        shakeCard();
        showAlert('Terjadi kesalahan koneksi. Pastikan server berjalan.', 'error');
        console.error('[Login Error]', err);
      });
  }

  /* ── 7. Event Listeners ──────────────────────────────────────── */
  if (loginBtn) {
    loginBtn.addEventListener('click', handleLogin);
  }

  /* Enter key submit */
  document.addEventListener('keydown', function (e) {
    if (e.key === 'Enter' && loginBtn && !loginBtn.disabled) {
      handleLogin();
    }
  });

  /* ── 8. Input Focus Effect ───────────────────────────────────── */
  document.querySelectorAll('.input-wrap input').forEach(function (input) {
    input.addEventListener('focus', function () {
      var label = this.closest('.form-group');
      if (label) label.querySelector('label').style.color = 'var(--green-primary)';
    });
    input.addEventListener('blur', function () {
      var label = this.closest('.form-group');
      if (label) label.querySelector('label').style.color = '';
    });
  });

})();
