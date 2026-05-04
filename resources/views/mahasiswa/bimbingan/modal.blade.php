<div class="modal-overlay" id="modal-bimb" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal">
    <div class="modal-title">
      <div class="modal-icon"><svg width="17" height="17" fill="none" stroke="var(--primary-mid)" stroke-width="2" viewBox="0 0 24 24"><path d="M12 5v14M5 12h14"/></svg></div>
      Tambah Bimbingan
    </div>
    <div class="form-group"><label>Judul</label><input placeholder="Judul bimbingan..."></div>
    <div class="form-group"><label>Tanggal</label><input type="date" value="2026-05-02"></div>
    <div class="form-group"><label>Aktivitas</label><textarea placeholder="Deskripsi aktivitas..."></textarea></div>
    <div class="form-group"><label>Upload File (Opsional)</label><input type="file"></div>
    <div class="modal-footer">
      <button class="btn btn-cancel" onclick="document.getElementById('modal-bimb').classList.remove('open')">Batal</button>
      <button class="btn btn-primary" onclick="document.getElementById('modal-bimb').classList.remove('open')">Tambah</button>
    </div>
  </div>
</div>
