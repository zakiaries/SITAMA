<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Lupa Password - SIMAMA</title>
  <link rel="stylesheet" href="{{ asset('css/simama.css') }}">
  <style>
    body { min-height:100vh; display:flex; align-items:center; justify-content:center; background:#f1f5f9; }
    .box { background:#fff; border-radius:14px; padding:36px 32px; width:100%; max-width:420px; box-shadow:0 4px 24px rgba(0,0,0,.09); }
    .logo { font-size:22px; font-weight:800; color:var(--primary); margin-bottom:4px; }
    .logo-sub { font-size:12px; color:var(--text-muted); margin-bottom:24px; }
    .title { font-size:17px; font-weight:700; color:var(--text); margin-bottom:6px; }
    .sub   { font-size:13px; color:var(--text-muted); margin-bottom:20px; }
    .form-label { font-size:12px; font-weight:600; color:var(--text); display:block; margin-bottom:5px; }
    .form-input { width:100%; padding:10px 12px; border:1.5px solid var(--border); border-radius:8px; font-size:13px; font-family:inherit; box-sizing:border-box; }
    .form-input:focus { outline:none; border-color:var(--primary); }
    .error-msg { color:#dc2626; font-size:12px; margin-top:4px; }
    .back { display:block; text-align:center; margin-top:16px; font-size:12.5px; color:var(--primary); }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">SIMAMA</div>
  <div class="logo-sub">Sistem Informasi Magang · Polines</div>

  <div class="title">Lupa Password</div>
  <p class="sub">Masukkan <strong>NIM / username</strong> akunmu. Link untuk membuat password baru akan dikirim ke <strong>email yang kamu daftarkan</strong>.</p>

  @if(session('status'))
    <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 12px;font-size:12px;color:#16a34a;margin-bottom:14px;">
      {{ session('status') }}
    </div>
  @endif

  <form method="POST" action="{{ route('password.email') }}">
    @csrf
    <div style="margin-bottom:14px;">
      <label class="form-label">NIM / Username</label>
      <input class="form-input" type="text" name="username" value="{{ old('username') }}"
        placeholder="mis. 3.34.23.2.12" required autofocus>
      @error('username')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;">Kirim Link Reset</button>
  </form>

  <a class="back" href="{{ route('login') }}">← Kembali ke Login</a>
</div>
</body>
</html>
