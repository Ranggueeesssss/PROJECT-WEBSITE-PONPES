/**
 * Data Santri JS Logic
 * Handling CRUD and dynamic columns
 */

document.addEventListener('DOMContentLoaded', () => {
    console.log('Data Santri module initialized.');
});

/**
 * Open Modal to Add Column
 */
function openAddColumnModal() {
    // We use the existing modal logic if available, or a simple prompt for now
    // In a production app, this would be a beautiful custom modal
    if (typeof window.showInputModal === 'function') {
        window.showInputModal('Tambah Kolom Baru', 'Masukkan nama kolom (misal: Kamar, Kelas, Alamat Asal)', 'add_column');
    } else {
        const colName = prompt('Nama kolom baru:');
        if (colName && colName.trim() !== '') {
            submitAction('add_column', { col_name: colName });
        }
    }
}

/**
 * Delete a dynamic column
 */
function deleteColumn(colId, colName) {
    const msg = `Apakah Anda yakin ingin menghapus kolom "${colName}"? Data yang sudah diisi pada kolom ini akan hilang.`;
    if (typeof showConfirm === 'function') {
        showConfirm('Konfirmasi Hapus Kolom', msg, function() {
            submitAction('del_column', { col_id: colId });
        }, 'delete');
    } else if (confirm(msg)) {
        submitAction('del_column', { col_id: colId });
    }
}

/**
 * Delete a student record
 */
function deleteSantri(id, name) {
    const msg = `Apakah Anda yakin ingin menghapus data santri "${name}" secara permanen?`;
    if (typeof showConfirm === 'function') {
        showConfirm('Konfirmasi Hapus', msg, function() {
            submitAction('delete_santri', { santri_id: id });
        }, 'delete');
    } else if (confirm(msg)) {
        submitAction('delete_santri', { santri_id: id });
    }
}

/**
 * Open Edit Modal for Student
 */
function openEditSantri(santri, columns) {
    // Fill basic info
    document.getElementById('editSantriId').value = santri.id;
    document.getElementById('editNama').textContent = santri.nama_lengkap;
    document.getElementById('editJenjang').textContent = santri.jenjang;
    
    // Status Select
    const statusSelect = document.getElementById('editStatus');
    if (statusSelect) statusSelect.value = santri.status_santri;

    // Custom Fields
    const container = document.getElementById('customFieldsContainer');
    const emptyMsg = document.getElementById('emptyFieldsMsg');
    container.innerHTML = '';
    
    let parsedData = santri.parsed_custom || {};
    // Fallback jika berupa string
    if (typeof parsedData === 'string') {
        try { parsedData = JSON.parse(parsedData); } catch(e) { parsedData = {}; }
    }


    if (columns.length === 0) {
        if(emptyMsg) emptyMsg.style.display = 'block';
    } else {
        if(emptyMsg) emptyMsg.style.display = 'none';
        columns.forEach(col => {
            const cName = col.col_name;
            const val = parsedData[cName] || '';
            
            const fg = document.createElement('div');
            fg.className = 'form-group mb-4'; // Ditambah margin bawah lebih besar
            
            const lbl = document.createElement('label');
            lbl.className = 'form-label';
            lbl.textContent = cName;
            
            const inp = document.createElement('input');
            inp.type = 'text';
            inp.name = `custom_fields[${cName}]`;
            inp.className = 'form-control';
            inp.value = val;
            inp.placeholder = `Masukkan ${cName}...`;
            
            fg.appendChild(lbl);
            fg.appendChild(inp);
            container.appendChild(fg);
        });
    }

    const modal = document.getElementById('modalEditData');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

function closeEditSantri() {
    const modal = document.getElementById('modalEditData');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Open Modal to Add Student
 */
function openAddSantri() {
    const modal = document.getElementById('modalAddSantri');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close Modal to Add Student
 */
function closeAddSantri() {
    const modal = document.getElementById('modalAddSantri');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Open Modal to Add Column
 */
function openAddCol() {
    const modal = document.getElementById('modalAddCol');
    if (modal) {
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}

/**
 * Close Modal to Add Column
 */
function closeAddCol() {
    const modal = document.getElementById('modalAddCol');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}

/**
 * Helper to submit post actions
 */
function submitAction(action, params) {
    const form = document.createElement('form');
    form.method = 'POST';
    form.action = 'data_santri.php';
    
    const actionInput = document.createElement('input');
    actionInput.type = 'hidden';
    actionInput.name = 'action';
    actionInput.value = action;
    form.appendChild(actionInput);
    
    for (const key in params) {
        const input = document.createElement('input');
        input.type = 'hidden';
        input.name = key;
        input.value = params[key];
        form.appendChild(input);
    }
    
    document.body.appendChild(form);
    form.submit();
}
