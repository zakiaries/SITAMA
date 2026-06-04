<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Daftar — SITAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    body {
      min-height: 100vh; display: flex;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
      background: #f0ece3;
    }
    .left-panel {
      width: 45%; min-height: 100vh; background: #2d3e6e;
      display: flex; flex-direction: column; justify-content: center;
      align-items: center; padding: 48px 40px; position: relative; overflow: hidden;
    }
    .left-panel::before {
      content: ''; position: absolute; inset: 0;
      background-image: url('{{ asset("images/pattern.png") }}');
      background-size: 220px; background-repeat: repeat; opacity: 0.08;
    }
    .left-content { position: relative; z-index: 1; text-align: center; }
    .logo-wrap { margin: 0 auto 24px; width: 110px; height: 110px; filter: drop-shadow(0 4px 16px rgba(0,0,0,0.25)); }
    .logo-wrap img { width: 110px; height: 110px; object-fit: contain; }
    .brand-name { font-size: 36px; font-weight: 800; color: #fff; letter-spacing: 3px; margin-bottom: 12px; }
    .brand-tagline { font-size: 18px; font-weight: 600; color: rgba(255,255,255,0.9); margin-bottom: 10px; }
    .brand-sub { font-size: 13px; color: rgba(255,255,255,0.6); line-height: 1.6; max-width: 280px; margin: 0 auto; }
    .left-divider { width: 40px; height: 3px; background: rgba(255,255,255,0.3); border-radius: 2px; margin: 20px auto; }

    .right-panel {
      flex: 1; display: flex; flex-direction: column; align-items: center;
      justify-content: center; background: #f0ece3; padding: 40px 24px;
    }
    .register-card {
      width: 100%; max-width: 480px; background: #fff;
      border-radius: 24px; overflow: hidden;
      box-shadow: 0 8px 40px rgba(45,62,110,0.10);
    }
    .form-block { padding: 32px 32px 36px; }
    .form-title { font-size: 22px; font-weight: 800; color: #1e2a4a; margin-bottom: 4px; }
    .form-subtitle { font-size: 13px; color: #9ca3af; margin-bottom: 24px; }

    .alert-error {
      background: #fef2f2; border: 1px solid #fca5a5; color: #dc2626;
      padding: 10px 14px; border-radius: 10px; font-size: 13px; margin-bottom: 18px;
    }

    .row-2 { display: grid; grid-template-columns: 1fr 1fr; gap: 12px; }
    .field { margin-bottom: 14px; }
    .field label { display: block; font-size: 12px; font-weight: 600; color: #374151; margin-bottom: 6px; }
    .field input, .field select {
      width: 100%; padding: 11px 14px;
      border: 1.5px solid #e5e7eb; border-radius: 10px;
      font-size: 13px; font-family: inherit; color: #1e2a4a; background: #fafafa;
      outline: none; transition: border-color .2s, box-shadow .2s;
    }
    .field input::placeholder { color: #b0b7c3; }
    .field input:focus, .field select:focus {
      border-color: #2d3e6e; background: #fff;
      box-shadow: 0 0 0 3px rgba(45,62,110,0.08);
    }
    .section-label {
      font-size: 11px; font-weight: 700; color: #9ca3af;
      text-transform: uppercase; letter-spacing: 0.8px;
      margin: 18px 0 12px;
    }
    .btn-submit {
      width: 100%; padding: 14px; background: #2d3e6e; color: #fff;
      border: none; border-radius: 12px; font-size: 15px; font-weight: 700;
      font-family: inherit; cursor: pointer; margin-top: 8px;
      transition: background .2s;
    }
    .btn-submit:hover { background: #3a52a0; }
    .login-link { text-align: center; margin-top: 16px; font-size: 13px; color: #6b7280; }
    .login-link a { color: #2d3e6e; font-weight: 700; text-decoration: none; }
    .login-link a:hover { text-decoration: underline; }

    @media (max-width: 768px) {
      body { flex-direction: column; }
      .left-panel { width: 100%; min-height: auto; padding: 28px 24px; }
      .brand-name { font-size: 26px; }
      .right-panel { padding: 24px 16px 40px; }
      .form-block { padding: 24px 20px 28px; }
      .row-2 { grid-template-columns: 1fr; }
    }
  </style>
</head>
<body>

  <div class="left-panel">
    <div class="left-content">
      <div class="logo-wrap">
        <img src="{{ asset('images/logo.png') }}" alt="SITAMA Logo">
      </div>
      <div class="brand-name">SITAMA</div>
      <div class="left-divider"></div>
      <div class="brand-tagline">Daftar Akun Mahasiswa</div>
      <div class="brand-sub">Isi data diri Anda untuk mendaftar. Akun akan diverifikasi oleh Kaprodi sebelum aktif.</div>
    </div>
  </div>

  <div class="right-panel">
    <div class="register-card">
      <div class="form-block">
        <div class="form-title">Pendaftaran</div>
        <div class="form-subtitle">Khusus mahasiswa — isi semua data dengan benar</div>

        @if($errors->any())
          <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('register') }}">
          @csrf

          <div class="section-label">Data Akun</div>

          <div class="field">
            <label>Nama Lengkap</label>
            <input type="text" name="name" value="{{ old('name') }}" placeholder="Sesuai KTM" required>
          </div>

          <div class="row-2">
            <div class="field">
              <label>NIM</label>
              <input type="text" name="username" value="{{ old('username') }}" placeholder="Nomor Induk Mahasiswa" required>
            </div>
            <div class="field">
              <label>Email</label>
              <input type="email" name="email" value="{{ old('email') }}" placeholder="email@kampus.ac.id" required>
            </div>
          </div>

          <div class="row-2">
            <div class="field">
              <label>Password</label>
              <input type="password" name="password" placeholder="Min. 8 karakter" required>
            </div>
            <div class="field">
              <label>Konfirmasi Password</label>
              <input type="password" name="password_confirmation" placeholder="Ulangi password" required>
            </div>
          </div>

          <div class="section-label">Data Akademik</div>

          <div class="row-2">
            <div class="field">
              <label>Program Studi</label>
              <input type="text" name="study_program" value="{{ old('study_program') }}" placeholder="cth: Teknik Informatika" required>
            </div>
            <div class="field">
              <label>Jurusan</label>
              <input type="text" name="major" value="{{ old('major') }}" placeholder="cth: TI" required>
            </div>
          </div>

          <div class="row-2">
            <div class="field">
              <label>Kelas</label>
              <input type="text" name="the_class" value="{{ old('the_class') }}" placeholder="cth: TI-6A" required>
            </div>
            <div class="field">
              <label>Tahun Akademik</label>
              <input type="text" name="academic_year" value="{{ old('academic_year') }}" placeholder="cth: 2024/2025" required>
            </div>
          </div>

          <button type="submit" class="btn-submit">Daftar Sekarang</button>
        </form>

        <div class="login-link">
          Sudah punya akun? <a href="{{ route('login') }}">Login di sini</a>
        </div>
      </div>
    </div>
  </div>

</body>
</html>
