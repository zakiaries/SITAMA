@extends('layouts.public')
@section('title','Bantuan')
@section('content')
  <div class="hero an">
    <div class="eyebrow">Pusat Bantuan</div>
    <h1>Ada yang bisa kami bantu?</h1>
    <p>Temukan jawaban dari pertanyaan yang sering diajukan seputar pendaftaran, akun, dan penggunaan SIMAMA.</p>
  </div>
  <div class="sec-title an">Pertanyaan yang sering diajukan</div>
  <div class="faq an" style="animation-delay:.06s">
    <div class="faq-item open">
      <div class="faq-q"><span class="lic"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><path d="M9.09 9a3 3 0 015.83 1c0 2-3 3-3 3"/><line x1="12" y1="17" x2="12.01" y2="17"/></svg></span>Bagaimana cara mendaftar akun?<span class="chev"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></span></div>
      <div class="faq-wrap"><div class="faq-inner"><div class="faq-a">Klik "Daftar di sini" pada halaman login, lalu isi data akun dan data akademik. Akun akan diverifikasi oleh Kaprodi sebelum bisa digunakan.</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q"><span class="lic"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>Saya lupa kata sandi, bagaimana?<span class="chev"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></span></div>
      <div class="faq-wrap"><div class="faq-inner"><div class="faq-a">Hubungi Kaprodi atau admin program studi untuk melakukan reset kata sandi akunmu.</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q"><span class="lic"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="3" width="18" height="18" rx="2"/><path d="M3 9h18M9 21V9"/></svg></span>Bagaimana cara mengisi logbook?<span class="chev"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></span></div>
      <div class="faq-wrap"><div class="faq-inner"><div class="faq-a">Masuk sebagai mahasiswa, buka menu Log Book, klik "Tambah Log Book", lalu isi tanggal dan aktivitasmu. Pembimbing kampus &amp; industri dapat memberi catatan.</div></div></div>
    </div>
    <div class="faq-item">
      <div class="faq-q"><span class="lic"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></span>Siapa yang menyetujui pendaftaran saya?<span class="chev"><svg width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><polyline points="6 9 12 15 18 9"/></svg></span></div>
      <div class="faq-wrap"><div class="faq-inner"><div class="faq-a">Ketua Program Studi (Kaprodi) meninjau dan menyetujui setiap pendaftar baru sebelum akun aktif.</div></div></div>
    </div>
  </div>
  <div class="cta an" style="animation-delay:.1s">
    <div style="flex:1;min-width:220px;"><h3>Masih butuh bantuan?</h3><p>Tim kami siap membantu lewat email atau telepon.</p></div>
    <a href="{{ route('contact') }}" class="btn btn-primary"><svg width="15" height="15" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg> Hubungi Kami</a>
  </div>
@endsection
