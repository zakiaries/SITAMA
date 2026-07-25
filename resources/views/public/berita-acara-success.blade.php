<!DOCTYPE html>
<html lang="id">
<head>
<meta charset="UTF-8">
<meta name="viewport" content="width=device-width, initial-scale=1.0">
<title>Kehadiran Tercatat — SIMAMA</title>
<style>
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#eef2f9;color:#1e2430;
       min-height:100vh;display:flex;align-items:center;justify-content:center;padding:24px;}
  .card{max-width:420px;width:100%;background:#fff;border-radius:18px;padding:34px 26px;text-align:center;box-shadow:0 8px 28px rgba(20,30,60,.1);}
  .check{width:72px;height:72px;border-radius:50%;background:#e0f7ef;color:#0AC27D;display:flex;align-items:center;justify-content:center;margin:0 auto 18px;}
  h1{font-size:20px;font-weight:800;margin-bottom:8px;}
  p{font-size:14px;color:#6b7480;line-height:1.65;}
  .sem{margin-top:16px;background:#f7f9fc;border-radius:12px;padding:14px;font-size:13px;color:#37414f;}
  .sem b{display:block;font-size:14px;color:#1e2430;margin-bottom:2px;}
</style>
</head>
<body>
  <div class="card">
    <div class="check">
      <svg width="38" height="38" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg>
    </div>
    <h1>Terima Kasih!</h1>
    <p>Kehadiran Anda sudah tercatat di berita acara seminar. Anda boleh menutup halaman ini.</p>
    <div class="sem">
      <b>{{ $seminar->title }}</b>
      @if($seminar->date){{ $seminar->date->format('d M Y') }}@endif @if($seminar->time)· {{ $seminar->time }}@endif
    </div>
  </div>
</body>
</html>
