<?php // includes/dash_footer.php ?>
<footer class="dash-footer">
    <i class="fas fa-mosque"></i>
    <span>&copy; <?php echo date('Y'); ?> Ponpes Al-Barokah An-Nur Khumairoh &mdash; All Rights Reserved</span>
</footer>

<!-- Custom Confirm Modal (Global) -->
<div class="confirm-overlay" id="customConfirm">
    <div class="confirm-box">
        <i class="fas fa-question-circle confirm-icon" id="confirmIcon"></i>
        <div class="confirm-title" id="confirmTitle">Konfirmasi</div>
        <div class="confirm-msg" id="confirmMsg">Apakah Anda yakin?</div>
        <div class="confirm-actions">
            <button type="button" class="btn-confirm-cancel" onclick="closeConfirm()">Batal</button>
            <button type="button" class="btn-confirm-yes" id="confirmYesBtn">Ya, Lanjutkan</button>
        </div>
    </div>
</div>
