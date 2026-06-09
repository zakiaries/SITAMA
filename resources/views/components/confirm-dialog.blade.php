{{-- Dialog konfirmasi/peringatan kustom (pengganti native alert/confirm) --}}
<style>
  .cd-overlay {
    position: fixed; inset: 0; z-index: 9999;
    background: rgba(15, 23, 42, 0.45);
    display: none; align-items: center; justify-content: center;
    padding: 20px;
  }
  .cd-overlay.open { display: flex; }
  .cd-box {
    background: #fff; border-radius: 14px; width: 100%; max-width: 360px;
    padding: 24px; text-align: center; box-shadow: 0 20px 50px rgba(0,0,0,0.25);
    font-family: inherit;
  }
  .cd-icon {
    width: 48px; height: 48px; border-radius: 50%; margin: 0 auto 14px;
    display: flex; align-items: center; justify-content: center;
    font-size: 24px; font-weight: 800;
    background: #fef3c7; color: #b45309;
  }
  .cd-overlay.cd-danger .cd-icon { background: #fee2e2; color: #dc2626; }
  .cd-overlay.cd-info   .cd-icon { background: #dbeafe; color: #2563eb; }
  .cd-msg { font-size: 14px; color: #1e293b; line-height: 1.5; margin-bottom: 20px; }
  .cd-actions { display: flex; gap: 10px; justify-content: center; }
  .cd-btn {
    padding: 9px 20px; border-radius: 8px; font-size: 13px; font-weight: 600;
    cursor: pointer; font-family: inherit; border: 1.5px solid transparent;
  }
  .cd-cancel { background: #fff; color: #475569; border-color: #cbd5e1; }
  .cd-cancel:hover { background: #f1f5f9; }
  .cd-ok { background: #2d3e6e; color: #fff; }
  .cd-ok:hover { filter: brightness(1.08); }
  .cd-overlay.cd-danger .cd-ok { background: #dc2626; }
</style>

<div class="cd-overlay" id="confirm-dialog">
  <div class="cd-box">
    <div class="cd-icon" id="confirm-dialog-icon">?</div>
    <div class="cd-msg" id="confirm-dialog-msg"></div>
    <div class="cd-actions">
      <button type="button" class="cd-btn cd-cancel" id="confirm-dialog-cancel">Batal</button>
      <button type="button" class="cd-btn cd-ok" id="confirm-dialog-ok">Ya</button>
    </div>
  </div>
</div>

<script>
(function () {
  var overlay  = document.getElementById('confirm-dialog');
  var iconEl   = document.getElementById('confirm-dialog-icon');
  var msgEl    = document.getElementById('confirm-dialog-msg');
  var okBtn    = document.getElementById('confirm-dialog-ok');
  var cancelBtn = document.getElementById('confirm-dialog-cancel');

  var pendingForm = null;
  var pendingCb   = null;

  function reset() {
    overlay.classList.remove('cd-danger', 'cd-info');
  }
  function close() {
    overlay.classList.remove('open');
    pendingForm = null;
    pendingCb = null;
  }

  // Konfirmasi Ya/Batal. variant: 'danger' | 'default'
  window.confirmDialog = function (message, onConfirm, variant) {
    reset();
    pendingForm = null;
    pendingCb = onConfirm || null;
    msgEl.textContent = message || 'Apakah Anda yakin?';
    iconEl.textContent = variant === 'danger' ? '!' : '?';
    if (variant === 'danger') overlay.classList.add('cd-danger');
    cancelBtn.style.display = '';
    okBtn.textContent = 'Ya';
    overlay.classList.add('open');
  };

  // Pesan informasi (pengganti alert). Hanya tombol OK.
  window.alertDialog = function (message) {
    reset();
    overlay.classList.add('cd-info');
    pendingForm = null;
    pendingCb = null;
    msgEl.textContent = message || '';
    iconEl.textContent = 'i';
    cancelBtn.style.display = 'none';
    okBtn.textContent = 'Mengerti';
    overlay.classList.add('open');
  };

  // Form dengan atribut data-confirm akan otomatis dikonfirmasi.
  document.addEventListener('submit', function (e) {
    var form = e.target;
    if (form && form.matches && form.matches('form[data-confirm]')) {
      e.preventDefault();
      reset();
      pendingForm = form;
      pendingCb = null;
      msgEl.textContent = form.getAttribute('data-confirm');
      iconEl.textContent = form.hasAttribute('data-confirm-danger') ? '!' : '?';
      if (form.hasAttribute('data-confirm-danger')) overlay.classList.add('cd-danger');
      cancelBtn.style.display = '';
      okBtn.textContent = 'Ya';
      overlay.classList.add('open');
    }
  }, true);

  okBtn.addEventListener('click', function () {
    var f = pendingForm, cb = pendingCb;
    close();
    if (f) { f.submit(); }           // .submit() melewati listener, tidak loop
    else if (cb) { cb(); }
  });
  cancelBtn.addEventListener('click', close);
  overlay.addEventListener('click', function (e) { if (e.target === overlay) close(); });
})();
</script>
