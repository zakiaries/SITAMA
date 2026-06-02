<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SITAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link href="https://fonts.googleapis.com/css2?family=Plus+Jakarta+Sans:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }

    body {
      min-height: 100vh;
      display: flex;
      font-family: 'Plus Jakarta Sans', 'Segoe UI', sans-serif;
      background: #f0ece3;
    }

    /* ── LEFT PANEL ── */
    .left-panel {
      width: 45%;
      min-height: 100vh;
      background: #2d3e6e;
      display: flex;
      flex-direction: column;
      justify-content: center;
      align-items: center;
      padding: 48px 40px;
      position: relative;
      overflow: hidden;
    }
    .left-panel::before {
      content: '';
      position: absolute;
      inset: 0;
      background-image: url('{{ asset("images/pattern.png") }}');
      background-size: 220px;
      background-repeat: repeat;
      opacity: 0.08;
    }
    .left-content {
      position: relative;
      z-index: 1;
      text-align: center;
    }
    .logo-wrap {
      margin: 0 auto 24px;
      width: 110px;
      height: 110px;
      filter: drop-shadow(0 4px 16px rgba(0,0,0,0.25));
    }
    .logo-wrap img {
      width: 110px;
      height: 110px;
      object-fit: contain;
    }
    .brand-name {
      font-size: 36px;
      font-weight: 800;
      color: #fff;
      letter-spacing: 3px;
      margin-bottom: 12px;
    }
    .brand-tagline {
      font-size: 18px;
      font-weight: 600;
      color: rgba(255,255,255,0.9);
      margin-bottom: 10px;
    }
    .brand-sub {
      font-size: 13px;
      color: rgba(255,255,255,0.6);
      line-height: 1.6;
      max-width: 280px;
      margin: 0 auto;
    }
    .left-divider {
      width: 40px;
      height: 3px;
      background: rgba(255,255,255,0.3);
      border-radius: 2px;
      margin: 20px auto;
    }

    /* ── RIGHT PANEL ── */
    .right-panel {
      flex: 1;
      display: flex;
      flex-direction: column;
      align-items: center;
      justify-content: center;
      background: #f0ece3;
      padding: 40px 24px;
    }
    .login-card {
      width: 100%;
      max-width: 420px;
      background: #fff;
      border-radius: 24px;
      overflow: hidden;
      box-shadow: 0 8px 40px rgba(45,62,110,0.10);
    }

    /* Illustration block */
    .illus-block {
      background: #e8e4dc;
      display: flex;
      align-items: center;
      justify-content: center;
      padding: 32px 24px 24px;
      min-height: 220px;
    }
    .illus-block img {
      max-width: 240px;
      max-height: 180px;
      object-fit: contain;
    }

    /* Form block */
    .form-block {
      padding: 28px 32px 32px;
    }
    .form-title {
      font-size: 22px;
      font-weight: 800;
      color: #1e2a4a;
      margin-bottom: 6px;
    }
    .form-subtitle {
      font-size: 13px;
      color: #9ca3af;
      margin-bottom: 24px;
    }

    .alert-error {
      background: #fef2f2;
      border: 1px solid #fca5a5;
      color: #dc2626;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 18px;
    }
    .alert-success {
      background: #f0fdf4;
      border: 1px solid #86efac;
      color: #16a34a;
      padding: 10px 14px;
      border-radius: 10px;
      font-size: 13px;
      margin-bottom: 18px;
    }

    .field {
      margin-bottom: 16px;
    }
    .field label {
      display: none;
    }
    .input-wrap {
      position: relative;
    }
    .input-wrap > svg {
      position: absolute;
      left: 14px;
      top: 50%;
      transform: translateY(-50%);
      color: #9ca3af;
      pointer-events: none;
    }
    .input-wrap input {
      width: 100%;
      padding: 13px 14px 13px 42px;
      border: 1.5px solid #e5e7eb;
      border-radius: 12px;
      font-size: 14px;
      font-family: inherit;
      color: #1e2a4a;
      background: #fafafa;
      outline: none;
      transition: border-color .2s, box-shadow .2s;
    }
    .input-wrap input::placeholder { color: #b0b7c3; }
    .input-wrap input:focus {
      border-color: #2d3e6e;
      background: #fff;
      box-shadow: 0 0 0 3px rgba(45,62,110,0.08);
    }
    .eye-btn {
      position: absolute;
      right: 14px;
      top: 50%;
      transform: translateY(-50%);
      background: none;
      border: none;
      cursor: pointer;
      color: #9ca3af;
      padding: 4px;
      display: flex;
      align-items: center;
    }
    .eye-btn:hover { color: #2d3e6e; }

    .forgot {
      text-align: right;
      margin-bottom: 22px;
    }
    .forgot a {
      font-size: 12px;
      color: #2d3e6e;
      font-weight: 600;
      text-decoration: none;
    }
    .forgot a:hover { text-decoration: underline; }

    .btn-login {
      width: 100%;
      padding: 14px;
      background: #2d3e6e;
      color: #fff;
      border: none;
      border-radius: 12px;
      font-size: 15px;
      font-weight: 700;
      font-family: inherit;
      cursor: pointer;
      transition: background .2s, transform .1s;
      letter-spacing: 0.3px;
    }
    .btn-login:hover { background: #3a52a0; }
    .btn-login:active { transform: scale(0.99); }

    /* ── RESPONSIVE ── */
    @media (max-width: 768px) {
      body { flex-direction: column; }
      .left-panel {
        width: 100%;
        min-height: auto;
        padding: 32px 24px;
      }
      .logo-wrap { width: 72px; height: 72px; margin-bottom: 16px; }
      .logo-wrap img { width: 44px; height: 44px; }
      .brand-name { font-size: 26px; }
      .brand-tagline { font-size: 15px; }
      .right-panel { padding: 28px 16px 40px; }
      .form-block { padding: 24px 24px 28px; }
    }
  </style>
</head>
<body>

  {{-- LEFT: Branding --}}
  <div class="left-panel">
    <div class="left-content">
      <div class="logo-wrap">
        <img src="{{ asset('images/logo.png') }}" alt="SITAMA Logo">
      </div>
      <div class="brand-name">SITAMA</div>
      <div class="left-divider"></div>
      <div class="brand-tagline">Selamat Datang di SITAMA</div>
      <div class="brand-sub">Bantu kegiatan magang dan bimbingan jadi lebih mudah!</div>
    </div>
  </div>

  {{-- RIGHT: Login Form --}}
  <div class="right-panel">
    <div class="login-card">

      {{-- Illustration --}}
      <div class="illus-block">
        <img src="{{ asset('images/login_vektor.png') }}" alt="Login Illustration">
      </div>

      {{-- Form --}}
      <div class="form-block">
        <div class="form-title">Login</div>
        <div class="form-subtitle">Masuk untuk melanjutkan</div>

        @if(session('success'))
          <div class="alert-success">{{ session('success') }}</div>
        @endif

        @if($errors->any())
          <div class="alert-error">{{ $errors->first() }}</div>
        @endif

        <form method="POST" action="{{ route('login') }}">
          @csrf

          {{-- NIM / NIP --}}
          <div class="field">
            <label for="username">NIM / NIP</label>
            <div class="input-wrap">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/>
              </svg>
              <input id="username" type="text" name="username"
                value="{{ old('username') }}"
                placeholder="NIM / NIP"
                autocomplete="username">
            </div>
          </div>

          {{-- Kata Sandi --}}
          <div class="field">
            <label for="password">Kata Sandi</label>
            <div class="input-wrap">
              <svg width="16" height="16" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                <rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/>
              </svg>
              <input id="password" type="password" name="password"
                placeholder="Kata sandi"
                autocomplete="current-password">
              <button type="button" class="eye-btn" onclick="togglePassword()" title="Tampilkan/sembunyikan">
                <svg id="eye-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24">
                  <path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/>
                  <path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/>
                  <line x1="1" y1="1" x2="23" y2="23"/>
                </svg>
                <svg id="eye-off-icon" width="18" height="18" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;">
                  <path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/>
                </svg>
              </button>
            </div>
          </div>

          <div class="forgot">
            <a href="#">Lupa Kata Sandi ?</a>
          </div>

          <button type="submit" class="btn-login">Login</button>
        </form>
      </div>

    </div>
  </div>

  <script>
    function togglePassword() {
      var input    = document.getElementById('password');
      var eyeOn    = document.getElementById('eye-off-icon');
      var eyeOff   = document.getElementById('eye-icon');
      if (input.type === 'password') {
        input.type = 'text';
        eyeOn.style.display  = 'block';
        eyeOff.style.display = 'none';
      } else {
        input.type = 'password';
        eyeOn.style.display  = 'none';
        eyeOff.style.display = 'block';
      }
    }
  </script>
</body>
</html>
