/* PENGAJAR JS — Ponpes Al-Barokah An-Nur Khumairoh */

document.addEventListener('DOMContentLoaded', () => {

    /* 1. Animasi Fade-Up pada Scroll & Load */
    const fadeElements = document.querySelectorAll('.fade-up-element');

    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
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

    /* 2. Fitur Interaktif: Tab Filter Pengajar */
    const tabBtns = document.querySelectorAll('.tab-btn');
    const pengajarCards = document.querySelectorAll('.pengajar-card');
    const noResultsMsg = document.getElementById('noResultsMsg');

    tabBtns.forEach(btn => {
        btn.addEventListener('click', () => {
            // Hapus class active dari semua tombol, berikan ke tombol yg di-klik
            tabBtns.forEach(t => t.classList.remove('active'));
            btn.classList.add('active');

            const filterValue = btn.getAttribute('data-filter');
            let visibleCount = 0;

            // Filter kartu
            pengajarCards.forEach(card => {
                // Reset animasi
                card.classList.remove('show');
                
                // Cek kategori
                const category = card.getAttribute('data-category');
                
                if (filterValue === 'all' || filterValue === category) {
                    card.classList.remove('hide');
                    visibleCount++;
                    // Trigger reflow untuk animasi ulang
                    void card.offsetWidth;
                    card.classList.add('show');
                } else {
                    card.classList.add('hide');
                }
            });

            // Tampilkan pesan kosong jika tidak ada hasil
            if (visibleCount === 0) {
                noResultsMsg.style.display = 'block';
            } else {
                noResultsMsg.style.display = 'none';
            }
        });
    });

});
