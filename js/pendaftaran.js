/* PENDAFTARAN JS — Ponpes Al-Barokah An-Nur Khumairoh */

document.addEventListener('DOMContentLoaded', () => {

    /* 1. Animasi Fade-Up pada Scroll & Load */
    const fadeElements = document.querySelectorAll('.fade-up-element');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.10
    };

    const fadeObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target);
            }
        });
    }, observerOptions);

    fadeElements.forEach(el => fadeObserver.observe(el));

    // Fallback load
    setTimeout(() => {
        fadeElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                el.classList.add('visible');
            }
        });
    }, 100);

    /* 2. Interaksi Tombol Form Pendaftaran */
    const btnForm = document.getElementById('btnFormPend');
    if (btnForm) {
        btnForm.addEventListener('click', (e) => {
            e.preventDefault();
            // Redirect ke halaman form PHP
            window.location.href = 'form-pendaftaran.php';
        });
    }

});
