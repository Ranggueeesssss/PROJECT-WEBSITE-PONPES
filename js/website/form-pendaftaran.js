/* FORM PENDAFTARAN JS — Interactive Form Behavior (Modern Theme) */

document.addEventListener('DOMContentLoaded', () => {

    // --- Form Step Navigation ---
    const btnNext = document.getElementById('btnNext');
    const btnPrev = document.getElementById('btnPrev');
    const step1 = document.getElementById('step-1');
    const step2 = document.getElementById('step-2');
    const form = document.getElementById('ppdbForm');
    
    const indicator1 = document.getElementById('indicator-1');
    const indicator2 = document.getElementById('indicator-2');

    // --- Custom Validation Modal ---
    function showValidationModal(message) {
        const overlay = document.getElementById('valModalOverlay');
        const msgEl = document.getElementById('valModalMsg');
        if (overlay && msgEl) {
            msgEl.textContent = message;
            overlay.classList.add('active');
        } else {
            alert(message);
        }
    }

    const valModalBtn = document.getElementById('valModalBtn');
    if (valModalBtn) {
        valModalBtn.addEventListener('click', () => {
            document.getElementById('valModalOverlay').classList.remove('active');
        });
    }

    // Function to check if required fields in step 1 are filled
    function validateStep1() {
        const requiredInputs = step1.querySelectorAll('input[required]');
        let isValid = true;
        
        // Remove previous error outlines
        step1.querySelectorAll('.error-border').forEach(el => el.classList.remove('error-border'));

        requiredInputs.forEach(input => {
            if (input.type !== 'radio' && input.type !== 'checkbox') {
                if (!input.value.trim()) {
                    isValid = false;
                    input.classList.add('error-border');
                }
            }
        });

        // Special check for Radio (Jenjang)
        const jenjangRadios = step1.querySelectorAll('input[name="jenjang_pendaftaran"]');
        let jenjangChecked = false;
        jenjangRadios.forEach(r => { if(r.checked) jenjangChecked = true; });
        if(!jenjangChecked && jenjangRadios.length > 0) {
            isValid = false;
        }

        // Checkboxes (Tahu Ponpes) require at least one
        const tahuCheckboxes = step1.querySelectorAll('input[name="tahu_ponpes[]"]');
        let tahuChecked = false;
        tahuCheckboxes.forEach(c => { if(c.checked) tahuChecked = true; });
        
        // If 'Yang lain' is checked, its text input is required
        const checkLainnya = document.getElementById('check-lainnya');
        const inputLainnya = document.getElementById('input-lainnya');
        if (checkLainnya && checkLainnya.checked && !inputLainnya.value.trim()) {
            isValid = false;
            inputLainnya.style.borderBottomColor = '#e74c3c';
        } else if (inputLainnya) {
            inputLainnya.style.borderBottomColor = ''; // reset
        }

        if(!tahuChecked && tahuCheckboxes.length > 0) {
            if (checkLainnya && checkLainnya.checked) {
                // already checked above
            } else {
                isValid = false;
            }
        }

        // Validasi NIK: harus tepat 16 digit angka
        const nikInput = document.getElementById('inputNIK');
        if (nikInput) {
            const nikVal = nikInput.value.replace(/\D/g, '');
            if (nikVal.length !== 16) {
                isValid = false;
                nikInput.classList.add('error-border');
                showValidationModal("Gagal: NIK harus berupa 16 digit angka sesuai KTP/KK.");
                return false;
            }
        }

        // Validasi HP: 10-13 digit
        const hpInput = document.getElementById('inputHP');
        if (hpInput) {
            const hpVal = hpInput.value.replace(/\D/g, '');
            if (hpVal.length < 10 || hpVal.length > 13) {
                isValid = false;
                hpInput.classList.add('error-border');
                showValidationModal("Gagal: Nomor Handphone harus berjumlah antara 10 hingga 13 digit angka.");
                return false;
            }
        }

        if(!isValid) {
            showValidationModal("Harap isi semua kolom yang wajib (*) sebelum melanjutkan.");
        }
        return isValid;
    }

    if (btnNext) {
        btnNext.addEventListener('click', () => {
            if(validateStep1()) {
                step1.classList.remove('active');
                step2.classList.add('active');
                
                if(indicator1 && indicator2) {
                    indicator1.classList.remove('active');
                    indicator2.classList.add('active');
                }
                
                window.scrollTo({ top: 0, behavior: 'smooth' });
            }
        });
    }

    if (btnPrev) {
        btnPrev.addEventListener('click', () => {
            step2.classList.remove('active');
            step1.classList.add('active');
            
            if(indicator1 && indicator2) {
                indicator2.classList.remove('active');
                indicator1.classList.add('active');
            }
            
            window.scrollTo({ top: 0, behavior: 'smooth' });
        });
    }

    // --- Handle 'Yang lain:' Checkbox ---
    const checkLainnya = document.getElementById('check-lainnya');
    const inputLainnya = document.getElementById('input-lainnya');

    if (checkLainnya && inputLainnya) {
        checkLainnya.addEventListener('change', (e) => {
            if (e.target.checked) {
                inputLainnya.disabled = false;
                inputLainnya.focus();
                e.target.name = "tahu_ponpes[]";
            } else {
                inputLainnya.disabled = true;
                inputLainnya.value = "";
                inputLainnya.style.borderBottomColor = '';
                e.target.name = ""; // Remove from array if not checked
            }
        });
        checkLainnya.name = "tahu_ponpes[]";
    }

    // --- File Upload UI (auto-discovery) ---
    // Otomatis mendeteksi semua .file-input dalam form,
    // sehingga input baru tidak perlu didaftarkan manual.
    document.querySelectorAll('.file-input').forEach(function(input) {
        const card        = input.closest('.file-upload-card');
        const nameDisplay = card ? card.querySelector('.file-name-display') : null;

        if (!nameDisplay) return;

        input.addEventListener('change', function() {
            if (this.files && this.files.length > 0) {
                const file = this.files[0];
                if (file.size > 10 * 1024 * 1024) {
                    alert('Ukuran file "' + file.name + '" terlalu besar. Maksimal 10 MB.');
                    this.value = '';
                    nameDisplay.textContent = 'Belum ada file dipilih';
                    nameDisplay.classList.remove('has-file');
                } else {
                    nameDisplay.textContent = file.name;
                    nameDisplay.classList.add('has-file');
                }
            } else {
                nameDisplay.textContent = 'Belum ada file dipilih';
                nameDisplay.classList.remove('has-file');
            }
        });
    });

    // --- Validasi Real-time NIK ---
    const nikInput = document.getElementById('inputNIK');
    const nikHint  = document.getElementById('nikHint');

    if (nikInput && nikHint) {
        // Hanya izinkan angka saat mengetik
        nikInput.addEventListener('input', function () {
            // Hapus karakter non-angka
            this.value = this.value.replace(/\D/g, '');

            const len = this.value.length;
            if (len === 0) {
                nikHint.style.color = '#6b7280'; // abu default
                nikHint.textContent = 'Hanya angka, tepat 16 digit sesuai KTP/KK.';
            } else if (len < 16) {
                nikHint.style.color = '#e67e22'; // oranye
                nikHint.textContent = `${len}/16 digit — kurang ${16 - len} digit lagi.`;
            } else {
                nikHint.style.color = '#16a34a'; // hijau
                nikHint.textContent = '✓ NIK valid (16 digit).';
                this.classList.remove('error-border');
            }
        });
    }

    // --- Validasi Real-time HP ---
    const hpInput = document.getElementById('inputHP');
    const hpHint  = document.getElementById('hpHint');

    if (hpInput && hpHint) {
        hpInput.addEventListener('input', function () {
            this.value = this.value.replace(/\D/g, '');
            const len = this.value.length;
            if (len === 0) {
                hpHint.style.color = '#6b7280';
                hpHint.textContent = 'Hanya angka, 10-13 digit.';
            } else if (len < 10) {
                hpHint.style.color = '#e67e22';
                hpHint.textContent = `${len}/10 digit — kurang ${10 - len} digit lagi.`;
            } else if (len > 13) {
                hpHint.style.color = '#e74c3c';
                hpHint.textContent = `Maksimal 13 digit (kelebihan ${len - 13} digit).`;
            } else {
                hpHint.style.color = '#16a34a';
                hpHint.textContent = '✓ Nomor Handphone valid.';
                this.classList.remove('error-border');
            }
        });
    }
});
