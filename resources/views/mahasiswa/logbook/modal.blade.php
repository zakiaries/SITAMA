<div class="modal-overlay" id="modal-logbook" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Tambah Log Book</div>
      <button class="modal-close" onclick="document.getElementById('modal-logbook').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('mahasiswa.logbook.store') }}">
      @csrf
      <div class="form-group">
        <label>Judul Kegiatan</label>
        <input type="text" name="title" placeholder="Contoh: Implementasi Fitur Login" required>
      </div>
      <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="date" required>
      </div>
      <div class="form-group">
        <label>Deskripsi Aktivitas</label>
        <textarea name="activity" rows="4" placeholder="Ceritakan aktivitas hari ini..." required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-logbook').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
