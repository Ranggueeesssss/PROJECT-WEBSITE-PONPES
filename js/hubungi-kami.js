/* ================================================================
   HUBUNGI KAMI JS — Ponpes Al-Barokah An-Nur Khumairoh
   ================================================================ */

document.addEventListener('DOMContentLoaded', () => {

    /* ── 1. Animasi Fade-Up pada Scroll & Load ── */
    const fadeElements = document.querySelectorAll('.fade-up-element');

    // Menggunakan IntersectionObserver untuk animasi saat scroll
    const observerOptions = {
        root: null,
        rootMargin: '0px',
        threshold: 0.15
    };

    const fadeObserver = new IntersectionObserver((entries, observer) => {
        entries.forEach(entry => {
            if (entry.isIntersecting) {
                entry.target.classList.add('visible');
                observer.unobserve(entry.target); // hanya animasi sekali
            }
        });
    }, observerOptions);

    fadeElements.forEach(el => {
        fadeObserver.observe(el);
    });

    // Fallback: Jika observer gagal atau elemen sudah di viewport saat load
    setTimeout(() => {
        fadeElements.forEach(el => {
            const rect = el.getBoundingClientRect();
            if (rect.top < window.innerHeight) {
                el.classList.add('visible');
            }
        });
    }, 100);

    /* ── 2. Handle Form Kirim Pesan ── */
    // Form submission is now handled natively via PHP POST request in hubungi-kami.php

    /* ── 3. Interaksi Tombol Map ── */
    const btnMap = document.querySelector('.btn-map');
    if (btnMap) {
        btnMap.addEventListener('click', (e) => {
            // Karena ini link eksternal dengan target="_blank", kita biarkan berjalan normal.
            // Namun kita tambahkan sedikit efek riak (ripple) visual di sini jika mau,
            // untuk saat ini cukup memastikan link tersebut interaktif.
            console.log('Mengalihkan ke Google Maps...');
        });
    }

    /* ── 4. API Jadwal Sholat (Aladhan) GET/JSON ── */
    const prayerContainer = document.getElementById('prayer-times-container');
    const prayerDateEl = document.getElementById('prayer-date');

    if (prayerContainer) {
        // Fetch API Jadwal Sholat menggunakan GET untuk kota Jember
        fetch('https://api.aladhan.com/v1/timingsByCity?city=Jember&country=Indonesia&method=20')
            .then(response => response.json())
            .then(data => {
                if(data.code === 200) {
                    const timings = data.data.timings;
                    const dateReadable = data.data.date.readable; // Contoh: "09 May 2026"
                    
                    if(prayerDateEl) {
                        prayerDateEl.textContent = dateReadable;
                    }

                    // Mapping waktu sholat yang ingin ditampilkan
                    const prayers = [
                        { name: 'Subuh', time: timings.Fajr, icon: 'fa-cloud-moon' },
                        { name: 'Terbit', time: timings.Sunrise, icon: 'fa-sun' },
                        { name: 'Dzuhur', time: timings.Dhuhr, icon: 'fa-sun' },
                        { name: 'Ashar', time: timings.Asr, icon: 'fa-cloud-sun' },
                        { name: 'Maghrib', time: timings.Maghrib, icon: 'fa-moon' },
                        { name: 'Isya', time: timings.Isha, icon: 'fa-star-and-crescent' }
                    ];

                    // Kosongkan container dari elemen loading
                    prayerContainer.innerHTML = '';

                    // Buat elemen HTML (card) untuk masing-masing jadwal
                    prayers.forEach(prayer => {
                        const box = document.createElement('div');
                        box.className = 'prayer-box';
                        box.innerHTML = `
                            <div class="prayer-box__icon"><i class="fas ${prayer.icon}"></i></div>
                            <div class="prayer-box__name">${prayer.name}</div>
                            <div class="prayer-box__time">${prayer.time}</div>
                        `;
                        prayerContainer.appendChild(box);
                    });
                } else {
                    prayerContainer.innerHTML = '<div style="text-align:center; color:red; width:100%;">Gagal memuat jadwal sholat.</div>';
                }
            })
            .catch(error => {
                console.error('Error fetching prayer times:', error);
                prayerContainer.innerHTML = '<div style="text-align:center; color:red; width:100%;">Gagal terhubung ke API Server.</div>';
            });
    }

});
