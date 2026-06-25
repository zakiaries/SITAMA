<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menunggu Persetujuan — SITAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="{{ asset('css/sitama.css') }}">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Archivo', 'Segoe UI', sans-serif;
      background: var(--warm);
      padding: 24px;
    }
    .card {
      background: #fff;
      border-radius: 24px;
      box-shadow: 0 8px 40px rgba(45,62,110,0.10);
      padding: 48px 40px;
      max-width: 460px;
      width: 100%;
      text-align: center;
    }
    .icon-wrap {
      width: 80px; height: 80px;
      background: var(--warn-bg);
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px;
      font-size: 36px;
    }
    h1 { font-size: 22px; font-weight: 800; color: var(--primary); margin-bottom: 10px; }
    p  { font-size: 14px; color: var(--text-muted); line-height: 1.7; margin-bottom: 8px; }
    .name { font-weight: 700; color: var(--primary); }
    .divider { height: 1px; background: var(--border); margin: 24px 0; }
    .info-box {
      background: var(--blue-tint);
      border: 1.5px solid var(--blue-tint);
      border-radius: 12px;
      padding: 16px;
      font-size: 13px;
      color: var(--text);
      text-align: left;
      line-height: 1.8;
    }
    .info-box strong { color: var(--primary); }
    .btn-logout {
      display: inline-block;
      margin-top: 28px;
      padding: 12px 28px;
      background: var(--primary);
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 14px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      text-decoration: none;
      transition: background .2s;
    }
    .btn-logout:hover { background: var(--primary); }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon-wrap" style="color:var(--warn-text);"><x-icon name="clock" :size="36"/></div>
    <h1>Menunggu Persetujuan</h1>
    <p>Halo, <span class="name">{{ Auth::user()->name }}</span>.</p>
    <p>Akun Anda sudah terdaftar dan sedang dalam proses verifikasi oleh Kaprodi.</p>

    <div class="divider"></div>

    <div class="info-box">
      <strong>Yang perlu Anda ketahui:</strong><br>
      • Proses verifikasi dilakukan oleh Kaprodi program studi Anda.<br>
      • Setelah disetujui, Anda dapat langsung menggunakan semua fitur SITAMA.<br>
      • Jika ada pertanyaan, hubungi Kaprodi secara langsung.
    </div>

    <form method="POST" action="{{ route('logout') }}">
      @csrf
      <button type="submit" class="btn-logout">Keluar</button>
    </form>
  </div>
</body>
</html>
