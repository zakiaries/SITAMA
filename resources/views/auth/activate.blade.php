<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Aktivasi Akun - SIMAMA</title>
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
    .hint { font-size:11.5px; color:var(--text-muted); margin-top:3px; }
  </style>
</head>
<body>
<div class="box">
  <div class="logo">SIMAMA</div>
  <div class="logo-sub">Sistem Informasi Magang · Polines</div>

  @if($expired)
    <div style="text-align:center;padding:12px 0;">
      <div style="font-size:40px;margin-bottom:12px;">⏰</div>
      <div class="title">Link Tidak Valid</div>
      <p style="font-size:13px;color:var(--text-muted);margin-bottom:20px;">
        Link aktivasi ini sudah kedaluwarsa atau tidak valid.<br>
        Hubungi pihak program studi Polines untuk meminta link aktivasi baru.
      </p>
      <a href="{{ route('login') }}" class="btn btn-outline" style="display:block;text-align:center;">← Kembali ke Login</a>
    </div>
  @else
    <div class="title">Aktivasi Akun Pembimbing</div>
    <p class="sub">Halo, <strong>{{ $invitation->user->name }}</strong>. Buat username dan password untuk akun SIMAMA Anda.</p>

    @if($errors->any())
      <div style="background:#fef2f2;border:1px solid #fca5a5;border-radius:8px;padding:10px 12px;font-size:12px;color:#dc2626;margin-bottom:14px;">
        {{ $errors->first() }}
      </div>
    @endif

    @if(session('success'))
      <div style="background:#f0fdf4;border:1px solid #86efac;border-radius:8px;padding:10px 12px;font-size:12px;color:#16a34a;margin-bottom:14px;">
        {{ session('success') }}
      </div>
    @endif

    <form method="POST" action="{{ route('aktivasi.submit', $token) }}">
      @csrf
      <div class="form-group">
        <label class="form-label">Username <span style="color:#dc2626;">*</span></label>
        <input class="form-input" type="text" name="username" value="{{ old('username') }}"
          placeholder="Pilih username untuk login" required maxlength="50" autocomplete="username">
        <div class="hint">Hanya huruf, angka, dan underscore. Akan dipakai untuk login setiap saat.</div>
        @error('username')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Password <span style="color:#dc2626;">*</span></label>
        <div class="pw-wrap">
          <input class="form-input" type="password" name="password"
            placeholder="Minimal 6 karakter" required minlength="6" autocomplete="new-password">
          <x-password-toggle />
        </div>
        @error('password')<div class="error-msg">{{ $message }}</div>@enderror
      </div>
      <div class="form-group">
        <label class="form-label">Konfirmasi Password <span style="color:#dc2626;">*</span></label>
        <div class="pw-wrap">
          <input class="form-input" type="password" name="password_confirmation"
            placeholder="Ulangi password" required minlength="6" autocomplete="new-password">
          <x-password-toggle />
        </div>
      </div>
      <button type="submit" class="btn btn-primary" style="width:100%;justify-content:center;margin-top:6px;">
        Aktifkan Akun
      </button>
    </form>
  @endif
</div>
</body>
</html>
