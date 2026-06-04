<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Menunggu Persetujuan — SITAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh;
      display: flex;
      align-items: center;
      justify-content: center;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
      background: #f0ece3;
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
      background: #fef9c3;
      border-radius: 50%;
      display: flex; align-items: center; justify-content: center;
      margin: 0 auto 24px;
      font-size: 36px;
    }
    h1 { font-size: 22px; font-weight: 800; color: #1e2a4a; margin-bottom: 10px; }
    p  { font-size: 14px; color: #6b7280; line-height: 1.7; margin-bottom: 8px; }
    .name { font-weight: 700; color: #2d3e6e; }
    .divider { height: 1px; background: #e5e7eb; margin: 24px 0; }
    .info-box {
      background: #f8faff;
      border: 1.5px solid #dce6f5;
      border-radius: 12px;
      padding: 16px;
      font-size: 13px;
      color: #374151;
      text-align: left;
      line-height: 1.8;
    }
    .info-box strong { color: #1e2a4a; }
    .btn-logout {
      display: inline-block;
      margin-top: 28px;
      padding: 12px 28px;
      background: #2d3e6e;
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
    .btn-logout:hover { background: #3a52a0; }
  </style>
</head>
<body>
  <div class="card">
    <div class="icon-wrap">⏳</div>
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
