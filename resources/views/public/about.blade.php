@extends('layouts.public')
@section('title','Tentang Kami')
@section('content')
  <div class="hero an">
    <div class="eyebrow">Tentang Kami</div>
    <h1>Apa itu SITAMA?</h1>
    <p>SITAMA (Sistem Informasi Magang) menyatukan pengelolaan magang dan bimbingan — dari logbook, bimbingan, seminar, hingga penilaian — dalam satu tempat yang rapi untuk mahasiswa, dosen, pembimbing industri, dan kaprodi.</p>
  </div>
  <div class="lead-card an" style="animation-delay:.06s">
    <p>SITAMA dibuat untuk menyederhanakan proses magang yang biasanya tersebar di banyak dokumen dan grup chat. Lewat satu sistem, <b>mahasiswa</b> mencatat logbook &amp; bimbingan, <b>dosen</b> memantau dan menilai, <b>pembimbing industri</b> memberi catatan, dan <b>kaprodi</b> mengawasi keseluruhan program studi.</p>
    <p>Tujuannya sederhana: membuat kegiatan magang lebih <b>transparan, terdokumentasi, dan mudah dipantau</b> oleh semua pihak.</p>
  </div>
  <div class="sec-title an" style="animation-delay:.1s">Visi &amp; Misi</div>
  <div class="grid g2">
    <div class="card an" style="animation-delay:.14s">
      <div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg></div>
      <h3>Visi</h3><p>Menjadi sistem informasi magang yang andal dan memudahkan seluruh proses akademik dan industri dalam satu platform terpadu.</p>
    </div>
    <div class="card an" style="animation-delay:.18s">
      <div class="ic green"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M9 11l3 3L22 4"/><path d="M21 12v7a2 2 0 01-2 2H5a2 2 0 01-2-2V5a2 2 0 012-2h11"/></svg></div>
      <h3>Misi</h3><p>Menyediakan pencatatan yang transparan, mempercepat bimbingan &amp; penilaian, dan menghubungkan mahasiswa dengan dunia industri secara efisien.</p>
    </div>
  </div>
  <div class="sec-title an" style="animation-delay:.2s">Fitur Utama</div>
  <div class="grid g3">
    <div class="card an" style="animation-delay:.24s"><div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></div><h3>Logbook</h3><p>Catat aktivitas harian magang dan dapatkan catatan dari pembimbing.</p></div>
    <div class="card an" style="animation-delay:.28s"><div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M2 3h6a4 4 0 014 4v14a3 3 0 00-3-3H2z"/><path d="M22 3h-6a4 4 0 00-4 4v14a3 3 0 013-3h7z"/></svg></div><h3>Bimbingan</h3><p>Ajukan bimbingan ke dosen, lampirkan file, dan pantau status persetujuan.</p></div>
    <div class="card an" style="animation-delay:.32s"><div class="ic amber"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div><h3>Seminar &amp; Nilai</h3><p>Ajukan jadwal seminar dan lihat rekap nilai akhir magang.</p></div>
  </div>
@endsection
