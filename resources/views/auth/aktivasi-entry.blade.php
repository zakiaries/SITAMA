<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aktivasi Akun — SIMAMA</title>
  <link rel="stylesheet" href="{{ asset('css/simama.css') }}">
  <style>
    body { min-height:100vh; display:flex; align-items:center; justify-content:center;
      background:radial-gradient(1200px 600px at 50% -10%,#EAF1FF,#F2F4FF 45%,#EEF1F8); padding:24px; }
    .box { background:#fff; border-radius:16px; padding:36px 32px; width:100%; max-width:440px; box-shadow:0 20px 60px rgba(30,40,90,.14); }
    .logo { font-size:22px; font-weight:800; color:var(--primary); margin-bottom:2px; letter-spacing:1px; }
    .logo-sub { font-size:12px; color:var(--text-muted); margin-bottom:22px; }
    .title { font-size:18px; font-weight:800; color:var(--text); margin-bottom:6px; }
    .sub   { font-size:13px; color:var(--text-secondary); margin-bottom:20px; line-height:1.6; }
    .form-label { font-size:12px; font-weight:600; color:var(--text); display:block; margin-bottom:6px; }
    .form-input { width:100%; padding:11px 13px; border:1.5px solid var(--border); border-radius:10px; font-size:13.5px; font-family:inherit; box-sizing:border-box; background:var(--warm); outline:none; }
    .form-input:focus { border-color:var(--primary); background:#fff; }
    .hint { font-size:11.5px; color:var(--text-muted); margin-top:6px; line-height:1.5; }
    .err  { display:none; color:var(--error); font-size:12.5px; margin-top:8px; }
    .back { display:block; text-align:center; margin-top:16px; font-size:13px; color:var(--text-secondary); }
    .back a { color:var(--primary); font-weight:700; }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">SIMAMA</div>
  <div class="logo-sub">Sistem Informasi Magang</div>

  <div class="title">Aktivasi Akun Pembimbing Industri</div>
  <p class="sub">Akun pembimbing industri dibuat otomatis saat Kaprodi menyetujui pengajuan magang mahasiswa.
    Cek <strong>email</strong> Anda untuk <strong>link aktivasi</strong>, lalu tempel token atau link tersebut di bawah ini.</p>

  <label class="form-label">Token / Link Aktivasi</label>
  <input class="form-input" id="tok" type="text" placeholder="Tempel link dari email atau kode token" autofocus
         onkeydown="if(event.key==='Enter'){go();}">
  <div class="hint">Contoh link: <code>{{ url('/aktivasi') }}/xxxxxxxx</code> — boleh tempel link penuh, sistem akan mengambil tokennya.</div>
  <div class="err" id="err">Masukkan token atau link aktivasi terlebih dahulu.</div>

  <button type="button" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:18px;" onclick="go()">
    Lanjutkan
  </button>

  <div class="back">Sudah punya akun? <a href="{{ route('login') }}">Kembali ke Login</a></div>
</div>

<script>
  function go(){
    var raw = document.getElementById('tok').value.trim();
    if(!raw){ document.getElementById('err').style.display='block'; return; }
    var token = raw;
    var i = raw.indexOf('/aktivasi/');
    if(i > -1){ token = raw.substring(i + '/aktivasi/'.length).split(/[?#]/)[0]; }
    token = token.replace(/\/+$/, '');
    window.location.href = '{{ url('/aktivasi') }}/' + encodeURIComponent(token);
  }
</script>
</body>
</html>
