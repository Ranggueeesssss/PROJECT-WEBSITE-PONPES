document.addEventListener('DOMContentLoaded', function() {
    const formPengumuman = document.getElementById('form-pengumuman');
    const resultContainer = document.getElementById('pengumuman-result');

    if(formPengumuman) {
        // Tambahkan filter angka real-time
        const inputNik = document.getElementById('cek_nik');
        if (inputNik) {
            inputNik.addEventListener('input', function() {
                this.value = this.value.replace(/\D/g, '');
            });
        }

        formPengumuman.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const nik = document.getElementById('cek_nik').value.trim();
            const tglLahir = document.getElementById('cek_tgl_lahir').value;
            
            if(!nik || !tglLahir) {
                alert('Silakan lengkapi NIK dan tanggal lahir.');
                return;
            }

            const nikClean = nik.replace(/\D/g, '');
            if (nikClean.length !== 16) {
                alert('Gagal: NIK harus berupa 16 digit angka.');
                return;
            }

            // Show loading state
            resultContainer.classList.remove('hidden');
            resultContainer.innerHTML = '<div class="text-center"><div class="loader"></div><p style="color: var(--text-soft); font-weight: 500;">Menghubungkan ke sistem...</p></div>';

            // Siapkan Form Data
            const formData = new FormData();
            formData.append('nik', nikClean);
            formData.append('tgl_lahir', tglLahir);

            // Fetch ke API Backend
            fetch('api_cek_pengumuman.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                setTimeout(() => {
                    renderResult(data);
                }, 800); // Sedikit delay agar animasi loading natural
            })
            .catch(error => {
                console.error('Error:', error);
                resultContainer.innerHTML = `
                    <div class="result-card failed">
                        <div class="status-icon"><i class="fas fa-exclamation-triangle"></i></div>
                        <h4 class="status-title">Terjadi Kesalahan Server</h4>
                        <p class="status-detail">Gagal memuat pengumuman. Silakan coba kembali beberapa saat lagi.</p>
                        <button class="btn-action" style="max-width: 200px; margin: 0 auto; justify-content: center; background: #e74c3c; color: white; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; font-family: 'Poppins', sans-serif;" onclick="document.getElementById('pengumuman-result').classList.add('hidden')">
                            Tutup
                        </button>
                    </div>
                `;
            });
        });
    }

    function renderResult(data) {
        let html = '';
        
        if (data.status === 'success') {
            if (data.is_lolos) {
                html = `
                    <div class="result-card success">
                        <div class="status-icon"><i class="fas fa-check"></i></div>
                        <h4 class="status-title">Selamat! Anda Dinyatakan Sebagai Santri Baru</h4>
                        <p class="status-detail">
                            Santri atas nama <strong>${escapeHtml(data.nama)}</strong> telah dinyatakan lolos administrasi penerimaan santri baru Pondok Pesantren Al-Barokah An-Nur Khumairoh.
                        </p>
                        <a href="https://wa.me/6285232375228" target="_blank" class="btn-action" style="max-width: 250px; margin: 0 auto; text-decoration: none; justify-content: center; background: #27ae60; color: #fff; padding: 12px 20px; border-radius: 12px; display: flex; align-items: center; gap: 10px; font-weight: 600; transition: transform 0.3s;">
                            <i class="fab fa-whatsapp" style="font-size: 1.2rem;"></i>
                            <span>Hubungi Panitia</span>
                        </a>
                    </div>
                `;
            } else {
                html = `
                    <div class="result-card failed">
                        <div class="status-icon"><i class="fas fa-times"></i></div>
                        <h4 class="status-title">Mohon Maaf, Anda Belum Dinyatakan Sebagai Santri Baru</h4>
                        <p class="status-detail">
                            Santri atas nama <strong>${escapeHtml(data.nama)}</strong> belum memenuhi kriteria kelulusan. Tetap semangat dan jangan menyerah.
                        </p>
                        <button class="btn-action" style="max-width: 200px; margin: 0 auto; text-decoration: none; justify-content: center; background: #e74c3c; color: white; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Poppins', sans-serif;" onclick="document.getElementById('pengumuman-result').classList.add('hidden')">
                            <i class="fas fa-undo"></i> Cek Ulang
                        </button>
                    </div>
                `;
            }
        } else if (data.status === 'pending_waktu') {
            // Waktu belum tiba
            html = `
                <div class="result-card" style="background: #f8fafc; border: 1px solid #cbd5e1;">
                    <div class="status-icon" style="color: #3b82f6; background: #eff6ff;"><i class="fas fa-clock"></i></div>
                    <h4 class="status-title" style="color: #1e293b;">Pengumuman Belum Dibuka</h4>
                    <p class="status-detail">${escapeHtml(data.pesan)}</p>
                    <button class="btn-action" style="max-width: 200px; margin: 0 auto; justify-content: center; background: #3b82f6; color: white; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Poppins', sans-serif;" onclick="document.getElementById('pengumuman-result').classList.add('hidden')">
                        <i class="fas fa-arrow-left"></i> Kembali
                    </button>
                </div>
            `;
        } else if (data.status === 'not_found') {
            // Nama / tgl lahir tidak ada
            html = `
                <div class="result-card failed">
                    <div class="status-icon"><i class="fas fa-search-minus"></i></div>
                    <h4 class="status-title">Data Tidak Ditemukan, Silahkan Cek Kembali</h4>
                    <p class="status-detail">${escapeHtml(data.pesan)}</p>
                    <button class="btn-action" style="max-width: 200px; margin: 0 auto; justify-content: center; background: #e74c3c; color: white; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Poppins', sans-serif;" onclick="document.getElementById('pengumuman-result').classList.add('hidden')">
                        <i class="fas fa-undo"></i> Cek Ulang
                    </button>
                </div>
            `;
        } else {
            // Pesan error umum (contoh: status masih Pending di dashboard)
            html = `
                <div class="result-card" style="background: #fffbeb; border: 1px solid #fde68a;">
                    <div class="status-icon" style="color: #d97706; background: #fef3c7;"><i class="fas fa-info-circle"></i></div>
                    <h4 class="status-title" style="color: #92400e;">Informasi</h4>
                    <p class="status-detail">${escapeHtml(data.pesan)}</p>
                    <button class="btn-action" style="max-width: 200px; margin: 0 auto; justify-content: center; background: #d97706; color: white; padding: 12px 20px; border-radius: 12px; border: none; cursor: pointer; display: flex; align-items: center; gap: 10px; font-weight: 600; font-family: 'Poppins', sans-serif;" onclick="document.getElementById('pengumuman-result').classList.add('hidden')">
                        Mengerti
                    </button>
                </div>
            `;
        }
        
        resultContainer.innerHTML = html;
        resultContainer.scrollIntoView({ behavior: 'smooth', block: 'nearest' });
    }
    
    function escapeHtml(unsafe) {
        if (!unsafe) return '';
        return unsafe
             .replace(/&/g, "&amp;")
             .replace(/</g, "&lt;")
             .replace(/>/g, "&gt;")
             .replace(/"/g, "&quot;")
             .replace(/'/g, "&#039;");
    }
});
