<div class="modal-overlay" id="modal-bimb" onclick="if(event.target===this)this.classList.remove('open')">
  <div class="modal-box">
    <div class="modal-header">
      <div class="modal-title">Tambah Bimbingan</div>
      <button class="modal-close" onclick="document.getElementById('modal-bimb').classList.remove('open')">✕</button>
    </div>
    <form method="POST" action="{{ route('mahasiswa.bimbingan.store') }}" enctype="multipart/form-data">
      @csrf
      <div class="form-group">
        <label>Judul Bimbingan</label>
        <input type="text" name="title" placeholder="Contoh: Konsultasi Progress Magang" required>
      </div>
      <div class="form-group">
        <label>Tanggal</label>
        <input type="date" name="date" required>
      </div>
      <div class="form-group">
        <label>Aktivitas / Deskripsi</label>
        <textarea name="activity" rows="4" placeholder="Ceritakan aktivitas bimbingan..." required style="width:100%;padding:10px 12px;border:1.5px solid var(--border);border-radius:8px;font-size:13px;resize:vertical;"></textarea>
      </div>
      <div class="form-group">
        <label>Upload File (opsional)</label>
        <input type="file" name="file" accept=".pdf,.doc,.docx">
      </div>
      <div style="display:flex;gap:10px;justify-content:flex-end;margin-top:16px;">
        <button type="button" class="btn btn-outline" onclick="document.getElementById('modal-bimb').classList.remove('open')">Batal</button>
        <button type="submit" class="btn btn-primary">Simpan</button>
      </div>
    </form>
  </div>
</div>
