document.addEventListener('DOMContentLoaded', () => {

  /* ── 1. Sidebar Toggle ── */
  const toggle    = document.getElementById('sidebarToggle');
  const sidebar   = document.getElementById('dashSidebar');
  const mainEl    = document.getElementById('dashMain');
  const overlay   = document.getElementById('sidebarOverlay');
  const isMobile  = () => window.innerWidth <= 768;

  function openSidebar() {
    sidebar.classList.add('open');
    if (overlay) { overlay.classList.add('active'); }
    document.body.style.overflow = 'hidden';
  }

  function closeSidebar() {
    sidebar.classList.remove('open');
    if (overlay) { overlay.classList.remove('active'); }
    document.body.style.overflow = '';
  }

  function handleResize() {
    if (!isMobile()) {
      sidebar.classList.remove('open');
      if (overlay) overlay.classList.remove('active');
      document.body.style.overflow = '';
      mainEl.classList.remove('expanded');
    }
  }

  if (toggle && sidebar) {
    toggle.addEventListener('click', () => {
      if (isMobile()) {
        sidebar.classList.contains('open') ? closeSidebar() : openSidebar();
      } else {
        // Desktop: push layout
        sidebar.classList.toggle('collapsed');
        mainEl.classList.toggle('expanded');
      }
    });
  }

  if (overlay) {
    overlay.addEventListener('click', closeSidebar);
  }

  window.addEventListener('resize', handleResize);
  handleResize();

  /* ── 2. Dynamic Greeting ── */
  const greetEl = document.getElementById('greetingText');
  if (greetEl) {
    const h = new Date().getHours();
    let g = 'Selamat Datang';
    if (h >= 4  && h < 11) g = 'Selamat Pagi';
    else if (h >= 11 && h < 15) g = 'Selamat Siang';
    else if (h >= 15 && h < 18) g = 'Selamat Sore';
    else g = 'Selamat Malam';
    greetEl.textContent = g + ',';
  }

  /* ── 3. Live Clock ── */
  const clockEl = document.getElementById('liveClock');
  const dateEl  = document.getElementById('liveDate');
  const days   = ['Minggu','Senin','Selasa','Rabu','Kamis','Jumat','Sabtu'];
  const months = ['Jan','Feb','Mar','Apr','Mei','Jun','Jul','Agu','Sep','Okt','Nov','Des'];

  function updateClock() {
    const now = new Date();
    if (clockEl) clockEl.textContent = now.toLocaleTimeString('id-ID', { hour:'2-digit', minute:'2-digit' });
    if (dateEl)  dateEl.textContent  = `${days[now.getDay()]}, ${now.getDate()} ${months[now.getMonth()]} ${now.getFullYear()}`;
  }
  updateClock();
  setInterval(updateClock, 1000);

  /* ── 4. Counter Animation ── */
  function animateCounter(el, target, duration = 1200) {
    let start = 0;
    const step = target / (duration / 16);
    const run = () => {
      start = Math.min(start + step, target);
      el.textContent = Math.floor(start).toLocaleString('id-ID');
      if (start < target) requestAnimationFrame(run);
    };
    requestAnimationFrame(run);
  }

  const counters = document.querySelectorAll('[data-counter]');
  const observer = new IntersectionObserver((entries) => {
    entries.forEach(entry => {
      if (entry.isIntersecting) {
        const el  = entry.target;
        const val = parseInt(el.dataset.counter);
        animateCounter(el, val);
        observer.unobserve(el);
      }
    });
  }, { threshold: 0.3 });

  counters.forEach(el => observer.observe(el));

  /* ── 5. Message Modal ── */
  const modalOverlay = document.getElementById('msgModal');
  const modalClose   = document.getElementById('modalClose');

  window.openMsgModal = function(nama, email, subjek, tanggal, pesan) {
    if (!modalOverlay) return;
    document.getElementById('mNama').textContent    = nama;
    document.getElementById('mEmail').textContent   = email;
    document.getElementById('mSubjek').textContent  = subjek;
    document.getElementById('mTanggal').textContent = tanggal;
    document.getElementById('mPesan').textContent   = pesan;
    modalOverlay.classList.add('active');
    document.body.style.overflow = 'hidden';
  };

  function closeMsgModal() {
    if (!modalOverlay) return;
    modalOverlay.classList.remove('active');
    document.body.style.overflow = '';
  }

  if (modalClose) modalClose.addEventListener('click', closeMsgModal);
  if (modalOverlay) {
    modalOverlay.addEventListener('click', (e) => {
      if (e.target === modalOverlay) closeMsgModal();
    });
  }

  /* ── 6. Delete Confirm & Global Custom Confirm ── */
  window.confirmCallback = null;
  const confirmOverlay = document.getElementById('customConfirm');
  
  window.showConfirm = function(title, message, target, type = 'info') {
      if (!confirmOverlay) return;
      document.getElementById('confirmTitle').textContent = title;
      document.getElementById('confirmMsg').textContent = message;
      
      const icon = document.getElementById('confirmIcon');
      const yesBtn = document.getElementById('confirmYesBtn');
      
      icon.className = 'fas confirm-icon';
      yesBtn.style.background = '';
      
      if (type === 'delete' || type === 'reject') {
          icon.classList.add('fa-exclamation-triangle');
          icon.style.color = '#ef4444';
          yesBtn.style.background = '#ef4444';
          yesBtn.textContent = 'Ya, Hapus';
      } else if (type === 'accept') {
          icon.classList.add('fa-check-circle');
          icon.style.color = '#10b981';
          yesBtn.style.background = '#10b981';
          yesBtn.textContent = 'Ya, Terima';
      } else {
          icon.classList.add('fa-question-circle');
          icon.style.color = 'var(--dash-primary)';
          yesBtn.style.background = 'var(--dash-primary)';
          yesBtn.textContent = 'Ya, Lanjutkan';
      }
      if(type === 'reject') yesBtn.textContent = 'Ya, Tolak';

      window.confirmCallback = function() {
          if (typeof target === 'string') window.location.href = target;
          else if (typeof target === 'function') target();
      };
      
      confirmOverlay.classList.add('active');
  };
  
  window.closeConfirm = function() {
      if (confirmOverlay) confirmOverlay.classList.remove('active');
      window.confirmCallback = null;
  };
  
  const confirmYesBtn = document.getElementById('confirmYesBtn');
  if (confirmYesBtn) {
      confirmYesBtn.addEventListener('click', function() {
          if (window.confirmCallback) window.confirmCallback();
          window.closeConfirm();
      });
  }

  // Intercept all delete links
  document.querySelectorAll('.action-btn.delete').forEach(btn => {
    btn.addEventListener('click', (e) => {
      e.preventDefault();
      // Use existing showConfirm
      const href = btn.getAttribute('href');
      if (href) {
        showConfirm('Konfirmasi Hapus', 'Apakah Anda yakin ingin menghapus data ini secara permanen?', href, 'delete');
      }
    });
  });

  /* ── 7. Ripple Effect ── */
  document.querySelectorAll('.btn-action, .btn-logout, .action-btn').forEach(btn => {
    btn.addEventListener('click', function(e) {
      const r = document.createElement('span');
      r.style.cssText = `position:absolute;border-radius:50%;transform:scale(0);animation:ripple 0.5s linear;background:rgba(255,255,255,0.3);`;
      const rect = this.getBoundingClientRect();
      const size = Math.max(rect.width, rect.height);
      r.style.width = r.style.height = size + 'px';
      r.style.left  = (e.clientX - rect.left  - size / 2) + 'px';
      r.style.top   = (e.clientY - rect.top   - size / 2) + 'px';
      if (getComputedStyle(this).position === 'static') this.style.position = 'relative';
      this.style.overflow = 'hidden';
      this.appendChild(r);
      setTimeout(() => r.remove(), 600);
    });
  });

  const rippleStyle = document.createElement('style');
  rippleStyle.textContent = '@keyframes ripple { to { transform: scale(2.5); opacity: 0; } }';
  document.head.appendChild(rippleStyle);

});
