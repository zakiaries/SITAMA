<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>QR Daftar Hadir — {{ $seminar->title }}</title>
  @php $reload = max(5, (int) ceil($interval / 2)); @endphp
  <style>
    *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
    body{font-family:'Segoe UI',system-ui,-apple-system,sans-serif;background:#0b1220;color:#fff;
         min-height:100vh;display:flex;flex-direction:column;align-items:center;justify-content:center;padding:24px;}
    .title{font-size:24px;font-weight:800;text-align:center;margin-bottom:4px;}
    .meta{font-size:14px;color:#9fb2d0;text-align:center;margin-bottom:20px;}
    .qrbox{background:#fff;border-radius:20px;padding:22px;box-shadow:0 12px 40px rgba(0,0,0,.4);}
    .qrbox svg{display:block;width:min(60vw,340px);height:auto;}
    .hint{font-size:15px;color:#cdd8ea;text-align:center;margin-top:20px;max-width:460px;line-height:1.6;}
    .live{display:inline-flex;align-items:center;gap:7px;font-size:13px;color:#7fe0b0;margin-top:14px;font-weight:600;}
    .dot{width:9px;height:9px;border-radius:50%;background:#22c55e;animation:pulse 1.4s infinite;}
    @keyframes pulse{0%,100%{opacity:1;}50%{opacity:.3;}}
    .count{font-size:16px;color:#fff;margin-top:10px;font-weight:700;}
    .bar{width:min(60vw,340px);height:4px;background:#1e2b45;border-radius:4px;margin-top:18px;overflow:hidden;}
    .bar span{display:block;height:100%;background:#0061FF;width:100%;animation:shrink {{ $reload }}s linear forwards;}
    @keyframes shrink{from{width:100%;}to{width:0;}}
  </style>
</head>
<body>
  <div class="title">{{ $seminar->title }}</div>
  <div class="meta">
    @if($seminar->date){{ $seminar->date->format('d M Y') }}@endif
    @if($seminar->time) · {{ $seminar->time }}@endif
    @if($seminar->location) · {{ $seminar->location }}@endif
  </div>

  <div class="qrbox">{!! $qrSvg !!}</div>
  <div class="bar"><span></span></div>

  <div class="count">{{ $guests }} / {{ $seminar->minGuests() }} audiens hadir</div>
  <div class="live"><span class="dot"></span> QR berganti otomatis — pindai langsung dari layar</div>
  <div class="hint">Audiens: buka kamera / scanner, pindai QR di layar ini, login SIMAMA, lalu tekan <strong>Saya Hadir</strong>. Tautan hasil screenshot/share akan kedaluwarsa.</div>

  <script>
    // Muat ulang halaman untuk mendapatkan QR (token) terbaru & memperbarui jumlah hadir.
    setTimeout(function(){ location.reload(); }, {{ $reload }} * 1000);
  </script>
</body>
</html>
