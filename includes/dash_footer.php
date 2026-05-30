<?php // includes/dash_footer.php ?>
<footer class="dash-footer">
    <i class="fas fa-mosque"></i>
    <span>&copy; <?php echo date('Y'); ?> Ponpes Al-Barokah An-Nur Khumairoh &mdash; All Rights Reserved</span>
</footer>

<!-- Custom Confirm Modal (Global) -->
<div class="confirm-overlay" id="customConfirm">
    <div class="confirm-box">
        <i class="fas fa-question-circle confirm-icon" id="confirmIcon"></i>        <div class="confirm-title" id="confirmTitle">Konfirmasi</div>
        <div class="confirm-msg" id="confirmMsg">Apakah Anda yakin?</div>
        <div class="confirm-actions">
            <button type="button" class="btn-confirm-cancel" onclick="closeConfirm()">Batal</button>
            <button type="button" class="btn-confirm-yes" id="confirmYesBtn">Ya, Lanjutkan</button>
        </div>
    </div>
</div>

<!-- Alert Notifikasi Profil -->
<?php if(isset($_SESSION['profil_msg'])): ?>
<script>
    document.addEventListener("DOMContentLoaded", function() {
        if (typeof showToast === "function") {
            showToast("<?php echo addslashes($_SESSION['profil_msg']); ?>", "<?php echo $_SESSION['profil_status']; ?>");
        } else {
            alert("<?php echo addslashes($_SESSION['profil_msg']); ?>");
        }
    });
</script>
<?php 
    unset($_SESSION['profil_msg']); 
    unset($_SESSION['profil_status']); 
endif; 
?>

<!-- Modal Global Profile Edit -->
<div class="modal-overlay" id="modalGlobalProfileEdit">
    <div class="modal-box">
        <div class="modal-header">
            <h3><i class="fas fa-user-edit"></i> Edit Profil Anda</h3>
            <button class="modal-close" onclick="closeGlobalProfileModal()"><i class="fas fa-times"></i></button>
        </div>
        <form action="api/api_edit_profil.php" method="POST">
            <input type="hidden" name="action" value="edit_profil_global">
            <div class="modal-body form-layout">
                <div class="form-group">
                    <label>Nama Lengkap</label>
                    <input type="text" name="new_nama" id="globalProfileNama" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Username</label>
                    <input type="text" name="new_username" id="globalProfileUsername" class="form-control" required>
                </div>
                <div class="form-group">
                    <label>Password Baru (Kosongkan jika tidak ingin mengubah)</label>
                    <input type="password" name="new_password" class="form-control" placeholder="******">
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn-action" onclick="closeGlobalProfileModal()" style="background:#e2e8f0;color:#475569;border:none;">Batal</button>
                <button type="submit" class="btn-action primary" style="border:none;"><i class="fas fa-save"></i> Simpan Perubahan</button>
            </div>
        </form>
    </div>
</div>

<script>
function openGlobalProfileEdit() {
    var modal = document.getElementById('modalGlobalProfileEdit');
    if (modal) {
        document.getElementById('globalProfileNama').value = "<?php echo addslashes($_SESSION['user_nama'] ?? ''); ?>";
        document.getElementById('globalProfileUsername').value = "<?php echo addslashes($_SESSION['user_username'] ?? ''); ?>";
        modal.classList.add('active');
        document.body.style.overflow = 'hidden';
    }
}
function closeGlobalProfileModal() {
    var modal = document.getElementById('modalGlobalProfileEdit');
    if (modal) {
        modal.classList.remove('active');
        document.body.style.overflow = '';
    }
}
</script>
