<!DOCTYPE html>
<html lang="id">
<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Login — SITAMA</title>
  <link rel="preconnect" href="https://fonts.googleapis.com">
  <link rel="preconnect" href="https://fonts.gstatic.com" crossorigin>
  <link href="https://fonts.googleapis.com/css2?family=Archivo:wght@400;500;600;700;800&display=swap" rel="stylesheet">
  <style>
  :root{
    --primary:#0061FF;--primary-dark:#0048BD;
    --text:#1E1919;--text-secondary:#637282;--text-muted:#9EA9B2;
    --bg:#FFFFFF;--warm:#F7F5F2;--warm-2:#F0EDE8;
    --border:#D8D6D3;--border-subtle:#EDECEA;--blue-tint:#EAF1FF;
    --ease:cubic-bezier(.22,.61,.36,1);
  }
  *,*::before,*::after{box-sizing:border-box;margin:0;padding:0;}
  body{min-height:100vh;display:flex;align-items:center;justify-content:center;padding:32px 20px;
    font-family:'Archivo','Segoe UI',sans-serif;color:var(--text);
    background:radial-gradient(1200px 600px at 50% -10%,#EAF1FF,#F2F4FF 45%,#EEF1F8);-webkit-font-smoothing:antialiased;}
  a{text-decoration:none;}
  @keyframes rise{from{opacity:0;transform:translateY(16px);}to{opacity:1;transform:none;}}
  @keyframes bgfade{from{opacity:0;}to{opacity:1;}}
  .an{opacity:0;animation:rise .55s var(--ease) forwards;}

  .shell{position:relative;z-index:1;width:100%;max-width:960px;display:flex;border-radius:28px;
    background:var(--bg);box-shadow:0 30px 80px rgba(30,40,90,.18);}
  .shell::before{content:'';position:absolute;width:120px;height:120px;border-radius:50%;
    background:var(--primary);top:-34px;left:-34px;z-index:0;opacity:.9;}
  .shell::after{content:'';position:absolute;width:120px;height:120px;border-radius:50%;
    background:#E7ECFF;bottom:-34px;right:-34px;z-index:0;}

  .pane-left{position:relative;z-index:2;flex:1;background:var(--bg);border-radius:28px 0 0 28px;
    padding:54px 50px;display:flex;flex-direction:column;justify-content:center;}
  .brand{display:flex;align-items:center;gap:10px;justify-content:center;margin-bottom:22px;}
  .brand .logo{width:40px;height:40px;border-radius:50%;object-fit:contain;}
  .brand .bn{font-size:16px;font-weight:800;letter-spacing:1px;color:var(--text);}
  .f-title{font-size:30px;font-weight:800;letter-spacing:-0.01em;text-align:center;}
  .f-sub{font-size:13.5px;color:var(--text-secondary);text-align:center;margin-top:6px;margin-bottom:26px;}

  .alert{padding:11px 14px;border-radius:10px;font-size:13px;margin-bottom:16px;font-weight:500;}
  .alert.err{background:#FBEAE8;border:1px solid #F0C4BE;color:#C0392B;}
  .alert.ok{background:#E0F7EF;border:1px solid #A7E8CF;color:#07976A;}

  .field{margin-bottom:15px;}
  .iwrap{position:relative;}
  .iwrap>.lead-ic{position:absolute;left:16px;top:50%;transform:translateY(-50%);color:var(--text-muted);pointer-events:none;}
  .iwrap input{width:100%;height:52px;padding:0 16px 0 46px;border:1.5px solid transparent;border-radius:12px;
    font-size:14.5px;font-family:inherit;color:var(--text);background:var(--warm);outline:none;
    transition:border-color .18s var(--ease),box-shadow .18s var(--ease),background .18s;}
  .iwrap input::placeholder{color:var(--text-muted);}
  /* Hide browser's built-in password reveal/clear (Edge/IE) so it doesn't
     duplicate our custom eye button. */
  .iwrap input::-ms-reveal,.iwrap input::-ms-clear{display:none;}
  .iwrap input:focus{border-color:var(--primary);background:#fff;box-shadow:0 0 0 4px rgba(0,97,255,.12);}
  .eye{position:absolute;right:14px;top:50%;transform:translateY(-50%);background:none;border:none;cursor:pointer;color:var(--text-muted);padding:6px;display:flex;border-radius:8px;}
  .eye:hover{color:var(--primary);}
  .frow{display:flex;align-items:center;justify-content:space-between;margin:4px 2px 22px;}
  .remember{display:flex;align-items:center;gap:7px;font-size:12.5px;color:var(--text-secondary);cursor:pointer;user-select:none;}
  .remember input{width:16px;height:16px;accent-color:var(--primary);}
  .forgot{font-size:12.5px;color:var(--primary);font-weight:600;}
  .forgot:hover{text-decoration:underline;}

  .btn{display:block;margin:0 auto;min-width:170px;height:50px;padding:0 26px;background:var(--primary);color:#fff;border:none;border-radius:9999px;
    font-size:15px;font-weight:700;font-family:inherit;cursor:pointer;letter-spacing:-0.01em;
    box-shadow:0 12px 24px rgba(0,97,255,.3);transition:background .15s,transform .1s var(--ease),box-shadow .15s;}
  .btn:hover{background:var(--primary-dark);transform:translateY(-1px);box-shadow:0 16px 30px rgba(0,97,255,.36);}
  .btn:active{transform:scale(.98);}

  .divider{display:flex;align-items:center;gap:12px;margin:26px 0 16px;color:var(--text-muted);font-size:12.5px;font-weight:600;}
  .divider::before,.divider::after{content:'';flex:1;height:1px;background:var(--border-subtle);}
  .signup{text-align:center;font-size:13.5px;color:var(--text-secondary);}
  .signup a{color:var(--primary);font-weight:700;}
  .signup a:hover{text-decoration:underline;}

  .pane-right{position:relative;z-index:2;flex:1;border-radius:0 28px 28px 0;overflow:hidden;
    background:linear-gradient(150deg,#0061FF 0%,#2A78FF 55%,#0048BD 100%);
    display:flex;align-items:center;justify-content:center;padding:40px;min-height:540px;}
  .waves{position:absolute;inset:0;opacity:.5;}
  .glass{position:relative;z-index:2;width:330px;max-width:90%;border-radius:22px;
    background:rgba(255,255,255,.14);border:1px solid rgba(255,255,255,.28);
    backdrop-filter:blur(6px);-webkit-backdrop-filter:blur(6px);
    box-shadow:0 20px 50px rgba(0,0,0,.2);padding:26px 22px;}
  .glass img{display:block;width:100%;height:auto;}
  .badge{position:absolute;z-index:3;left:calc(50% - 200px);top:calc(50% + 30px);
    width:64px;height:64px;border-radius:18px;background:#fff;box-shadow:0 14px 30px rgba(0,0,0,.18);
    display:flex;align-items:center;justify-content:center;color:var(--primary);}
  .badge2{position:absolute;z-index:3;right:calc(50% - 196px);top:calc(50% - 150px);
    width:54px;height:54px;border-radius:16px;background:#fff;box-shadow:0 14px 30px rgba(0,0,0,.18);
    display:flex;align-items:center;justify-content:center;color:#0AC27D;}

  .bg{position:fixed;inset:0;z-index:0;overflow:hidden;pointer-events:none;opacity:0;animation:bgfade .9s ease .1s forwards;}
  .bg .orb{position:absolute;border-radius:50%;filter:blur(46px);opacity:.5;}
  .bg .o1{width:360px;height:360px;background:#9DB8FF;left:-90px;top:-70px;}
  .bg .o2{width:320px;height:320px;background:#CBD8FF;right:-80px;bottom:-60px;}
  .bg .o3{width:200px;height:200px;background:#BFE0FF;right:16%;top:6%;}
  .bg .o4{width:160px;height:160px;background:#D7E6FF;left:10%;bottom:8%;}
  .bg .ring{position:absolute;border-radius:50%;border:2px solid rgba(0,97,255,.12);}
  .bg .r1{width:130px;height:130px;left:8%;bottom:18%;}
  .bg .r2{width:64px;height:64px;right:11%;top:22%;}
  .bg .r3{width:38px;height:38px;left:22%;top:16%;border-color:rgba(0,97,255,.18);}
  .bg .dots{position:absolute;inset:0;background-image:radial-gradient(rgba(0,97,255,.12) 1.4px,transparent 1.4px);background-size:26px 26px;opacity:.6;
    -webkit-mask-image:radial-gradient(circle at 50% 46%,transparent 340px,#000 600px);mask-image:radial-gradient(circle at 50% 46%,transparent 340px,#000 600px);}

  .topmini{position:fixed;top:22px;left:0;right:0;z-index:1;display:flex;align-items:center;justify-content:space-between;padding:0 32px;pointer-events:none;}
  .topmini .tm-brand{font-weight:800;letter-spacing:1.5px;color:var(--text);font-size:15px;pointer-events:auto;}
  .topmini .tm-help{font-size:13px;font-weight:600;color:var(--text-secondary);pointer-events:auto;}
  .topmini .tm-help:hover{color:var(--primary);}
  .footmini{position:fixed;bottom:18px;left:0;right:0;z-index:1;text-align:center;font-size:12px;color:var(--text-secondary);}
  .footmini a{color:var(--text-secondary);font-weight:600;}
  .footmini a:hover{color:var(--primary);}

  @media (max-width:820px){
    .pane-right{display:none;}
    .pane-left{border-radius:28px;padding:44px 30px;}
    .topmini .tm-help{display:none;}
  }
  @media (prefers-reduced-motion: reduce){
    .an{animation:none!important;opacity:1!important;}
    .bg{animation:none!important;opacity:1!important;}
  }
  </style>
</head>
<body>

  <div class="bg">
    <span class="orb o1"></span><span class="orb o2"></span><span class="orb o3"></span><span class="orb o4"></span>
    <span class="ring r1"></span><span class="ring r2"></span><span class="ring r3"></span>
    <span class="dots"></span>
  </div>
  <div class="topmini">
    <div class="tm-brand an" style="animation-delay:.08s">SITAMA</div>
    <a class="tm-help an" style="animation-delay:.12s" href="{{ route('bantuan') }}">Butuh bantuan?</a>
  </div>

  <div class="shell">
    {{-- LEFT: FORM --}}
    <div class="pane-left">
      <div class="brand an" style="animation-delay:.05s">
        <img class="logo" src="{{ asset('images/logo.png') }}" alt="SITAMA logo">
        <span class="bn">SITAMA</span>
      </div>
      <div class="f-title an" style="animation-delay:.1s">LOGIN</div>
      <div class="f-sub an" style="animation-delay:.14s">Masuk untuk melanjutkan ke sistem magang &amp; bimbingan.</div>

      @if(session('success'))
        <div class="alert ok">{{ session('success') }}</div>
      @endif
      @if($errors->any())
        <div class="alert err">{{ $errors->first() }}</div>
      @endif

      <form method="POST" action="{{ route('login') }}">
        @csrf
        <div class="field an" style="animation-delay:.2s">
          <div class="iwrap">
            <span class="lead-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M20 21v-2a4 4 0 00-4-4H8a4 4 0 00-4 4v2"/><circle cx="12" cy="7" r="4"/></svg></span>
            <input type="text" name="username" value="{{ old('username') }}" placeholder="NIM / NIP" autocomplete="username" required>
          </div>
        </div>
        <div class="field an" style="animation-delay:.26s">
          <div class="iwrap">
            <span class="lead-ic"><svg width="17" height="17" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><rect x="3" y="11" width="18" height="11" rx="2"/><path d="M7 11V7a5 5 0 0110 0v4"/></svg></span>
            <input id="pw" type="password" name="password" placeholder="Kata sandi" autocomplete="current-password" required>
            <button type="button" class="eye" onclick="togPw()" title="Tampilkan / sembunyikan">
              <svg id="es" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path d="M1 12s4-8 11-8 11 8 11 8-4 8-11 8-11-8-11-8z"/><circle cx="12" cy="12" r="3"/></svg>
              <svg id="eh" width="19" height="19" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24" style="display:none;"><path d="M17.94 17.94A10.07 10.07 0 0112 20c-7 0-11-8-11-8a18.45 18.45 0 015.06-5.94"/><path d="M9.9 4.24A9.12 9.12 0 0112 4c7 0 11 8 11 8a18.5 18.5 0 01-2.16 3.19"/><line x1="1" y1="1" x2="23" y2="23"/></svg>
            </button>
          </div>
        </div>

        <div class="frow an" style="animation-delay:.3s">
          <label class="remember"><input type="checkbox" name="remember"> Ingat saya</label>
          <a class="forgot" href="#">Lupa kata sandi?</a>
        </div>

        <button type="submit" class="btn an" style="animation-delay:.36s">Masuk Sekarang</button>
      </form>

      <div class="divider an" style="animation-delay:.42s">SITAMA Politeknik</div>
      <div class="signup an" style="animation-delay:.46s">Belum punya akun? <a href="{{ route('register') }}">Daftar di sini</a></div>
    </div>

    {{-- RIGHT: VISUAL --}}
    <div class="pane-right">
      <svg class="waves" viewBox="0 0 400 540" preserveAspectRatio="xMidYMid slice" fill="none" stroke="rgba(255,255,255,.18)" stroke-width="2">
        <path d="M-40 120 C80 60 140 200 260 150 S440 80 470 170"/>
        <path d="M-40 220 C80 160 140 300 260 250 S440 180 470 270"/>
        <path d="M-40 360 C80 300 140 440 260 390 S440 320 470 410"/>
      </svg>

      <div class="badge an" style="animation-delay:.5s"><svg width="28" height="28" fill="none" stroke="currentColor" stroke-width="2.2" viewBox="0 0 24 24"><path d="M22 10v6M2 10l10-5 10 5-10 5z"/><path d="M6 12v5c3 3 9 3 12 0v-5"/></svg></div>
      <div class="badge2 an" style="animation-delay:.58s"><svg width="24" height="24" fill="none" stroke="currentColor" stroke-width="2.4" viewBox="0 0 24 24"><polyline points="20 6 9 17 4 12"/></svg></div>

      <div class="glass an" style="animation-delay:.34s">
        <img src="{{ asset('images/login_vektor.png') }}" alt="Ilustrasi login">
      </div>
    </div>
  </div>

  <div class="footmini an" style="animation-delay:.55s">© {{ date('Y') }} SITAMA · Sistem Informasi Magang &nbsp;·&nbsp; <a href="{{ route('about') }}">Tentang</a> &nbsp;·&nbsp; <a href="{{ route('bantuan') }}">Bantuan</a> &nbsp;·&nbsp; <a href="{{ route('contact') }}">Kontak</a></div>

  <script>
    function togPw(){
      var i=document.getElementById('pw'),s=document.getElementById('es'),h=document.getElementById('eh');
      if(i.type==='password'){i.type='text';s.style.display='none';h.style.display='block';}
      else{i.type='password';s.style.display='block';h.style.display='none';}
    }
  </script>
</body>
</html>
