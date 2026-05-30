document.addEventListener('DOMContentLoaded', function() {
    /* Fungsi Jadwal Pengumuman */
    const formSchedule = document.getElementById('formJadwalPengumuman');
    const btnSave = document.getElementById('btnSaveSchedule');
    const inputJadwal = document.getElementById('inputJadwal');

    if (formSchedule) {
        formSchedule.addEventListener('submit', function(e) {
            e.preventDefault();
            
            const jadwalVal = inputJadwal.value;
            if(!jadwalVal) return;

            // Efek Loading
            const originalText = btnSave.innerHTML;
            btnSave.innerHTML = '<i class="fas fa-spinner fa-spin"></i> Menyimpan...';
            btnSave.disabled = true;

            const formData = new FormData();
            formData.append('jadwal_pengumuman', jadwalVal);

            fetch('api_save_jadwal.php', {
                method: 'POST',
                body: formData
            })
            .then(response => response.json())
            .then(data => {
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
                
                if (data.status === 'success') {
                    showToast(data.pesan, 'success');
                } else {
                    showToast(data.pesan, 'error');
                }
            })
            .catch(error => {
                btnSave.innerHTML = originalText;
                btnSave.disabled = false;
                showToast('Gagal terhubung ke server. Periksa koneksi Anda.', 'error');
                console.error(error);
            });
        });
    }

    function showToast(message, type) {
        // Hapus toast lama jika ada
        const oldToast = document.getElementById('toastSchedule');
        if(oldToast) oldToast.remove();

        const toast = document.createElement('div');
        toast.id = 'toastSchedule';
        toast.className = `toast-schedule ${type === 'error' ? 'error' : ''}`;
        
        const icon = type === 'error' ? '<i class="fas fa-exclamation-circle"></i>' : '<i class="fas fa-check-circle"></i>';
        toast.innerHTML = `${icon} <span>${message}</span>`;
        
        document.body.appendChild(toast);
        
        // Animasi masuk
        setTimeout(() => {
            toast.classList.add('show');
        }, 10);

        // Auto close
        setTimeout(() => {
            toast.classList.remove('show');
            setTimeout(() => toast.remove(), 400);
        }, 4000);
    }
});

/* Fungsi Modal dan Validasi data_pendaftaran */
const detailModal = document.getElementById('detailModal');

// Form Validasi
function validateForm(statusValue, message, type) {
    if(typeof window.showConfirm !== 'undefined') {
        window.showConfirm('Konfirmasi Validasi', message, function() {
            const form = document.getElementById('formValidasi');
            let oldInput = document.getElementById('hiddenStatusInput');
            if(oldInput) oldInput.remove();
            
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'status';
            input.value = statusValue;
            input.id = 'hiddenStatusInput';
            form.appendChild(input);
            form.submit();
        }, type);
    } else {
        if(confirm(message)) {
            const form = document.getElementById('formValidasi');
            let oldInput = document.getElementById('hiddenStatusInput');
            if(oldInput) oldInput.remove();
            const input = document.createElement('input');
            input.type = 'hidden';
            input.name = 'status';
            input.value = statusValue;
            input.id = 'hiddenStatusInput';
            form.appendChild(input);
            form.submit();
        }
    }
}

function checkDoc(val, stId, btnId) {
    const st = document.getElementById(stId);
    const btn = document.getElementById(btnId);
    if(val && val.trim() !== '') {
        st.textContent = 'Tersedia';
        st.className = 'doc-status';
        btn.href = 'uploads/' + val;
        btn.classList.remove('disabled');
    } else {
        st.textContent = 'Tidak Dilampirkan';
        st.className = 'doc-status missing';
        btn.href = '#';
        btn.classList.add('disabled');
    }
}

function openDetailModal(data) {
    document.getElementById('detNama').textContent = data.nama_lengkap;
    document.getElementById('detNIK').textContent  = data.nik || '-';
    document.getElementById('detJenjang').textContent = data.jenjang_pendaftaran;
    
    // Format Date
    const tgl = new Date(data.tanggal_lahir).toLocaleDateString('id-ID', {day:'numeric', month:'long', year:'numeric'});
    document.getElementById('detTTL').textContent = data.tempat_lahir + ', ' + tgl;
    
    document.getElementById('detNoHP').textContent = data.nomor_handphone;
    document.getElementById('detAlamat').textContent = data.alamat_lengkap;
    document.getElementById('detInfo').textContent = data.tahu_ponpes + ' (Info dari: ' + (data.nama_informan || '-') + ')';
    
    // Cek Dokumen
    checkDoc(data.file_ijazah, 'stIjazah', 'btnIjazah');
    checkDoc(data.file_kk, 'stKK', 'btnKK');
    checkDoc(data.file_akta, 'stAkta', 'btnAkta');
    checkDoc(data.file_ktp_ortu, 'stKTP', 'btnKTP');
    checkDoc(data.file_surat_tjm, 'stSPJM', 'btnSPJM');
    
    document.getElementById('valId').value = data.id;

    if(detailModal) {
        detailModal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeDetailModal() {
    if(detailModal) {
        detailModal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
