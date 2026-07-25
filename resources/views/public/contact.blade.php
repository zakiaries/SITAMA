@extends('layouts.public')
@section('title','Hubungi Kami')
@section('content')
  <div class="hero an">
    <div class="eyebrow">Kontak</div>
    <h1>Hubungi Kami</h1>
    <p>Punya pertanyaan atau kendala? Kirim pesan, atau hubungi kami lewat kontak di bawah ini.</p>
  </div>
  <div class="grid g2">
    <div>
      <div class="card an" style="margin-bottom:14px;">
        <div class="cinfo" style="margin-bottom:18px;"><div class="ic"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="2" y="4" width="20" height="16" rx="2"/><path d="M22 6l-10 7L2 6"/></svg></div><div><div class="lbl">Email</div><div class="val">support@simama.ac.id</div></div></div>
        <div class="cinfo" style="margin-bottom:18px;"><div class="ic green"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M22 16.92v3a2 2 0 01-2.18 2 19.8 19.8 0 01-8.63-3.07 19.5 19.5 0 01-6-6A19.8 19.8 0 012.12 4.18 2 2 0 014.11 2h3a2 2 0 012 1.72c.13.96.36 1.9.7 2.81a2 2 0 01-.45 2.11L8.09 9.91a16 16 0 006 6l1.27-1.27a2 2 0 012.11-.45c.91.34 1.85.57 2.81.7A2 2 0 0122 16.92z"/></svg></div><div><div class="lbl">Telepon</div><div class="val">(0341) 123-4567</div></div></div>
        <div class="cinfo"><div class="ic amber"><svg width="21" height="21" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M21 10c0 7-9 13-9 13s-9-6-9-13a9 9 0 0118 0z"/><circle cx="12" cy="10" r="3"/></svg></div><div><div class="lbl">Alamat</div><div class="val">Jl. Soekarno-Hatta No. 9, Malang</div></div></div>
      </div>
      <div class="card an" style="animation-delay:.06s">
        <h3 style="margin-bottom:12px;">Jam Operasional</h3>
        <div class="hours-row"><span>Senin – Jumat</span><span>08.00 – 16.00</span></div>
        <div class="hours-row"><span>Sabtu</span><span>08.00 – 12.00</span></div>
        <div class="hours-row"><span>Minggu &amp; Libur</span><span>Tutup</span></div>
      </div>
    </div>
    <div class="card an" style="animation-delay:.1s">
      <h3 style="margin-bottom:16px;">Kirim Pesan</h3>
      <div class="field"><label>Nama</label><input type="text" placeholder="Nama lengkap"></div>
      <div class="field"><label>Email</label><input type="email" placeholder="email@contoh.com"></div>
      <div class="field"><label>Pesan</label><textarea placeholder="Tulis pesanmu di sini..."></textarea></div>
      <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;height:46px;"><svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><line x1="22" y1="2" x2="11" y2="13"/><polygon points="22 2 15 22 11 13 2 9 22 2"/></svg> Kirim Pesan</button>
    </div>
  </div>
@endsection
