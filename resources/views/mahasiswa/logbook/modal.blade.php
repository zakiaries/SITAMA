<div class="modal-overlay" id="modal-logbook" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">
      <div class="modal-icon"><svg width="17" height="17" fill="none" stroke="var(--primary-mid)" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div>
      Tambah Log Book
    </div>
    <div class="form-group"><label>Judul</label><input placeholder="Judul aktivitas..."></div>
    <div class="form-group"><label>Tanggal</label><input type="date" value="2026-05-02"></div>
    <div class="form-group"><label>Aktivitas</label><textarea placeholder="Deskripsi aktivitas..."></textarea></div>
    <div class="modal-footer">
      <button class="btn btn-cancel" onclick="document.getElementById('modal-logbook').classList.remove('open')">Batal</button>
      <button class="btn btn-primary" onclick="document.getElementById('modal-logbook').classList.remove('open')">Tambah</button>
    </div>
  </div>
</div>
