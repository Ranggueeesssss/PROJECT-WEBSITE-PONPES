/* HOME.JS — Ponpes Al-Barokah An-Nur Khumairoh
   Script khusus halaman Home (index.html) */

(function () {
  'use strict';

  /* Counter Animasi (Stats Strip) */
  function animateCounter(el) {
    const target  = parseInt(el.dataset.target, 10);
    const suffix  = el.dataset.suffix || '';
    const duration = 1800;
    const step    = Math.ceil(target / (duration / 16));
    let   current = 0;

    const timer = setInterval(function () {
      current += step;
      if (current >= target) {
        current = target;
        clearInterval(timer);
      }
      el.textContent = current + suffix;
    }, 16);
  }

  /* Jalankan counter saat stats masuk viewport */
  const statsSection = document.querySelector('.stats');

  if (statsSection) {
    const observer = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            document.querySelectorAll('.stats__number[data-target]').forEach(animateCounter);
            observer.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.4 }
    );
    observer.observe(statsSection);
  }

  /* Scroll Reveal (kartu & section) */
  const revealEls = document.querySelectorAll(
    '.vm-card, .about__image, .about__content, .stats__item'
  );

  if ('IntersectionObserver' in window && revealEls.length) {
    /* Set state awal */
    revealEls.forEach(function (el) {
      el.style.opacity   = '0';
      el.style.transform = 'translateY(24px)';
      el.style.transition = 'opacity .5s ease, transform .5s ease';
    });

    const revealObserver = new IntersectionObserver(
      function (entries) {
        entries.forEach(function (entry) {
          if (entry.isIntersecting) {
            entry.target.style.opacity   = '1';
            entry.target.style.transform = 'translateY(0)';
            revealObserver.unobserve(entry.target);
          }
        });
      },
      { threshold: 0.15 }
    );

    revealEls.forEach(function (el) { revealObserver.observe(el); });
  }

})();
