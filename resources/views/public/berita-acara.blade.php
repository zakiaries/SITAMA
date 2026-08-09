<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
{{-- Cubit-perbesar TIDAK dikunci. `maximum-scale=1.0, user-scalable=no` dulu
     ada di sini karena halaman ini memuat kanvas tanda tangan: gerakan mencubit
     akan beradu dengan gerakan menggores. Kanvas itu sudah lama dibongkar —
     kehadiran kini dicatat dari akun yang login, sehingga isi halaman tinggal
     teks dan satu tombol. Yang tersisa dari kunci itu hanya ruginya: audiens
     yang matanya kurang awas tak bisa memperbesar tulisan untuk memastikan
     namanya benar sebelum menekan Hadir. --}}
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Daftar Hadir Seminar — SIMAMA</title>
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#eef2f9;color:#1e2430;
       min-height:100vh;padding:18px 14px 40px;-webkit-font-smoothing:antialiased;}
  .wrap{max-width:460px;margin:0 auto;}
  .head{background:linear-gradient(150deg,#0061FF,#0048BD);color:#fff;border-radius:16px;padding:20px 18px;margin-bottom:16px;box-shadow:0 8px 24px rgba(0,72,189,.22);}
  .head .eyebrow{font-size:11px;font-weight:700;letter-spacing:.12em;text-transform:uppercase;opacity:.82;}
  .head h1{font-size:19px;font-weight:800;margin-top:6px;line-height:1.25;}
  .head .meta{font-size:12.5px;opacity:.9;margin-top:10px;line-height:1.7;}
  .card{background:#fff;border-radius:16px;padding:22px 18px;box-shadow:0 2px 10px rgba(20,30,60,.06);text-align:center;}
  .card h2{font-size:15px;font-weight:800;margin-bottom:6px;}
  .card .sub{font-size:12.5px;color:#6b7480;margin-bottom:18px;line-height:1.6;}
  .who{background:#f2f6ff;border:1px solid #d7e3ff;border-radius:12px;padding:12px 14px;margin-bottom:18px;text-align:left;}
  .who .n{font-size:14px;font-weight:700;color:#1e2430;}
  .who .i{font-size:12px;color:#6b7480;margin-top:2px;}
  .submit{width:100%;background:#0061FF;color:#fff;border:none;border-radius:12px;padding:14px;font-size:15px;font-weight:800;cursor:pointer;font-family:inherit;transition:.15s;}
  .submit:hover{background:#0048BD;}
  .ok{background:#e0f7ef;border:1px solid #a7e8cf;color:#07976a;border-radius:12px;padding:14px;font-size:13.5px;font-weight:700;}
  .err{background:#fdeceb;border:1px solid #f4c4bf;color:#c0392b;border-radius:10px;padding:10px 13px;font-size:12.5px;margin-bottom:14px;text-align:left;}
  .foot{text-align:center;font-size:11.5px;color:#9aa3af;margin-top:18px;}
</style>
</head>
<body>
<div class="wrap">

  <div class="head">
    <div class="eyebrow">Daftar Hadir Seminar</div>
    <h1>{{ $seminar->title }}</h1>
    <div class="meta">
      @if($seminar->date){{ $seminar->date->format('d M Y') }}@endif
      @if($seminar->time) · {{ $seminar->time }}@endif
      @if($seminar->location)<br>{{ $seminar->location }}@endif
      @if($seminar->presenters->count())<br>Penyaji: {{ $seminar->presenters->map(fn($p) => $p->student->user->name ?? '-')->join(', ') }}@endif
    </div>
  </div>

  <div class="card">
    @if($errors->any())
      <div class="err">{{ $errors->first() }}</div>
    @endif

    @if($already)
      <div class="ok">✓ Kamu sudah tercatat hadir di seminar ini. Terima kasih!</div>
    @elseif(! $rtValid)
      <h2>Pindai QR Terbaru</h2>
      <div class="sub">
        QR daftar hadir <strong>berganti otomatis</strong> tiap beberapa detik untuk mencegah titip absen.
        Pindai langsung QR yang sedang <strong>ditampilkan dosen di layar</strong> — tautan lama / hasil
        share tidak berlaku.
      </div>
      <div class="err" style="text-align:center;margin-bottom:0;">QR kedaluwarsa atau tidak valid.</div>
    @else
      <h2>Konfirmasi Kehadiran</h2>
      <div class="sub">Kehadiranmu akan dicatat atas nama akun ini. Satu akun terhitung satu kali.</div>
      <div class="who">
        <div class="n">{{ Auth::user()->name }}</div>
        <div class="i">NIM: {{ Auth::user()->username }}</div>
      </div>
      <form method="POST" action="{{ route('berita-acara.store', $seminar->access_token) }}">
        @csrf
        <input type="hidden" name="rt" value="{{ $rt }}">
        <button type="submit" class="submit">Saya Hadir</button>
      </form>
    @endif
  </div>

  <div class="foot">SIMAMA · Sistem Informasi Magang</div>
</div>
</body>
</html>
