<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Reset Password - SIMAMA</title>
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
    .form-group { margin-bottom:14px; }
    .error-msg { color:#dc2626; font-size:12px; margin-top:4px; }
    .back { display:block; text-align:center; margin-top:16px; font-size:12.5px; color:var(--primary); }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">SIMAMA</div>
  <div class="logo-sub">Sistem Informasi Magang · Polines</div>

  <div class="title">Buat Password Baru</div>
  <p class="sub">Masukkan password baru untuk akunmu.</p>

  @if($errors->any())
    <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 12px;font-size:12px;color:#dc2626;margin-bottom:14px;">
      {{ $errors->first() }}
    </div>
  @endif

  <form method="POST" action="{{ route('password.update') }}">
    @csrf
    <input type="hidden" name="token" value="{{ $token }}">
    <input type="hidden" name="email" value="{{ $email }}">
    <div class="form-group">
      <label class="form-label">Password Baru <span style="color:#dc2626;">*</span></label>
      <input class="form-input" type="password" name="password" placeholder="Minimal 8 karakter" required minlength="8" autocomplete="new-password" autofocus>
      @error('password')<div class="error-msg">{{ $message }}</div>@enderror
    </div>
    <div class="form-group">
      <label class="form-label">Konfirmasi Password <span style="color:#dc2626;">*</span></label>
      <input class="form-input" type="password" name="password_confirmation" placeholder="Ulangi password baru" required minlength="8" autocomplete="new-password">
    </div>
    <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:6px;">Simpan Password Baru</button>
  </form>

  <a class="back" href="{{ route('login') }}">← Kembali ke Login</a>
</div>
</body>
</html>
